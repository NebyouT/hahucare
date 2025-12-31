<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Http\Requests\MedicineRequest;
use Modules\Pharma\Models\Manufacturer;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\MedicineCategory;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Models\Supplier;
use Modules\Pharma\Traits\PharmaOwnershipChecker;
use Modules\Tax\Models\Tax;
use Yajra\DataTables\DataTables;
use Modules\Pharma\Exports\MedicineExport;
use Modules\Pharma\Models\MedicineHistory;
use Modules\Clinic\Models\Clinics;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use PharmaOwnershipChecker;
    protected string $exportClass = '\Modules\Pharma\Exports\MedicineExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'sidebar.medicine';
        $this->edit_module_title = 'Edit Medicine';

        // module name
        $this->module_name = 'medicines';

        view()->share([
            'module_title' => $this->module_title,
            'module_name'  => $this->module_name,
            'edit_module_title' => $this->edit_module_title,
        ]);

        $this->middleware('check.permission:view_medicine')->only(['index', 'show']);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = 'sidebar.all_medicine';
        $create_title = 'medicine.add_medicine';

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
                'value' => 'dosage',
                'text' => __('pharma::messages.dosage'),
            ],
            [
                'value' => 'form',
                'text' => __('pharma::messages.form'),
            ],
            [
                'value' => 'category',
                'text' => __('pharma::messages.category'),
            ],
            [
                'value' => 'supplier',
                'text' => __('pharma::messages.supplier'),
            ],
            [
                'value' => 'manufacturer',
                'text' => __('pharma::messages.manufacturer'),
            ],
            [
                'value' => 'expiry_date',
                'text' => __('pharma::messages.expiry_date'),
            ],
            [
                'value' => 'selling_price',
                'text' => __('pharma::messages.selling_price'),
            ],
            [
                'value' => 'quntity',
                'text' => __('pharma::messages.quantity'),
            ],
        ];

        if (multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))) {
            $export_columns[] = [
                'value' => 'pharma_id',
                'text' => __('multivendor.singular_title'),
            ];
        }

        $export_url = route('backend.medicine.export');

        return view('pharma::medicine.index_datatable', compact(
            'module_action',
            'module_title',
            'create_title',
            'filter',
            'export_import',
            'export_columns',
            'export_url'
        ));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $dateformate = \App\Models\Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';

        $query = Medicine::setRole(auth()->user())
            ->with(['category', 'form', 'supplier', 'pharmaUser', 'manufacturer'])
            ->select('medicines.*')
            ->where('expiry_date', '>=', Carbon::today());

        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('medicines.pharma_id', auth()->user()->id);
        }

        if (!$request->has('order')) {
            $query->orderBy('medicines.id', 'desc');
        }

        $query = $query->where('expiry_date', '>=', Carbon::today());


        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['name'])) {
                $query->where('id', $filter['name']);
            }

            if (isset($filter['dosage'])) {
                $query->where('id', $filter['dosage']);
            }

            if (isset($filter['form'])) {
                $query->where('form_id', $filter['form']);
            }

            if (isset($filter['category'])) {
                $query->where('category_id', $filter['category']);
            }

            if (isset($filter['supplier'])) {
                $query->where('supplier_id', $filter['supplier']);
            }

            if (isset($filter['manufacturer'])) {
                $query->where('manufacturer_id', $filter['manufacturer']);
            }

            if (isset($filter['batch_no'])) {
                $query->where('id', $filter['batch_no']);
            }
        }
        return $datatable->eloquent($query)

            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })

            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->orderColumn('name', fn($q, $o) => $q->orderBy('name', $o))

            ->addColumn('dosage', function ($row) {
                return $row->dosage;
            })
            ->filterColumn('dosage', function ($query, $keyword) {
                $query->where('dosage', 'like', "%{$keyword}%");
            })
            ->addColumn('pharma_id', function ($row) {
                return $row->pharmaUser ? $row->pharmaUser->fullname : '-';
            })
            ->filterColumn('pharma_id', function ($query, $keyword) {
                $query->whereHas('pharmaUser', function ($q) use ($keyword) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->orderColumn('pharma_id', function ($query, $order) {
                $query->leftJoin('users as pharma_user', 'pharma_user.id', '=', 'medicines.pharma_id')
                    ->orderByRaw("CONCAT(pharma_user.first_name, ' ', pharma_user.last_name) $order")
                    ->select('medicines.*');
            })

            ->orderColumn('dosage', fn($q, $o) => $q->orderBy('dosage', $o))

            ->addColumn('category.name', function ($row) {
                return $row->category ? $row->category->name : '-';
            })
            ->filterColumn('category.name', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })


            ->addColumn('form.name', function ($row) {

                return $row->form ? $row->form->name : '-';
            })
            ->filterColumn('form.name', function ($query, $keyword) {
                $query->whereHas('form', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->orderColumn('form.name', function ($q, $o) {
                $q->leftJoin('medicine_forms as mf', 'mf.id', '=', 'medicines.form_id')
                    ->orderBy('mf.name', $o)
                    ->select('medicines.*');
            })

            ->addColumn('expiry_date', function ($row) use ($dateformate) {
                return \Carbon\Carbon::parse($row->expiry_date)->format($dateformate);
            })
            ->filterColumn('expiry_date', function ($query, $keyword) {
                $query->where('expiry_date', 'like', "%{$keyword}%");
            })
            ->orderColumn('expiry_date', fn($q, $o) => $q->orderBy('expiry_date', $o))

            ->addColumn('note', function ($row) {
                return $row->note;
            })
            ->filterColumn('note', function ($query, $keyword) {
                $query->where('note', 'like', "%{$keyword}%");
            })

            ->addColumn('supplier.name', function ($row) {
                return $row->supplier ? $row->supplier->full_name : '-';
            })
            ->filterColumn('supplier.name', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->orderColumn('supplier.name', function ($q, $o) {
                $q->leftJoin('suppliers as sup', 'sup.id', '=', 'medicines.supplier_id')
                    ->orderByRaw("CONCAT(sup.first_name, ' ', sup.last_name) $o")
                    ->select('medicines.*');
            })

            ->addColumn('contact_number', function ($row) {
                return $row->contact_number;
            })
            ->filterColumn('contact_number', function ($query, $keyword) {
                $query->where('contact_number', 'like', "%{$keyword}%");
            })

            ->addColumn('payment_terms', function ($row) {
                return $row->payment_terms;
            })
            ->filterColumn('payment_terms', function ($query, $keyword) {
                $query->where('payment_terms', 'like', "%{$keyword}%");
            })

            ->addColumn('manufacturer.name', function ($row) {
                return $row->manufacturer ? $row->manufacturer->name : '-';
            })
            ->filterColumn('manufacturer.name', function ($query, $keyword) {
                $query->whereHas('manufacturer', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('manufacturer.name', function ($q, $o) {
                $q->leftJoin('manufacturers as mfg', 'mfg.id', '=', 'medicines.manufacturer_id')
                    ->orderBy('mfg.name', $o)
                    ->select('medicines.*');
            })

            ->addColumn('selling_price', function ($row) {
                return \Currency::format($row->selling_price);
            })
            ->filterColumn('selling_price', function ($query, $keyword) {
                $query->where('selling_price', 'like', "%{$keyword}%");
            })
            ->orderColumn('selling_price', fn($q, $o) => $q->orderBy('selling_price', $o))

            ->addColumn('quntity', function ($row) {
                if ($row->quntity <= $row->re_order_level) {
                    return $row->quntity . ' <a href="javascript:void(0)" class="ms-2 text-success open-add-stock" data-id="' . $row->id . '"><i class="fa fa-plus-circle"></i> Add</a>';
                } elseif ($row->quntity <= ($row->re_order_level + 10)) {
                    return $row->quntity . ' <span class="ms-2 text-danger">Low</span>';
                } else {
                    return $row->quntity;
                }
            })

            ->filterColumn('quntity', function ($query, $keyword) {
                $query->where('quntity', 'like', "%{$keyword}%");
            })
            ->orderColumn('quntity', fn($q, $o) => $q->orderBy('quntity', $o))

            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked="checked"' : '';
                return '
                    <div class="form-check form-switch">
                        <input type="checkbox" data-url="' . route('backend.suppliers.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->addColumn('action', function ($data) {
                return view('pharma::medicine.action_column', compact('data'));
            })
            ->rawColumns(['check', 'action', 'status', 'quntity'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureCanCreate();
        $isEdit       = false;

        $medicine         = new Medicine();
        $medicineCategory = MedicineCategory::all();
        $medicineForm     = MedicineForm::where('status', 1)->get();
        $manufacturers    = Manufacturer::all();
        $tax              = Tax::where('category', 'medicine')->get();
        return view('pharma::medicine.create', compact('medicineCategory', 'medicine', 'isEdit', 'medicineForm', 'manufacturers', 'tax'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineRequest $request)
    {
        $pharmaId = auth()->user()->user_type === 'pharma'
            ? auth()->id()
            : $request->pharma_id;

        $data = [
            'name'            => $request->name,
            'dosage'          => $request->dosage,
            'category_id'     => $request->medicine_category_id,
            'form_id'         => $request->form_id,
            'expiry_date'     => $request->expiry_date,
            'note'            => $request->note,
            'supplier_id'     => $request->supplier_id,
            'contact_number'  => $request->contact_number,
            'payment_terms'   => $request->payment_terms,
            'quntity'         => $request->quntity, // keep typo if that's your column
            're_order_level'  => $request->re_order_level,
            'manufacturer_id' => $request->manufacturer, // input → field
            'batch_no'        => $request->batch_no,
            'start_serial_no' => $request->start_serial_no, // keep typo if column is wrong
            'end_serial_no'   => $request->end_serial_no,
            'purchase_price'  => $request->purchase_price,
            'selling_price'   => $request->selling_price,
            'is_inclusive_tax'   => $request->is_inclusive_tax ?? 0,
            'stock_value'     => $request->quntity * $request->selling_price, // or null if calculating later
            'pharma_id'       => $pharmaId,
        ];

        $medicine = Medicine::create($data);

        MedicineHistory::create([
            'medicine_id'     => $medicine->id,
            'batch_no'        => $medicine->batch_no,
            'quntity'         => $medicine->quntity,
            'start_serial_no'  => $medicine->start_serial_no,
            'end_serial_no'   => $medicine->end_serial_no,
            'stock_value'     => $medicine->stock_value,
        ]);
        $clinicId = $medicine->pharmaUser->clinic_id;
        $clinic = isset($clinicId)  ? Clinics::where('id', $clinicId)->first() : null;

        // Send add_medicine notification to doctor/admin/vendor
        sendNotification([
            'notification_type' => 'add_medicine',
            'medicine_name' => $request->name,
            'pharma_id' => $pharmaId,
            'quantity' => $request->quntity,
            'clinic_id' => $medicine->pharmaUser->clinic_id ?? $clinic->id ?? null,
            'medicine' => $medicine,
            'vendor_id' => $clinic->vendor_id ?? null,
        ]);

        return redirect()->route('backend.medicine.index')
            ->with('success', __('pharma::messages.created_successfully'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $medicine = Medicine::setRole(auth()->user())->where('id', $id)->first();
        if ($medicine == null) {
            abort(403, 'Unauthorized access');
        }
        $suppliers = Supplier::where('id', $medicine->supplier_id)->first();
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
        return view('pharma::medicine.show', compact('medicine', 'suppliers', 'dateformate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->ensureHasEditPermission();
        $isEdit       = true;
        $medicine = Medicine::findOrFail($id);
        $this->ensurePharmaOwns($medicine);

        $medicineCategory = MedicineCategory::all();
        $medicineForm     = MedicineForm::where('status', 1)->get();
        $manufacturers    = Manufacturer::all();
        $tax              = Tax::where('category', 'medicine')->get();

        $selectedPharma = null;
        if ($medicine->pharma_id) {
            $selectedPharma = \App\Models\User::where('id', $medicine->pharma_id)
                ->where('user_type', 'pharma')
                ->first();
        }

        return view('pharma::medicine.create', compact(
            'medicineCategory',
            'medicine',
            'medicineForm',
            'manufacturers',
            'tax',
            'isEdit',
            'selectedPharma'
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        $this->ensureHasEditPermission();
        $this->ensurePharmaOwns($medicine);
        $pharmaId = auth()->user()->user_type === 'pharma'
            ? auth()->id()
            : $request->pharma_id;

        $data = [
            'name'             => $request->name,
            'dosage'           => $request->dosage,
            'category_id'      => $request->medicine_category_id,
            'form_id'          => $request->form_id,
            'expiry_date'      => $request->expiry_date,
            'note'             => $request->note,
            'supplier_id'      => $request->supplier_id,
            'contact_number'   => $request->contact_number,
            'payment_terms'    => $request->payment_terms,
            'quntity'          => $request->quntity, // keep typo if that's your column
            're_order_level'   => $request->re_order_level,
            'manufacturer_id'  => $request->manufacturer, // input → field
            'batch_no'         => $request->batch_no,
            'start_serial_no'  => $request->start_serial_no, // keep typo if column is wrong
            'end_serial_no'    => $request->end_serial_no,
            'purchase_price'   => $request->purchase_price,
            'selling_price'    => $request->selling_price,
            'tax'              => $request->tax,
            'is_inclusive_tax' => $request->is_inclusive_tax ?? 0,
            'stock_value'      => $request->quntity * $request->selling_price,
            'pharma_id'        => $pharmaId,
        ];
        $medicine = Medicine::findOrFail($id);
        $medicine->update($data);
        sendNotification([
            'notification_type' => 'add_medicine',
            'medicine_name' => $request->name,
            'pharma_id' => $pharmaId,
            'quantity' => $request->quntity,
            'clinic_id' => $medicine->pharmaUser->clinic_id,
            'medicine' => $medicine,
        ]);
        return redirect()->route('backend.medicine.index')
            ->with('success', __('pharma::messages.medicine_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy($id)
    {
        $this->ensureCanDelete();
        $medicine = Medicine::findOrFail($id);
        $this->ensurePharmaOwns($medicine);

        $medicine->delete();
        $message = __('pharma::messages.medicine_delete');
        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function medicineDetailTable(Datatables $datatable, Request $request)
    {

        $dateformate = \App\Models\Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';

        $query                = Medicine::query();
        $medicineExclusiveTax = Tax::where(['category' => 'medicine', 'module_type' => 'medicine', 'status' => 1, 'tax_type' => 'exclusive'])->get();
        if ($request->filter['medicine_id']) {
            $query->where('id', $request->filter['medicine_id']);
        }

        $query = $query->where('expiry_date', '>=', Carbon::today());


        if (auth()->user()->hasRole(roles: 'pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }
        $query = $query->orderBy('created_at', 'desc');
        return $datatable->eloquent($query)

            ->addColumn('created_at', function ($row) use ($dateformate) {
                return \Carbon\Carbon::parse($row->created_at)->format($dateformate);
            })

            ->addColumn('supplier.name', function ($row) {
                return $row->supplier ? $row->supplier->full_name : '-';
            })

            ->addColumn('quntity', function ($row) {
                return $row->quntity;
            })

            ->addColumn('purchase_price', function ($row) {
                return \Currency::format($row->purchase_price);
            })

            ->addColumn('selling_price', function ($row) {
                return \Currency::format($row->selling_price);
            })

            ->addColumn('manufacturer_name', function ($row) {
                return $row->manufacturer ? $row->manufacturer->name : '-';
            })

            ->addColumn('tax', function ($row) use ($medicineExclusiveTax) {
                if ($medicineExclusiveTax->isEmpty()) {
                    return '-';
                }

                $taxes = $medicineExclusiveTax->map(function ($tax) {
                    $label = $tax->type === 'percent'
                        ? $tax->title . ' (' . $tax->value . '%)'
                        : $tax->title . ' (' . \Currency::format($tax->value) . ')';

                    return '<span class="badge bg-primary me-1">' . $label . '</span>';
                });

                return $taxes->implode(' ');
            })

            ->addColumn('payment_terms', function ($row) {
                return $row->payment_terms . ' ' . 'Days';
            })

            ->addColumn('action', function ($data) {
                return view('pharma::medicine.show_action_column', compact(var_name: 'data'));
            })
            ->rawColumns(['action', 'tax']) // Ensure HTML is rendered
            ->addIndexColumn()
            ->make(true);
    }

    public function medicineDetails($medicineId, $supplierId)
    {

        $medicine  = Medicine::findOrFail($medicineId);
        $suppliers = Supplier::findOrFail($supplierId);
        $tax = Tax::where(['category' => 'medicine', 'module_type' => 'medicine', 'status' => 1, 'tax_type' => 'exclusive'])->get();
        $taxes = $tax->map(function ($tax) {
            $label = $tax->type === 'percent'
                ? $tax->title . ' (' . $tax->value . '%)'
                : $tax->title . ' (' . \Currency::format($tax->value) . ')';

            return '<span class="badge bg-primary me-1">' . $label . '</span>';
        });
        $taxesHtml = $taxes->implode(' ');

        $html      = view('pharma::medicine.partials.details', compact('suppliers', 'medicine', 'taxesHtml'))->render();

        return response()->json(['html' => $html]);
    }

    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'delete':
                if (env('IS_DEMO')) {
                    return response()->json(['message' => __('messages.permission_denied'), 'status' => false], 200);
                }
                Medicine::whereIn('id', $ids)->delete();
                $message = __('pharma::messages.medicine_delete');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }


    public function addStock(Request $request)
    {
        $request->validate([
            'medicine_id'      => 'required|exists:medicines,id',
            'quantity'         => 'required|integer|min:1',
            'batch_no'         => 'nullable|string|max:255',
            'start_serial_no'  => 'nullable|integer',
            'end_serial_no'    => 'nullable|integer|gte:start_serial_no',
        ]);

        $medicine = Medicine::findOrFail($request->medicine_id);
        $medicine->quntity += $request->quantity;

        $medicine->batch_no = $request->batch_no;
        $medicine->start_serial_no = $request->start_serial_no;
        $medicine->end_serial_no = $request->end_serial_no;

        $medicine->save();

        MedicineHistory::create([
            'medicine_id'     => $medicine->id,
            'batch_no'        => $medicine->batch_no,
            'quntity'         => $request->quantity,
            'start_serial_no'  => $medicine->start_serial_no,
            'end_serial_no'   => $medicine->end_serial_no,
            'stock_value'     => $request->quantity * $medicine->selling_price,
        ]);

        if ($medicine->re_order_level > 0 && $medicine->quntity <= $medicine->re_order_level) {
            sendNotification([
                'notification_type'    => 'low_stock_alert',
                'medicine_name'        => $medicine->name,
                'available_quantity'   => $medicine->quntity,
                'required_quantity'    => $medicine->re_order_level,
                'pharma_id'            => $medicine->pharma_id,
                'low_stock_medicine'   => $medicine,
            ]);
        }

        return response()->json(['message' => 'Stock updated']);
    }
    public function getMedicineHistory($id)
    {
        $medicine = Medicine::findOrFail($id);
        $this->ensurePharmaOwns($medicine);
        $module_title = __('pharma::messages.medicine_history');

        $historyList = MedicineHistory::where('medicine_id', $id)->orderBy('updated_at', 'desc')->get();
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';

        if ($historyList->isEmpty()) {
            return response()->json(['message' => 'No history found for this medicine'], 404);
        }

        return view('pharma::medicine.medicine_history', compact('medicine', 'module_title', 'historyList', 'dateformate'));
    }
}
