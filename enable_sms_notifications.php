<?php

/**
 * Enable SMS Notifications for Appointment Events
 * 
 * This script enables the IS_SMS channel for appointment-related
 * notification templates so SMS will be sent automatically.
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\NotificationTemplate\Models\NotificationTemplate;

echo "\n===========================================\n";
echo "   Enable SMS for Appointment Notifications\n";
echo "===========================================\n\n";

// Notification types to enable SMS for
$notificationTypes = [
    'new_appointment' => 'New Appointment Booked',
    'appointment_status' => 'Appointment Status Changed',
    'cancel_appointment' => 'Appointment Cancelled',
    'appointment_approved' => 'Appointment Approved',
    'check_in' => 'Patient Check-in',
    'check_out' => 'Patient Check-out',
];

echo "📋 Notification types to update:\n";
foreach ($notificationTypes as $type => $label) {
    echo "   - {$label} ({$type})\n";
}
echo "\n";

$updated = 0;
$notFound = 0;

foreach ($notificationTypes as $type => $label) {
    echo "🔍 Checking: {$label}...\n";
    
    $template = NotificationTemplate::where('type', $type)->first();
    
    if ($template) {
        $channels = $template->channels;
        
        // Check current status
        $currentStatus = isset($channels['IS_SMS']) ? $channels['IS_SMS'] : '0';
        echo "   Current SMS status: " . ($currentStatus == '1' ? '✅ Enabled' : '❌ Disabled') . "\n";
        
        if ($currentStatus == '0') {
            // Enable SMS
            $channels['IS_SMS'] = '1';
            $template->channels = $channels;
            $template->save();
            
            echo "   ✅ SMS ENABLED!\n";
            $updated++;
        } else {
            echo "   ℹ️  Already enabled\n";
        }
    } else {
        echo "   ⚠️  Template not found in database\n";
        $notFound++;
    }
    echo "\n";
}

echo "===========================================\n";
echo "   SUMMARY\n";
echo "===========================================\n\n";

echo "✅ Updated: {$updated} templates\n";
if ($notFound > 0) {
    echo "⚠️  Not found: {$notFound} templates\n";
}
echo "\n";

// Show all notification templates with SMS status
echo "📊 All Notification Templates SMS Status:\n\n";

$allTemplates = NotificationTemplate::all();

foreach ($allTemplates as $template) {
    $channels = $template->channels;
    $smsStatus = isset($channels['IS_SMS']) && $channels['IS_SMS'] == '1' ? '✅' : '❌';
    echo "   {$smsStatus} {$template->label} ({$template->type})\n";
}

echo "\n===========================================\n";
echo "   Next Steps\n";
echo "===========================================\n\n";

echo "1. ✅ SMS notifications are now enabled for appointment events\n";
echo "2. 🔧 Make sure SMS integration is enabled in admin panel:\n";
echo "      Settings → Integration → Enable SMS Integration\n";
echo "3. 📝 Enter your AfroMessage credentials:\n";
echo "      - API Token\n";
echo "      - Identifier ID\n";
echo "      - Sender Name\n";
echo "4. 🧪 Test by creating a new appointment\n";
echo "5. 📱 SMS will be sent automatically to patients/doctors\n\n";

echo "💡 To manually enable/disable SMS for specific templates:\n";
echo "   Go to: Admin Panel → Notification Templates → Edit Template\n";
echo "   Toggle the SMS channel on/off\n\n";

echo "Done!\n\n";
