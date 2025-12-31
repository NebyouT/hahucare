<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\Medicine;
use App\Models\Setting;
use Carbon\Carbon;
use Modules\Pharma\Models\PurchasedOrder;
use Yajra\DataTables\DataTables;
use Modules\Pharma\Exports\PurchasedOrderExport;
use Modules\Pharma\Models\MedicineHistory;

class OrderController extends Controller
{
    protected string $exportClass = '\Modules\Pharma\Exports\PurchasedOrderExport';

    public function __construct()
    {
        // Page Title
        $this->module_title = 'All Orders';

        // module name
        $this->module_name = 'purchased_orders';

        view()->share([
            'module_title' => $this->module_title,
            'module_name'  => $this->module_name,
        ]);

        $this->middleware('check.permission:view_purchased_order')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $module_action = 'List';
        $user          = auth()->user();

        $module_title = __('pharma::messages.purchased_orders');

        $filter = [
            'status'         => $request->status,
            'payment_status' => $request->payment_status,
        ];

        $export_import = true;
        $export_columns = [
            [
                'value' => 'created_at',
                'text' => __('messages.date'),
            ],
            [
                'value' => 'medicine',
                'text' => __('pharma::messages.medicine'),
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
                'value' => 'quantity',
                'text' => __('pharma::messages.quantity'),
            ],
            [
                'value' => 'delivery_date',
                'text' => __('pharma::messages.delivery_date'),
            ],
            [
                'value' => 'payment_status',
                'text' => __('pharma::messages.payment_status'),
            ],
        ];

        if (multiVendor() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin'))) {
            $export_columns[] = [
                'value' => 'pharma_id',
                'text' => __('multivendor.singular_title'),
            ];
        }

        $export_url = route('backend.order-medicine.export');

        return view('pharma::purchased_orders.index_datatable', compact(
            'module_action',
            'module_title',
            'filter',
            'export_import',
            'export_columns',
            'export_url'
        ));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $query = PurchasedOrder::setRole(auth()->user())
            ->with(['pharmaUser','medicine.manufacturer']);

        if (auth()->user()->hasRole('pharma')) {
            $query->where('pharma_id', auth()->user()->id);
        }



        $filter = $request->filter;
        if (isset($filter['column_status'])) {
            $query->where('order_status', $filter['column_status']);
        }

        return $datatable->eloquent($query)

            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('created_at', function ($row) {
                $timezone = Setting::where('name', 'default_time_zone')->value('val') ?? 'UTC';
                $dateSetting = Setting::where('name', 'date_formate')->first();
                $dateformate = $dateSetting ? $dateSetting->val : 'Y-m-d';

                return Carbon::parse($row->created_at)
                    ->timezone($timezone)
                    ->format($dateformate);
            })
            ->filterColumn('created_at', fn($query, $keyword) =>
            $query->where('purchased_orders.created_at', 'like', "%{$keyword}%"))
            ->orderColumn('created_at', fn($query, $order) =>
            $query->orderBy('purchased_orders.created_at', $order))

            ->addColumn('medicine.name', fn($row) => optional($row->medicine)->name ?? '-')
            ->filterColumn('medicine.name', function ($query, $keyword) {
                $query->whereHas('medicine', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('medicine.name', function ($query, $order) {
                $query->leftJoin('medicines as m1', 'm1.id', '=', 'purchased_orders.medicine_id')
                    ->orderBy('m1.name', $order)
                    ->select('purchased_orders.*');
            })
            ->addColumn('medicine.supplier.name', function ($data) {
                return view('pharma::purchased_orders.supplier.detail', compact('data'));
            })
            ->filterColumn('medicine.supplier.name', function ($query, $keyword) {
                $query->whereHas('medicine.supplier', function ($q) use ($keyword) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->orderColumn('medicine.supplier.name', function ($query, $order) {
                $query->leftJoin('medicines as m3', 'm3.id', '=', 'purchased_orders.medicine_id')
                    ->leftJoin('suppliers as sup', 'sup.id', '=', 'm3.supplier_id')
                    ->orderByRaw("CONCAT(sup.first_name, ' ', sup.last_name) {$order}")
                    ->select('purchased_orders.*');
            })

            ->addColumn('medicine.manufacturer.name', function ($row) {
                return optional(optional($row->medicine)->manufacturer)->name ?? '-';
            })
            ->filterColumn('medicine.manufacturer.name', function ($query, $keyword) {
                $query->whereHas('medicine.manufacturer', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('medicine.manufacturer.name', function ($query, $order) {
                $query->leftJoin('medicines as m2', 'm2.id', '=', 'purchased_orders.medicine_id')
                    ->leftJoin('manufacturers as man', 'man.id', '=', 'm2.manufacturer_id')
                    ->orderBy('man.name', $order)
                    ->select('purchased_orders.*');
            })

            ->editColumn('quantity', fn($row) => $row->quantity)
            ->filterColumn('quantity', fn($query, $keyword) =>
            $query->where('purchased_orders.quantity', 'like', "%{$keyword}%"))
            ->orderColumn('quantity', fn($query, $order) =>
            $query->orderBy('purchased_orders.quantity', $order))

            ->editColumn('delivery_date', function ($row) {
                $timezone = Setting::where('name', 'default_time_zone')->value('val') ?? 'UTC';
                $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';

                return Carbon::parse($row->delivery_date)->timezone($timezone)->format($dateformate);
            })

            ->filterColumn('delivery_date', fn($query, $keyword) =>
            $query->where('purchased_orders.delivery_date', 'like', "%{$keyword}%"))
            ->orderColumn('delivery_date', fn($query, $order) =>
            $query->orderBy('purchased_orders.delivery_date', $order))

            ->addColumn('payment_status', function ($row) {
                $statuses = config('constant.PAYMENT_STATUS');

                if ($row->payment_status === $statuses['PAID']) {
                    return '<span class="text-capitalize badge bg-success-subtle p-2">Paid</span>';
                }

                $dropdown = '<select class="form-control select2 payment_status" data-order-id="' . $row->id . '" name="payment_status" style="width: 100%">';

                foreach ($statuses as $key => $status) {
                    $selected = $row->payment_status === $status ? 'selected' : '';

                    // Custom label for dropdown
                    $label = ($status === $statuses['PENDING']) ? 'Unpaid' : 'Paid';

                    $dropdown .= "<option class='form-control select2 text-capitalize' value='$status' $selected>$label</option>";
                }

                $dropdown .= '</select>';
                return $dropdown;
            })


            ->addColumn('order_status', function ($row) {
                $statuses = config('constant.ORDER_STATUS');
                if ($row->order_status == $statuses['DELIVERED']) {
                    return '<span class="text-capitalize badge bg-success-subtle p-2">Delivered</span>';
                }

                $dropdown = '<select class="form-control select2 order_status" data-order-id="' . $row->id . '" name="order_status" style="width: 100%">';
                foreach ($statuses as $key => $status) {
                    $selected = $row->order_status == $status ? 'selected' : '';
                    $dropdown .= "<option class='form-control select2 text-capitalize' value='$status' $selected>" . ucfirst($status) . "</option>";
                }
                $dropdown .= '</select>';
                return $dropdown;
            })

            ->addColumn('action', function ($data) {
                return view('pharma::purchased_orders.action_column', compact('data'));
            })

            ->rawColumns(['check', 'action', 'status', 'quantity', 'medicine.manufacturer.name', 'payment_status', 'order_status'])
            ->addIndexColumn()
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($medicineId = null)
    {
        $medicine = null;

        if ($medicineId) {
            $medicine = Medicine::with(['supplier', 'manufacturer'])->findOrFail($medicineId);
        }

        $html = view('pharma::medicine.partials.order-medicine', compact('medicine'))->render();
        return response()->json(['html' => $html]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $medicine = Medicine::findOrFail($request->medicine_id);
        $quantity = $request->quantity;
        $purchasePrice = $medicine->purchase_price;
        $total    = $medicine->purchase_price * $quantity;

        $orderMedicine = [
            'medicine_id'    => $medicine->id,
            'pharma_id'      => $medicine->pharma_id,
            'quantity'       => $quantity,
            'purchase_price' => $purchasePrice,
            'delivery_date'  => $request->delivery_date,
            'total_amount'   => $total,
            'order_status'   => $request->order_status ?? config('constant.ORDER_STATUS.PENDING'),
            'payment_status' => $request->payment_status ?? config('constant.PAYMENT_STATUS.PENDING'),
        ];

        PurchasedOrder::create($orderMedicine);

        if($request->order_status == 'delivered'){
            $medicine->quntity += $quantity;
            $medicine->stock_value += $medicine->selling_price * ($medicine->quntity);
            $medicine->save();

            MedicineHistory::create([
                'medicine_id'     => $medicine->id,
                'batch_no'        => $medicine->batch_no,
                'quntity'         => $quantity,
                'start_serial_no' => $medicine->start_serial_no,
                'end_serial_no'   => $medicine->end_serial_no,
                'stock_value'     => $quantity * $medicine->selling_price,
            ]);
        }

        return response()->json(['success' => true]);


    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();

        $orderDetail = PurchasedOrder::findOrFail($id);
        $dateformate = Setting::where('name', 'date_formate')->value('val') ?? 'Y-m-d';

        $html = view('pharma::purchased_orders.partials.show-order-medicine-detail', compact('orderDetail', 'dateformate'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user        = auth()->user();
        $orderDetail = PurchasedOrder::findOrFail($id);

        $html = view('pharma::purchased_orders.partials.order-medicine-detail', compact('orderDetail'))->render();


        return response()->json(['html' => $html]);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $order    = PurchasedOrder::findOrFail($id);
        $medicine = Medicine::findOrFail($request->medicine_id);

        $newQuantity = $request->quantity;
        $oldQuantity = $order->quantity;
        if($request->order_status == 'delivered'){

            $medicine->quntity -= $oldQuantity;
            $medicine->stock_value -= $medicine->selling_price * $oldQuantity;

            $medicine->quntity += $newQuantity;
            $medicine->stock_value += $medicine->selling_price * $newQuantity;

            // Save updated stock
            $medicine->save();

            MedicineHistory::create([
                'medicine_id'     => $medicine->id,
                'batch_no'        => $medicine->batch_no,
                'quntity'         => $newQuantity,
                'start_serial_no' => $medicine->start_serial_no,
                'end_serial_no'   => $medicine->end_serial_no,
                'stock_value'     => $newQuantity * $medicine->selling_price,
            ]);
        }

        $order->update([
            'medicine_id'    => $medicine->id,
            'quantity'       => $newQuantity,
            'delivery_date'  => $request->delivery_date,
            'total_amount'   => $medicine->purchase_price * $newQuantity,
            'payment_status' => $request->payment_status ?? config('constant.PAYMENT_STATUS.PENDING'),
            'order_status'   => $request->order_status ?? config('constant.ORDER_STATUS.PENDING'),
        ]);

        return response()->json(['success' => true]);
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
                PurchasedOrder::whereIn('id', $ids)->delete();
                $message = __('pharma::messages.medicine_delete');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user     = auth()->user();
        $order    = PurchasedOrder::findOrFail($id);
        $medicine = Medicine::findOrFail($order->medicine_id);

        // Revert stock
        $medicine->quntity -= $order->quantity;
        $medicine->stock_value -= $medicine->selling_price * $order->quantity;
        $medicine->save();

        // Delete order
        $order->delete();

        $message = __('pharma::messages.order_deleted_successfully');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function updatePaymentStatus(Request $request)
    {

        $orderId               = $request->input('order_id');
        $paymentStatus         = $request->input('payment_status');
        $order                 = PurchasedOrder::findOrFail($orderId);
        $order->payment_status = $paymentStatus;
        $order->save();
        return response()->json(['success' => true]);
    }

    public function updateOrderStatus(Request $request)
    {
        $orderId = $request->input('order_id');
        $orderStatus = $request->input('order_status');
        $order = PurchasedOrder::findOrFail($orderId);
        $order->order_status = $orderStatus;
        $order->save();

        if($orderStatus == 'delivered'){
            $medicine = Medicine::findOrFail($order->medicine_id);
            $medicine->quntity += $order->quantity;
            $medicine->stock_value += $medicine->selling_price * $order->quantity;

            // Save updated stock
            $medicine->save();

            $medicineHistory = MedicineHistory::create([
                'medicine_id'     => $medicine->id,
                'batch_no'        => $medicine->batch_no,
                'quntity'         => $order->quantity,
                'start_serial_no' => $medicine->start_serial_no,
                'end_serial_no'   => $medicine->end_serial_no,
                'stock_value'     => $order->quantity * $medicine->selling_price,
            ]);
        }
        return response()->json(['success' => true]);
    }
}
