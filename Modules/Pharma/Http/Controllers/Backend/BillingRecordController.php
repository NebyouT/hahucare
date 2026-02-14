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

class BillingRecordController extends Controller
{

    public function __construct()
    {
        $this->module_title = 'Billing Record';
        $this->module_name = 'billing-record';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
        ]);
        $this->middleware('check.permission:view_billing_record')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('pharma::messages.billing_record');


        $filter = [
            'status' => $request->status,
        ];
        return view('pharma::billing_records.index_datatable', compact('filter', 'module_title'));
    }

    public function index_data(Request $request)
    {
        $query = PatientEncounter::with(['prescriptions', 'billingDetail', 'billingrecord.clinicservice'])
            ->where('status', 0)
            ->where('pharma_id', auth()->user()->id)
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

        if ($request->has('order')) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            $columns = [
                0 => 'id',
                1 => 'created_at',
            ];

            if (isset($columns[$orderColumnIndex])) {
                $query->orderBy($columns[$orderColumnIndex], $orderDir);
            } else {
                $query->orderByDesc('id');
            }
        } else {
            $query->orderByDesc('id');
        }


        return datatables()->of($query)
            ->addColumn('id', function ($row) {
                return '#' . $row->id;
            })
            ->addColumn('created_at', function ($row) {
                $dateSetting = Setting::where('name', 'date_formate')->first();
                $dateformate = $dateSetting ? $dateSetting->val : 'Y-m-d';
                return $row->created_at->format($dateformate);
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
                $amount = $row->billingDetail->total_amount ?? 0;
                return \Currency::format($amount);
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

            ->filterColumn('service', function ($query, $keyword) {
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
            ->filterColumn('prescription_payment_status', function ($query, $keyword) {
                if (stripos('paid', $keyword) !== false) {
                    $query->where('prescription_payment_status', 1);
                } elseif (stripos('unpaid', $keyword) !== false) {
                    $query->where('prescription_payment_status', 0);
                }
            })
            ->filterColumn('total_medicine', function ($query, $keyword) {
                $query->whereHas('prescriptions', function ($q) use ($keyword) {
                    $q->selectRaw('SUM(quantity) as total')
                        ->groupBy('encounter_id')
                        ->havingRaw('SUM(quantity) LIKE ?', ["%{$keyword}%"]);
                });
            })
            ->filterColumn('total_amount', function ($query, $keyword) {
                $query->whereHas('billingDetail', function ($q) use ($keyword) {
                    $q->where('total_amount', 'like', "%{$keyword}%");
                });
            })

            ->rawColumns(['prescription_payment_status', 'action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function billing_detail(Request $request)
    {
        $id = $request->id;
        $user = auth()->user();

        $appointments = PatientEncounter::with('user', 'doctor', 'billingrecord.clinicservice', 'clinic', 'billingrecord.billingItem', 'billingrecord', 'encounterPrescription.billingDetail')
            ->where('id', $id)
            ->whereHas('encounterPrescription')
            ->first();

        if (!$appointments) {
            return redirect()->back()->with('error', 'Appointment not found.');
        }
        if ($user->hasRole('pharma')) {
            if ($appointments->pharma_id != $user->id) {
                abort(403, 'Unauthorized access.');
            }
        }
        $module_action = 'Billing Detail';
        $pharma = User::where('id', $appointments->pharma_id)
            ->where('user_type', 'pharma')
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
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
        $timeformate = Setting::where('name', 'time_formate')->value('val') ?? 'h:i A';
        $combinedFormat = $dateformate . ' ' . $timeformate;
        return view('pharma::billing_records.billing_detail', compact('exclusiveTaxes', 'module_action', 'billing', 'dateformate', 'timeformate', 'timezone', 'combinedFormat', 'pharma', 'totalExclusiveTaxAmount', 'totalAmountWithExclusiveTax'));
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
