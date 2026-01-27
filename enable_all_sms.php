<?php

/**
 * Enable SMS for All Important Notification Types
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\NotificationTemplate\Models\NotificationTemplate;

echo "\n===========================================\n";
echo "   Enable SMS for All Notifications\n";
echo "===========================================\n\n";

// Enable SMS for all notification types
$templates = NotificationTemplate::all();

$enabled = 0;
$alreadyEnabled = 0;

foreach ($templates as $template) {
    $channels = $template->channels;
    $currentStatus = isset($channels['IS_SMS']) ? $channels['IS_SMS'] : '0';
    
    if ($currentStatus == '0') {
        $channels['IS_SMS'] = '1';
        $template->channels = $channels;
        $template->save();
        echo "✅ Enabled SMS for: {$template->label}\n";
        $enabled++;
    } else {
        echo "ℹ️  Already enabled: {$template->label}\n";
        $alreadyEnabled++;
    }
}

echo "\n===========================================\n";
echo "   SUMMARY\n";
echo "===========================================\n\n";

echo "✅ Newly enabled: {$enabled} templates\n";
echo "ℹ️  Already enabled: {$alreadyEnabled} templates\n";
echo "📊 Total templates: " . ($enabled + $alreadyEnabled) . "\n\n";

echo "🎉 All notification templates now have SMS enabled!\n\n";

echo "Done!\n\n";
