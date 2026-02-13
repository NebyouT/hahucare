<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Transformers\MedicineFormResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MedicineFormController extends Controller
{
    public function list(Request $request)
    {
        try{
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $medicineFormQuery = MedicineForm::query()
            ->where('status', 1)
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })
                ->orderBy('name');

            $medicineFormPaginated = $medicineFormQuery->paginate($perPage);
            $medicineFormCollection = MedicineFormResource::collection($medicineFormPaginated);

            $response = [
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_form"),
                'data' => $medicineFormCollection,
            ];
            return comman_custom_response($response, 200);
        } catch (\Throwable $e) {
            \Log::error('Medicine Form list error: ' . $e->getMessage());
            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => 'Something went wrong while fetching medicine form list.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:medicine_forms,name,' . $request->id,
            'status' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'status' => $request->status ? 1 : 0,
            ];

            if ($request->filled('id')) {
                // Update existing medicine form
                $medicineForm = MedicineForm::findOrFail($request->id);
                $medicineForm->update($data);
                $message = __("pharma::messages.medicine_form_updated");
            } else {
                // Create new medicine form
                $medicineForm = MedicineForm::create($data);
                $message = __("pharma::messages.medicine_form_added");
            }

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => $message,
                'data' => $medicineForm,
            ], 200);

        } catch (\Throwable $e) {


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
            $form = MedicineForm::findOrFail($request->id);

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_form"),
                'data' => $form,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_form_not_found"),
            ], 404);

        } catch (\Throwable $e) {


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
            $form = MedicineForm::findOrFail($id);
            $form->delete();

            return comman_custom_response([
                'code' => 200,
                'status' => true,
                'message' => __("pharma::messages.medicine_form_deleted"),
            ], 200);

        } catch (ModelNotFoundException $e) {
            return comman_custom_response([
                'code' => 404,
                'status' => false,
                'message' => __("pharma::messages.medicine_form_not_found"),
            ], 404);

        } catch (\Throwable $e) {


            return comman_custom_response([
                'code' => 500,
                'status' => false,
                'message' => __("pharma::messages.something_went_wrong"),
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
