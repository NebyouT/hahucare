<?php

namespace Modules\Laboratory\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Laboratory\Models\LabOrder;
use Modules\Laboratory\Models\LabOrderItem;
use Modules\Laboratory\Transformers\LabOrderResource;

class LabOrderAPIController extends Controller
{
    public function myLabOrders(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = LabOrder::with(['lab', 'clinic', 'doctor', 'labOrderItems'])
            ->where('patient_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate($perPage);

        $orderCollection = LabOrderResource::collection($orders);

        return response()->json([
            'status' => true,
            'data' => $orderCollection,
            'message' => __('laboratory::laboratory.lab_orders_list'),
        ], 200);
    }

    public function labOrderDetail(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:lab_orders,id',
        ]);

        $order = LabOrder::with(['lab', 'clinic', 'doctor', 'labOrderItems'])
            ->where('id', $request->id)
            ->where('patient_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => __('laboratory::laboratory.lab_order_not_found'),
            ], 404);
        }

        $responseData = new LabOrderResource($order);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('laboratory::laboratory.lab_order_detail'),
        ], 200);
    }
}
