<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Transformers\ManufracturerResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ManufracturerController extends Controller
{
    public function list(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $user = auth()->user();
            $menufracturerList = DB::table('manufacturers');

            if($user->hasRole('pharma')) {
                $menufracturerList->where('pharma_id', auth()->user()->id);
            }

            $menufracturerPaginated = $menufracturerList->paginate($perPage);
            $menufracturerCollection = ManufracturerResource::collection($menufracturerPaginated);

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __('pharma::messages.manufacturer_list'),
                'data' => $menufracturerCollection
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Manufacturer list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching Manufacturer list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request)
    {
        $user = auth()->user();
        $manufacturer = DB::table('manufacturers')->where('id', $request->manufacturer_id)->first();
        if (!$manufacturer) {
            return comman_custom_response([
                    'code' => 404,
                    'status' => false,
                    'message' => __("pharma::messages.manufacturer_not_found"),
                ], 404);
            }

        $manufacturerCollection = new ManufracturerResource($manufacturer);
         return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __('pharma::messages.manufacturer_detail'),
                'data' => $manufacturerCollection
        ], 200);
        
    }

    public function store(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return comman_custom_response([
                    'code' => 422,
                    'status' => false,
                    'message' => __("pharma::messages.validation_failed"),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();
            $now = now();

            if (!empty($data['manufacturer_id'])) {
                // UPDATE existing manufacturer
                $updated = DB::table('manufacturers')
                    ->where('id', $data['manufacturer_id'])
                    ->update([
                        'name' => $data['name'],
                        'pharma_id' => auth()->user()->id,
                        'updated_at' => $now,
                    ]);

                if (!$updated) {
                    return comman_custom_response([
                        'code' => 404,
                        'status' => false,
                        'message' => __("pharma::messages.medicine_form_not_found"),
                    ], 404);
                }

                $manufacturer = DB::table('manufacturers')->where('id', $data['manufacturer_id'])->first();
            } else {
                // CREATE new manufacturer
                $id = DB::table('manufacturers')->insertGetId([
                    'name' => $data['name'],
                    'pharma_id' => auth()->user()->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $manufacturer = DB::table('manufacturers')->where('id', $id)->first();
            }

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.prescription_list"),
                'data' => $manufacturer,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Manufacturer store/update error: ' . $e->getMessage());

            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
