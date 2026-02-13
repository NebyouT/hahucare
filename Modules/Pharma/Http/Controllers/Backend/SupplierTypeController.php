<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\SupplierType;
use Modules\Pharma\Http\Requests\SupplierTypeRequest;
use Modules\Pharma\Exports\SupplierTypeExport;
use Maatwebsite\Excel\Facades\Excel;

class SupplierTypeController extends Controller
{
    protected string $exportClass = '\Modules\Pharma\Exports\SupplierTypeExport';

    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Supplier Type';
        $this->add_module_title = 'Add Supplier Type';
        $this->edit_module_title = 'Edit Supplier Type';
        // module name
        $this->module_name = 'supplier-types';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'add_module_title' => $this->add_module_title,
            'edit_module_title' => $this->edit_module_title,
        ]);
    }


    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('pharma::messages.supplier_type');

        $filter = [
            'status' => $request->status,
        ];

        $export_import = true;
        $export_columns = [
            [
                'value' => 'name',
                'text' => __('pharma::messages.name'),
            ],

        ];

        $export_columns[] = [
            'value' => 'status',
            'text' => __('pharma::messages.status'),
        ];

        $export_url = route('backend.supplier-type.export');

        return view('pharma::supplier_type.index_datatable', compact('filter', 'module_title', 'export_import', 'export_columns', 'export_url'));
    }

    public function index_data(Request $request)
    {
        $query = SupplierType::query();

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
                            <input type="checkbox" data-url="' . route('backend.supplier-type.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $row->id . '"  name="status" value="' . $row->id . '" ' . $checked . '>
                        </div>
                    ';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('backend.supplier-type.edit', $row->id) . '">
                            <button type="button" class="btn text-success p-0 fs-5" data-bs-toggle="tooltip" aria-label="Edit" data-bs-original-title="Edit">
                                <i class="ph ph-pencil-simple-line align-middle"></i>
                            </button>
                        </a>';
            })

            ->rawColumns(['check', 'action', 'status']) // Ensure HTML is rendered
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $isEdit = false;
        $supplierType = new SupplierType();
        return view('pharma::supplier_type.create', compact('supplierType', 'isEdit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierTypeRequest $request)
    {
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;
        SupplierType::create($data);

        return redirect()->route('backend.supplier-type.index')->with('success', __('pharma::messages.created_successfully'));
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
        $supplierType = SupplierType::findOrFail($id); // Fetch the record for edit
        return view('pharma::supplier_type.create', compact('supplierType', 'isEdit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierTypeRequest $request, $id)
    {
        $supplierType = SupplierType::findOrFail($id);
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;

        $supplierType->update($data);

        return redirect()->route('backend.supplier-type.index')->with('success', __('pharma::messages.updated_successfully'));
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
                SupplierType::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('pharma::messages.status_update_message');
                break;

            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json([
                        'status' => false,
                        'message' => __('messages.permission_denied'),
                    ], 200);
                }

                SupplierType::whereIn('id', $ids)->delete();
                $message = __('pharma::messages.supplier_type_delete');
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => __('service_providers.invalid_action')
                ]);
        }

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function update_status(Request $request, SupplierType $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('pharma::messages.status_update_message')]);
    }
}
