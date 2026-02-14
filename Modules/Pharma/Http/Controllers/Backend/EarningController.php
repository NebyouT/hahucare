<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Earning\Models\Earning;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CustomField\Models\CustomField;
use Modules\CustomField\Models\CustomFieldGroup;
use Yajra\DataTables\DataTables;
use App\Models\User;
use Currency;
use Modules\Commission\Models\CommissionEarning;
use Modules\Appointment\Models\Appointment;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Appointment\Models\PatientEncounter;


class EarningController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = 'earning.lbl_pharma_earnings';
        // module name
        $this->module_name = 'earning';

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
    public function index(Request $request)
    {

        $filter = [
            'status' => $request->status,
        ];

        $module_action = 'List';

        return view('pharma::earnings.index_datatable', compact('module_action', 'filter',));

    }

    public function index_data(DataTables $datatable)
    {
        $module_name = $this->module_name;
        $query = User::select('users.*')
                ->with('commission_earning')
                ->with('commissionData')
                ->with('doctor')
                ->with('doctorclinic')
                ->whereHas('commission_earning', function ($q) {
                    $q->where('commission_status', 'unpaid')
                    ->where('user_type', 'pharma')
                    ->where('commissionable_type', 'Modules\Appointment\Models\PatientEncounter');
                });

        return $datatable->eloquent($query)
            ->addColumn('action', function ($data) use ($module_name) {
                $commissionData = $data->commission_earning()
                    ->where('commission_status', 'unpaid')
                    ->where('user_type', 'pharma')
                    ->where('commissionable_type', PatientEncounter::class)
                    ->whereHas('getPatientEncounter', function ($query) {
                        $query->where('status', 'checkout');
                    });

                $commissionAmount = $commissionData->sum('commission_amount');

                $encounter = $commissionData->first()?->getPatientEncounter;

                $doctorId = $encounter?->doctor_id; // Null-safe, to prevent errors if no encounter

                $appointmentIds = PatientEncounter::whereIn(
                        'id',
                        $commissionData->pluck('commissionable_id')
                    )
                    ->whereNotNull('appointment_id')
                    ->pluck('appointment_id')
                    ->unique();

                $totalAppointment = $appointmentIds->count();

                // Store needed data for reuse in other columns
                $data['total_pay'] = $commissionAmount;
                $data['commission'] = $commissionData->get();
                $data['total_appointment'] = $totalAppointment;
                $data['doctor_id'] = $doctorId;

                return view('pharma::earnings.action_column', compact('module_name', 'data'));
            })



            ->editColumn('user_id', function ($data) {
                return view('pharma::earnings.user_id', compact('data'));
            })
            ->filterColumn('user_id', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('first_name', 'like', '%'.$keyword.'%')->orWhere('last_name', 'like', '%'.$keyword.'%')->orWhere('email', 'like', '%'.$keyword.'%');
                }
            })
            ->orderColumn('user_id', function ($query, $order) {
                $query->orderBy('first_name', $order)
                      ->orderBy('last_name', $order);
            }, 1)

           ->editColumn('total_prescription', function ($data) {
                $userId = $data->id;

                // Get related commission_earnings for unpaid pharma commissions
                $commissionableIds = CommissionEarning::where('user_type', 'pharma')
                    ->where('commission_status', 'unpaid')
                    ->where('commissionable_type', 'Modules\Appointment\Models\PatientEncounter')
                    ->where('employee_id', $userId)
                    ->pluck('commissionable_id');

                if ($commissionableIds->isEmpty()) {
                    return "<b><span class='text-muted'>0</span></b>";
                }

                // Count matching PatientEncounter records with both statuses = 1
                $totalPrescription = PatientEncounter::whereIn('id', $commissionableIds)
                    ->where('prescription_status', 1)
                    ->where('prescription_payment_status', 1)
                    ->count();
                $count = PatientEncounter::whereIn('id', $commissionableIds)
                    ->where('prescription_status', 1)
                    ->where('prescription_payment_status', 1)
                    ->count();


                if ($totalPrescription > 0) {
                     $countHtml = "<b><span class='" . ($count > 0 ? 'text-primary' : 'text-muted') . "'>$count</span></b>";

                    // Add a special filter param: prescription_filter_user
                    $url = route('backend.prescription.index', [
                        'filter[pharma_prescription_user]' => $userId,
                        'filter[special_match]' => 1 // flag to identify the special condition
                    ]);

                    return "<a href='$url' class='text-decoration-none'>$countHtml</a>";
                }

                return "<b><span class='text-muted'>0</span></b>";
            })


           ->editColumn('total_service_amount', function ($data) {

                $totalEarning = 0;
                foreach($data['commission'] as $commission){
                    $commission_data = CommissionEarning::where('commissionable_id', $commission->commissionable_id)->where('commissionable_type', 'Modules\Appointment\Models\PatientEncounter')->where('commission_status', 'unpaid')->get();
                    foreach($commission_data as $comm){
                        $totalEarning += $comm->commission_amount;
                    }
                }

                return Currency::format($totalEarning);
            })

            ->editColumn('total_admin_earning', function ($data) {

                $totalAdminEarning = 0;
                foreach($data['commission'] as $commission){
                    $commission_data = CommissionEarning::where('commissionable_id', $commission->commissionable_id)->where('user_type', 'admin')->where('commissionable_type', 'Modules\Appointment\Models\PatientEncounter')->where('commission_status', 'unpaid')->orderby('created_at', 'desc')->first();
                    $totalAdminEarning += $commission_data->commission_amount;
                }


                return Currency::format($totalAdminEarning);

            })


           ->editColumn('total_commission_earn', function ($data) {
                return "<b><span
                    data-id='".$data->id."'
                    data-url='".route('backend.earning.view.commission.detail', ['id' => $data->id])."'
                    class='btn text-primary p-0 fs-5 view-commission-details'
                    data-bs-toggle='tooltip'
                    title='View'>
                    <i class='ph ph-eye align-middle'></i>
                </span></b>";

            })

            ->editColumn('total_pay', function ($data) {
                $totalPharmaCommission = $data->commission_earning()
                    ->where('commission_status', 'unpaid')
                    ->where('user_type', 'pharma')
                    ->where('commissionable_type', PatientEncounter::class)
                    ->sum('commission_amount');

                return Currency::format($totalPharmaCommission);
            })




            ->orderColumn('total_service_amount', function ($query, $order) {
                $query->orderBy(new Expression('(SELECT SUM(service_price) FROM booking_services WHERE employee_id = users.id)'), $order);
            }, 1)

            ->addIndexColumn()
            ->rawColumns(['action', 'image','user_id','total_commission_earn','total_prescription'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {

        $commissionType = $request->commission_type;
        $userType = ($commissionType == 'doctor_commission') ? 'doctor' : 'vendor';
        $query = User::where('id', $id)
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.mobile')
            ->with('commission_earning')
            ->with('commissionData')
            ->whereHas('commission_earning', function ($q) use ($userType) {
                $q->where('commission_status', 'unpaid')
                    ->where('user_type', $userType)
                    ->where('commissionable_type', 'Modules\Appointment\Models\Appointment');
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        $commissionData = $query->commission_earning()
            ->whereHas('getAppointment', function ($query) {
                $query->where('status', 'checkout');
            })
            ->where('commission_status', 'unpaid')
            ->where('user_type', $userType);

        $commissionAmount = $commissionData->sum('commission_amount');

        $data = [
            'id' => $query->id,
            'full_name' => $query->full_name,
            'email' => $query->email,
            'mobile' => $query->mobile,
            'profile_image' => $query->profile_image,
            'description' => '',
            'commission_earn' => Currency::format($commissionAmount),
            'amount' => Currency::format($commissionAmount),
            'payment_method' => '',
        ];

        return response()->json(['data' => $data, 'status' => true]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate input if needed (optional)
        $request->validate([
            'payment_method' => 'required|string',
            'description' => 'required|string',
        ]);

        $userType = 'pharma';

        $user = User::where('id', $id)
            ->select('id', 'first_name', 'last_name', 'email', 'mobile')
            ->with('commission_earning')
            ->whereHas('commission_earning', function ($q) use ($userType) {
                $q->where('commission_status', 'unpaid')
                    ->where('user_type', $userType)
                    ->where('commissionable_type', 'Modules\Appointment\Models\PatientEncounter');
            })
            ->first();
        if (!$user) {
            return response()->json(['message' => 'User or unpaid commissions not found'], 404);
        }

        $commissionData = $user->commission_earning()
            ->where('commission_status', 'unpaid')
            ->where('user_type', $userType)
            ->where('commissionable_type', PatientEncounter::class)
            ->whereHas('getPatientEncounter', function ($query) {
                $query->where('status', 'checkout');
            });

            $commissionAmount = $commissionData->sum('commission_amount');
            $total_pay = $commissionAmount;

        // Create EmployeeEarning record
        $employeeearning =EmployeeEarning::create([
            'employee_id' => $id,
            'total_amount' => $total_pay,
            'payment_date' => now(),
            'payment_type' => $request->payment_method,
            'description' => $request->description,
            'commission_amount' => $commissionAmount,
            'user_type' => $userType,
        ]);

        // Mark commissions as paid
        CommissionEarning::where('employee_id', $id)
            ->where('commission_status', 'unpaid')
            ->update(['commission_status' => 'paid']);

        // Send notification to pharma about the payout
        sendNotification([
            'notification_type' => 'pharma_payout',
            'pharma_id' => $id,
            'amount' => Currency::format($total_pay),
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'payment_date' => now()->format('Y-m-d H:i:s'),
            'user_id' => $id,
            'pharma_payout' => $employeeearning,
        ]);

        return response()->json([
            'message' => __('messages.payment_done'),
            'status' => true
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function payoutDetails($pharmaId)
    {
        $pharma = User::findOrFail($pharmaId);

        $pharma = User::with(['commission_earning' => function ($q) {
            $q->where('commission_status', 'unpaid')
            ->where('user_type', 'pharma')
            ->where('commissionable_type', PatientEncounter::class);
        }])->findOrFail($pharmaId);

        // Calculate total_pay same as in your datatable
        $commissionData = $pharma->commission_earning()
            ->where('commission_status', 'unpaid')
            ->where('user_type', 'pharma')
            ->where('commissionable_type', PatientEncounter::class)
            ->get();

        $totalPay = \Currency::format($commissionData->sum('commission_amount'));

        $html = view('pharma::earnings.partials.payout_form', compact('pharma', 'totalPay'))->render();

        return response()->json(['html' => $html]);
    }

    public function viewCommissionDetail(Request $request, $id)
    {
        if($request->has('type') && $request->type !='' && $request->has('id') && $request->id !='' ){

            $type = $request->type;
            $data =  User::where('id', $request->id)->with(['commissionData' => function($query) use ($type) {
                $query->whereHas('mainCommission', function($subQuery) use ($type) {
                        $subQuery->where('type', $type);
                    });
            }])->first();
        }

        $html = view('pharma::earnings.partials.pharma_commission', compact('data'))->render();

        return response()->json(['html' => $html]);

    }
}
