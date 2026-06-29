<?php

namespace Modules\PatientReferral\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PatientReferral\Models\PatientReferral;
use Modules\PatientReferral\Transformers\PatientReferralResource;

class PatientReferralAPIController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
            ->where('patient_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $referrals = $query->paginate($perPage);

        $collection = PatientReferralResource::collection($referrals);

        return response()->json([
            'status' => true,
            'data' => $collection,
            'message' => 'Referral list retrieved successfully',
        ], 200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:patient_referrals,id',
        ]);

        $referral = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
            ->where('id', $request->id)
            ->where('patient_id', auth()->id())
            ->first();

        if (!$referral) {
            return response()->json([
                'status' => false,
                'message' => 'Referral not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new PatientReferralResource($referral),
            'message' => 'Referral detail retrieved successfully',
        ], 200);
    }
}
