<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabOrder;
use Modules\Laboratory\Models\LabOrderItem;
use Modules\Laboratory\Models\Lab;
use Modules\Laboratory\Models\LabTest;
use Modules\Clinic\Models\Clinics;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class LabOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_lab_orders', ['only' => ['index', 'index_data', 'show']]);
        $this->middleware('permission:create_lab_orders', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_orders', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_orders', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::lab-orders.index');
    }

    public function index_data(Request $request)
    {
        $query = LabOrder::with(['clinic', 'lab', 'patient', 'doctor']);

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('patient_name', function($row) {
                return $row->patient ? $row->patient->full_name : '-';
            })
            ->addColumn('doctor_name', function($row) {
                return $row->doctor ? $row->doctor->full_name : '-';
            })
            ->addColumn('clinic_name', function($row) {
                return $row->clinic ? $row->clinic->name : '-';
            })
            ->addColumn('lab_name', function($row) {
                return $row->lab ? $row->lab->name : '-';
            })
            ->addColumn('status_badge', function($row) {
                $badges = [
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'in_progress' => 'primary',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
                $class = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-'.$class.'">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
            })
            ->addColumn('amount', function($row) {
                return number_format($row->final_amount, 2);
            })
            ->addColumn('action', function($row) {
                $showUrl = route('backend.lab-orders.show', $row->id);
                $editUrl = route('backend.lab-orders.edit', $row->id);
                $deleteUrl = route('backend.lab-orders.destroy', $row->id);
                
                return '<div class="btn-group">
                    <a href="'.$showUrl.'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    <a href="'.$editUrl.'" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="'.$deleteUrl.'"><i class="fas fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $clinics = Clinics::where('is_active', true)->orderBy('name')->get();
        $labs = Lab::where('is_active', true)->orderBy('name')->get();
        $patients = User::whereHas('roles', function($q) {
            $q->where('name', 'patient');
        })->orderBy('first_name')->orderBy('last_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('first_name')->orderBy('last_name')->get();
        
        return view('laboratory::lab-orders.create', compact('clinics', 'labs', 'patients', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'lab_id' => 'required|exists:labs,id',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'encounter_id' => 'nullable|integer',
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
            $labOrder->encounter_id = $validated['encounter_id'] ?? null;
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
            
            return redirect()->route('backend.lab-orders.index')
                ->with('success', 'Lab order created successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating lab order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $labOrder = LabOrder::with(['clinic', 'lab', 'patient', 'doctor', 'labOrderItems.labTest'])->findOrFail($id);
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
        $labOrder = LabOrder::findOrFail($id);

        $validated = $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'lab_id' => 'required|exists:labs,id',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'encounter_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'collection_type' => 'required|in:clinic,home',
            'collection_notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        $validated['updated_by'] = auth()->id();
        
        if ($validated['status'] === 'confirmed' && $labOrder->status !== 'confirmed') {
            $validated['confirmed_date'] = now();
        }
        
        if ($validated['status'] === 'completed' && $labOrder->status !== 'completed') {
            $validated['completed_date'] = now();
        }
        
        $labOrder->update($validated);

        return redirect()->route('backend.lab-orders.index')
            ->with('success', 'Lab order updated successfully');
    }

    public function destroy($id)
    {
        $labOrder = LabOrder::findOrFail($id);
        
        if ($labOrder->status === 'in_progress' || $labOrder->status === 'completed') {
            return response()->json(['message' => 'Cannot delete order that is in progress or completed'], 400);
        }
        
        $labOrder->deleted_by = auth()->id();
        $labOrder->save();
        $labOrder->delete();

        return response()->json(['message' => 'Lab order deleted successfully']);
    }

    public function getLabTests($lab_id)
    {
        $labTests = LabTest::where('lab_id', $lab_id)
                           ->where('is_active', true)
                           ->orderBy('test_name')
                           ->get(['id', 'test_name', 'price', 'category_id']);

        return response()->json($labTests);
    }
}
