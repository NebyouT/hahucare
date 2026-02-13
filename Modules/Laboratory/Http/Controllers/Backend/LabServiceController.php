<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabService;
use Modules\Laboratory\Models\LabTestCategory;
use Modules\Laboratory\Models\Lab;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class LabServiceController extends Controller
{
    public function __construct()
    {
        // Temporarily disabled permissions for debugging
        // $this->middleware('permission:view_lab_services', ['only' => ['index', 'index_data']]);
        // $this->middleware('permission:create_lab_services', ['only' => ['create', 'store']]);
        // $this->middleware('permission:edit_lab_services', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:delete_lab_services', ['only' => ['destroy']]);
    }

    public function index()
    {
        try {
            $serviceCount = LabService::count();
            // Get all services with relationships
            $allServices = LabService::with(['category', 'lab'])->get();
            return view('laboratory::lab-services.index', compact('serviceCount', 'allServices'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error in index method: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function index_data(Request $request)
    {
        $query = LabService::with(['category', 'lab']);

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function($row) {
                return $row->category ? $row->category->name : 'N/A';
            })
            ->addColumn('lab_name', function($row) {
                return $row->lab ? $row->lab->name : 'N/A';
            })
            ->addColumn('price', function($row) {
                return '$' . number_format($row->price, 2);
            })
            ->addColumn('status', function($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="'.$row->id.'" '.$checked.'>
                </div>';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.lab-services.edit', $row->id);
                $deleteUrl = route('backend.lab-services.destroy', $row->id);
                
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
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();
        $labs = Lab::where('is_active', true)->orderBy('name')->get();
        return view('laboratory::lab-services.create', compact('categories', 'labs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:lab_test_categories,id',
            'lab_id' => 'required|exists:labs,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        
        while (LabService::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['created_by'] = auth()->id();

        LabService::create($validated);

        return redirect()->route('backend.lab-services.index')
            ->with('success', 'Lab Service created successfully');
    }

    public function edit($id)
    {
        $service = LabService::findOrFail($id);
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();
        $labs = Lab::where('is_active', true)->orderBy('name')->get();
        return view('laboratory::lab-services.edit', compact('service', 'categories', 'labs'));
    }

    public function update(Request $request, $id)
    {
        $service = LabService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:lab_test_categories,id',
            'lab_id' => 'required|exists:labs,id',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate unique slug (excluding current service)
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        
        while (LabService::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['updated_by'] = auth()->id();
        
        $service->update($validated);

        return redirect()->route('backend.lab-services.index')
            ->with('success', 'Lab Service updated successfully');
    }

    public function destroy($id)
    {
        $service = LabService::findOrFail($id);
        
        $service->deleted_by = auth()->id();
        $service->save();
        $service->delete();

        return response()->json(['message' => 'Lab Service deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $service = LabService::findOrFail($id);
        $service->is_active = $request->status;
        $service->updated_by = auth()->id();
        $service->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            LabService::whereIn('id', $ids)->update(['deleted_by' => auth()->id()]);
            LabService::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected services deleted successfully']);
        }

        if ($action === 'activate') {
            LabService::whereIn('id', $ids)->update(['is_active' => true, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected services activated successfully']);
        }

        if ($action === 'deactivate') {
            LabService::whereIn('id', $ids)->update(['is_active' => false, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected services deactivated successfully']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }
}
