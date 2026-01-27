<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "\n===========================================\n";
echo "   Firebase Project ID Status\n";
echo "===========================================\n\n";

$setting = Setting::where('name', 'firebase_project_id')->first();

if ($setting) {
    echo "✅ Firebase Project ID is already configured!\n\n";
    echo "📋 Details:\n";
    echo "   Project ID: {$setting->val}\n";
    echo "   Type: {$setting->type}\n";
    echo "   Created: " . ($setting->created_at ? $setting->created_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
    echo "   Updated: " . ($setting->updated_at ? $setting->updated_at->format('Y-m-d H:i:s') : 'N/A') . "\n\n";
    
    echo "✅ No action needed - already set up correctly!\n\n";
} else {
    echo "❌ Firebase Project ID not found in database\n\n";
    echo "Run this command to set it up:\n";
    echo "   php setup_firebase_push.php\n\n";
}

echo "Done!\n\n";
