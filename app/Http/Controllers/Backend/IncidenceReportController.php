<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use App\Models\Incidence;
use App\Models\Setting;
use Modules\NotificationTemplate\Models\NotificationTemplate;
use App\Mail\ReplyIncidenceMail;
use Illuminate\Support\Facades\Mail;
use Modules\Appointment\Trait\AppointmentTrait;
use Modules\Clinic\Models\Receptionist;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Laboratory\Models\Lab;
use Spatie\Activitylog\Models\Activity;

class IncidenceReportController extends Controller
{
    use AppointmentTrait;
    protected string $exportClass = '\App\Exports\CustomerExport';

    public function __construct()
    {
        $this->module_title = 'frontend.incidence';
        $this->module_name = 'incidence';
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $filter = ['status' => $request->status];
        $module_action = '';
        $columns = CustomFieldGroup::columnJsonValues(new Appointment());
        $customefield = CustomField::exportCustomFields(new Appointment());
        $export_import = true;
        $export_columns = [['value' => 'name', 'text' => ' Name']];
        $export_url = route('backend.incidence.export');
        $data = Incidence::first();

        return view('backend.incidence.index_datatable', compact(
            'module_action', 'filter', 'columns', 'customefield',
            'export_import', 'export_columns', 'export_url', 'data'
        ));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $user = auth()->user();
        $query = Incidence::query();

        $filter = $request->filter;
        if (isset($filter) && isset($filter['column_status'])) {
            $query->where('incident_type', $filter['column_status']);
        }

        // Receptionist: only see incidents from their clinic
        if ($user->hasRole('receptionist')) {
            $receptionist = Receptionist::where('receptionist_id', $user->id)->first();
            $clinicDoctorIds = [];
            if ($receptionist) {
                $clinicDoctorIds = DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }
            // Show incidents created by anyone in the same clinic (doctors, receptionists, etc.)
            $clinicUserIds = User::where(function ($q) use ($receptionist, $clinicDoctorIds) {
                $q->whereIn('id', $clinicDoctorIds)
                  ->orWhereHas('receptionist', function ($r) use ($receptionist) {
                      $r->where('clinic_id', $receptionist->clinic_id);
                  });
            })->pluck('id')->toArray();
            $query->whereIn('created_by', $clinicUserIds)
                  ->orWhere('clinic_id', $receptionist?->clinic_id);
        }

        // Lab technician: only see incidents from their clinic
        if ($user->hasRole('lab_technician')) {
            $lab = Lab::where('user_id', $user->id)->first();
            if ($lab && $lab->clinic_id) {
                $query->where('clinic_id', $lab->clinic_id);
            }
        }

        $appointment_status = [__('messages.lbl_open') => '1', __('messages.lbl_closed') => '2', __('messages.lbl_reject') => '3'];

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->editColumn('image', function ($data) {
                return '<img src="' . $data->file_url . '" class="avatar avatar-50 rounded-pill me-3" style="cursor:pointer;" onclick="setPreview(\'' . addslashes($data->file_url) . '\')">';
            })
            ->addColumn('name', function ($data) {
                $createdBy = User::selectRaw('CONCAT(first_name," ",last_name) as name')->where('id', $data->user_id)->pluck('name')->first();
                return $createdBy;
            })
            ->filterColumn('name', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', '%' . $keyword . '%')
                            ->orWhere('last_name', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderColumn('name', function ($query, $order) {
                $query->leftJoin('users', 'incidences.created_by', '=', 'users.id')
                    ->orderByRaw('CONCAT(users.first_name, " ", users.last_name) ' . $order);
            })
            ->addColumn('action', function ($data) {
                return view('backend.incidence.datatable.action_column', compact('data'));
            })
            ->editColumn('status', function ($data) use ($appointment_status) {
                if ($data->incident_type == 1) {
                    // Receptionist and lab_technician cannot change status — show read-only badge
                    if (auth()->user()->hasRole('receptionist') || auth()->user()->hasRole('lab_technician')) {
                        return '<span class="badge bg-warning">' . __('messages.lbl_open') . '</span>';
                    }
                    return view('backend.incidence.datatable.select_column', compact('data', 'appointment_status'));
                } elseif ($data->incident_type == 2) {
                    return '<span class="badge bg-success">' . __('messages.lbl_closed') . '</span>';
                } elseif ($data->incident_type == 3) {
                    return '<span class="badge bg-danger">' . __('messages.lbl_reject') . '</span>';
                } else {
                    return '<span class="badge bg-secondary">' . __('messages.unknown') . '</span>';
                }
            })
            ->editColumn('incident_date', function ($data) {
                $setting = Setting::where('name', 'date_formate')->first();
                $dateformate = $setting ? $setting->val : 'Y-m-d';
                return $data->incident_date ? Carbon::parse($data->incident_date)->format($dateformate) : '';
            })
            ->editColumn('updated_at', function ($data) {
                $diff = Carbon::now()->diffInHours($data->updated_at);
                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->editColumn('description', function ($data) {
                $maxLength = 50;
                $description = $data->description ?? '';
                $fullDescription = is_array($description) ? e(json_encode($description)) : e($description);
                $shortDescription = Str::limit($fullDescription, $maxLength);
                return '<span title="' . $fullDescription . '">' . $shortDescription . '</span>';
            })
            ->rawColumns(['status', 'check', 'action', 'image', 'description'])
            ->orderColumns(['id'], '-:column $1');
        return $datatable->toJson();
    }

    public function updateStatus($id, Request $request)
    {
        // Receptionist and lab_technician cannot change status
        if (auth()->user()->hasRole('receptionist') || auth()->user()->hasRole('lab_technician')) {
            return response()->json(['message' => 'You are not authorized to change status.', 'status' => false], 403);
        }

        $status = $request->value;
        $data = Incidence::find($id)->update(['incident_type' => $status]);
        if ($data) {
            $message = __('appointment.status_update');
            return response()->json(['message' => $message, 'status' => true]);
        } else {
            return response()->json(['message' => 'Something went wrong', 'status' => false]);
        }
    }

    public function bulk_action(Request $request)
    {
        // Receptionist and lab_technician cannot bulk action
        if (auth()->user()->hasRole('receptionist') || auth()->user()->hasRole('lab_technician')) {
            return response()->json(['message' => 'You are not authorized.', 'status' => false], 403);
        }

        $ids = explode(',', $request->rowIds);
        $status = $request->status;
        $message = __('messages.bulk_update');
        $data = Incidence::whereIn('id', $ids)->update(['incident_type' => $status]);
        if ($data) {
            $message = __('appointment.status_update');
            return response()->json(['status' => true, 'message' => $message]);
        } else {
            return response()->json(['message' => 'Something went wrong', 'status' => false]);
        }
    }

    public function reply(Request $request)
    {
        // Receptionist and lab_technician cannot reply
        if (auth()->user()->hasRole('receptionist') || auth()->user()->hasRole('lab_technician')) {
            flash('<i class="fas fa-times"></i> You are not authorized.')->error()->important();
            return redirect()->route('backend.incidence.index');
        }

        $id = $request->incidence_id;
        $Reply = $request->Reply;
        $message = __('messages.bulk_update');
        $data = Incidence::where('id', $id)->update(['reply' => $Reply]);
        $incidence_data = Incidence::where('id', $id)->first();
        self::sendNotificationOnIncidence($incidence_data);

        if ($data) {
            $message = __('appointment.reply_status');
            flash('<i class="fas fa-check"></i> ' . $message . '')->success()->important();
            return redirect()->route('backend.incidence.index')->with('success', $message);
        } else {
            $message = __('appointment.reply_fail');
            flash('<i class="fas fa-check"></i> Something went wrong')->error()->important();
            return redirect()->route('backend.incidence.index')->with('error', $message);
        }
    }

    public function sendNotificationOnIncidence($data)
    {
        $createdBy = User::selectRaw('CONCAT(first_name," ",last_name) as name')->where('id', $data->created_by)->pluck('name')->first();
        $notification_data = [
            'id' => $data->id,
            'user_id' => $data->created_by,
            'phone' => $data->phone,
            'email' => $data->email,
            'reply' => $data->reply,
            'user_name' => $createdBy
        ];
        $template = NotificationTemplate::where('type', 'incidence_reply')->with('defaultNotificationTemplateMap')->firstOrFail();
        $mail_template = $template->defaultNotificationTemplateMap->mail_template_detail ?? '<p>Incidence report reply from Admin.</p><p>Your reply: [[ reply ]]</p>';
        $bodyData = $mail_template;
        foreach ($notification_data as $key => $value) {
            $bodyData = str_replace('[[ ' . $key . ' ]]', $value, $bodyData);
        }
        try {
            Mail::to($data->email)->send(new ReplyIncidenceMail($bodyData));
        } catch (\Exception $e) {
            \Log::error('Mail not sent: ' . $e->getMessage());
        }
        $this->sendNotificationOnIncidenceCreate('incidence_reply', $notification_data);
    }

    public function getReply($id)
    {
        $incidence = Incidence::find($id);
        if (!$incidence) {
            return response()->json(['reply' => null]);
        }
        return response()->json(['reply' => $incidence->reply]);
    }

    public function create()
    {
        return view('backend.incidence.create');
    }

    /**
     * Store a newly created incidence (for receptionist).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'incident_date' => 'nullable|date',
        ]);

        $clinicId = null;
        if ($user->hasRole('receptionist')) {
            $receptionist = Receptionist::where('receptionist_id', $user->id)->first();
            $clinicId = $receptionist?->clinic_id;
        } elseif ($user->hasRole('lab_technician')) {
            $lab = Lab::where('user_id', $user->id)->first();
            $clinicId = $lab?->clinic_id;
        }

        $incidence = Incidence::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'phone' => $request->phone,
            'email' => $request->email,
            'incident_date' => $request->incident_date ?? now(),
            'incident_type' => 1,
            'status' => 1,
            'created_by' => $user->id,
            'clinic_id' => $clinicId,
        ]);

        activity()
            ->performedOn($incidence)
            ->causedBy($user)
            ->withProperties(['attributes' => $incidence->toArray()])
            ->event('created')
            ->log('incidence_created');

        return redirect()->route('backend.incidence.index')
            ->with('success', 'Incidence report created successfully.');
    }
}
