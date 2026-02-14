<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabTest;
use Modules\Laboratory\Models\LabTestCategory;
use Yajra\DataTables\DataTables;

class LabTestController extends Controller
{
    public function __construct()
    {
        // Restore permissions for proper role-based access
        $this->middleware('permission:view_lab_tests', ['only' => ['index', 'index_data']]);
        $this->middleware('permission:create_lab_tests', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_tests', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_tests', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::lab-tests.index');
    }

    public function index_data(Request $request)
    {
        $query = LabTest::with('category');

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhere('test_code', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function($row) {
                return $row->category ? $row->category->name : '-';
            })
            ->addColumn('status', function($row) {
                $checked = $row->is_active ? 'checked' : '';
                return '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="'.$row->id.'" '.$checked.'>
                </div>';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.lab-tests.edit', $row->id);
                $deleteUrl = route('backend.lab-tests.destroy', $row->id);
                
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
        return view('laboratory::lab-tests.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'test_code' => 'required|string|unique:lab_tests,test_code',
            'test_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:lab_test_categories,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'unit_of_measurement' => 'nullable|string',
            'sample_type' => 'nullable|string',
            'reporting_time' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        LabTest::create($validated);

        return redirect()->route('backend.lab-tests.index')
            ->with('success', 'Lab test created successfully');
    }

    public function edit($id)
    {
        $labTest = LabTest::findOrFail($id);
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();
        return view('laboratory::lab-tests.edit', compact('labTest', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $labTest = LabTest::findOrFail($id);

        $validated = $request->validate([
            'test_code' => 'required|string|unique:lab_tests,test_code,' . $id,
            'test_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:lab_test_categories,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'preparation_instructions' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'unit_of_measurement' => 'nullable|string',
            'sample_type' => 'nullable|string',
            'reporting_time' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['updated_by'] = auth()->id();
        $labTest->update($validated);

        return redirect()->route('backend.lab-tests.index')
            ->with('success', 'Lab test updated successfully');
    }

    public function destroy($id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->deleted_by = auth()->id();
        $labTest->save();
        $labTest->delete();

        return response()->json(['message' => 'Lab test deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->is_active = $request->status;
        $labTest->updated_by = auth()->id();
        $labTest->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            LabTest::whereIn('id', $ids)->update(['deleted_by' => auth()->id()]);
            LabTest::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected tests deleted successfully']);
        }

        if ($action === 'activate') {
            LabTest::whereIn('id', $ids)->update(['is_active' => true, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected tests activated successfully']);
        }

        if ($action === 'deactivate') {
            LabTest::whereIn('id', $ids)->update(['is_active' => false, 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected tests deactivated successfully']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }

    public function export(Request $request)
    {
        // Export functionality can be implemented later
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    public function getTestsByCategory($category_id)
    {
        $tests = LabTest::with('category', 'lab')
            ->where('is_active', true)
            ->when($category_id, function($query, $category_id) {
                return $query->where('category_id', $category_id);
            })
            ->get();

        return response()->json([
            'tests' => $tests->map(function($test) {
                return [
                    'id' => $test->id,
                    'test_name' => $test->test_name,
                    'description' => $test->description,
                    'price' => $test->price,
                    'final_price' => $test->final_price,
                    'duration_minutes' => $test->duration_minutes,
                    'category' => $test->category ? $test->category->name : null,
                    'lab' => $test->lab ? $test->lab->name : null,
                ];
            })
        ]);
    }
}
