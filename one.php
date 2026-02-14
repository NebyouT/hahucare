<?php

/**
 * Configure OneSignal Web Push Notifications
 * 
 * This script configures OneSignal with the App ID provided by the user.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "\n===========================================\n";
echo "   Configure OneSignal Web Push\n";
echo "===========================================\n\n";

$oneSignalAppId = '275eb5fc-02c9-45da-bb47-b01edd3a9154';
$safariWebId = 'web.onesignal.auto.613528e9-2930-4b07-a098-5a9518822d98';

echo "📋 OneSignal Configuration:\n";
echo "   App ID: {$oneSignalAppId}\n";
echo "   Safari Web ID: {$safariWebId}\n\n";

// Update or create onesignal_app_id
$appIdSetting = Setting::where('name', 'onesignal_app_id')->first();

if ($appIdSetting) {
    echo "🔄 Updating existing onesignal_app_id setting...\n";
    $appIdSetting->val = $oneSignalAppId;
    $appIdSetting->updated_at = now();
    $appIdSetting->save();
} else {
    echo "📝 Creating onesignal_app_id setting...\n";
    Setting::create([
        'name' => 'onesignal_app_id',
        'val' => $oneSignalAppId,
        'type' => 'is_one_signal_notification',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
}

echo "✅ OneSignal App ID configured\n\n";

// Enable OneSignal notifications
$enabledSetting = Setting::where('name', 'is_one_signal_notification')->first();

if ($enabledSetting) {
    echo "🔄 Enabling OneSignal web push notifications...\n";
    $enabledSetting->val = '1';
    $enabledSetting->updated_at = now();
    $enabledSetting->save();
} else {
    echo "📝 Creating is_one_signal_notification setting...\n";
    Setting::create([
        'name' => 'is_one_signal_notification',
        'val' => '1',
        'type' => 'integration',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
}

echo "✅ OneSignal web push notifications ENABLED\n\n";

// Verify configuration
echo "===========================================\n";
echo "   Verification\n";
echo "===========================================\n\n";

$appIdCheck = Setting::where('name', 'onesignal_app_id')->first();
$enabledCheck = Setting::where('name', 'is_one_signal_notification')->first();

echo "✅ onesignal_app_id: " . ($appIdCheck ? $appIdCheck->val : 'NOT FOUND') . "\n";
echo "✅ is_one_signal_notification: " . ($enabledCheck && $enabledCheck->val == '1' ? 'Enabled' : 'Disabled') . "\n\n";

if ($appIdCheck && $appIdCheck->val == $oneSignalAppId && $enabledCheck && $enabledCheck->val == '1') {
    echo "🎉 SUCCESS! OneSignal is fully configured!\n\n";
    
    echo "===========================================\n";
    echo "   What to Do Next\n";
    echo "===========================================\n\n";
    
    echo "1. Clear Laravel cache:\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n\n";
    
    echo "2. Clear your browser cache:\n";
    echo "   Ctrl + Shift + Delete (Windows)\n";
    echo "   Cmd + Shift + Delete (Mac)\n\n";
    
    echo "3. Login to your admin panel:\n";
    echo "   https://your-domain.com/app/login\n\n";
    
    echo "4. You should see a notification permission prompt\n";
    echo "   Click 'Allow' or 'Subscribe'\n\n";
    
    echo "5. Test by creating an appointment\n";
    echo "   You should receive a browser push notification!\n\n";
    
    echo "===========================================\n";
    echo "   Notification Events\n";
    echo "===========================================\n\n";
    
    echo "Web push notifications will be sent for:\n";
    echo "   ✅ New appointments\n";
    echo "   ✅ Appointment updates/cancellations\n";
    echo "   ✅ Payment confirmations\n";
    echo "   ✅ Prescription ready\n";
    echo "   ✅ Patient check-ins\n";
    echo "   ✅ Medicine stock alerts\n";
    echo "   ✅ And 20+ other events\n\n";
    
    echo "📱 Who receives notifications:\n";
    echo "   ✅ Admin users (on website)\n";
    echo "   ✅ Doctors (on website)\n";
    echo "   ✅ Staff (on website)\n";
    echo "   ✅ Patients (on website)\n\n";
    
    echo "🌐 Works on:\n";
    echo "   ✅ Desktop browsers (Chrome, Firefox, Edge, Safari)\n";
    echo "   ✅ Mobile browsers\n";
    echo "   ✅ Even when browser tab is closed!\n\n";
    
} else {
    echo "❌ Configuration incomplete!\n\n";
    echo "Please check the settings manually in the database.\n\n";
}

echo "Done!\n\n";
