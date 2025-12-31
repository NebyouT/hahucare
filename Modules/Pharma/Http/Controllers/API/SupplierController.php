<?php
namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Pharma\Models\Supplier;
use Modules\Pharma\Models\SupplierType;
use Modules\Pharma\Transformers\SupplierTypeResource;
use Modules\Pharma\Transformers\SupplierResource;
use Modules\Pharma\Http\Requests\StoreOrUpdateSupplierRequest;
use DB;

class SupplierController extends Controller
{

    public function list(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        $supplierQuery = Supplier::query()
            ->when(auth()->user()->hasRole('pharma'), function ($query) {
                $query->where('pharma_id', auth()->user()->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('contact_number', 'like', "%$search%")
                        ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%$search%");
                });
            })
            ->when(isset($status) && $status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id');

        $suppliers = $supplierQuery->paginate($perPage);
        $supplierCollection = SupplierResource::collection($suppliers);

        $response = [
            'code' => 200,
            'status' => true,
            'message' => __("pharma::messages.supplier_list"),
            'data' => $supplierCollection
        ];

        return comman_custom_response($response, 200);
    }

    public function store(StoreOrUpdateSupplierRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->filled('supplier_id')) {
                $supplier = Supplier::findOrFail($request->supplier_id);
                $supplier->update($data);
                $message = __('pharma::messages.supplier_updated');
            } else {
                $data['pharma_id'] = auth()->id();
                $supplier = Supplier::create($data);
                $message = __('pharma::messages.supplier_created_successfully');
            }

             if ($request->hasFile('image')) {
                $supplier->clearMediaCollection('supplier_image');
            storeMediaFile($supplier, $request->file('image'), 'supplier_image');
        }
        if ($supplier->pharma_id) {
            sendNotification([
                'notification_type' => 'add_supplier',
                'pharma_id' => $supplier->pharma_id,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->first_name . ' ' . $supplier->last_name,
                'supplier' => $supplier,
            ]);
        }

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => $message,
                'data' => new SupplierResource($supplier),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Supplier store error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while adding supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detail($id)
    {
        try{
            $supplier = Supplier::with('supplierType')->find($id);
    
            if (!$supplier) {
                return comman_custom_response([
                    'code' => 404,
                    'status' => false,
                    'message' => __("pharma::messages.supplier_not_found"),
                ], 404);
            }
    
            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.supplier_detail"),
                'data' => new SupplierResource($supplier),
            ], 200);
            
        }catch (\Throwable $e) {
            \Log::error('Supplier store error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while adding supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function typeList(Request $request)
    {
        
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        $supplierTypeQuery = SupplierType::where('status', 1)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
        $supplierTypeCollection = SupplierTypeResource::collection($supplierTypeQuery);
        return comman_custom_response([
            'code' => 200,
            'status' => true,
            'message' => __("pharma::messages.supplier_type_list"),
            'data' => $supplierTypeCollection
        ]);
    }

    public function delete($id)
    {
        try {
            $supplier = Supplier::find($id);
            if (!$supplier) {
                return comman_custom_response([
                    'code' => 404,
                    'status' => false,
                    'message' => __("pharma::messages.supplier_not_found"),
                ], 404);
            }
            $supplier->delete();
            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.supplier_deleted"),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Supplier delete error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while deleting supplier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
