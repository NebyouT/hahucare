<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use Modules\Appointment\Models\PatientEncounter;
use App\Models\Setting;
use App\Models\User;
use Modules\Commission\Models\CommissionEarning;
use Yajra\DataTables\DataTables;
use Modules\Earning\Models\EmployeeEarning;
use Currency;

class PharmaPayoutController extends Controller
{

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Pharma Payout';

        // module name
        $this->module_name = 'pharma-payout';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
        ]);
          $this->middleware('check.permission:view_pharma_payout')->only(['index', 'pharmaPayoutReport']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('pharma::messages.pharma_payout');



        $filter = [
            'status' => $request->status,
        ];
        return view('pharma::payout.index_datatable', compact('filter', 'module_title'));
    }

    public function index_data(Request $request)
    {
        $query = PatientEncounter::with(['prescriptions', 'billingDetail', 'billingrecord.clinicservice'])
            ->where('status', 0)
            ->whereHas('prescriptions');

        if ($request->has('filter') && $request->filter['column_status'] != '') {
            $query->where('prescription_payment_status', $request->filter['column_status']);
        }

        $filter = $request->filter;
        if (isset($filter)) {
            if (isset($filter['patient'])) {
                $query->where('user_id', $filter['patient']);
            }

            if (isset($filter['service'])) {
                $query->whereHas('billingrecord.clinicservice', function ($q) use ($filter) {
                    $q->where('id', $filter['service']);
                });
            }

            if (!empty($filter['payment_status'])) {
                $query->where('prescription_payment_status', $filter['payment_status']);
            }

        }

        return datatables()->of($query)
            ->addColumn('id', function ($row) {
                return '#'. $row->id;
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d g:i A');
            })
            ->addColumn('patient', function ($data) {
                return view('pharma::billing_records.user.detail', compact('data'));
            })
            ->addColumn('service', function ($data) {
                return $data->billingrecord->clinicservice->name ?? '-';
            })
            ->addColumn('total_medicine', function ($row) {
                return $row->prescriptions->sum('quantity') ?? 0;
            })
            ->addColumn('total_amount', function ($row) {
                return $row->billingDetail->total_amount ?? 0;
            })
            ->addColumn('prescription_payment_status', function ($row) {
                if ($row->prescription_payment_status == 1) {
                    return '<span class="text-capitalize badge bg-success-subtle p-2">Paid</span>';
                } else {
                    return '<span class="text-capitalize badge bg-danger-subtle p-2">Unpaid</span>';
                }
            })
            ->addColumn('action', function ($data) {
                return view('pharma::billing_records.action_column', compact('data'));
            })

            ->filterColumn('service', function($query, $keyword) {
                $query->whereHas('billingrecord.clinicservice', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('patient', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                    });
                });
            })
            ->filterColumn('prescription_payment_status', function($query, $keyword) {
                if (stripos('paid', $keyword) !== false) {
                    $query->where('prescription_payment_status', 1);
                } elseif (stripos('unpaid', $keyword) !== false) {
                    $query->where('prescription_payment_status', 0);
                }
            })
            ->filterColumn('total_medicine', function($query, $keyword) {
                $query->whereHas('prescriptions', function ($q) use ($keyword) {
                    $q->selectRaw('SUM(quantity) as total')
                    ->groupBy('encounter_id')
                    ->havingRaw('SUM(quantity) LIKE ?', ["%{$keyword}%"]);
                });
            })
            ->filterColumn('total_amount', function($query, $keyword) {
                $query->whereHas('billingDetail', function ($q) use ($keyword) {
                    $q->where('total_amount', 'like', "%{$keyword}%");
                });
            })

            ->rawColumns(['prescription_payment_status', 'action']) // Ensure HTML is rendered
            ->addIndexColumn()
            ->make(true);
    }

    public function billing_detail(Request $request)
    {
        $id = $request->id;
        $module_action = 'Billing Detail';
        $appointments = PatientEncounter::with('user', 'doctor', 'billingrecord.clinicservice', 'clinic', 'billingrecord.billingItem', 'billingrecord','encounterPrescription.billingDetail')
            ->where('id', $id)
            ->whereHas('encounterPrescription')
            ->first();
        $pharma = User::where('user_type', 'pharma')
        ->where('clinic_id', $appointments->clinic_id)
        ->first();
        $prescriptionBilling = EncounterPrescriptionBillingDetail::where('encounter_id', $id)->first();
        $totalAmountWithExclusiveTax = $prescriptionBilling->total_amount ?? 0;
        $totalExclusiveTaxAmount = $prescriptionBilling->exclusive_tax_amount ?? 0;

        $exclusiveTaxes = [];
        if (!empty($prescriptionBilling->exclusive_tax)) {
            $exclusiveTaxes = json_decode($prescriptionBilling->exclusive_tax, true);
        }


        $billing = $appointments;
        $timezone = Setting::where('name', 'default_time_zone')->value('val') ?? 'UTC';
        $setting = Setting::where('name', 'date_formate')->first();
        $dateformate = $setting ? $setting->val : 'Y-m-d';
        $setting = Setting::where('name', 'time_formate')->first();
        $timeformate = $setting ? $setting->val : 'h:i A';
        $combinedFormat = $dateformate . ' ' . $timeformate;
        return view('pharma::billing_records.billing_detail', compact('module_action', 'billing', 'dateformate', 'timeformate', 'timezone', 'combinedFormat','pharma','totalExclusiveTaxAmount', 'totalAmountWithExclusiveTax'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('pharma::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('pharma::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function pharmaPayoutReport(Request $request)
    {
        $module_title = __('sidebar.pharma_payout');

        return view('pharma::payout.pharma_payout_report', compact('module_title'));
    }


    public function pharmaPayoutReportIndexData(Datatables $datatable, Request $request)
    {
        $query = EmployeeEarning::doctorRole(auth()->user())->with('employee')->where('user_type', 'pharma');

        $filter = $request->filter;

        if (isset($filter['appointment_date'])) {
            $appointmentDates = explode(' to ', $filter['appointment_date']);

            if (count($appointmentDates) >= 2) {
                $startDate = date('Y-m-d 00:00:00', strtotime($appointmentDates[0]));
                $endDate = date('Y-m-d 23:59:59', strtotime($appointmentDates[1]));

                $query->where('payment_date', '>=', $startDate)
                    ->where('payment_date', '<=', $endDate);
            }
        }

        if (isset($filter['employee_id'])) {
            $query->whereHas('employee', function ($q) use ($filter) {
                $q->where('employee_id', $filter['employee_id']);
            });
        }
    $query->orderBy('payment_date', 'desc');

        return $datatable->eloquent($query)
            ->editColumn('payment_date', function ($data) {
                return formatDate($data->payment_date);
            })
            ->editColumn('first_name', function ($data) {
                return '
                        <div class="d-flex gap-3 align-items-center">
                            <img src="' . (optional($data->employee)->profile_image ?? default_user_avatar()) . '" alt="avatar" class="avatar avatar-40 rounded-pill">
                            <div class="text-start">
                                <h6 class="m-0">' . (optional($data->employee)->full_name ?? default_user_name()) . '</h6>
                                <span>' . (optional($data->employee)->email ?? '--') . '</span>
                            </div>
                        </div>
                    ';
            })
            ->orderColumn('first_name', function ($query, $order) {
                $query->orderBy(new Expression('(SELECT first_name FROM users WHERE id = bookings.employee_id LIMIT 1)'), $order);
            }, 1)
            ->editColumn('commission_amount', function ($data) {
                return Currency::format($data->commission_amount ?? 0);
            })
            ->editColumn('tip_amount', function ($data) {
                return Currency::format($data->tip_amount ?? 0);
            })
            ->editColumn('total_pay', function ($data) {
                return Currency::format($data->total_amount ?? 0);
            })
            ->addIndexColumn()
            ->rawColumns(['first_name'])
            ->toJson();
    }

}
