<?php

namespace Modules\Laboratory\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabOrder;
use Modules\Laboratory\Models\LabOrderItem;
use Modules\Laboratory\Models\Lab;
use Modules\Laboratory\Models\LabTest;
use Modules\Laboratory\Models\LabTestCategory;
use Modules\Clinic\Models\Clinics;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LabTestOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:order_lab_tests');
    }

    /**
     * Show lab test ordering form for doctor encounter
     */
    public function create($encounter_id)
    {
        $clinics = Clinics::where('is_active', true)->orderBy('name')->get();
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();
        
        return view('laboratory::lab-orders.order-from-encounter', compact('encounter_id', 'clinics', 'categories'));
    }

    /**
     * Get labs by clinic
     */
    public function getLabsByClinic($clinic_id)
    {
        $labs = Lab::where('clinic_id', $clinic_id)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get(['id', 'name', 'lab_code']);

        return response()->json($labs);
    }

    /**
     * Get tests by category and lab
     */
    public function getTestsByCategoryAndLab($category_id, $lab_id)
    {
        $query = LabTest::where('category_id', $category_id)
                       ->where('is_active', true);
        
        if ($lab_id) {
            $query->where('lab_id', $lab_id);
        }
        
        $tests = $query->orderBy('test_name')
                      ->get(['id', 'test_name', 'price', 'description', 'normal_range', 'unit_of_measurement']);

        return response()->json($tests);
    }

    /**
     * Store lab test order from encounter
     */
    public function store(Request $request, $encounter_id)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinic,id',
            'lab_id' => 'required|exists:labs,id',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'collection_type' => 'required|in:clinic,home',
            'collection_notes' => 'nullable|string',
            'tests' => 'required|array|min:1',
            'tests.*.lab_test_id' => 'required|exists:lab_tests,id',
            'tests.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $labOrder = new LabOrder();
            $labOrder->order_number = $labOrder->generateOrderNumber();
            $labOrder->clinic_id = $validated['clinic_id'];
            $labOrder->lab_id = $validated['lab_id'];
            $labOrder->patient_id = $validated['patient_id'];
            $labOrder->doctor_id = $validated['doctor_id'];
            $labOrder->encounter_id = $encounter_id;
            $labOrder->notes = $validated['notes'] ?? null;
            $labOrder->collection_type = $validated['collection_type'];
            $labOrder->collection_notes = $validated['collection_notes'] ?? null;
            $labOrder->order_date = now();
            $labOrder->status = 'pending';
            $labOrder->created_by = auth()->id();
            
            $totalAmount = 0;
            $discountAmount = 0;
            
            $labOrder->save();
            
            foreach ($validated['tests'] as $test) {
                $labTest = LabTest::find($test['lab_test_id']);
                
                $orderItem = new LabOrderItem();
                $orderItem->lab_order_id = $labOrder->id;
                $orderItem->lab_test_id = $test['lab_test_id'];
                $orderItem->test_name = $labTest->test_name;
                $orderItem->test_description = $labTest->description;
                $orderItem->price = $test['price'];
                $orderItem->discount_amount = 0;
                $orderItem->final_price = $test['price'];
                $orderItem->status = 'pending';
                $orderItem->save();
                
                $totalAmount += $test['price'];
            }
            
            $labOrder->total_amount = $totalAmount;
            $labOrder->discount_amount = $discountAmount;
            $labOrder->final_amount = $totalAmount - $discountAmount;
            $labOrder->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lab order created successfully',
                'order_id' => $labOrder->id,
                'order_number' => $labOrder->order_number
            ]);
                
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating lab order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab orders for a specific encounter
     */
    public function getOrdersByEncounter($encounter_id)
    {
        $orders = LabOrder::with(['clinic', 'lab', 'labOrderItems.labTest'])
                         ->where('encounter_id', $encounter_id)
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json($orders);
    }

    /**
     * Show lab order details
     */
    public function show($order_id)
    {
        $labOrder = LabOrder::with([
            'clinic', 
            'lab', 
            'patient', 
            'doctor', 
            'labOrderItems.labTest',
            'labOrderItems.labResult'
        ])->findOrFail($order_id);

        return view('laboratory::lab-orders.encounter-show', compact('labOrder'));
    }
}
