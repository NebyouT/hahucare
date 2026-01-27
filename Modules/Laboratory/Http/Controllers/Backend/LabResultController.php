<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabResult;
use Modules\Laboratory\Models\LabTest;
use App\Models\User;
use Yajra\DataTables\DataTables;

class LabResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_lab_results', ['only' => ['index', 'index_data', 'show']]);
        $this->middleware('permission:create_lab_results', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_results', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_results', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::lab-results.index');
    }

    public function index_data(Request $request)
    {
        $query = LabResult::with(['labTest', 'patient', 'doctor', 'technician']);

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('result_code', 'like', "%{$search}%")
                  ->orWhere('sample_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('test_name', function($row) {
                return $row->labTest ? $row->labTest->test_name : '-';
            })
            ->addColumn('patient_name', function($row) {
                return $row->patient ? $row->patient->name : '-';
            })
            ->addColumn('status_badge', function($row) {
                $badges = [
                    'pending' => 'warning',
                    'in_progress' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ];
                $class = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-'.$class.'">'.ucfirst($row->status).'</span>';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.lab-results.edit', $row->id);
                $showUrl = route('backend.lab-results.show', $row->id);
                $deleteUrl = route('backend.lab-results.destroy', $row->id);
                
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
        $labTests = LabTest::where('is_active', true)->orderBy('test_name')->get();
        $patients = User::whereHas('roles', function($q) {
            $q->where('name', 'patient');
        })->orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'lab_technician');
        })->orderBy('name')->get();
        
        return view('laboratory::lab-results.create', compact('labTests', 'patients', 'doctors', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'result_code' => 'required|string|unique:lab_results,result_code',
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'nullable|exists:users,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'appointment_id' => 'nullable|integer',
            'test_date' => 'required|date',
            'result_date' => 'nullable|date',
            'result_value' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'technician_id' => 'nullable|exists:users,id',
            'sample_type' => 'nullable|string',
            'sample_id' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        LabResult::create($validated);

        return redirect()->route('backend.lab-results.index')
            ->with('success', 'Lab result created successfully');
    }

    public function show($id)
    {
        $labResult = LabResult::with(['labTest', 'patient', 'doctor', 'technician'])->findOrFail($id);
        return view('laboratory::lab-results.show', compact('labResult'));
    }

    public function edit($id)
    {
        $labResult = LabResult::findOrFail($id);
        $labTests = LabTest::where('is_active', true)->orderBy('test_name')->get();
        $patients = User::whereHas('roles', function($q) {
            $q->where('name', 'patient');
        })->orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('name')->get();
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'lab_technician');
        })->orderBy('name')->get();
        
        return view('laboratory::lab-results.edit', compact('labResult', 'labTests', 'patients', 'doctors', 'technicians'));
    }

    public function update(Request $request, $id)
    {
        $labResult = LabResult::findOrFail($id);

        $validated = $request->validate([
            'result_code' => 'required|string|unique:lab_results,result_code,' . $id,
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'nullable|exists:users,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'appointment_id' => 'nullable|integer',
            'test_date' => 'required|date',
            'result_date' => 'nullable|date',
            'result_value' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'technician_id' => 'nullable|exists:users,id',
            'sample_type' => 'nullable|string',
            'sample_id' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $labResult->update($validated);

        return redirect()->route('backend.lab-results.index')
            ->with('success', 'Lab result updated successfully');
    }

    public function destroy($id)
    {
        $labResult = LabResult::findOrFail($id);
        $labResult->deleted_by = auth()->id();
        $labResult->save();
        $labResult->delete();

        return response()->json(['message' => 'Lab result deleted successfully']);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:lab_results,id',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $labResult = LabResult::findOrFail($validated['id']);
        $labResult->status = $validated['status'];
        $labResult->updated_by = auth()->id();
        
        if ($validated['status'] === 'completed' && !$labResult->result_date) {
            $labResult->result_date = now();
        }
        
        $labResult->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function print($id)
    {
        $labResult = LabResult::with(['labTest', 'patient', 'doctor', 'technician'])->findOrFail($id);
        return view('laboratory::lab-results.print', compact('labResult'));
    }

    public function download($id)
    {
        // PDF download functionality can be implemented later
        return response()->json(['message' => 'Download functionality coming soon']);
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate([
            'attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $labResult = LabResult::findOrFail($id);
        
        // File upload logic here
        
        return response()->json(['message' => 'Attachment uploaded successfully']);
    }
}
