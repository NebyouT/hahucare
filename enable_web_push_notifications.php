<?php

/**
 * Enable OneSignal Web Push Notifications
 * 
 * This script enables OneSignal web push notifications for browser users
 * (admin, doctors, staff) to receive push notifications on the website.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "\n===========================================\n";
echo "   Enable OneSignal Web Push Notifications\n";
echo "===========================================\n\n";

echo "This will enable browser push notifications for:\n";
echo "   ✅ Admin users on the website\n";
echo "   ✅ Doctors using web browser\n";
echo "   ✅ Staff using web browser\n";
echo "   ✅ All users accessing via web\n\n";

// Step 1: Check if web_player_id column exists
echo "🔍 Step 1: Checking database schema...\n";

if (!Schema::hasColumn('users', 'web_player_id')) {
    echo "⚠️  Column 'web_player_id' not found in users table!\n";
    echo "   Adding column...\n";
    
    try {
        Schema::table('users', function ($table) {
            $table->string('web_player_id')->nullable()->after('player_id');
        });
        echo "✅ Column 'web_player_id' added successfully\n\n";
    } catch (\Exception $e) {
        echo "❌ Error adding column: {$e->getMessage()}\n\n";
        exit(1);
    }
} else {
    echo "✅ Column 'web_player_id' already exists\n\n";
}

// Step 2: Check OneSignal settings
echo "🔍 Step 2: Checking OneSignal configuration...\n\n";

$isOneSignalEnabled = Setting::where('name', 'is_one_signal_notification')->first();
$oneSignalAppId = Setting::where('name', 'onesignal_app_id')->first();

if (!$isOneSignalEnabled) {
    echo "📝 Creating 'is_one_signal_notification' setting...\n";
    Setting::create([
        'name' => 'is_one_signal_notification',
        'val' => '0',
        'type' => 'integration',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
    $isOneSignalEnabled = Setting::where('name', 'is_one_signal_notification')->first();
    echo "✅ Setting created\n\n";
}

if (!$oneSignalAppId) {
    echo "📝 Creating 'onesignal_app_id' setting...\n";
    Setting::create([
        'name' => 'onesignal_app_id',
        'val' => '',
        'type' => 'is_one_signal_notification',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
    $oneSignalAppId = Setting::where('name', 'onesignal_app_id')->first();
    echo "✅ Setting created\n\n";
}

echo "📋 Current Configuration:\n";
echo "   is_one_signal_notification: " . ($isOneSignalEnabled->val == '1' ? '✅ Enabled' : '❌ Disabled') . "\n";
echo "   onesignal_app_id: " . ($oneSignalAppId->val ? $oneSignalAppId->val : '❌ Not set') . "\n\n";

// Step 3: Guide user to get OneSignal App ID
if (empty($oneSignalAppId->val)) {
    echo "===========================================\n";
    echo "   ⚠️  OneSignal App ID Required\n";
    echo "===========================================\n\n";
    
    echo "You need to create a FREE OneSignal account and get your App ID.\n\n";
    
    echo "📚 How to Get OneSignal App ID:\n\n";
    
    echo "1. Go to OneSignal website:\n";
    echo "   https://onesignal.com/\n\n";
    
    echo "2. Click 'Sign Up' (it's FREE)\n\n";
    
    echo "3. After login, click 'New App/Website'\n\n";
    
    echo "4. Choose 'Web Push' platform\n\n";
    
    echo "5. Enter your website details:\n";
    echo "   - App Name: HahuCare\n";
    echo "   - Site URL: https://your-domain.com\n\n";
    
    echo "6. Complete the setup wizard\n\n";
    
    echo "7. Copy your App ID (looks like: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)\n\n";
    
    echo "8. Run this command to set it:\n";
    echo "   UPDATE settings SET val = 'YOUR_APP_ID' WHERE name = 'onesignal_app_id';\n\n";
    
    echo "9. Then run this script again\n\n";
    
    echo "===========================================\n\n";
}

// Step 4: Ask if user wants to enable it now
if ($isOneSignalEnabled->val != '1') {
    echo "===========================================\n";
    echo "   Enable Web Push Notifications?\n";
    echo "===========================================\n\n";
    
    if (!empty($oneSignalAppId->val)) {
        echo "Do you want to enable web push notifications now? (yes/no): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        $answer = trim(strtolower($line));
        fclose($handle);
        
        if ($answer === 'yes' || $answer === 'y') {
            $isOneSignalEnabled->val = '1';
            $isOneSignalEnabled->save();
            echo "\n✅ Web push notifications ENABLED!\n\n";
        } else {
            echo "\n⚠️  Web push notifications remain disabled\n";
            echo "   Enable manually in Admin Panel → Settings → Integration\n\n";
        }
    } else {
        echo "⚠️  Cannot enable without OneSignal App ID\n";
        echo "   Please set the App ID first (see instructions above)\n\n";
    }
}

// Step 5: Summary
echo "===========================================\n";
echo "   Configuration Summary\n";
echo "===========================================\n\n";

$isOneSignalEnabled = Setting::where('name', 'is_one_signal_notification')->first();
$oneSignalAppId = Setting::where('name', 'onesignal_app_id')->first();

$hasColumn = Schema::hasColumn('users', 'web_player_id');
$isEnabled = $isOneSignalEnabled && $isOneSignalEnabled->val == '1';
$hasAppId = $oneSignalAppId && !empty($oneSignalAppId->val);

echo "✅ Database column 'web_player_id': " . ($hasColumn ? "Present" : "Missing") . "\n";
echo ($isEnabled ? "✅" : "❌") . " Web push notifications: " . ($isEnabled ? "Enabled" : "Disabled") . "\n";
echo ($hasAppId ? "✅" : "❌") . " OneSignal App ID: " . ($hasAppId ? $oneSignalAppId->val : "Not set") . "\n\n";

if ($hasColumn && $isEnabled && $hasAppId) {
    echo "🎉 READY! Web push notifications are fully configured!\n\n";
    
    echo "===========================================\n";
    echo "   What Happens Next\n";
    echo "===========================================\n\n";
    
    echo "1. ✅ Users will see a permission prompt when they visit the website\n";
    echo "2. ✅ After allowing, they'll receive push notifications in browser\n";
    echo "3. ✅ Works even when browser tab is closed (if browser is running)\n";
    echo "4. ✅ Notifications sent for all appointment events automatically\n\n";
    
    echo "📱 Notification Types:\n";
    echo "   - New appointments\n";
    echo "   - Appointment updates/cancellations\n";
    echo "   - Payment confirmations\n";
    echo "   - Prescription ready\n";
    echo "   - And 20+ other events\n\n";
    
    echo "🧪 To Test:\n";
    echo "   1. Login to your admin panel\n";
    echo "   2. Allow notifications when prompted\n";
    echo "   3. Create a test appointment\n";
    echo "   4. You should receive a browser push notification!\n\n";
    
} else {
    echo "⚠️  Configuration incomplete!\n\n";
    
    if (!$hasAppId) {
        echo "❌ Missing: OneSignal App ID\n";
        echo "   Follow the instructions above to get it\n\n";
    }
    
    if (!$isEnabled) {
        echo "❌ Web push notifications are disabled\n";
        echo "   Enable in: Admin Panel → Settings → Integration\n";
        echo "   Or run this script again and choose 'yes'\n\n";
    }
}

echo "Done!\n\n";
