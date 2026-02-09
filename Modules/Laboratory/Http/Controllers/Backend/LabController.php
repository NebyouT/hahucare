<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\Lab;
use Modules\Clinic\Models\Clinics;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class LabController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_labs', ['only' => ['index', 'index_data']]);
        $this->middleware('permission:create_labs', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_labs', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_labs', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::labs.index');
    }

    public function index_data(Request $request)
    {
        $query = Lab::with(['clinic']);

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lab_code', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('clinic_name', function($row) {
                return $row->clinic ? $row->clinic->name : '-';
            })
            ->addColumn('status', function($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="'.$row->id.'" '.$checked.'>
                </div>';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.labs.edit', $row->id);
                $deleteUrl = route('backend.labs.destroy', $row->id);
                
                return '<div class="btn-group">
                    <a href="'.$editUrl.'" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="'.$deleteUrl.'"><i class="fas fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $clinics = Clinics::where('is_active', true)->orderBy('name')->get();
        return view('laboratory::labs.create', compact('clinics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'clinic_id' => 'required|exists:clinics,id',
            'lab_code' => 'required|string|unique:labs,lab_code',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'operating_hours' => 'nullable|array',
            'time_slot_duration' => 'nullable|integer|min:15',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();
        
        Lab::create($validated);

        return redirect()->route('backend.labs.index')
            ->with('success', 'Lab created successfully');
    }

    public function edit($id)
    {
        $lab = Lab::findOrFail($id);
        $clinics = Clinics::where('is_active', true)->orderBy('name')->get();
        return view('laboratory::labs.edit', compact('lab', 'clinics'));
    }

    public function update(Request $request, $id)
    {
        $lab = Lab::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'clinic_id' => 'required|exists:clinics,id',
            'lab_code' => 'required|string|unique:labs,lab_code,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'operating_hours' => 'nullable|array',
            'time_slot_duration' => 'nullable|integer|min:15',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['updated_by'] = auth()->id();
        
        $lab->update($validated);

        return redirect()->route('backend.labs.index')
            ->with('success', 'Lab updated successfully');
    }

    public function destroy($id)
    {
        $lab = Lab::findOrFail($id);
        
        if ($lab->labOrders()->count() > 0) {
            return response()->json(['message' => 'Cannot delete lab with associated orders'], 400);
        }
        
        $lab->deleted_by = auth()->id();
        $lab->save();
        $lab->delete();

        return response()->json(['message' => 'Lab deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $lab = Lab::findOrFail($id);
        $lab->is_active = $request->status;
        $lab->updated_by = auth()->id();
        $lab->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function getLabsByClinic($clinic_id)
    {
        $labs = Lab::where('clinic_id', $clinic_id)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get(['id', 'name', 'lab_code']);

        return response()->json($labs);
    }
}
