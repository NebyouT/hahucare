<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabResult;
use Modules\Laboratory\Models\LabTest;
use Modules\Laboratory\Models\LabOrder;
use Modules\Laboratory\Models\Lab;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Storage;

class LabResultController extends Controller
{
    public function __construct()
    {
        // Restore permissions for proper role-based access
        $this->middleware('permission:view_lab_results', ['only' => ['index', 'index_data', 'show']]);
        $this->middleware('permission:create_lab_results', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_results', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_results', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = LabOrder::with(['patient', 'doctor', 'lab', 'clinic', 'labOrderItems'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('lab_id')) {
            $query->where('lab_id', $request->lab_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();
        $labs   = Lab::orderBy('name')->get(['id', 'name']);

        return view('laboratory::lab-results.index', compact('orders', 'labs'));
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
                return $row->patient ? $row->patient->full_name : '-';
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
        })->orderBy('first_name')->orderBy('last_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('first_name')->orderBy('last_name')->get();
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'lab_technician');
        })->orderBy('first_name')->orderBy('last_name')->get();
        
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
        })->orderBy('first_name')->orderBy('last_name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->orderBy('first_name')->orderBy('last_name')->get();
        $technicians = User::whereHas('roles', function($q) {
            $q->where('name', 'lab_technician');
        })->orderBy('first_name')->orderBy('last_name')->get();
        
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
            'description' => 'nullable|string|max:255',
        ]);

        $labResult = LabResult::findOrFail($id);
        
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('lab_result_attachments', $fileName, 'public');
            
            $attachment = new \Modules\Laboratory\Models\LabResultAttachment();
            $attachment->lab_result_id = $labResult->id;
            $attachment->file_name = $file->getClientOriginalName();
            $attachment->file_path = $filePath;
            $attachment->file_type = $file->getMimeType();
            $attachment->file_size = $file->getSize();
            $attachment->description = $request->description ?? null;
            $attachment->save();
            
            return response()->json([
                'message' => 'Attachment uploaded successfully',
                'attachment' => $attachment
            ]);
        }
        
        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function removeAttachment($attachment_id)
    {
        $attachment = \Modules\Laboratory\Models\LabResultAttachment::findOrFail($attachment_id);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $attachment->delete();
        
        return response()->json(['message' => 'Attachment removed successfully']);
    }
}
