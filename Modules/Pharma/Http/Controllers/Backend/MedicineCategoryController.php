<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\MedicineCategory;
use Modules\Pharma\Http\Requests\MedicineCategoryRequest;
use Modules\Pharma\Exports\MedicineCategoryExport;

class MedicineCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected string $exportClass = '\Modules\Pharma\Exports\MedicineCategoryExport';

    public function __construct()
    {
        $this->module_title = 'pharma::messages.medicine_category';
        $this->edit_module_title = 'pharma::messages.edit_medicine_category';
        $this->module_name = 'medicine_categories';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'edit_module_title' => $this->edit_module_title,
        ]);
    }


    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('pharma::messages.singular_title');

        $filter = [
            'status' => $request->status,
        ];

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('messages.name'),
            ],
            [
                'value' => 'status',
                'text' => __('messages.status'),
            ],
        ];

        $export_url = route('backend.medicine-category.export');

        return view('pharma::medicine_category.index_datatable', compact(
            'filter',
            'export_import',
            'export_columns',
            'export_url'
        ));
    }

    public function index_data(Request $request)
    {
        $query = MedicineCategory::query();

        if ($request->has('filter') && $request->filter['column_status'] != '') {
            $query->where('status', $request->filter['column_status']);
        }

        if ($request->has('search') && $request->search['value'] != '') {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search['value'] . '%');
            });
        }

        return datatables()->of($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';

            })
            ->editColumn('status', function ($row) {
                    $checked = '';
                    if ($row->status) {
                        $checked = 'checked="checked"';
                    }

                    return '
                        <div class="form-check form-switch ">
                            <input type="checkbox" data-url="'.route('backend.medicine-category.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input"  id="datatable-row-'.$row->id.'"  name="status" value="'.$row->id.'" '.$checked.'>
                        </div>
                    ';
                })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('backend.medicine-category.edit', $row->id) . '">
                            <button type="button" class="btn text-success p-0 fs-5" data-bs-toggle="tooltip" aria-label="' . __('pharma::messages.edit') . '" data-bs-original-title="' . __('pharma::messages.edit') . '">
                                <i class="ph ph-pencil-simple-line align-middle"></i>
                            </button>
                        </a>';
            })

            ->rawColumns(['check', 'action','status'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $isEdit = false;
        $medicineCategory = new MedicineCategory();
        return view('pharma::medicine_category.create', compact('medicineCategory','isEdit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineCategoryRequest $request)
    {
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;
        MedicineCategory::create($data);

        return redirect()->route('backend.medicine-category.index')->with('success', __('pharma::messages.category_created_successfully'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $isEdit = true;
        $medicineCategory = MedicineCategory::findOrFail($id);
        return view('pharma::medicine_category.create', compact('medicineCategory','isEdit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicineCategoryRequest $request, $id)
    {
        $medicineCategory = MedicineCategory::findOrFail($id);
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;

        $medicineCategory->update($data);

        return redirect()->route('backend.medicine-category.index')->with('success', __('pharma::messages.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $clinic = MedicineCategory::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('pharma::messages.status_update_message');
                break;
            case 'delete':
                MedicineCategory::whereIn('id', $ids)->delete();
                $message = __('messages.deleted_successfully');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
    public function update_status(Request $request, MedicineCategory $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('pharma::messages.status_update_message')]);
    }
}
