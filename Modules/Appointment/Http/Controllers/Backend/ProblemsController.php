<?php

namespace Modules\Appointment\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Constant\Models\Constant;
use Yajra\DataTables\DataTables;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Carbon\Carbon;
use Illuminate\Support\Str;
class ProblemsController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = 'appointment.problem';
        // module name
        $this->module_name = 'problems';

        // module icon
        $this->module_icon = 'fa-solid fa-clipboard-list';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filter = [];

        $columns = CustomFieldGroup::columnJsonValues(new Constant());
        $customefield = CustomField::exportCustomFields(new Constant());
        $problems = Constant::where('type', 'encounter_problem')->get();
        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('appointment.lbl_name'),
            ],
            [
                'value' => 'updated_at',
                'text' => __('appointment.lbl_update_at'),
            ]
        ];
        $export_url = route('backend.encounter-template.export');

        return view('appointment::backend.problems.index_datatable', compact('filter', 'columns', 'customefield', 'export_import', 'export_columns', 'export_url', 'problems'));
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Constant::whereIn('id', $ids)->delete();
                $message = __('appointment.problem_delete');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $user = auth()->user();
        $query = Constant::query();
        $query = $query->where('type', 'encounter_problem');

        // View problem list: Admin (Full), Clinic Admin (Read Only), Doctor (Own Patients), Receptionist (No), Pharmacist (Related Only), Lab Technologist (Related Only)
        if ($user && $user->hasRole('receptionist')) {
            abort(403, 'You are not allowed to view problem lists.');
        }
        
        if ($user && $user->hasRole('doctor')) {
            $query = $query->where('created_by', auth()->user()->id);
        }
        
        // Pharmacist and Lab Technologist - Related Only (filter by encounters they are related to)
        if ($user && ($user->hasRole('pharmacist') || $user->hasRole('lab_technologist'))) {
            // Filter to show only problems from encounters they are related to
            // This would require joining with encounter tables - for now, show all as read-only
            // Implementation can be refined based on actual relationship structure
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }

            if (isset($filter['template_name'])) {
                $query->where('id', $filter['template_name']);
            }
        }

        $datatable = $datatable->eloquent($query)
            ->addColumn('check', function ($data) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('appointment::backend.problems.action_column', compact('data'));
            })
            ->editColumn('name', function ($data) {
                return ucwords(str_replace('_', ' ', $data->name));
            })
            ->editColumn('updated_at', function ($data) {
                $module_name = $this->module_name;

                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })
            ->orderColumns(['id'], '-:column $1');

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatable, Constant::CUSTOM_FIELD_MODEL, null);

        return $datatable->rawColumns(array_merge(['action', 'name', 'check'], $customFieldColumns))
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('appointment::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Edit problem list: Admin (Full), Clinic Admin (No), Doctor (Own Patients), Receptionist (No), Pharmacist (No), Lab Technologist (No)
        if ($user && ($user->hasRole('vendor') || $user->hasRole('receptionist') || $user->hasRole('pharmacist') || $user->hasRole('lab_technologist'))) {
            abort(403, 'You are not allowed to edit problem lists.');
        }
        
        $data['name'] = $request->name;
        $data['type'] = 'encounter_problem';
        $data['value'] = strtolower(Str::slug($request->name, '-'));
        $data['created_by'] = auth()->id();
        $query = Constant::create($data);
        if ($request->custom_fields_data) {
            $query->updateCustomFieldData(json_decode($request->custom_fields_data));
        }
        $message = __('messages.create_form', ['form' => __('appointment.observation')]);

        if ($request->is('api/*')) {
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('appointment::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Constant::findOrFail($id);

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        // Edit problem list: Admin (Full), Clinic Admin (No), Doctor (Own Patients), Receptionist (No), Pharmacist (No), Lab Technologist (No)
        if ($user && ($user->hasRole('vendor') || $user->hasRole('receptionist') || $user->hasRole('pharmacist') || $user->hasRole('lab_technologist'))) {
            abort(403, 'You are not allowed to edit problem lists.');
        }
        
        // Doctor limited to own problems
        if ($user && $user->hasRole('doctor')) {
            $query = Constant::findOrFail($id);
            if ($query->created_by != $user->id) {
                abort(403, 'You can only edit your own problem lists.');
            }
        }
        
        $query = Constant::findOrFail($id);
        $data['name'] = $request->name;
        $data['type'] = 'encounter_problem';
        $data['value'] = strtolower(Str::slug($request->name, '_'));
        $query->update($data);

        $message = __('messages.update_form', ['form' => __('appointment.lbl_problem')]);

        if ($request->is('api/*')) {
            return response()->json(['message' => $message, 'data' => $data, 'status' => true], 200);
        } else {
            return response()->json(['message' => $message, 'status' => true], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        // Edit problem list: Admin (Full), Clinic Admin (No), Doctor (Own Patients), Receptionist (No), Pharmacist (No), Lab Technologist (No)
        if ($user && ($user->hasRole('vendor') || $user->hasRole('receptionist') || $user->hasRole('pharmacist') || $user->hasRole('lab_technologist'))) {
            abort(403, 'You are not allowed to delete problem lists.');
        }
        
        // Doctor limited to own problems
        if ($user && $user->hasRole('doctor')) {
            $data = Constant::findOrFail($id);
            if ($data->created_by != $user->id) {
                abort(403, 'You can only delete your own problem lists.');
            }
        }
        
        $data = Constant::findOrFail($id);

        $data->delete();

        $message = __('appointment.problem_delete');

        return response()->json(['message' => $message, 'status' => true], 200);
    }
}
