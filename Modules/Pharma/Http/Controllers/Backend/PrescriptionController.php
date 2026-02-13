<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Commission\Models\Commission;
use Modules\Commission\Models\CommissionEarning;
use Modules\Pharma\Http\Requests\Prescription;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Traits\PharmaOwnershipChecker;
use Modules\Tax\Models\Tax;
use Yajra\DataTables\DataTables;
use Modules\Pharma\Exports\PrescriptionExport;
use Modules\Commission\Models\EmployeeCommission;
use App\Models\User;

class PrescriptionController extends Controller
{
    use PharmaOwnershipChecker;
    protected string $exportClass = '\Modules\Pharma\Exports\PrescriptionExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'Prescription';

        // module name
        $this->module_name = 'prescriptions';

        view()->share([
            'module_title' => $this->module_title,
            'module_name'  => $this->module_name,
        ]);
        $this->middleware('check.permission:view_prescription')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $module_action = 'List';
        $user          = auth()->user();

        $module_title = __('pharma::messages.prescription');

        $filter = [
            'status' => $request->status,
            'column_status' => $request->column_status,
        ];

        $export_import = true;
        $export_columns = [
            [
                'value' => 'encounter_id',
                'text' => __('pharma::messages.encounter_id'),
            ],
            [
                'value' => 'created_at',
                'text' => __('messages.date_time'),
            ],
            [
                'value' => 'patient',
                'text' => __('pharma::messages.patient'),
            ],
            [
                'value' => 'doctor',
                'text' => __('pharma::messages.doctor'),
            ],
            [
                'value' => 'clinic',
                'text' => __('pharma::messages.clinic'),
            ],
            [
                'value' => 'total_amount',
                'text' => __('pharma::messages.medicine_total'),
            ],
            [
                'value' => 'prescription_status',
                'text' => __('pharma::messages.prescription_status'),
            ],
            [
                'value' => 'payment_status',
                'text' => __('pharma::messages.payment_status'),
            ],
        ];

