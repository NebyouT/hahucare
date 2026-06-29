<?php

namespace Modules\PatientReferral\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PatientReferralResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'referral_code' => $this->referral_code,
            'referral_type' => $this->referral_type,
            'patient_id' => $this->patient_id,
            'patient_name' => optional($this->patient)->full_name ?? 'N/A',
            'patient_age' => $this->patient_age,
            'patient_sex' => $this->patient_sex,
            'patient_address' => $this->patient_address,
            'referred_by' => $this->referred_by,
            'referred_by_name' => optional($this->referredByDoctor)->full_name ?? 'N/A',
            'referred_by_faculty' => $this->referring_faculty,
            'referred_to' => $this->referred_to,
            'referred_to_name' => optional($this->referredToDoctor)->full_name ?? 'N/A',
            'receiving_faculty' => $this->receiving_faculty,
            'referring_clinic_name' => $this->referring_clinic_name,
            'chief_complaint' => $this->chief_complaint,
            'history_findings' => $this->history_findings,
            'diagnosis' => $this->diagnosis,
            'treatment_given' => $this->treatment_given,
            'investigation_done' => $this->investigation_done,
            'contact_information' => $this->contact_information,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => $this->status,
            'referral_date' => $this->referral_date ? Carbon::parse($this->referral_date)->format('Y-m-d') : null,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
