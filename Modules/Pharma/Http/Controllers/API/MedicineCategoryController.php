<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\MedicineCategory;
use Modules\Pharma\Transformers\MedicineCategoryResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicineCategoryController extends Controller
{
    public function list(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $categories = MedicineCategory::query()
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }, function ($query) {
                    $query->where('status', 1);
                })
                ->orderBy('name');

            $categoryPaginated = $categories->paginate($perPage);
            $categoryCollection = MedicineCategoryResource::collection($categoryPaginated);

            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_category_list"),
                'data' => $categoryCollection,
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine category list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine category list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name,' . $request->id,
            'status' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'status' => $request->status ? 1 : 0,
            ];

            if ($request->filled('id')) {
                // Update existing category
                $category = MedicineCategory::findOrFail($request->id);
                $category->update($data);
                $message = __("pharma::messages.medicine_category_updated");
            } else {
                // Create new category
                $category = MedicineCategory::create($data);
                $message = __("pharma::messages.medicine_category_added");
            }

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => $message,
                'data' => $category,
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('MedicineCategory store/update error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function edit(Request $request)
    {
        try {
            $category = MedicineCategory::findOrFail($request->id);

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_category_detail"),
                'data' => $category,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_category_not_found"),
            ], 404);

        } catch (\Throwable $e) {
            \Log::error('MedicineCategory fetch error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete($id)
    {
        try {
            $category = MedicineCategory::findOrFail($id);
            $category->delete();

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_category_deleted"),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_category_not_found"),
            ], 404);

        } catch (\Throwable $e) {
            \Log::error('MedicineCategory delete error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
