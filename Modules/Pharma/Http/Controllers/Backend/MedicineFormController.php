<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Http\Requests\MedicineFormRequest;
use Modules\Pharma\Exports\MedicineFormExport;

class MedicineFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected string $exportClass = '\Modules\Pharma\Exports\MedicineFormExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'pharma::messages.medicine_form';
        $this->edit_module_title = 'pharma::messages.edit_medicine_form';
        // module name
        $this->module_name = 'medicine_forms';

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

        $module_title = __('pharma::messages.medicine_form');

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

        $export_url = route('backend.medicine-form.export');

        return view('pharma::medicine_form.index_datatable', compact(
            'filter',
            'module_title',
            'export_import',
            'export_columns',
            'export_url'
        ));
    }

    public function index_data(Request $request)
    {
        $query = MedicineForm::query();

        // Filter by status
        if ($request->has('filter') && $request->filter['column_status'] != '') {
            $query->where('status', $request->filter['column_status']);
        }

        // Search
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
                            <input type="checkbox" data-url="'.route('backend.medicine-form.update_status', $row->id).'" data-token="'.csrf_token().'" class="switch-status-change form-check-input"  id="datatable-row-'.$row->id.'"  name="status" value="'.$row->id.'" '.$checked.'>
                        </div>
                    ';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('backend.medicine-form.edit', $row->id) . '">
                                <button type="button" class="btn text-success p-0 fs-5" data-bs-toggle="tooltip" aria-label="' . __('pharma::messages.edit') . '" data-bs-original-title="' . __('pharma::messages.edit') . '">
                                    <i class="ph ph-pencil-simple-line align-middle"></i>
                                </button>
                            </a>';
                })


            ->rawColumns(['check', 'action','status']) // Ensure HTML is rendered
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicineForm = new MedicineForm();
        $isEdit       = false;
        $module_title = __('pharma::messages.medicine_form');
        return view('pharma::medicine_form.create', compact('medicineForm','isEdit','module_title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineFormRequest $request)
    {
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;
        MedicineForm::create($data);

        return redirect()->route('backend.medicine-form.index')->with('success', __('pharma::messages.created_successfully'));
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
            $isEdit       = true;
        $medicineForm = MedicineForm::findOrFail($id); // Fetch the record for edit
        return view('pharma::medicine_form.create', compact('medicineForm','isEdit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicineFormRequest $request, $id)
    {
        $medicineForm = MedicineForm::findOrFail($id);
        $data = $request->all();
        $data['status'] = $request->status ? 1 : 0;

        $medicineForm->update($data);

        return redirect()->route('backend.medicine-form.index')->with('success', __('pharma::messages.updated_successfully'));
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
                $clinic = MedicineForm::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('pharma::messages.status_update_message');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
    public function update_status(Request $request, MedicineForm $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('pharma::messages.status_update_message')]);
    }
}
