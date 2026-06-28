<?php
$file = __DIR__ . '\Modules\Appointment\Models\PatientEncounter.php';
$content = file_get_contents($file);

$search = "    public function billingDetail()\r\n    {\r\n        return \$this->hasOne(\Modules\Appointment\Models\EncounterPrescriptionBillingDetail::class, 'encounter_id', 'id');\r\n    }\r\n\r\n    public function encounterPrescription()";

$replace = "    public function billingDetail()\r\n    {\r\n        return \$this->hasOne(\Modules\Appointment\Models\EncounterPrescriptionBillingDetail::class, 'encounter_id', 'id');\r\n    }\r\n\r\n    public function labBillingDetail()\r\n    {\r\n        return \$this->hasOne(\Modules\Appointment\Models\LabOrderBillingDetail::class, 'encounter_id', 'id');\r\n    }\r\n\r\n    public function labOrders()\r\n    {\r\n        return \$this->hasMany(\Modules\Laboratory\Models\LabOrder::class, 'encounter_id', 'id');\r\n    }\r\n\r\n    public function encounterPrescription()";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "OK\n";
} else {
    echo "NOT_FOUND\n";
    // Debug: find billingDetail position
    $p = strpos($content, 'public function billingDetail');
    if ($p !== false) {
        echo "Found billingDetail at $p\n";
        $chunk = substr($content, $p, strpos($content, 'public function encounterPrescription') + 35 - $p);
        echo "Chunk length: " . strlen($chunk) . "\n";
        echo "Hex: " . bin2hex($chunk) . "\n";
    }
}
