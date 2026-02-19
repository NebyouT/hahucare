<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Laboratory\Models\LabOrder;
use Modules\Laboratory\Models\LabOrderItem;
use Modules\Laboratory\Models\LabService;
use Modules\Laboratory\Models\Lab;
use Modules\Clinic\Models\Clinics;
use App\Models\User;
use Yajra\DataTables\DataTables;

class LabOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = LabOrder::with(['clinic', 'lab', 'patient', 'doctor'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($q2) => $q2->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('laboratory::lab-orders.index', compact('orders'));
    }

    public function index_data(Request $request)
    {
        $labOrders = LabOrder::with(['clinic', 'lab', 'patient', 'doctor'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $labOrders->where('status', $request->status);
        }

        return DataTables::of($labOrders)
            ->addColumn('order_number', function ($o) {
                return '<strong class="text-primary">#' . $o->order_number . '</strong>';
            })
            ->addColumn('patient_name', function ($o) {
                return $o->patient->full_name ?? '—';
            })
            ->addColumn('doctor_name', function ($o) {
                return 'Dr. ' . ($o->doctor->full_name ?? '—');
            })
            ->addColumn('clinic_name', function ($o) {
                return $o->clinic->name ?? '—';
            })
            ->addColumn('lab_name', function ($o) {
                return $o->lab->name ?? '—';
            })
            ->addColumn('status_badge', function ($o) {
                $map = [
                    'completed'   => 'success',
                    'in_progress' => 'warning',
                    'confirmed'   => 'info',
                    'cancelled'   => 'danger',
                    'pending'     => 'secondary',
                ];
                $cls = $map[$o->status] ?? 'secondary';
                return '<span class="badge bg-' . $cls . '">' . ucfirst(str_replace('_', ' ', $o->status)) . '</span>';
            })
            ->addColumn('amount', function ($o) {
                return number_format($o->final_amount, 2);
            })
            ->addColumn('order_date', function ($o) {
                return $o->order_date ? $o->order_date->format('d M Y H:i') : '—';
            })
            ->addColumn('action', function ($o) {
                $view   = route('backend.lab-orders.show', $o->id);
                $edit   = route('backend.lab-orders.edit', $o->id);
                $delete = route('backend.lab-orders.destroy', $o->id);
                $btns   = '<a href="' . $view . '" class="btn btn-sm btn-outline-primary me-1" title="View"><i class="fas fa-eye"></i></a>';
                if ($o->status !== 'completed') {
                    $btns .= '<a href="' . $edit . '" class="btn btn-sm btn-outline-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>';
                }
                $btns .= '<button class="btn btn-sm btn-outline-danger delete-order-btn" data-url="' . $delete . '" title="Delete"><i class="fas fa-trash"></i></button>';
                return $btns;
            })
            ->rawColumns(['order_number', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $clinics = Clinics::where('status', 1)->orderBy('name')->get();
        
        return view('laboratory::lab-orders.create_simplified', compact('clinics'));
    }

    public function store(Request $request)
    {
        // Handle both simplified (encounter) and full form data
        $isEncounterOrder = $request->has('type') && $request->type === 'encounter_lab_order';
        
        if ($isEncounterOrder) {
            // Simplified validation for encounter lab orders
            $validated = $request->validate([
                'clinic_id' => 'required|exists:clinic,id',
                'lab_id' => 'required|exists:labs,id',
                'patient_id' => 'required|exists:users,id',
                'doctor_id' => 'required|exists:users,id',
                'encounter_id' => 'required|integer',
                'order_type' => 'required', // Will be set to 'outpatient' from hidden field
                'priority' => 'required', // Will be set to 'routine' from hidden field
                'collection_type' => 'required', // Will be set to 'venipuncture' from hidden field
                'referral_notes' => 'nullable|string',
                'services' => 'required|array|min:1',
                'services.*' => 'required|exists:lab_services,id',
            ]);
            
            // Set default values for hidden fields
            $validated['clinical_indication'] = null;
            $validated['diagnosis_suspected'] = null;
            $validated['notes'] = $validated['referral_notes'] ?? null;
            $validated['collection_notes'] = null;
            $validated['referred_by'] = $validated['doctor_id'];
            $validated['department'] = null;
            $validated['ward_room'] = null;
            
            // Convert services array to expected format
            $servicesArray = [];
            foreach ($validated['services'] as $serviceId) {
                $servicesArray[] = ['lab_service_id' => $serviceId];
            }
            $validated['services'] = $servicesArray;
        } else {
            // Simplified validation for regular lab orders
            $validated = $request->validate([
                'clinic_id' => 'required|exists:clinic,id',
                'lab_id' => 'required|exists:labs,id',
                'patient_id' => 'required|exists:users,id',
                'doctor_id' => 'required|exists:users,id',
                'encounter_id' => 'nullable|integer',
                'order_type' => 'required|in:outpatient,inpatient,emergency',
                'priority' => 'required|in:routine,urgent,stat',
                'notes' => 'nullable|string',
                'services' => 'required|array|min:1',
                'services.*' => 'required|exists:lab_services,id',
            ]);
            
            // Set default values for optional fields
            $validated['clinical_indication'] = null;
            $validated['diagnosis_suspected'] = null;
            $validated['collection_type'] = 'venipuncture';
            $validated['collection_notes'] = null;
            $validated['referred_by'] = $validated['doctor_id'];
            $validated['department'] = null;
            $validated['ward_room'] = null;
            
            // Convert services array to expected format
            $servicesArray = [];
            foreach ($validated['services'] as $serviceId) {
                $servicesArray[] = ['lab_service_id' => $serviceId];
            }
            $validated['services'] = $servicesArray;
        }

        DB::beginTransaction();
        try {
            $labOrder = new LabOrder();
            $labOrder->order_number = $labOrder->generateOrderNumber();
            $labOrder->clinic_id = $validated['clinic_id'];
            $labOrder->lab_id = $validated['lab_id'];
            $labOrder->patient_id = $validated['patient_id'];
            $labOrder->doctor_id = $validated['doctor_id'];
            $labOrder->encounter_id = $validated['encounter_id'] ?? null;
            $labOrder->order_type = $validated['order_type'];
            $labOrder->priority = $validated['priority'];
            $labOrder->clinical_indication = $validated['clinical_indication'] ?? null;
            $labOrder->diagnosis_suspected = $validated['diagnosis_suspected'] ?? null;
            $labOrder->notes = $validated['notes'] ?? null;
            $labOrder->collection_type = $validated['collection_type'];
            $labOrder->collection_notes = $validated['collection_notes'] ?? null;
            $labOrder->referred_by = $validated['referred_by'] ?? null;
            $labOrder->department = $validated['department'] ?? null;
            $labOrder->ward_room = $validated['ward_room'] ?? null;
            $labOrder->order_date = now();
            $labOrder->status = 'pending';
            $labOrder->created_by = auth()->id();
            
            $totalAmount = 0;
            $discountAmount = 0;
            
            $labOrder->save();
            
            foreach ($validated['services'] as $service) {
                $labService = LabService::find($service['lab_service_id']);
                
                $orderItem = new LabOrderItem();
                $orderItem->lab_order_id = $labOrder->id;
                $orderItem->lab_service_id = $service['lab_service_id'];
                $orderItem->service_name = $labService->name;
                $orderItem->service_description = $labService->description;
                $orderItem->price = $labService->price;
                $orderItem->discount_amount = 0;
                $orderItem->final_price = $labService->price;
                $orderItem->urgent_flag = false;
                $orderItem->clinical_notes = null;
                $orderItem->sample_type = null;
                $orderItem->fasting_required = false;
                $orderItem->special_instructions = null;
                $orderItem->status = 'pending';
                $orderItem->save();
                
                $totalAmount += $labService->price;
            }
            
            $labOrder->total_amount = $totalAmount;
            $labOrder->discount_amount = $discountAmount;
            $labOrder->final_amount = $totalAmount - $discountAmount;
            $labOrder->save();
            
            DB::commit();
            
            if ($isEncounterOrder) {
                return response()->json([
                    'status' => true,
                    'message' => 'Lab order created successfully',
                    'order_id' => $labOrder->id
                ]);
            }
            
            return redirect()->route('backend.lab-orders.index')
                ->with('success', 'Lab order created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($isEncounterOrder) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error creating lab order: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating lab order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $labOrder = LabOrder::with(['clinic', 'lab', 'patient', 'doctor', 'labOrderItems.labService'])->findOrFail($id);
        return view('laboratory::lab-orders.show', compact('labOrder'));
    }

    public function edit($id)
    {
        $labOrder = LabOrder::with(['labOrderItems.labService'])->findOrFail($id);
        $clinics = Clinics::where('status', 1)->orderBy('name')->get();
        $labs = Lab::where('is_active', true)->orderBy('name')->get();
        $labServices = LabService::where('lab_id', $labOrder->lab_id)->orderBy('name')->get();
        $patients = User::where('user_type', 'user')->where('status', 1)
            ->orderBy('first_name')->orderBy('last_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orWhere('user_type', 'doctor')
            ->where('status', 1)
            ->orderBy('first_name')->orderBy('last_name')->get();
        
        return view('laboratory::lab-orders.edit', compact('labOrder', 'clinics', 'labs', 'labServices', 'patients', 'doctors'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinic,id',
            'lab_id' => 'required|exists:labs,id',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'order_type' => 'required|in:outpatient,inpatient,emergency',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'nullable|string',
            'diagnosis_suspected' => 'nullable|string',
            'notes' => 'nullable|string',
            'collection_type' => 'required|in:venipuncture,urine,swab,other',
            'collection_notes' => 'nullable|string',
            'referred_by' => 'nullable|exists:users,id',
            'department' => 'nullable|string',
            'ward_room' => 'nullable|string',
        ]);

        $labOrder = LabOrder::findOrFail($id);
        $labOrder->update($validated);

        return redirect()->route('backend.lab-orders.index')
            ->with('success', 'Lab order updated successfully');
    }

    public function destroy($id)
    {
        $labOrder = LabOrder::findOrFail($id);
        $labOrder->delete();

        return redirect()->route('backend.lab-orders.index')
            ->with('success', 'Lab order deleted successfully');
    }

    public function getLabTests($lab_id)
    {
        $services = LabService::where('lab_id', $lab_id)
            ->where('is_active', true)
            ->with(['category'])
            ->get(['id', 'name', 'price', 'description', 'category_id']);
            
        return response()->json($services);
    }

    public function getLabsByClinic($clinic_id)
    {
        // Try clinic-specific labs first; fall back to all labs so the form is never empty
        $labs = Lab::with('clinic:id,name')
            ->where('clinic_id', $clinic_id)
            ->orderBy('name')
            ->get(['id', 'name', 'clinic_id']);

        if ($labs->isEmpty()) {
            $labs = Lab::with('clinic:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'clinic_id']);
        }

        $result = $labs->map(function ($lab) use ($clinic_id) {
            return [
                'id'          => $lab->id,
                'name'        => $lab->name,
                'clinic_name' => optional($lab->clinic)->name,
                'same_clinic' => (int)$lab->clinic_id === (int)$clinic_id,
            ];
        });

        return response()->json($result);
    }

    public function getCategoriesByLab($lab_id)
    {
        try {
            // Debug: Log the lab_id
            \Log::info("Getting categories for lab_id: " . $lab_id);
            
            // Get all services for this lab with their categories
            $services = LabService::where('lab_id', $lab_id)
                ->where('is_active', true)
                ->whereNotNull('category_id')
                ->with('category')
                ->get();
            
            \Log::info("Found " . $services->count() . " services for lab " . $lab_id);
            
            if ($services->isEmpty()) {
                \Log::info("No services found for lab " . $lab_id);
                return response()->json([]);
            }
            
            // Extract unique categories from services
            $categories = $services
                ->map(function ($service) {
                    return $service->category;
                })
                ->filter(function ($category) {
                    return $category !== null;
                })
                ->unique('id')
                ->values();
            
            \Log::info("Found " . $categories->count() . " unique categories");
            
            return response()->json($categories);
            
        } catch (\Exception $e) {
            \Log::error('Error loading categories for lab ' . $lab_id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load categories: ' . $e->getMessage()], 500);
        }
    }

    public function getServicesByCategory($category_id)
    {
        $services = LabService::where('category_id', $category_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'description', 'category_id']);
            
        return response()->json($services);
    }

    public function getServicesByLab($lab_id)
    {
        $services = LabService::where('lab_id', $lab_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'description', 'category_id']);
            
        return response()->json($services);
    }

    public function getDoctorsByClinic($clinic_id)
    {
        // Doctors are linked to clinics via DoctorClinicMapping, not a direct clinic_id on users
        $doctorUserIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $clinic_id)
            ->pluck('doctor_id');

        $doctors = User::whereIn('id', $doctorUserIds)
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        // Fallback: if none found via mapping, return all doctors by user_type
        if ($doctors->isEmpty()) {
            $doctors = User::where('user_type', 'doctor')
                ->where('status', 1)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name']);
        }
            
        return response()->json($doctors);
    }

    public function getPatientsByDoctor($doctor_id)
    {
        // Patients are stored as user_type='user' in this system (no 'patient' Spatie role)
        $patients = User::where('user_type', 'user')
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($patients);
    }

    public function getAllDoctors()
    {
        // Use user_type='doctor' — matches the actual DB column used in this system
        $doctors = User::where('user_type', 'doctor')
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($doctors);
    }

    public function getAllPatients()
    {
        // Patients are stored as user_type='user' in this system (no 'patient' Spatie role)
        $patients = User::where('user_type', 'user')
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($patients);
    }

    public function worklist(Request $request)
    {
        $query = LabOrder::with(['lab', 'patient', 'doctor', 'clinic', 'labOrderItems.labService'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['pending', 'in_progress']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('laboratory::lab-orders.worklist', compact('orders'));
    }

    public function storeResult(Request $request, $order_id)
    {
        $request->validate([
            'result_files'    => 'required|array|min:1',
            'result_files.*'  => 'file|mimes:pdf,jpg,jpeg,png,docx,doc|max:10240',
            'technician_note' => 'nullable|string|max:2000',
        ]);

        $labOrder = LabOrder::with('labOrderItems')->findOrFail($order_id);

        DB::beginTransaction();
        try {
            $uploadedFiles = [];
            foreach ($request->file('result_files') as $file) {
                $fileName  = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $filePath  = $file->storeAs('lab_results', $fileName, 'public');
                $uploadedFiles[] = $filePath;
            }

            foreach ($labOrder->labOrderItems as $item) {
                $item->result_file        = implode(',', $uploadedFiles);
                $item->technician_note    = $request->technician_note;
                $item->result_uploaded_at = now();
                $item->status             = 'completed';
                $item->save();
            }

            $labOrder->status         = 'completed';
            $labOrder->completed_date = now();
            $labOrder->updated_by     = auth()->id();
            $labOrder->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Result uploaded successfully. Order marked as completed.',
                ]);
            }

            return redirect()->route('backend.lab-orders.worklist')
                ->with('success', 'Result uploaded. Order #' . $labOrder->order_number . ' marked as completed.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function deleteLabOrder(Request $request, $id)
    {
        $labOrder = LabOrder::findOrFail($id);
        $labOrder->deleted_by = auth()->id();
        $labOrder->save();
        $labOrder->delete();

        $encounter_id = $labOrder->encounter_id;

        $labOrders = LabOrder::with(['lab', 'labOrderItems.labService'])
            ->where('encounter_id', $encounter_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $firstItem = $order->labOrderItems->first();
                return [
                    'id'                 => $order->id,
                    'order_number'       => $order->order_number,
                    'lab_name'           => optional($order->lab)->name ?? 'N/A',
                    'priority'           => $order->priority ?? 'routine',
                    'status'             => $order->status,
                    'order_date'         => $order->order_date,
                    'notes'              => $order->notes,
                    'result_file'        => $firstItem?->result_file,
                    'technician_note'    => $firstItem?->technician_note,
                    'result_uploaded_at' => $firstItem?->result_uploaded_at,
                    'services'           => $order->labOrderItems->map(fn($i) => ['service_name' => $i->service_name])->toArray(),
                ];
            });

        $html = view('laboratory::backend.patient_encounter.component.lab_order_table', [
            'data' => ['lab_orders' => $labOrders, 'status' => 1, 'id' => $encounter_id],
        ])->render();

        return response()->json(['status' => true, 'message' => 'Lab order deleted.', 'html' => $html]);
    }
}
