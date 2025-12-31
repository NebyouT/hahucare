<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Http\Requests\SupplierRequest;
use Modules\Pharma\Models\Supplier;
use Modules\Pharma\Models\SupplierType;
use Modules\Pharma\Traits\PharmaOwnershipChecker;
use Yajra\DataTables\DataTables;
use Modules\Pharma\Exports\SupplierExport;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    use PharmaOwnershipChecker;

    protected string $exportClass = '\Modules\Pharma\Exports\SupplierExport';

    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        // Page Title
        $this->module_title = __('messages.add_supplier');
        $this->edit_module_title = __('messages.edit_supplier');

        // module name
        $this->module_name = 'suppliers';

        view()->share([
            'module_title' => $this->module_title,
            'edit_module_title' => $this->edit_module_title,
            'module_name'  => $this->module_name,
        ]);
        $this->middleware('check.permission:view_suppliers')->only(['index', 'show']);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $user          = auth()->user();

        $module_title = __('pharma::messages.supplier');

        $filter = [
            'status' => $request->status,
        ];

        $export_import = true;
        $export_columns = [
            [
                'value' => 'first_name',
                'text' => __('pharma::messages.first_name'),
            ],
            [
                'value' => 'last_name',
                'text' => __('pharma::messages.last_name'),
            ],
            [
                'value' => 'email',
                'text' => __('pharma::messages.email'),
            ],
            [
                'value' => 'contact_number',
                'text' => __('pharma::messages.contact_number'),
            ],
            [
                'value' => 'supplier_type',
                'text' => __('pharma::messages.supplier_type'),
            ],
            [
                'value' => 'pharma',
                'text' => __('pharma::messages.pharma'),
            ],
            [
                'value' => 'payment_terms',
                'text' => __('pharma::messages.payment_terms'),
            ],
        ];

        if (multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))) {
            $export_columns[] = [
                'value' => 'pharma_id',
                'text' => __('multivendor.singular_title'),
            ];
        }

        $export_columns[] = [
            'value' => 'status',
            'text' => __('pharma::messages.status'),
        ];

        $export_url = route('backend.suppliers.export');

        return view('pharma::supplier.index_datatable', compact('filter', 'module_title', 'export_import', 'export_columns', 'export_url'));
    }
    public function index_data(Datatables $datatable, Request $request)
    {
        $query = Supplier::setRole(auth()->user())->with('supplierType', 'pharmaUser');

        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['supplier_name'])) {
                $query->where('id', $filter['supplier_name']);
            }
            if (isset($filter['supplier_type'])) {
                $query->where('supplier_type_id', $filter['supplier_type']);
            }
            if (isset($filter['contact_number'])) {
                $query->where('contact_number', 'LIKE', '%' . $filter['contact_number'] . '%');
            }
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
            if (isset($filter['pharma_id'])) {
                $query->where('pharma_id', 'LIKE', '%' . $filter['pharma_id'] . '%');
            }
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', "%{$searchValue}%")
                    ->orWhere('last_name', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('payment_terms', 'like', "%{$searchValue}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$searchValue}%");
            });
        }

        return $datatable->eloquent($query)
             ->addColumn('supplier_name', function ($row) {
                $supplier_image = $row->supplier_image ?? asset('img/default.webp');
                $full_name = $row->full_name ?? ($row->first_name . ' ' . $row->last_name);
                $email = $row->email ?? '--';
                $data = $row;
                return view('pharma::supplier.user_id', compact('data'))->render();
            })
            ->orderColumn('supplier_name', function ($query, $order) {
                $query->orderBy('suppliers.first_name', $order)
                    ->orderBy('suppliers.last_name', $order);
            })
            ->editColumn('supplierType.name', function ($row) {
                return $row->supplierType ? $row->supplierType->name : '-';
            })
            ->orderColumn('supplierType.name', function ($query, $order) {
                $query->leftJoin('supplier_types as supplier_types_order', 'suppliers.supplier_type_id', '=', 'supplier_types_order.id')
                    ->orderBy('supplier_types_order.name', $order)
                    ->select('suppliers.*');
            })

            ->addColumn('pharma_id', function ($row) {
                return $row->pharmaUser ? $row->pharmaUser->fullname : '-';
            })
            ->orderColumn('pharma_id', function ($query, $order) {
                $query->leftJoin('users as pharma', 'suppliers.pharma_id', '=', 'pharma.id')
                    ->orderBy('pharma.first_name', $order)
                    ->select('suppliers.*');
            })

            ->addColumn('payment_terms', function ($row) {
                return $row->payment_terms;
            })
            ->orderColumn('payment_terms', function ($query, $order) {
                $query->orderBy('payment_terms', $order);
            })

            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked="checked"' : '';
                return '
                <div class="form-check form-switch">
                    <input type="checkbox" data-url="' . route('backend.suppliers.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
                </div>
            ';
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })

            ->orderColumn('contact_number', function ($query, $order) {
                $query->orderBy('contact_number', $order);
            })

            ->orderColumn('email', function ($query, $order) {
                $query->orderBy('email', $order);
            })

            ->filterColumn('supplier_name', function ($query, $keyword) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('supplierType.name', function ($query, $keyword) {
                $query->whereHas('supplierType', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('payment_terms', function ($query, $keyword) {
                $query->where('payment_terms', 'like', "%{$keyword}%");
            })
            ->filterColumn('email', function ($query, $keyword) {
                $query->where('email', 'like', "%{$keyword}%");
            })

            ->addColumn('supplier_info', function ($row) {
                $img = $row->supplier_image ?? asset('img/default.webp');
                $name = $row->full_name;
                $email = $row->email;
                return '<div class="d-flex align-items-center gap-2">'
                    . '<img src="' . $img . '" class="avatar avatar-40 avatar-rounded me-2" alt="Supplier Image">'
                    . '<div>'
                    . '<div class="fw-bold">' . $name . '</div>'
                    . '<div class="text-muted small">' . $email . '</div>'
                    . '</div>'
                    . '</div>';
            })
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($data) {
                return view('pharma::supplier.action_column', compact('data'));
            })
            ->rawColumns(['check', 'action', 'status', 'supplier_info','supplier_name'])
            ->addIndexColumn()
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->canCreateSupplier();
        $isEdit = false;
        $supplier = new Supplier();
        $supplierTypes = SupplierType::get();
        $pharmaList = User::where('user_type', 'pharma')->get();
        $imageUrl = getSingleMedia($supplier, 'supplier_image');
        return view('pharma::supplier.create', compact('supplier', 'supplierTypes', 'pharmaList', 'isEdit', 'imageUrl'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $data = $request->all();
        $data['supplier_type_id'] = $request->supplier_type;
        $data['pharma_id'] = auth()->user()->hasRole('pharma') ? auth()->user()->id : $request->pharma;
        $data['status'] = $request->status ? 1 : 0;
        $supplier = Supplier::create($data);
        if ($request->hasFile('supplier_image')) {
            storeMediaFile($supplier, $request->file('supplier_image'), 'supplier_image');
        }

        // Send add_supplier notification to pharma
        if ($supplier->pharma_id) {
            sendNotification([
                'notification_type' => 'add_supplier',
                'pharma_id' => $supplier->pharma_id,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->first_name . ' ' . $supplier->last_name,
                'supplier' => $supplier,
            ]);
        }
        // Redirect back to the supplier list page with a success message
        return redirect()->route('backend.suppliers.index')
            ->with('success', __('pharma::messages.supplier_created_successfully'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::with(['supplierType', 'pharmaUser.clinic'])->findOrFail($id);
        $html     = view('pharma::supplier.partials.details', compact('supplier'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->canEditSupplier();
        $isEdit = true;
        $supplier = Supplier::setRole(auth()->user())->findOrFail($id);
        if ($supplier == null) {
            abort(403, 'You are not authorized to access this supplier.');
        }
        $this->ensurePharmaOwnsSupplier($supplier);
        $supplierTypes = SupplierType::get();
        $pharmaList = User::where('user_type', 'pharma')->get();
        $imageUrl = getSingleMedia($supplier, 'supplier_image');
        view()->share('module_title', $this->edit_module_title);
        return view('pharma::supplier.create', compact('supplier', 'supplierTypes', 'pharmaList', 'isEdit', 'imageUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->all();
        $data['supplier_type_id'] = $request->supplier_type;
        $data['pharma_id'] = auth()->user()->hasRole('pharma') ? auth()->user()->id : $request->pharma;
        $data['status'] = $request->status ? 1 : 0;
        $supplier->update($data);
        if ($request->hasFile('supplier_image')) {
            storeMediaFile($supplier, $request->file('supplier_image'), 'supplier_image');
        }
        // Redirect back to the supplier list page with a success message
        return redirect()->route('backend.suppliers.index')
            ->with('success', __('pharma::messages.supplier_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->canDeleteSupplier();
        $supplier = Supplier::findOrFail($id);
        $this->ensurePharmaOwnsSupplier($supplier);

        $data = Supplier::where('id', $id)->delete();

        $message = __('pharma::messages.supplier_delete');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                $supplier = Supplier::whereIn('id', $ids)->update(['status' => $request->status]);
                $message  = __('pharma::messages.status_update_message');
                break;
            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Supplier::whereIn('id', $ids)->delete();
                $message = __('pharma::messages.supplier_delete');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
    public function update_status(Request $request, Supplier $id)
    {
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('pharma::messages.status_update_message')]);
    }

    public function supplierInfo(Request $request)
    {
        $suppliers = Supplier::where('id', $request->supplierId)->first();

        return response()->json(['status' => true, 'data' => $suppliers]);
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = Supplier::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Check if a contact number already exists (AJAX validation)
     */
    public function checkContact(Request $request)
    {
        $exists = \Modules\Pharma\Models\Supplier::where('contact_number', $request->contact_number)->exists();
        return response()->json(['exists' => $exists]);
    }
}
