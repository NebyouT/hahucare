<?php
namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\MedicineCategory;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Models\Supplier;
use Modules\Pharma\Models\Manufacturer;
use App\Models\User;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Pharma\Transformers\MedicineResource;
use Modules\Pharma\Transformers\CategoryResource;
use Modules\Pharma\Transformers\MedicineFormResource;
use Modules\Pharma\Transformers\SupplierResource;
use Modules\Pharma\Transformers\ManufacturerResource;
use Modules\Pharma\Http\Requests\MedicineRequest;
use Modules\Pharma\Http\Requests\UpdateMedicineRequest;
use Modules\Pharma\Http\Requests\Prescription;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Tax\Models\Tax;
use Modules\Pharma\Models\PurchasedOrder;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use Carbon\Carbon;
use DB;
use Illuminate\Validation\ValidationException;
use Modules\Pharma\Models\MedicineHistory;
use Modules\Pharma\Transformers\MedicineHistoryResource;
use Modules\Clinic\Models\Clinics;

class MedicineController extends Controller
{

    public function list(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $type = $request->input('type');
            $search = $request->input('search');
            $encounterId = $request->input('encounter_id');
            $pharmaId = auth()->user()->hasRole(['pharma']) ? auth()->user()->id : $request->input('pharma_id') ?? null;
            $clinicId = $request->input('clinic_id');
            $medicineQuery = Medicine::query();
            if ($type == 'expire-medicine') {
                $medicineQuery->where('expiry_date', '<=', Carbon::today());
            } elseif ($type == 'upcoming-expiry') {
                    $fromDate = Carbon::today();
                    $toDate = Carbon::today()->copy()->addDays(5);
                    $medicineQuery->whereBetween('expiry_date', [$fromDate, $toDate]);
            } elseif ($type == 'low-stock') {
                $medicineQuery->whereRaw("CAST(quntity AS UNSIGNED) <= CAST(re_order_level AS UNSIGNED)")
                    ->where('expiry_date', '>', Carbon::today());
            } elseif ($type === 'top-medicine') {
            $topMedicines = EncounterPrescription::with('medicine')
                ->whereHas('medicine', function ($query) {
                    $query->where('pharma_id', auth()->id());
                })
                ->select('medicine_id', DB::raw('COUNT(medicine_id) as total_sold'))
                ->groupBy('medicine_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            if ($topMedicines->isNotEmpty()) {
                $idsString = $topMedicines->pluck('medicine_id')->implode(',');

                // Apply to medicineQuery (preserve order)
                $medicineQuery->whereIn('id', $topMedicines->pluck('medicine_id'))
                    ->orderByRaw("FIELD(id, $idsString)");
            } else {
                $medicineQuery->whereRaw('0=1'); // No results
            }
        }

            $medicineQuery->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('dosage', 'like', "%$search%")
                      ->orWhere('batch_no', 'like', "%$search%")
                      ->orWhereHas('form', function ($formQ) use ($search) {
                          $formQ->where('name', 'like', "%$search%");
                      })
                      ->orWhereHas('category', function ($catQ) use ($search) {
                          $catQ->where('name', 'like', "%$search%");
                      });
                });
            });


            if ($request->has('name')) {
                $medicineQuery->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('form_id')) {
                $medicineQuery->where('form_id', $request->input('form_id'));
            }
             if ($request->has('category_id')) {
                $medicineQuery->where('category_id', $request->input('category_id'));
            }

            if ($request->has('form')) {
                $formNames = explode(',', $request->input('form'));
                $formIds = MedicineForm::where(function ($query) use ($formNames) {
                    foreach ($formNames as $name) {
                        $query->orWhere('name', 'like', '%' . trim($name) . '%');
                    }
                })->pluck('id');
                if ($formIds->isNotEmpty()) {
                    $medicineQuery->whereIn('form_id', $formIds);
                } else {
                    $medicineQuery->whereRaw('0=1');
                }
            }
            if ($request->has('category')) {
                $categoryNames = explode(',', $request->input('category'));

                $categoryIds = MedicineCategory::where(function ($query) use ($categoryNames) {
                    foreach ($categoryNames as $name) {
                        $query->orWhere('name', 'like', '%' . trim($name) . '%');
                    }
                })->pluck('id');
                if ($categoryIds->isNotEmpty()) {
                    $medicineQuery->whereIn('category_id', $categoryIds);
                } else {
                    $medicineQuery->whereRaw('0=1');
                }
            }

           if ($request->has('supplier')) {
                $supplierNames = explode(',', $request->input('supplier'));

                $suppliers = Supplier::where(function ($query) use ($supplierNames) {
                    foreach ($supplierNames as $name) {
                        $query->orWhere('first_name', 'like', '%' . trim($name) . '%');
                    }
                })->pluck('id');

                if ($suppliers->isNotEmpty()) {
                    $medicineQuery->whereIn('supplier_id', $suppliers);
                } else {
                    $medicineQuery->whereRaw('0=1');
                }
            }
            if( $pharmaId != null ){
                $medicineQuery = $medicineQuery->where('pharma_id', $pharmaId);

            }elseif(auth()->user()->hasRole(['doctor']) && isset($clinicId) && $clinicId != null) {
                $pharmaIdsInClinic = User::where('clinic_id', $clinicId)
                    ->pluck('id')
                    ->toArray();
                $medicineQuery->whereIn('pharma_id', $pharmaIdsInClinic);
            }
            if ($encounterId) {
                $addedMedicineIds = EncounterPrescription::where('encounter_id', $encounterId)
                    ->pluck('medicine_id')
                    ->toArray();
                if (!empty($addedMedicineIds)) {
                    $medicineQuery->whereNotIn('id', $addedMedicineIds);
                }
            }

            $medicineQuery->orderBy('created_at', 'desc');
            $medicinePaginated = $medicineQuery->paginate($perPage);
            $medicineCollection = MedicineResource::collection($medicinePaginated);

            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_list"),
                'data' => $medicineCollection,
            ];

            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine list.',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function category(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $categories = MedicineCategory::query();
            $categoryPaginated = $categories->paginate($perPage);
            $categoryCollection = MedicineResource::collection($categoryPaginated);
            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_category_list"),
                'data' => $categoryCollection,
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine category list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine category list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function form(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $medicineForm = MedicineForm::query();
            $medicineFormPaginated = $medicineForm->paginate($perPage);
            $medicineFormCollection = MedicineFormResource::collection($medicineFormPaginated);
            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_form"),
                'data' => $medicineFormCollection,
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine Form list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine form list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function supplier(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $suplier = Supplier::query();
            $suplierPaginated = $suplier->paginate($perPage);
            $supplierCollection = SupplierResource::collection($suplierPaginated);

            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.supplier_list"),
                'data' => $supplierCollection,
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Suplier list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching Suplier list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  public function manufacturer(Request $request)
{
    try {
        $user = auth()->user();
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $manufacturerQuery = Manufacturer::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        if ($user && $user->user_type === 'pharma') {
            $manufacturerQuery->where('pharma_id', $user->id);
        }
        $manufacturerQuery->orderBy('name');

        $manufacturerPaginated = $manufacturerQuery->paginate($perPage);
        $manufacturerCollection = ManufacturerResource::collection($manufacturerPaginated);

        $response = [
            'code' => 200,
            'status' => true,
            'message' => __("pharma::messages.manufacturer_list"),
            'data' => $manufacturerCollection,
        ];
        return comman_custom_response($response, 200);
    } catch (\Throwable $e) {
        \Log::error('Manufacturer list error: ' . $e->getMessage());
        return comman_custom_response([
            'code' => 500,
            'status' => false,
            'message' => 'Something went wrong while fetching Manufacturer list.',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function store(MedicineRequest $request)
    {

        try{
            $data = [
                'name'              => $request->name,
                'dosage'            => $request->dosage,
                'category_id'       => $request->medicine_category_id,
                'form_id'           => $request->form_id,
                'expiry_date'       => $request->expiry_date,
                'note'              => $request->note,
                'supplier_id'       => $request->supplier_id,
                'contact_number'    => $request->contact_number,
                'payment_terms'     => $request->payment_terms,
                'quntity'           => $request->quntity,
                're_order_level'    => $request->re_order_level,
                'manufacturer_id'   => $request->manufacturer,
                'batch_no'          => $request->batch_no,
                'start_serial_no'   => $request->start_serial_no,
                'end_serial_no'     => $request->end_serial_no,
                'purchase_price'    => $request->purchase_price,
                'selling_price'     => $request->selling_price,
                'is_inclusive_tax'     => $request->is_inclusive_tax ?? 0,
                'stock_value'       => $request->quntity * $request->selling_price,
                'pharma_id'         => auth()->user()->id,
            ];

            $medicine = Medicine::create($data);
            $medicine->load('pharmaUser');
            MedicineHistory::create([
                'medicine_id'     => $medicine->id,
                'batch_no'        => $medicine->batch_no,
                'quntity'         => $medicine->quntity,
                'start_serial_no'  => $medicine->start_serial_no,
                'end_serial_no'   => $medicine->end_serial_no,
                'stock_value'     => $medicine->stock_value,
            ]);
            $message = __("pharma::messages.medicine_added");

            $clinicId = optional($medicine->pharmaUser)->clinic_id;
            $clinic = $clinicId ? Clinics::find($clinicId) : null;

            // Send add_medicine notification to doctor/admin/vendor
            sendNotification([
                'notification_type' => 'add_medicine',
                'medicine_name' => $request->name,
                'pharma_id' => $medicine->pharma_id,
                'quantity' => $request->quntity,
                'clinic_id' => $clinicId ?? optional($clinic)->id,
                'medicine' => $medicine,
                'vendor_id' => $clinic->vendor_id,
            ]);

            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_added"),
                'data' => new MedicineResource($medicine),
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine add error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while adding medicine.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateMedicineRequest $request, $id)
    {
        try {
            $medicine = Medicine::findOrFail($id);

            $data = $request->validated();
            $data['stock_value'] = $request->quntity * $request->selling_price;
            $data['is_inclusive_tax'] = $request->is_inclusive_tax ?? 0;
            $data['pharma_id'] = auth()->id();

            $medicine->update($data);

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __('pharma::messages.medicine_updated'),
                'data' => new MedicineResource($medicine),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Medicine update error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while updating medicine.',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function addExtraMedicine(Prescription $request, $id)
    {
        try {
            $request_data = $request->all();

            $medicine = Medicine::findOrFail($request->medicine_id);
            $encounter = PatientEncounter::findOrFail($id);
            $request_data['name'] = $medicine->name . ' - ' . $medicine->dosage;
            $quantity = $request->quantity ?? 1;
            $selling_price = $medicine->selling_price ?? 0;

            $medicine_price = $quantity * $selling_price;
            $request_data['medicine_price'] = $medicine_price;
            $request_data['encounter_id'] = $id;
            $request_data['user_id'] = $encounter->user_id;

            // Inclusive Tax Logic
            $inclusive_tax_amount = 0;
            if ($medicine->is_inclusive_tax == 1) {
                $inclusiveTaxes = Tax::where(['category' => 'medicine', 'tax_type' => 'inclusive', 'status' => 1])->get();
                $request_data['inclusive_tax'] = $inclusiveTaxes->toJson();

                foreach ($inclusiveTaxes as $tax) {
                    $inclusive_tax_amount += $tax->type === 'percent'
                        ? ($medicine_price * $tax->value) / 100
                        : $tax->value;
                }
            }

            $request_data['inclusive_tax_amount'] = $inclusive_tax_amount;
            $request_data['total_amount'] = $medicine_price + $inclusive_tax_amount;

            $prescription = EncounterPrescription::create($request_data);

            // Recalculate encounter subtotal and tax
            $encounterId = $prescription->encounter_id;
            $subtotal = EncounterPrescription::where('encounter_id', $encounterId)
                ->selectRaw('SUM(medicine_price + inclusive_tax_amount) as subtotal')
                ->first()
                ->subtotal ?? 0;

            $exclusiveTaxAmount = 0;
            $exclusiveTaxes = Tax::where(['category' => 'medicine', 'module_type'  => 'medicine', 'tax_type' => 'exclusive', 'status' => 1])->get();

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


            return response()->json([
                'status' => true,
                'message' => 'Medicine added successfully.',
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $e->getModel() === Medicine::class
                    ? 'Medicine not found.'
                    : 'Encounter not found.',
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('Medicine add error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while adding medicine.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addStock(Request $request, $id)
    {
        try{
            $request->validate([

                'quantity'    => 'required|integer|min:1',
            ]);
            $medicine = Medicine::findOrFail($id);
            $medicine->quntity += $request->quantity;
            $medicine->save();

            MedicineHistory::create([
                'medicine_id'     => $medicine->id,
                'batch_no'        => $medicine->batch_no,
                'quntity'         => $request->quantity,
                'start_serial_no'  => $medicine->start_serial_no,
                'end_serial_no'   => $medicine->end_serial_no,
                'stock_value'     => $request->quantity * $medicine->selling_price,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Stock updated',
            ]);

        }  catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $e->getModel() === Medicine::class
                    ? 'Medicine not found.'
                    : 'Encounter not found.',
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('Medicine add error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while adding medicine.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function medicineHistory(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $medicineId = $request->input('medicine_id');

            if (!$medicineId) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Medicine ID is required.',
                ], 400);
            }
            $history = MedicineHistory::with('medicine:id,name') // avoid N+1
            ->where('medicine_id', $medicineId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
            $history = MedicineHistoryResource::collection($history);

            return response()->json([
                'status' => true,
                'data' => $history,
                'message' => __('pharma::messages.lbl_medicine_history_list'),
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine history error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine history.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
