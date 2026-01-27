<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabTestCategory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class LabCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_lab_categories', ['only' => ['index', 'index_data']]);
        $this->middleware('permission:create_lab_categories', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_categories', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_categories', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::lab-categories.index');
    }

    public function index_data(Request $request)
    {
        $query = LabTestCategory::withCount('labTests');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where('name', 'like', "%{$search}%");
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tests_count', function($row) {
                return $row->lab_tests_count;
            })
            ->addColumn('status', function($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="'.$row->id.'" '.$checked.'>
                </div>';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.lab-categories.edit', $row->id);
                $deleteUrl = route('backend.lab-categories.destroy', $row->id);
                
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
        return view('laboratory::lab-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();
        
        LabTestCategory::create($validated);

        return redirect()->route('backend.lab-categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit($id)
    {
        $category = LabTestCategory::findOrFail($id);
        return view('laboratory::lab-categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = LabTestCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['updated_by'] = auth()->id();
        
        $category->update($validated);

        return redirect()->route('backend.lab-categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = LabTestCategory::findOrFail($id);
        
        if ($category->labTests()->count() > 0) {
            return response()->json(['message' => 'Cannot delete category with associated tests'], 400);
        }
        
        $category->deleted_by = auth()->id();
        $category->save();
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $category = LabTestCategory::findOrFail($id);
        $category->is_active = $request->status;
        $category->updated_by = auth()->id();
        $category->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            LabTestCategory::whereIn('id', $ids)->update(['deleted_by' => auth()->id()]);
            LabTestCategory::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected categories deleted successfully']);
        }

        if ($action === 'activate') {
            LabTestCategory::whereIn('id', $ids)->update(['is_active' => true, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected categories activated successfully']);
        }

        if ($action === 'deactivate') {
            LabTestCategory::whereIn('id', $ids)->update(['is_active' => false, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected categories deactivated successfully']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }
}
