<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Modules\Pharma\Models\Medicine;
use Modules\Pharma\Models\Supplier;
use Modules\Tax\Models\Tax;
use Modules\Pharma\Http\Requests\MedicineRequest;
use Carbon\Carbon;
use App\Models\Setting;

class ExpiredMedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected string $exportClass = '\App\Exports\ExpiredMedicineExport';
    public function __construct()
    {
        $this->module_title = 'Expired Medicine';
        $this->module_name = 'expired_medicine';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
        ]);

        $this->middleware('check.permission:view_expired_medicine')->only(['index', 'show']);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('sidebar.expired_medicine');
        $create_title = 'medicine.add_expired_medicine';


        $filter = [
            'status' => $request->status,
        ];

        $export_columns = [
            [
                'value' => 'name',
                'text' => __('pharma::messages.lbl_name'),
            ],
            [
                'value' => 'dosage',
                'text' => __('pharma::messages.lbl_dosage'),
            ],
            [
                'value' => 'form',
                'text' => __('pharma::messages.lbl_form'),
            ],
            [
                'value' => 'supplier',
                'text' => __('pharma::messages.lbl_supplier'),
            ],
            [
                'value' => 'manufacturer',
                'text' => __('pharma::messages.lbl_manufacturer'),
            ],

            [
                'value' => 'selling_price',
                'text' => __('pharma::messages.lbl_selling_price'),
            ],
            [
                'value' => 'quntity',
                'text' => __('pharma::messages.lbl_stock'),
            ],

        ];

        $export_url = route('backend.expired-medicine.export');

        $export_import = true;

        return view('pharma::expired_medicine.index_datatable', compact(
            'module_action',
            'module_title',
            'create_title',
            'filter',
            'export_columns',
            'export_url',
            'export_import'
        ));
    }

    public function index_data(Datatables $datatable, Request $request)
    {

        $query = Medicine::setRole(auth()->user());
        if (auth()->user()->hasRole('pharma')) {
            $query = $query->where('pharma_id', auth()->user()->id);
        }

        $query = $query->where('expiry_date', '<=', Carbon::today());

        $expiredMedicines = $query->get();
        foreach ($expiredMedicines as $medicine) {
            if ($medicine->pharma_id) {
                sendNotification([
                    'notification_type' => 'expired_medicine',
                    'pharma_id' => $medicine->pharma_id,
                    'medicine_id' => $medicine->id,
                    'medicine_name' => $medicine->name,
                    'expiry_date' => $medicine->expiry_date->format('Y-m-d'),
                    'expired_medicine' => $medicine,
                ]);
            }
        }

        $query = $query->orderBy('id', 'desc');

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
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
        return $datatable->eloquent($query)

            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })

            ->addColumn('dosage', function ($row) {
                return $row->dosage;
            })
            ->filterColumn('dosage', function ($query, $keyword) {
                $query->where('dosage', 'like', "%{$keyword}%");
            })

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

            ->addColumn('expiry_date', function ($row) use ($dateformate) {
                return Carbon::parse($row->expiry_date)->format($dateformate);
            })
            ->filterColumn('expiry_date', function ($query, $keyword) {
                $query->where('expiry_date', 'like', "%{$keyword}%");
            })

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

            ->addColumn('selling_price', function ($row) {
                return \Currency::format($row->selling_price);
            })
            ->filterColumn('selling_price', function ($query, $keyword) {
                $query->where('selling_price', 'like', "%{$keyword}%");
            })

            ->addColumn('quntity', function ($row) {
                return $row->quntity;
            })

            ->filterColumn('quntity', function ($query, $keyword) {
                $query->where('quntity', 'like', "%{$keyword}%");
            })


            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked="checked"' : '';
                return '
                    <div class="form-check form-switch">
                        <input type="checkbox" data-url="' . route('backend.suppliers.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->addColumn('action', function ($data) {
                return view('pharma::expired_medicine.action_column', compact('data'));
            })
            ->rawColumns(['check', 'action', 'status', 'quntity'])
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicineRequest $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);
        $suppliers = Supplier::where('id', $medicine->supplier_id)->first();
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';
        return view('pharma::expired_medicine.show', compact('medicine', 'suppliers', 'dateformate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}


    public function medicineDetails($medicineId, $supplierId)
    {
        $medicine = Medicine::findOrFail($medicineId);
        $suppliers = Supplier::findOrFail($supplierId);
        $tax = Tax::where(['category' => 'medicine', 'module_type' => 'medicine', 'status' => 1, 'tax_type' => 'exclusive'])->get();
        $taxes = $tax->map(function ($tax) {
            $label = $tax->type === 'percent'
                ? $tax->title . ' (' . $tax->value . '%)'
                : $tax->title . ' (' . \Currency::format($tax->value) . ')';

            return '<span class="badge bg-primary me-1">' . $label . '</span>';
        });
        $taxesHtml = $taxes->implode(' ');
        $html = view('pharma::expired_medicine.partials.details', compact('suppliers', 'medicine', 'taxesHtml'))->render();

        return response()->json(['html' => $html]);
    }
}
