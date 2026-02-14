<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\Medicine;
use App\Models\User;
use Modules\Pharma\Models\PurchasedOrder;
use Modules\Pharma\Transformers\PurchasedOrderResource;
use Modules\Pharma\Models\MedicineHistory;

class OrderController extends Controller
{
public function store(Request $request)
{


    $medicine = Medicine::findOrFail($request->medicine_id);
    $quantity = $request->quantity;
    $total    = $medicine->purchase_price * $quantity;

    $orderMedicine = [
        'medicine_id'    => $medicine->id,
        'pharma_id'      => auth()->user()->id,
        'quantity'       => $quantity,
        'delivery_date'  => $request->delivery_date,
        'total_amount'   => $total,
        'payment_status' => config('constant.PAYMENT_STATUS.PENDING'),
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
    $message = __('pharma::messages.order_placed');

            return response()->json(['message' => $message, 'status' => true], 200);
    }

public function purchesList(Request $request)
{
    try {
        $query = PurchasedOrder::with(['medicine.supplier', 'medicine.manufacturer', 'medicine.category', 'medicine.form']);

        // Filter by pharma_id if provided
            if ($request->has('pharma_id') && !empty($request->pharma_id)) {
                $query->where('pharma_id', $request->pharma_id);
            }
            if(isset($request->vendor_id) && !empty($request->vendor_id)){
                $vendorUser = User::where('id', $request->vendor_id)->first();
                 $pharmaIds = User::whereIn('clinic_id', $vendorUser->getClinic->pluck('id'))->where('user_type', 'pharma')->pluck('id');

                $query->where('pharma_id', $pharmaIds);
            }


        $orders = $query->orderBy('created_at', 'desc')
                        ->paginate($request->input('per_page', 15));
        // Transform the data to include more details
        $transformedOrders = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'medicine' => [
                    'id' => $order->medicine->id ?? null,
                    'name' => $order->medicine->name ?? 'N/A',
                    'dosage' => $order->medicine->dosage ?? 'N/A',
                    'category' => $order->medicine->category->name ?? 'N/A',
                    'form' => $order->medicine->form->name ?? 'N/A',
                    'supplier' => [
                        'id' => $order->medicine->supplier->id ?? null,
                        'first_name' => $order->medicine->supplier->first_name ?? '',
                        'last_name' => $order->medicine->supplier->last_name ?? '',
                        'payment_terms' => $order->medicine->supplier->payment_terms ?? '',
                        'supplier_type' => $order->medicine->supplier->supplierType->name ?? '',
                        'status' => $order->medicine->supplier->status ?? '',
                        'name' => $order->medicine->supplier ?
                            $order->medicine->supplier->first_name . ' ' . $order->medicine->supplier->last_name : 'N/A',
                        'email' => $order->medicine->supplier->email ?? 'N/A',
                        'contact_number' => $order->medicine->supplier->contact_number ?? 'N/A',
                        'image_url' => $order->medicine->supplier ? getSingleMedia($order->medicine->supplier, 'supplier_image') : asset('img/default.webp'),
                    ],
                    'manufacturer' => [
                        'id' => $order->medicine->manufacturer->id ?? null,
                        'name' => $order->medicine->manufacturer->name ?? 'N/A'
                    ],
                    'purchase_price' => $order->medicine->purchase_price ?? 0,
                    'expiry_date' => $order->medicine->expiry_date ?? 0,
                    'order_date' => $order->medicine->created_at ?? 0,
                    'selling_price' => $order->medicine->selling_price ?? 0,
                    'current_stock' => $order->medicine->quntity ?? 0,
                    're_order_level' => $order->medicine->re_order_level ?? 0
                ],
                'quantity' => $order->quantity,
                'delivery_date' => $order->delivery_date,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status ?? 'N/A',

                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Purchase orders retrieved successfully',
            'total_records' => $orders->total(),
            'data' => [
                'orders' => $transformedOrders,

            ]
        ]);

    } catch (\Throwable $e) {
        \Log::error('Purchase order list error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong while fetching purchase orders.',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function update(Request $request, $id)
{
    try {
        $order = PurchasedOrder::findOrFail($id);
        $medicine = Medicine::find($order->medicine_id);
        // Update fields if present
        if ($request->has('medicine_id')) {
            $order->medicine_id = $request->medicine_id;
        }
        if ($request->has('quantity')) {
            $order->quantity = $request->quantity;
        }
        if ($request->has('delivery_date')) {
            $order->delivery_date = $request->delivery_date;
        }
        if ($request->has('order_status')) {
            $order->order_status = $request->order_status;
        }
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        // Recalculate total_amount if medicine or quantity changed
        if ($request->has('medicine_id') || $request->has('quantity')) {
            $order->total_amount = $medicine ? $medicine->purchase_price * $order->quantity : $order->total_amount;
        }

        if($request->order_status == 'delivered'){
            $quantity = $request->quantity ?? $order->quantity;
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

        $order->save();
        $message = __('pharma::messages.order_edited');
        return response()->json(['message' => $message, 'status' => true], 200);

    } catch (\Throwable $e) {
        \Log::error('Purchase order update error: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong while updating purchase order.',
            'error' => $e->getMessage()
        ], 500);
    }
}
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
}
