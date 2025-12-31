<?php

namespace Modules\Pharma\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Pharma\Transformers\PayoutHistoryResource;
use App\Models\User;
use Modules\Commission\Models\Commission;
use Modules\Clinic\Transformers\CommissionResource;
use Modules\Clinic\Transformers\EmployeeCommissionResource;

class PharmaController extends Controller
{
    public function payoutHistory(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $user = auth()->user();

            $payout_data = EmployeeEarning::with('employee')
                ->where('user_type', 'pharma')
                ->orderBy('updated_at', 'desc');
            if (
                $user &&
                (
                    (isset($user->user_type) && $user->user_type == 'pharma') ||
                    (method_exists($user, 'getRoleNames') && $user->getRoleNames()->contains('pharma'))
                )
            ) {
                $payout_data->where('employee_id', $user->id);
            }
            $payout_data = $payout_data->paginate($perPage);
            $responseData = PayoutHistoryResource::collection($payout_data);

            return response()->json([
                'status' => true,
                'data' => $responseData,
                'message' => __('clinic.commission_list'),
            ], 200);
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

    public function listPharma(Request $request)
    {
         $clinicId = $request->input('clinic_id');

        $query = User::pharmaRole(auth()->user())->with('pharmaCommission')->where('user_type', 'pharma');
        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $pharmas = $query->get();
        $pharmas = $pharmas->map(function ($pharma) {
            $pharma->commission = EmployeeCommissionResource::collection($pharma->pharmaCommission);
            return $pharma;
        });

        return response()->json([
            'status' => true,
            'message' => 'Pharma list fetched successfully',
            'data' => $pharmas
        ]);
    }

    public function pharmaCommissionList(Request $request){
        $perPage = $request->input('per_page', 10);
        $commission_list = Commission::where('type', 'pharma_commission')->where('status', 1);

        $commission = $commission_list->orderBy('updated_at', 'desc');

        $commission = $commission->paginate($perPage);
        $responseData = CommissionResource::collection($commission);

        return response()->json([
            'status' => true,
            'data' => $responseData,
            'message' => __('clinic.commission_list'),
        ], 200);
    }

}