        if (multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))) {
            $export_columns[] = [
                'value' => 'pharma_id',
                'text' => __('multivendor.singular_title'),
            ];
        }

        $export_url = route('backend.prescription.export');

        return view('pharma::prescription.index_datatable', compact(
            'filter',
            'module_title',
            'export_import',
            'export_columns',
            'export_url'
        ));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $query = PatientEncounter::pharmaRole(auth()->user())->where('patient_encounters.status', 0)
            ->whereHas('prescriptions')
            ->with(['prescriptions.medicine', 'billingrecord']);




        $filter                 = $request->filter;
        $pharmaPrescriptionUser = $filter['pharma_prescription_user'] ?? null;
        $specialMatch           = $filter['special_match'] ?? null;

        if (! empty($pharmaPrescriptionUser) && ! empty($specialMatch)) {
            $employeeId = $pharmaPrescriptionUser;

            $commissionableIds = CommissionEarning::where('user_type', 'pharma')
                ->where('commission_status', 'unpaid')
                ->where('commissionable_type', PatientEncounter::class)
                ->where('employee_id', $employeeId)
                ->pluck('commissionable_id');

            $query->whereIn('id', $commissionableIds)
                ->where('prescription_status', 1)
                ->where('prescription_payment_status', 1);
        }

        if (isset($filter)) {

            if (isset($filter['patient'])) {
                $query->where('user_id', $filter['patient']);
            }

            if (isset($filter['doctor'])) {
                $query->where('doctor_id', $filter['doctor']);
            }
            if (isset($filter['status']) && $filter['status'] !== '') {
                $query->where('prescription_status', $filter['status']);
            }

            // Handle payment status filters - column_status takes precedence
            if (isset($filter['column_status']) && $filter['column_status'] !== '') {
                $query->where('prescription_payment_status', $filter['column_status']);
            } elseif (isset($filter['payment_status']) && $filter['payment_status'] !== '') {
                $query->where('prescription_payment_status', $filter['payment_status']);
            }
        }
        return $datatable->eloquent($query)
            ->addColumn('encounter_id', function ($row) {

                return "#" . $row->id;
            })
            ->orderColumn('encounter_id', function ($query, $order) {
                $query->orderBy('id', $order);
            })

            ->addColumn('created_at', function ($row) {
                // Get timezone and format settings
                $timezone   = Setting::where('name', 'default_time_zone')->value('val') ?? 'UTC';
                $dateFormat = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
                $timeFormat = Setting::where('name', 'time_formate')->value('val') ?? 'h:i A';

                // Combine and format
                $createdAt = Carbon::parse($row->created_at)->timezone($timezone);
                return $createdAt->format($dateFormat) . ' At ' . $createdAt->format($timeFormat);
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %h:%i %p') like ?", ["%{$keyword}%"]);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })

            ->addColumn('user.encounter.user', function ($data) {
                return view('pharma::prescription.user.detail', compact('data'));
            })
            ->filterColumn('user.encounter.user', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                    });
                });
            })
            ->orderColumn('user.encounter.user', function ($query, $order) {
                $query->leftJoin('users as u', 'u.id', '=', 'patient_encounters.user_id')
                    ->orderByRaw("CONCAT(u.first_name, ' ', u.last_name) $order")
                    ->select('patient_encounters.*');
            })

            ->addColumn('user.encounter.doctor', function ($data) {
                return view('pharma::prescription.doctor.detail', compact('data'));
            })
            ->addColumn('pharma', function ($data) {
                $data = $data->pharma_id ? User::find($data->pharma_id) : null;
                return view('pharma::pharma.pharma_id', compact('data'));
            })
            ->filterColumn('pharma', function ($query, $keyword) {
                $query->whereHas('pharma', function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->orderColumn('pharma', function ($query, $order) {
                $query->leftJoin('users as p', 'p.id', '=', 'patient_encounters.pharma_id')
                    ->orderByRaw("CONCAT(p.first_name, ' ', p.last_name) $order")
                    ->select('patient_encounters.*');
            })
            ->filterColumn('user.encounter.doctor', function ($query, $keyword) {
                $query->whereHas('doctor', function ($q) use ($keyword) {
                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                    });
                });
            })
            ->orderColumn('user.encounter.doctor', function ($query, $order) {
                $query->leftJoin('users as d', 'd.id', '=', 'patient_encounters.doctor_id')
                    ->orderByRaw("CONCAT(d.first_name, ' ', d.last_name) $order")
                    ->select('patient_encounters.*');
            })

            ->addColumn('clinic_name', function ($row) {
                // Assuming $row is a PatientEncounter or related model
                return optional($row->clinic)->name ?? '-';
            })

            ->addColumn('prescriptions.medicine.selling_price', function ($data) {
                $billingDetail = EncounterPrescriptionBillingDetail::where('encounter_id', $data->id)->first();
                return $billingDetail ? \Currency::format($billingDetail->total_amount) : '-';
            })
            ->filterColumn('prescriptions.medicine.selling_price', function ($query, $keyword) {
                $query->whereHas('prescriptions.billingDetail', function ($q) use ($keyword) {
                    $q->where('total_amount', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('prescriptions.medicine.selling_price', function ($query, $order) {
                $query->leftJoin('encounter_prescription_billing_details as epbd', 'epbd.encounter_id', '=', 'patient_encounters.id')
                    ->orderByRaw("CAST(REPLACE(epbd.total_amount, ',', '') AS DECIMAL(10,2)) {$order}")
                    ->select('patient_encounters.*');
            })

            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })

            ->addColumn('prescription_status', function ($row) {

                $hasPaidCommission = $row->commission_earnings()
                    ->where('user_type', 'pharma')
                    ->where('commission_status', 'paid')
                    ->exists();

                if ($row->prescription_status == 1) {
                    return '<span class="badge bg-success">Completed</span>';
                }

                $selectedPending   = $row->prescription_status == 0 ? 'selected' : '';
                $selectedCompleted = $row->prescription_status == 1 ? 'selected' : '';

                $dropdown = '<select class="form-control select2 prescription_status" style="width: 100%;" data-encounter-id="' . $row->id . '" name="status">';
                $dropdown .= "<option value='0' $selectedPending>Pending</option>";
                $dropdown .= "<option value='1' $selectedCompleted>Completed</option>";
                $dropdown .= '</select>';

                return $dropdown;
            })

            ->addColumn('payment_status', function ($row) {
                $hasPaidCommission = $row->commission_earnings()
                    ->where('user_type', 'pharma')
                    ->where('commission_status', 'paid')
                    ->exists();

                if ($row->prescription_payment_status == 1) {
                    return '<span class="badge bg-success">Paid</span>';
                }

                $selectedPending   = $row->prescription_payment_status == 0 ? 'selected' : '';
                $selectedCompleted = $row->prescription_payment_status == 1 ? 'selected' : '';

                $dropdown = '<select class="form-control select2 payment_status" style="width: 100%;" data-payment-status-id="' . $row->id . '" name="status">';
                $dropdown .= "<option value='0' $selectedPending>Unpaid</option>";
                $dropdown .= "<option value='1' $selectedCompleted>Paid</option>";
                $dropdown .= '</select>';

                return $dropdown;
            })

            ->filterColumn('encounter_id', function ($query, $keyword) {
                $query->where('id', 'like', "%{$keyword}%");
            })




            ->addColumn('action', function ($data) {
                return view('pharma::prescription.action_column', compact('data'));
            })
            ->rawColumns(['check', 'prescription_status', 'payment_status']) // Ensure HTML is rendered
            ->addIndexColumn()
            ->make(true);
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
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $user      = auth()->user();
        $encounter = PatientEncounter::pharmaRole($user)->where('id', $id)->first();

        if ($encounter == null) {
            abort(403, 'Unauthorized access.');
        }

        $prescriptions = EncounterPrescription::with('billingDetail')
            ->where('encounter_id', $id)
            ->get();

        $totalMedicinePrice = $prescriptions->sum('total_amount');

        $inclusiveTaxes = [];
        $exclusiveTaxes = [];
        $totalTaxAmount = 0;
        $totalAmount    = 0;


        if ($prescriptions->isNotEmpty() && $prescriptions->first()->inclusive_tax) {
            $inclusiveTaxes = json_decode($prescriptions->first()->inclusive_tax, true);
            if (!is_array($inclusiveTaxes)) {
                $inclusiveTaxes = [];
            }
        }

        $billingDetail = optional($prescriptions->first()->billingDetail);

        if ($billingDetail && $billingDetail->exclusive_tax) {
            $exclusiveTaxes = json_decode($billingDetail->exclusive_tax, true);
            if (!is_array($exclusiveTaxes)) {
                $exclusiveTaxes = [];
            }
            $totalTaxAmount = $billingDetail->exclusive_tax_amount;
            $totalAmount    = is_numeric($billingDetail->total_amount) ? $billingDetail->total_amount : 0; // Final grand total
        }

        $encounter          = PatientEncounter::find($id);
        $prescriptionStatus = $encounter ? $encounter->prescription_status : 0;
        $paymentStatus      = $encounter ? $encounter->prescription_payment_status : 0;
        return view('pharma::prescription.show', compact(
            'id',
            'inclusiveTaxes',
            'exclusiveTaxes',
            'totalTaxAmount',
            'totalAmount',
            'totalMedicinePrice',
            'prescriptions',
            'prescriptionStatus',
            'paymentStatus'
        ));
    }

    public function addExtraMedicine($id)
    {
        $user = auth()->user();
        $patientEncounter = PatientEncounter::pharmaRole($user)->where('id', $id)->first();
        if ($patientEncounter == null) {
            abort(403, 'Unauthorized access.');
        }
        return view('pharma::prescription.patient_prescription.add_extra_medicine', compact('patientEncounter'));
    }

    public function saveExtraMedicine(Prescription $request, $id)
    {
        $request_data = $request->all();
        $medicine     = \Modules\Pharma\Models\Medicine::find($request->medicine_id);
        $request_data['name'] = $medicine ? $medicine->name . ' - ' . $medicine->dosage : null;

        $quantity      = $request->quantity ?? 1;
        $selling_price = $medicine->selling_price ?? 0;

        $medicine_price                 = $quantity * $selling_price;
        $request_data['medicine_price'] = $medicine_price;
        $request_data['encounter_id']   = $id;
        $request_data['user_id']        = PatientEncounter::findOrFail($id)->user_id;

        // Inclusive Tax Logic
        $inclusive_tax_amount = 0;
        if ($medicine && $medicine->is_inclusive_tax == 1) {
            $inclusiveTaxes = Tax::where([
                'category'     => 'medicine',
                'tax_type'     => 'inclusive',
                'module_type'  => 'medicine',
                'status'       => 1
            ])->get();

            $inclusiveTaxesArray = [];

            foreach ($inclusiveTaxes as $tax) {
                $taxPerUnit = $tax->type === 'percent'
                    ? ($selling_price * $tax->value) / 100
                    : $tax->value;

                $taxTotal = $taxPerUnit * $quantity;
                $inclusive_tax_amount += $taxTotal;

                $taxData = $tax->toArray();
                $taxData['amount'] = round($taxTotal, 2);

                $inclusiveTaxesArray[] = $taxData;
            }

            $request_data['inclusive_tax'] = json_encode($inclusiveTaxesArray);
        }

        $request_data['inclusive_tax_amount'] = $inclusive_tax_amount;
        $request_data['total_amount']         = $medicine_price + $inclusive_tax_amount;

        // Save prescription
        $prescription = EncounterPrescription::create($request_data);

        // Send add_prescription notification to pharma
        $pharmaId = $medicine ? $medicine->pharma_id : null;
        if ($pharmaId) {
            sendNotification([
                'notification_type' => 'add_prescription',
                'pharma_id' => $pharmaId,
                'encounter_id' => $request_data['encounter_id'],
                'medicine_name' => $medicine ? $medicine->name : '',
                'quantity' => $quantity,
                'user_id' => $request_data['user_id'],
                'prescription' => $prescription,
            ]);
        }

        // Calculate subtotal for the encounter
        $encounterId    = $prescription->encounter_id;
        $totalMedicines = EncounterPrescription::where('encounter_id', $encounterId)
            ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
            ->first();

        $subtotal = $totalMedicines->subtotal ?? 0;

        // Exclusive Tax Logic with amount field
        $exclusiveTaxAmount = 0;
        $exclusiveTaxes     = Tax::where([
            'category' => 'medicine',
            'module_type'  => 'medicine',
            'status'   => 1,
            'tax_type' => 'exclusive',
        ])->get();

        $exclusiveTaxesArray = [];

        foreach ($exclusiveTaxes as $tax) {
            $taxAmount = $tax->type === 'percent'
                ? ($subtotal * $tax->value) / 100
                : $tax->value;

            $exclusiveTaxAmount += $taxAmount;

            $taxData = $tax->toArray();
            $taxData['amount'] = round($taxAmount, 2);

            $exclusiveTaxesArray[] = $taxData;
        }

        $grandTotal = $subtotal + $exclusiveTaxAmount;

        // Create or update billing detail for this encounter
        EncounterPrescriptionBillingDetail::updateOrCreate(
            ['encounter_id' => $encounterId],
            [
                'exclusive_tax'        => json_encode($exclusiveTaxesArray),
                'exclusive_tax_amount' => $exclusiveTaxAmount,
                'total_amount'         => $grandTotal,
            ]
        );

        return response()->json([
            'redirect' => auth()->user()->hasRole(['admin', 'demo_admin'])
                ? route('backend.appointments.show-medicine-info', ['id' => $encounterId])
                : route('backend.prescription.show', ['prescription' => $encounterId]),
            'message'  => 'Medicine added successfully.',
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->ensureCanDeletePrescription();

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
        $this->ensureCanDeletePrescription();
        $prescription = EncounterPrescription::where('encounter_id', $id)->firstOrFail();
        $this->ensurePharmaOwnsPrescription($prescription);
        EncounterPrescription::where('encounter_id', $id)->delete();

        EncounterPrescriptionBillingDetail::where('encounter_id', $id)->delete();

        $deleted = PatientEncounter::find($id)?->delete();

        $message = __('pharma::messages.medicine_deleted');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function encounterBulkAction(Request $request)
    {

        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                EncounterPrescription::whereIn('encounter_id', $ids)->delete();
                EncounterPrescriptionBillingDetail::whereIn('encounter_id', $ids)->delete();

                // Delete patient encounters
                PatientEncounter::whereIn('id', $ids)->delete();

                $message = __('pharma::messages.medicine_deleted');
                return response()->json(['status' => true, 'message' => $message]);

                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }


    public function updatePrescriptionStatus(Request $request)
    {
        $encounterId = $request->input('encounter_id');
        $status = $request->input('status');

        // Step 1: Always update prescription status
        PatientEncounter::where('id', $encounterId)->update(['prescription_status' => $status]);

        $prescriptions = EncounterPrescription::where('encounter_id', $encounterId)->get();

        if ($status == 1) {
            $adjustedMedicines = [];

            foreach ($prescriptions as $prescription) {
                $medicine = Medicine::find($prescription->medicine_id);

                if ($medicine) {
                    $requiredQty = $prescription->quantity;
                    $currentStock = $medicine->quntity;

                    if ($currentStock < $requiredQty) {
                        // Not enough stock, zero out
                        $medicine->quntity = 0;
                        $medicine->stock_value = 0;

                        $adjustedMedicines[] = [
                            'name' => $medicine->name,
                            'available_quantity' => $currentStock,
                            'required_quantity' => $requiredQty,
                            'adjusted_to' => 0,
                            'pharma_id' => $medicine->pharma_id ?? null,

                        ];

                        $pharmaUserId = null;
                        if ($medicine->pharma_id) {
                            $pharmaUserId = \App\Models\User::where('id', $medicine->pharma_id)
                                ->whereHas('roles', function ($query) {
                                    $query->where('name', 'pharma');
                                })->value('id');
                        }

                        $notificationData = [
                            'notification_type'   => 'low_stock_alert',
                            'medicine_name'       => $medicine->name,
                            'available_quantity'  => $currentStock,
                            'required_quantity'   => $requiredQty,
                            'pharma_id'           => $medicine->pharma_id ?? null,
                            'medicine_id'         => $medicine->id,
                            'id'                  => $medicine->id,
                            'user_id'             => $pharmaUserId,
                            'low_stock_medicine'   => $medicine,
                        ];


                        sendLowStockNotification([$notificationData]);
                    } else {
                        $medicine->quntity -= $requiredQty;
                        $medicine->stock_value = $medicine->quntity * $medicine->selling_price;
                    }

                    $medicine->save();
                }
            }

            if (!empty($adjustedMedicines)) {
                return response()->json([
                    'message' => 'Some medicines had insufficient stock.',
                    'adjusted_medicines' => $adjustedMedicines,
                ], 422);
            }
        } elseif ($status == 0) {
            foreach ($prescriptions as $prescription) {
                $medicine = Medicine::find($prescription->medicine_id);

                if ($medicine) {
                    $medicine->quntity += $prescription->quantity;
                    $medicine->stock_value = $medicine->quntity * $medicine->selling_price;
                    $medicine->save();
                }
            }
        }

        return response()->json(['success' => true]);
    }


    public function getMedicineStock($id)
    {
        $medicine = Medicine::find($id);

        if (! $medicine) {
            return response()->json(['error' => 'Medicine not found'], 404);
        }

        return response()->json([
            'name'          => $medicine->name,
            'stock'         => $medicine->quntity,
            'dosage'        => $medicine->dosage,
            'selling_price' => $medicine->selling_price,
        ]);
    }

  public function updatePrescriptionPaymentStatus(Request $request)
{
    $encounterId = $request->input('encounter_id');
    $status      = $request->input('status');

    PatientEncounter::where('id', $encounterId)
        ->update(['prescription_payment_status' => $status]);

    $totalProfit = 0;

    $patientEncounter = PatientEncounter::with('encounterPrescription.medicine')->find($encounterId);

    if (! $patientEncounter) {
        return response()->json(['success' => false, 'message' => 'Encounter not found'], 404);
    }

    foreach ($patientEncounter->encounterPrescription as $prescription) {
        $medicine = $prescription->medicine;

        if ($medicine) {
            $profitPerUnit = $medicine->selling_price - $medicine->purchase_price;
            $totalProfit += $profitPerUnit * $prescription->quantity;
        }
    }

    // 🔸 Fetch pharma commissions only assigned to this employee
    $employeeId = $patientEncounter->pharma_id ?? null;

    if ($employeeId) {
        $assignedPharmaCommissionIds = EmployeeCommission::where('employee_id', $employeeId)
        ->pluck('commission_id');

        $pharmaCommissions = Commission::where('type', 'pharma_commission')
        ->where('status', 1)
        ->whereIn('id', $assignedPharmaCommissionIds)
        ->get();
    } else {
        $pharmaCommissions = [];
    }


    if ($pharmaCommissions->isEmpty()) {
        return response()->json(['success' => false, 'message' => 'No active assigned pharma commissions found'], 404);
    }

    $commissionAmount    = 0;
    $commissionBreakdown = [];

    foreach ($pharmaCommissions as $commission) {
        $amount = match ($commission->commission_type) {
            'percentage' => ($totalProfit * $commission->commission_value) / 100,
            'fixed' => $commission->commission_value,
            default => 0,
        };

        $commissionAmount += $amount;

        $commissionBreakdown[] = [
            'id'                => $commission->id,
            'type'              => $commission->commission_type,
            'value'             => $commission->commission_value,
            'calculated_amount' => $amount,
        ];
    }

    $adminAmount = $totalProfit - $commissionAmount;
    $user = User::where('user_type', 'admin')->first();

    if ($status == 1) {
        // ✅ Save Pharma Commission Earning
        CommissionEarning::updateOrCreate(
            [
                'commissionable_type' => PatientEncounter::class,
                'commissionable_id'   => $patientEncounter->id,
                'user_type'           => 'pharma',
            ],
            [
                'employee_id'       => $employeeId,
                'commission_amount' => $commissionAmount,
                'commission_status' => 'unpaid',
                'commissions'       => json_encode($commissionBreakdown),
            ]
        );

        // ✅ Save Admin Commission Earning (remaining amount)
        CommissionEarning::updateOrCreate(
            [
                'commissionable_type' => PatientEncounter::class,
                'commissionable_id'   => $patientEncounter->id,
                'user_type'           => 'admin',
            ],
            [
                'employee_id'       => $user->id,
                'commission_amount' => $adminAmount,
                'commission_status' => 'unpaid',
                'commissions'       => json_encode(['note' => 'Remaining admin profit']),
            ]
        );
    } else {
        CommissionEarning::where('commissionable_type', PatientEncounter::class)
            ->where('commissionable_id', $patientEncounter->id)
            ->delete();
    }

    return response()->json(['success' => true]);
}

    public function userPrescriptionDetail(Datatables $datatable, Request $request)
    {
        $prescriptionId     = $request->filter['prescription_id'];
        $encounter          = PatientEncounter::find($prescriptionId);
        $prescriptionStatus = $encounter ? $encounter->prescription_status : 0;
        $paymentStatus      = $encounter ? $encounter->prescription_payment_status : 0;
        $query              = EncounterPrescription::where('encounter_id', $prescriptionId)
            ->with(['medicine.category', 'medicine.form']); // eager load all needed

        return $datatable->eloquent($query)

            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })

            ->filterColumn('frequency', function ($query, $keyword) {
                $query->where('frequency', 'like', "%{$keyword}%");
            })

            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('medicine.category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('form', function ($query, $keyword) {
                $query->whereHas('medicine.form', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('dosage', function ($query, $keyword) {
                $query->whereHas('medicine', function ($q) use ($keyword) {
                    $q->where('dosage', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('name', function ($row) {
                return $row->name;
            })

            ->addColumn('category', function ($row) {
                return $row->medicine->category->name ?? '-';
            })

            ->addColumn('form', function ($row) {
                return $row->medicine->form->name ?? '-';
            })

            ->addColumn('duration', function ($row) {
                return $row->duration ?? '-';
            })

            ->addColumn('frequency', function ($row) {
                return $row->frequency ?? '-';
            })

            ->addColumn('quantity', function ($row) {
                return $row->quantity ?? '-';
            })

            ->addColumn('price', function ($row) {
                return $row->total_amount ? \Currency::format($row->total_amount) : '-';
            })

            ->addColumn('dosage', function ($row) {
                return $row->medicine->dosage ?? '-';
            })

            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })

            ->addColumn('action', function ($data) use ($prescriptionStatus, $paymentStatus) {
                return view('pharma::prescription.patient_prescription.action_column', [
                    'data'               => $data,
                    'encounter_id'       => $data->encounter_id,
                    'prescriptionStatus' => $prescriptionStatus,
                    'paymentStatus'      => $paymentStatus,
                ]);
            })
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
            })

            ->orderColumn('frequency', function ($query, $order) {
                $query->orderBy('frequency', $order);
            })

            ->orderColumn('category', function ($query, $order) {
                $query->leftJoin('medicines as m', 'm.id', '=', 'encounter_prescription.medicine_id')
                    ->leftJoin('medicine_categories as mc', 'mc.id', '=', 'm.category_id')
                    ->orderBy('mc.name', $order)
                    ->select('encounter_prescription.*');
            })

            ->orderColumn('form', function ($query, $order) {
                $query->leftJoin('medicines as m', 'm.id', '=', 'encounter_prescription.medicine_id')
                    ->leftJoin('medicine_forms as mf', 'mf.id', '=', 'm.form_id')
                    ->orderBy('mf.name', $order)
                    ->select('encounter_prescription.*');
            })

            ->orderColumn('dosage', function ($query, $order) {
                $query->leftJoin('medicines as m', 'm.id', '=', 'encounter_prescription.medicine_id')
                    ->orderBy('m.dosage', $order)
                    ->select('encounter_prescription.*');
            })

            ->orderColumn('duration', function ($query, $order) {
                $query->orderBy('duration', $order);
            })

            ->orderColumn('quantity', function ($query, $order) {
                $query->orderBy('quantity', $order);
            })

            ->orderColumn('price', function ($query, $order) {
                $query->orderByRaw("CAST(REPLACE(total_amount, ',', '') AS DECIMAL(10,2)) $order");
            })

            ->rawColumns(['check'])
            ->addIndexColumn()
            ->make(true);
    }

    public function patientPrescriptionEdit($id)
    {
        $user = auth()->user();
        $prescription     = EncounterPrescription::findOrFail($id);
        $patientEncounter = PatientEncounter::findOrFail($prescription->encounter_id);
        if (
            $user->user_type === 'pharma' &&
            (
                $patientEncounter->pharma_id === null ||
                $patientEncounter->pharma_id !== $user->id
            )
        ) {
            abort(403, 'Unauthorized access.');
        }
        $medicines        = Medicine::get();
        $isEdit           = true;
        return view('pharma::prescription.patient_prescription.add_extra_medicine', compact('prescription', 'patientEncounter', 'medicines', 'isEdit'));
    }

    public function patientPrescriptionUpdate(Request $request, $id)
    {
        $data = $request->all();
        $prescription = EncounterPrescription::findOrFail($id);
        $medicine     = \Modules\Pharma\Models\Medicine::findOrFail($request->medicine_id);

        $data['name'] = $medicine->name . ' - ' . ($medicine->dosage ?? '');

        $quantity       = $request->quantity ?? 1;
        $selling_price  = $medicine->selling_price ?? 0;
        $medicine_price = $quantity * $selling_price;
        $data['medicine_price'] = $medicine_price;

        // Inclusive Tax
        $inclusive_tax_amount = 0;
        $inclusiveTaxesArray = [];

        if ($medicine->is_inclusive_tax == 1) {
            $inclusiveTaxes = Tax::where([
                'category'     => 'medicine',
                'tax_type'     => 'inclusive',
                'module_type'  => 'medicine',
                'status'       => 1,
            ])->get();

            foreach ($inclusiveTaxes as $tax) {
                $taxPerUnit = $tax->type === 'percent'
                    ? ($selling_price * $tax->value) / 100
                    : $tax->value;

                $taxTotal = $taxPerUnit * $quantity;
                $inclusive_tax_amount += $taxTotal;

                $taxData = $tax->toArray();
                $taxData['amount'] = round($taxTotal, 2);

                $inclusiveTaxesArray[] = $taxData;
            }

            $data['inclusive_tax'] = json_encode($inclusiveTaxesArray);
        }

        $data['inclusive_tax_amount'] = $inclusive_tax_amount;
        $data['total_amount'] = $medicine_price + $inclusive_tax_amount;

        // Update prescription
        $prescription->update($data);

        $encounterId = $prescription->encounter_id;

        // Recalculate subtotal
        $totalMedicines = EncounterPrescription::where('encounter_id', $encounterId)
            ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
            ->first();

        $subtotal = $totalMedicines->subtotal ?? 0;

        // Exclusive Tax
        $exclusiveTaxAmount = 0;
        $exclusiveTaxesArray = [];

        $exclusiveTaxes = Tax::where([
            'category' => 'medicine',
            'module_type'  => 'medicine',
            'status'   => 1,
            'tax_type' => 'exclusive',
        ])->get();

        foreach ($exclusiveTaxes as $tax) {
            $taxAmount = $tax->type === 'percent'
                ? ($subtotal * $tax->value) / 100
                : $tax->value;

            $exclusiveTaxAmount += $taxAmount;

            $taxData = $tax->toArray();
            $taxData['amount'] = round($taxAmount, 2);

            $exclusiveTaxesArray[] = $taxData;
        }

        $grandTotal = $subtotal + $exclusiveTaxAmount;

        // Update billing detail
        EncounterPrescriptionBillingDetail::updateOrCreate(
            ['encounter_id' => $encounterId],
            [
                'exclusive_tax'        => json_encode($exclusiveTaxesArray),
                'exclusive_tax_amount' => $exclusiveTaxAmount,
                'total_amount'         => $grandTotal,
            ]
        );

        return response()->json([
            'message' => 'Medicine Updated Successfully.',
            'redirect' => auth()->user()->hasRole(['admin', 'demo_admin'])
                ? route('backend.appointments.show-medicine-info', ['id' => $encounterId])
                : route('backend.prescription.show', ['prescription' => $encounterId]),
        ]);
    }


    public function patientPrescriptionDelete($id)
    {

        $prescription = EncounterPrescription::findOrFail($id);
        $encounterId  = $prescription->encounter_id;

        // Delete the prescription
        $prescription->delete();

        // Recalculate subtotal after deletion
        $totalMedicines = EncounterPrescription::where('encounter_id', $encounterId)
            ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
            ->first();

        $subtotal = $totalMedicines->subtotal ?? 0;

        // Recalculate exclusive tax
        $exclusiveTaxAmount = 0;
        $exclusiveTaxes     = Tax::where([
            'category' => 'medicine',
            'module_type'  => 'medicine',
            'status'   => 1,
            'tax_type' => 'exclusive',
        ])->get();

        foreach ($exclusiveTaxes as $tax) {
            $exclusiveTaxAmount += ($tax->type === 'percent')
                ? ($subtotal * $tax->value) / 100
                : $tax->value;
        }

        $grandTotal = $subtotal + $exclusiveTaxAmount;

        // Update billing detail
        EncounterPrescriptionBillingDetail::updateOrCreate(
            ['encounter_id' => $encounterId],
            [
                'exclusive_tax'        => $exclusiveTaxes->toJson(),
                'exclusive_tax_amount' => $exclusiveTaxAmount,
                'total_amount'         => $grandTotal,
            ]
        );

        $message = __('pharma::messages.medicine_deleted');
        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function bulk_action(Request $request)
    {
        $ids        = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message    = __('messages.bulk_update');

        switch ($actionType) {
            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }

                $prescriptions = EncounterPrescription::whereIn('id', $ids)->get();

                // Group prescriptions by encounter_id
                $encounterGroups = $prescriptions->groupBy('encounter_id');

                // Delete the prescriptions
                EncounterPrescription::whereIn('id', $ids)->delete();

                foreach ($encounterGroups as $encounterId => $group) {
                    // Recalculate subtotal after deletion
                    $totalMedicines = EncounterPrescription::where('encounter_id', $encounterId)
                        ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
                        ->first();

                    $subtotal = $totalMedicines->subtotal ?? 0;

                    // Recalculate exclusive tax
                    $exclusiveTaxAmount = 0;
                    $exclusiveTaxes     = Tax::where([
                        'category' => 'medicine',
                        'status'   => 1,
                        'tax_type' => 'exclusive',
                    ])->get();

                    foreach ($exclusiveTaxes as $tax) {
                        $exclusiveTaxAmount += ($tax->type === 'percent')
                            ? ($subtotal * $tax->value) / 100
                            : $tax->value;
                    }

                    $grandTotal = $subtotal + $exclusiveTaxAmount;

                    // Update billing detail
                    EncounterPrescriptionBillingDetail::updateOrCreate(
                        ['encounter_id' => $encounterId],
                        [
                            'exclusive_tax'        => $exclusiveTaxes->toJson(),
                            'exclusive_tax_amount' => $exclusiveTaxAmount,
                            'total_amount'         => $grandTotal,
                        ]
                    );
                }

                $message = __('pharma::messages.medicine_deleted');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    public function getPaymentDetailHtml($id)
    {
        $prescriptions = EncounterPrescription::with('billingDetail')
            ->where('encounter_id', $id)
            ->get();

        $totalMedicinePrice = $prescriptions->sum('total_amount');
        $inclusiveTaxes = [];
        $exclusiveTaxes = [];
        $totalTaxAmount = 0;
        $totalAmount    = \Currency::format(0);

        if ($prescriptions->isNotEmpty() && $prescriptions->first()->inclusive_tax) {
            $inclusiveTaxes = json_decode($prescriptions->first()->inclusive_tax, true);
            // Ensure inclusiveTaxes is an array
            if (!is_array($inclusiveTaxes)) {
                $inclusiveTaxes = [];
            }
        }

        $billingDetail = optional($prescriptions->first()->billingDetail);

        if ($billingDetail && $billingDetail->exclusive_tax) {
            $exclusiveTaxes = json_decode($billingDetail->exclusive_tax, true);
            // Ensure exclusiveTaxes is an array
            if (!is_array($exclusiveTaxes)) {
                $exclusiveTaxes = [];
            }
            $totalTaxAmount = $billingDetail->exclusive_tax_amount;
            $totalAmount    = is_numeric($billingDetail->total_amount)
                ? \Currency::format($billingDetail->total_amount)
                : \Currency::format(0);
        }

        return view('pharma::prescription.partials.payment_detail', compact(
            'inclusiveTaxes',
            'exclusiveTaxes',
            'totalTaxAmount',
            'totalAmount',
            'totalMedicinePrice'
        ));
    }

    public function getmedicineDetailDetailHtml($id)
    {
        $prescriptions = EncounterPrescription::with(['billingDetail', 'medicine.pharmaUser'])
            ->where('encounter_id', $id)
            ->get();

        $totalMedicinePrice = \Currency::format($prescriptions->sum('total_amount'));

        $inclusiveTaxes = [];
        $exclusiveTaxes = [];
        $totalTaxAmount = 0;
        $totalAmount    = 0;

        if ($prescriptions->isNotEmpty() && $prescriptions->first()->inclusive_tax) {
            $inclusiveTaxes = json_decode($prescriptions->first()->inclusive_tax, true);
            if (!is_array($inclusiveTaxes)) {
                $inclusiveTaxes = [];
            }
        }

        $billingDetail = optional($prescriptions->first()->billingDetail);
        $dateFormat = Setting::get('date_formate', 'Y-m-d');
        $timeFormat = Setting::get('time_formate', 'H:i');
        $datetimeFormat = trim($dateFormat . ' ' . $timeFormat);
        if ($billingDetail && $billingDetail->exclusive_tax) {
            $exclusiveTaxes = json_decode($billingDetail->exclusive_tax, true);
            if (!is_array($exclusiveTaxes)) {
                $exclusiveTaxes = [];
            }
            $totalTaxAmount = $billingDetail->exclusive_tax_amount;
            $totalAmount    = is_numeric($billingDetail->total_amount) ? $billingDetail->total_amount : 0;
        }

        $encounter = PatientEncounter::with([
            'user',
            'appointment.clinicservice',
            'appointment.cliniccenter',
            'appointment.appointmenttransaction'
        ])->find($id);

        $prescriptionStatus = $encounter ? $encounter->prescription_status : 0;
        $paymentStatus      = $encounter ? $encounter->prescription_payment_status : 0;

        $patient     = optional($encounter)->user;
        $appointment = $encounter->appointment;

        // ✅ Only fetch pharma if encounter.pharma_id exists
        $pharma = null;
        if (!empty($encounter->pharma_id)) {
            $pharma = \App\Models\User::find($encounter->pharma_id);
        }
        return view('pharma::prescription.partials.pharma_details', compact(
            'patient',
            'totalMedicinePrice',
            'appointment',
            'pharma',
            'encounter',
            'datetimeFormat'
        ));
    }
}
