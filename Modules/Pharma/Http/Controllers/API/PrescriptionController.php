<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Appointment\Transformers\PrescriptionRescource;
use App\Http\Resources\PatientEncounterResource;
use App\Http\Resources\PatientEncounterDetailResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Pharma\Models\Medicine;
use Modules\Commission\Models\Commission;
use Modules\Commission\Models\CommissionEarning;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use Modules\Tax\Models\Tax;
use Modules\Commission\Models\EmployeeCommission;
use App\Models\User;

class PrescriptionController extends Controller
{

public function list(Request $request)
{

    try {
        $perPage = $request->input('per_page', 10);
        $user = auth()->user();

        $filter = $request->all();

        $query = PatientEncounter::where('status', 0)
            ->where('clinic_id', $user->clinic_id)
            ->whereHas('prescriptions')
            ->with(['prescriptions.medicine', 'billingrecord', 'user']);

        // If pharma_id is not provided in request, use auth user id (if role is pharma)
        if ($user->hasRole('pharma')) {
            $pharmaId = $request->input('pharma_id', $user->id);
            $query->where(function ($q) use ($pharmaId) {
                $q->where('pharma_id', $pharmaId)
                  ->orWhereNull('pharma_id');
            });
        }

        if (isset($filter['patient_name']) && $filter['patient_name'] !== '') {
            $patientIds = is_array($filter['patient_name']) ? $filter['patient_name'] : explode(',', $filter['patient_name']);
            $patientIds = array_map('trim', $patientIds);
            $query->whereIn('user_id', $patientIds);
        }

        if (isset($filter['doctor_name']) && $filter['doctor_name'] !== '') {
            $doctorIds = is_array($filter['doctor_name']) ? $filter['doctor_name'] : explode(',', $filter['doctor_name']);
            $doctorIds = array_map('trim', $doctorIds);
            $query->whereIn('doctor_id', $doctorIds);
        }

        if (isset($filter['status']) && $filter['status'] !== '') {
            $statusValues = is_array($filter['status']) ? $filter['status'] : explode(',', $filter['status']);

            $mappedStatusValues = [];
            foreach ($statusValues as $statusValue) {
                $statusValue = trim($statusValue);
                if (is_string($statusValue)) {
                    $statusMap = [
                        'Pending' => 0,
                        'Completed' => 1
                    ];
                    $mappedStatusValues[] = $statusMap[$statusValue] ?? $statusValue;
                } else {
                    $mappedStatusValues[] = $statusValue;
                }
            }

            $query->whereIn('prescription_status', $mappedStatusValues);
        }

        if (isset($filter['payment_status']) && $filter['payment_status'] !== '') {
            $statusValues = is_array($filter['payment_status']) ? $filter['payment_status'] : explode(',', $filter['payment_status']);

            $mappedStatusValues = [];
            foreach ($statusValues as $statusValue) {
                $statusValue = trim($statusValue);
                if (is_string($statusValue)) {
                    $statusMap = [
                        'Pending' => 0,
                        'Paid' => 1
                    ];
                    $mappedStatusValues[] = $statusMap[$statusValue] ?? $statusValue;
                } else {
                    $mappedStatusValues[] = $statusValue;
                }
            }

            $query->whereIn('prescription_payment_status', $mappedStatusValues);
        }


        if (isset($filter['column_status']) && $filter['column_status'] !== '') {
            $columnStatusValues = is_array($filter['column_status']) ? $filter['column_status'] : explode(',', $filter['column_status']);
            $columnStatusValues = array_map('trim', $columnStatusValues);
            $query->whereIn('prescription_payment_status', $columnStatusValues);
        }

        if (!empty($filter['pharma_prescription_user']) && !empty($filter['special_match'])) {
            $employeeId = $filter['pharma_prescription_user'];
            $commissionableIds = CommissionEarning::where('user_type', 'pharma')
                ->where('commission_status', 'unpaid')
                ->where('commissionable_type', PatientEncounter::class)
                ->where('employee_id', $employeeId)
                ->pluck('commissionable_id');

            $query->whereIn('id', $commissionableIds)
                ->where('prescription_status', 1)
                ->where('prescription_payment_status', 1);
        }

        $results = $query->orderByDesc('id')->paginate($perPage);

        return comman_custom_response([
            'code' => 200,
            'status' => true,
            'message' => __('pharma::messages.prescription_list'),
            'data' => PatientEncounterResource::collection($results)
        ], 200);
    } catch (\Throwable $e) {
        \Log::error('API prescription list error: ' . $e->getMessage());
        return comman_custom_response([
            'code' => 500,
            'status' => false,
            'message' => 'Something went wrong while fetching Prescription list.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function detail(Request $request)
    {
        $user = auth()->user();
        $patientEncounter = PatientEncounter::with([
            'user',
            'doctor',
            'encounterPrescription.medicine.form',
            'encounterPrescription.medicine.category',
            'billingDetail'
        ])->where('id', $request->id)->first();
        if (!$patientEncounter) {
        return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.patient_encounter_not_found"),
            ], 404);
        }

        $patientEncounterCollection = new PatientEncounterDetailResource($patientEncounter);
         return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __('pharma::messages.prescription_detail'),
                'data' => $patientEncounterCollection
        ], 200);

    }
    public function edit(Request $request)
    {
        try {
            $patientEncounter = PatientEncounter::findOrFail($request->id);
            $patientEncounterData = new PatientEncounterResource($patientEncounter);
            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.prescription_list"),
                'data' => $patientEncounterData,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_form_not_found"),
            ], 404);

        } catch (\Throwable $e) {
            \Log::error('MedicineForm edit error: ' . $e->getMessage());

            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $updated = PatientEncounter::where('id', $id)->update($data);
            if (!$updated) {
                return comman_custom_response([
                    'code' => 404,
                    'status' => false,
                    'message' => __("pharma::messages.medicine_form_not_found"),
                ], 404);
            }

            $patientEncounter = PatientEncounter::find($id);
            $patientEncounterData = new PatientEncounterResource($patientEncounter);

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.prescription_list"),
                'data' => $patientEncounterData,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('MedicineForm edit error: ' . $e->getMessage());

            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function medicineEdit(Request $request, $id)
    {
        try {
            // Get new values from request
            $medicineId = $request->input('medicine_id');
            $quantity = $request->input('quantity');
            $frequency = $request->input('frequency');
            $duration = $request->input('duration');
            $instruction = $request->input('instruction');
            $encounterId = $request->input('encounter_id');

            $encouterPresctiption = EncounterPrescription::where('medicine_id',$medicineId)->where('encounter_id',$encounterId)->first();

            // Find the new medicine
            $medicine = Medicine::findOrFail($medicineId);

            // Update fields
            $encouterPresctiption->medicine_id = $medicineId;
            $encouterPresctiption->quantity = $quantity;
            $encouterPresctiption->frequency = $frequency;
            $encouterPresctiption->duration = $duration;
            $encouterPresctiption->instruction = $instruction;

            // Update name and price based on new medicine and quantity
            $encouterPresctiption->name = $medicine->name ;
            $encouterPresctiption->medicine_price = $medicine->selling_price * $quantity;

            // Inclusive Tax Logic
            $inclusive_tax_amount = 0;
            if ($medicine->is_inclusive_tax == 1) {
                $inclusiveTaxes = Tax::where(['category' => 'medicine', 'tax_type' => 'inclusive', 'status' => 1])->get();
                $encouterPresctiption->inclusive_tax = $inclusiveTaxes->toJson();

                foreach ($inclusiveTaxes as $tax) {
                    $inclusive_tax_amount += $tax->type === 'percent'
                        ? ($encouterPresctiption->medicine_price * $tax->value) / 100
                        : $tax->value;
                }
            }
            $encouterPresctiption->inclusive_tax_amount = $inclusive_tax_amount;
            $encouterPresctiption->total_amount = $encouterPresctiption->medicine_price + $inclusive_tax_amount;

            $encouterPresctiption->save();

            // Recalculate encounter subtotal and exclusive tax
            $encounterId = $encouterPresctiption->encounter_id;
            $subtotal = EncounterPrescription::where('encounter_id', $encounterId)
                ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
                ->first()
                ->subtotal ?? 0;

            $exclusiveTaxAmount = 0;
            $exclusiveTaxes = Tax::where([
                'category' => 'medicine',
                'module_type'  => 'medicine',
                'status' => 1,
                'tax_type' => 'exclusive'
            ])->get();

            $exclusiveTaxesArray = [];
            foreach ($exclusiveTaxes as $tax) {
                if ($tax->type === 'percent') {
                    $taxAmount = ($subtotal * $tax->value) / 100;
                } else {
                    $taxAmount = $tax->value;
                }
                $exclusiveTaxAmount += $taxAmount;
                $taxData = $tax->toArray();
                $taxData['amount'] = $taxAmount;
                $exclusiveTaxesArray[] = $taxData;
            }

            $grandTotal = $subtotal + $exclusiveTaxAmount;

            EncounterPrescriptionBillingDetail::updateOrCreate(
                ['encounter_id' => $encounterId],
                [
                    'exclusive_tax' => json_encode($exclusiveTaxesArray),
                    'exclusive_tax_amount' => $exclusiveTaxAmount,
                    'total_amount' => $grandTotal,
                ]
            );

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_updated"),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_form_not_found"),
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('MedicineForm edit error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function medicineDelete(Request $request, $id)
    {
        try{

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
            $exclusiveTaxAmount = 0;
            $exclusiveTaxes = Tax::where([
                'category' => 'medicine',
                'module_type'  => 'medicine',
                'status' => 1,
                'tax_type' => 'exclusive'
            ])->get();

            $exclusiveTaxesArray = [];

            foreach ($exclusiveTaxes as $tax) {
                if ($tax->type === 'percent') {
                    $taxAmount = round(($subtotal * $tax->value) / 100, 2);
                } else {
                    $taxAmount = round($tax->value, 2);
                }

                $exclusiveTaxAmount += $taxAmount;

                $taxData = $tax->toArray();
                $taxData['amount'] = $taxAmount;

                $exclusiveTaxesArray[] = $taxData;
            }

            $grandTotal = $subtotal + $exclusiveTaxAmount;

            // Create or update billing detail for this encounter
            EncounterPrescriptionBillingDetail::updateOrCreate(
                ['encounter_id' => $encounterId],
                [
                    'exclusive_tax' => json_encode($exclusiveTaxesArray),
                    'exclusive_tax_amount' => $exclusiveTaxAmount,
                    'total_amount' => $grandTotal,
                ]
            );

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_deleted"),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_form_not_found"),
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('MedicineForm edit error: ' . $e->getMessage());

            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function updatePrescriptionStatus(Request $request)
    {
        try {
            $encounterId = $request->input('encounter_id');
            $status      = $request->input('status');

            $patientEncounter = PatientEncounter::find($encounterId);
            if (!$patientEncounter) {
                return comman_custom_response([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Encounter not found',
                ], 404);
            }

            // Step 1: Always update prescription status
            $patientEncounter->update(['prescription_status' => $status]);
            $prescriptions = EncounterPrescription::where('encounter_id', $encounterId)->get();

            if ($status == 1) {
                $adjustedMedicines = [];
                foreach ($prescriptions as $prescription) {
                    $medicine = Medicine::find($prescription->medicine_id);
                    if ($medicine) {
                        $requiredQty  = $prescription->quantity;
                        $currentStock = $medicine->quntity;
                        if ($currentStock < $requiredQty) {
                            $medicine->quntity     = 0;
                            $medicine->stock_value = 0;
                            $adjustedMedicines[]   = [
                                'name'               => $medicine->name,
                                'available_quantity' => $currentStock,
                                'required_quantity'  => $requiredQty,
                                'adjusted_to'        => 0,
                            ];
                        } else {
                            $medicine->quntity -= $requiredQty;
                            $medicine->stock_value = $medicine->quntity * $medicine->selling_price;
                        }
                        $medicine->save();
                    }
                }
                if (!empty($adjustedMedicines)) {
                    return comman_custom_response([
                        'code' => 422,
                        'status' => false,
                        'message' => 'Some medicines had insufficient stock.',
                        'data' => $adjustedMedicines,
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

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => 'Prescription status updated successfully.',
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('updatePrescriptionStatus error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function updatePrescriptionPaymentStatus(Request $request)
{
    try {
        $encounterId = $request->input('encounter_id');
        $status      = $request->input('status');

        $patientEncounter = PatientEncounter::with('encounterPrescription.medicine')->find($encounterId);
        if (!$patientEncounter) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => 'Encounter not found',
            ], 404);
        }

        $patientEncounter->update(['prescription_payment_status' => $status]);
        $patientEncounter->refresh();

        $totalProfit = 0;
        foreach ($patientEncounter->encounterPrescription as $prescription) {
            $medicine = $prescription->medicine;
            if ($medicine) {
                $profitPerUnit = $medicine->selling_price - $medicine->purchase_price;
                $totalProfit += $profitPerUnit * $prescription->quantity;
            }
        }

        $employeeId = $patientEncounter->pharma_id ?? null;

        if ($employeeId) {
            $assignedPharmaCommissionIds = EmployeeCommission::where('employee_id', $employeeId)
                ->pluck('commission_id')
                ->filter()
                ->toArray();

            $pharmaCommissionsQuery = Commission::where('type', 'pharma_commission')
                ->where('status', 1);

            if (!empty($assignedPharmaCommissionIds)) {
                $pharmaCommissionsQuery->whereIn('id', $assignedPharmaCommissionIds);
            }

            $pharmaCommissions = $pharmaCommissionsQuery->get();
        } else {
            $pharmaCommissions = [];
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
            // Pharma commission entry (only if commission exists)
            if ($employeeId && $commissionAmount > 0 && !empty($commissionBreakdown)) {
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
            } else {
                // Remove any existing pharma commission entry if no active commission assignment
                CommissionEarning::where('commissionable_type', PatientEncounter::class)
                    ->where('commissionable_id', $patientEncounter->id)
                    ->where('user_type', 'pharma')
                    ->delete();
            }

            // Admin commission entry
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

        return comman_custom_response([
            'code' => 200,
            'status' => true,
            'message' => 'Prescription payment status updated successfully.',
        ], 200);
    } catch (\Throwable $e) {
        \Log::error('updatePrescriptionPaymentStatus error: ' . $e->getMessage());
        return comman_custom_response([
            'code' => 500,
            'status' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
