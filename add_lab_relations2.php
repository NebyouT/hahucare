<?php
$file = __DIR__ . '/Modules/Appointment/Models/PatientEncounter.php';
$content = file_get_contents($file);

$search = "    public function billingDetail()
    {
        return \THIS_VAR->hasOne(\Modules\Appointment\Models\EncounterPrescriptionBillingDetail::class, 'encounter_id', 'id');
    }

    public function encounterPrescription()";

$replace = "    public function billingDetail()
    {
        return \THIS_VAR->hasOne(\Modules\Appointment\Models\EncounterPrescriptionBillingDetail::class, 'encounter_id', 'id');
    }

    public function labBillingDetail()
    {
        return \THIS_VAR->hasOne(\Modules\Appointment\Models\LabOrderBillingDetail::class, 'encounter_id', 'id');
    }

    public function labOrders()
    {
        return \THIS_VAR->hasMany(\Modules\Laboratory\Models\LabOrder::class, 'encounter_id', 'id');
    }

    public function encounterPrescription()";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "OK\n";
} else {
    echo "NOT_FOUND\n";
}
