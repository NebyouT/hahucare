$doctor = \App\Models\User::find(19); // The doctor user ID
echo 'Doctor roles: ' . $doctor->getRoleNames()->implode(', ') . PHP_EOL;
echo 'Doctor permissions: ' . $doctor->getAllPermissions()->pluck('name')->implode(', ') . PHP_EOL;
echo 'Can view patient referral: ' . ($doctor->can('view_patient_referral') ? 'YES' : 'NO') . PHP_EOL;