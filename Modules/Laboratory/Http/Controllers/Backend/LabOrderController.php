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
    public function index()
    {
        return view('laboratory::lab-orders.index');
    }

    public function index_data(Request $request)
    {
        $labOrders = LabOrder::with(['clinic', 'lab', 'patient', 'doctor'])
            ->orderBy('created_at', 'desc');

        return DataTables::of($labOrders)
            ->addColumn('order_number', function ($labOrder) {
                return '<strong>#' . $labOrder->order_number . '</strong>';
            })
            ->addColumn('patient_name', function ($labOrder) {
                return $labOrder->patient->full_name ?? 'N/A';
            })
            ->addColumn('doctor_name', function ($labOrder) {
                return 'Dr. ' . ($labOrder->doctor->full_name ?? 'N/A');
            })
            ->addColumn('lab_name', function ($labOrder) {
                return $labOrder->lab->name ?? 'N/A';
            })
            ->addColumn('status', function ($labOrder) {
                $badgeClass = $labOrder->status == 'completed' ? 'success' : 
                              ($labOrder->status == 'processing' ? 'warning' : 'secondary');
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($labOrder->status) . '</span>';
            })
            ->addColumn('priority', function ($labOrder) {
                $badgeClass = $labOrder->priority == 'urgent' ? 'danger' : 
                              ($labOrder->priority == 'stat' ? 'danger' : 'primary');
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($labOrder->priority) . '</span>';
            })
            ->addColumn('order_date', function ($labOrder) {
                return $labOrder->order_date->format('M d, Y H:i');
            })
            ->addColumn('total_amount', function ($labOrder) {
                return '$' . number_format($labOrder->final_amount, 2);
            })
            ->addColumn('action', function ($labOrder) {
                $buttons = '';
                $buttons .= '<a href="' . route('backend.lab-orders.show', $labOrder->id) . '" class="btn btn-sm btn-primary me-1">View</a>';
                if ($labOrder->status != 'completed') {
                    $buttons .= '<a href="' . route('backend.lab-orders.edit', $labOrder->id) . '" class="btn btn-sm btn-warning me-1">Edit</a>';
                }
                $buttons .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteLabOrder(' . $labOrder->id . ')">Delete</button>';
                return $buttons;
            })
            ->rawColumns(['order_number', 'status', 'priority', 'action'])
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
                'clinic_id' => 'required|exists:clinics,id',
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
                'clinic_id' => 'required|exists:clinics,id',
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
        $labOrder = LabOrder::findOrFail($id);
        $clinics = Clinics::where('is_active', true)->orderBy('name')->get();
        $labs = Lab::where('is_active', true)->orderBy('name')->get();
        $patients = User::whereHas('roles', function($q) {
            $q->where('name', 'patient');
        })->orderBy('first_name')->orderBy('last_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('first_name')->orderBy('last_name')->get();
        
        return view('laboratory::lab-orders.edit', compact('labOrder', 'clinics', 'labs', 'patients', 'doctors'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
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
        $labs = Lab::where('clinic_id', $clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($labs);
    }

    public function getCategoriesByLab($lab_id)
    {
        try {
            // Get all services for this lab with their categories
            $services = LabService::where('lab_id', $lab_id)
                ->where('is_active', true)
                ->whereNotNull('category_id')
                ->with('category')
                ->get();
            
            if ($services->isEmpty()) {
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
            
            return response()->json($categories);
            
        } catch (\Exception $e) {
            \Log::error('Error loading categories for lab ' . $lab_id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load categories'], 500);
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
        $doctors = User::whereHas('roles', function($q) {
                $q->where('name', 'doctor');
            })
            ->where('clinic_id', $clinic_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($doctors);
    }

    public function getPatientsByDoctor($doctor_id)
    {
        $patients = User::whereHas('roles', function($q) {
                $q->where('name', 'patient');
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($patients);
    }

    public function getAllDoctors()
    {
        $doctors = User::whereHas('roles', function($q) {
                $q->where('name', 'doctor');
            })
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($doctors);
    }

    public function getAllPatients()
    {
        $patients = User::whereHas('roles', function($q) {
                $q->where('name', 'patient');
            })
            ->where('status', 1)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
            
        return response()->json($patients);
    }
}
