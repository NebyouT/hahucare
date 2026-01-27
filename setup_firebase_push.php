<?php

/**
 * Setup Firebase Push Notifications
 * 
 * This script configures Firebase push notifications using your
 * google-services.json file and guides you to get the Admin SDK.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "\n===========================================\n";
echo "   Firebase Push Notification Setup\n";
echo "===========================================\n\n";

// Read google-services.json
$googleServicesPath = __DIR__ . '/google-services.json';

if (!file_exists($googleServicesPath)) {
    echo "❌ ERROR: google-services.json not found in root directory!\n\n";
    exit(1);
}

echo "✅ Found google-services.json\n\n";

$googleServices = json_decode(file_get_contents($googleServicesPath), true);

if (!$googleServices) {
    echo "❌ ERROR: Could not parse google-services.json\n\n";
    exit(1);
}

// Extract project information
$projectId = $googleServices['project_info']['project_id'] ?? null;
$projectNumber = $googleServices['project_info']['project_number'] ?? null;
$storageBucket = $googleServices['project_info']['storage_bucket'] ?? null;

echo "📋 Firebase Project Information:\n";
echo "   Project ID: {$projectId}\n";
echo "   Project Number: {$projectNumber}\n";
echo "   Storage Bucket: {$storageBucket}\n\n";

// Check if firebase_project_id already exists in settings
$existingSetting = Setting::where('name', 'firebase_project_id')->first();

if ($existingSetting) {
    echo "🔍 Current firebase_project_id in database: {$existingSetting->val}\n";
    
    if ($existingSetting->val !== $projectId) {
        echo "⚠️  Updating to match google-services.json...\n";
        $existingSetting->val = $projectId;
        $existingSetting->save();
        echo "✅ Updated firebase_project_id to: {$projectId}\n\n";
    } else {
        echo "✅ Already configured correctly\n\n";
    }
} else {
    echo "📝 Creating firebase_project_id setting...\n";
    Setting::create([
        'name' => 'firebase_project_id',
        'val' => $projectId,
        'type' => 'integration',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
    echo "✅ Created firebase_project_id: {$projectId}\n\n";
}

// Check for Firebase Admin SDK service account
$serviceAccountPath = storage_path('app/data');
$serviceAccountFiles = glob($serviceAccountPath . '/*.json');

echo "===========================================\n";
echo "   Firebase Admin SDK Service Account\n";
echo "===========================================\n\n";

if (empty($serviceAccountFiles)) {
    echo "⚠️  No Firebase Admin SDK service account found!\n\n";
    echo "📚 IMPORTANT: google-services.json vs Firebase Admin SDK\n\n";
    echo "   google-services.json:\n";
    echo "   ✓ Used by Android/iOS mobile apps\n";
    echo "   ✓ Contains client credentials\n";
    echo "   ✓ Safe to include in mobile apps\n";
    echo "   ✗ CANNOT send push notifications from backend\n\n";
    
    echo "   Firebase Admin SDK (service account):\n";
    echo "   ✓ Used by backend servers\n";
    echo "   ✓ Contains private key for authentication\n";
    echo "   ✓ CAN send push notifications\n";
    echo "   ✗ Must be kept SECRET (never commit to git)\n\n";
    
    echo "===========================================\n";
    echo "   How to Get Firebase Admin SDK\n";
    echo "===========================================\n\n";
    
    echo "1. Go to Firebase Console:\n";
    echo "   https://console.firebase.google.com/project/{$projectId}/settings/serviceaccounts/adminsdk\n\n";
    
    echo "2. Click 'Generate new private key'\n\n";
    
    echo "3. Download the JSON file (it will be named something like):\n";
    echo "   hahucare-9fe67-firebase-adminsdk-xxxxx-xxxxxxxxxx.json\n\n";
    
    echo "4. Move it to:\n";
    echo "   " . storage_path('app/data/') . "\n\n";
    
    echo "5. Make sure the directory exists:\n";
    echo "   mkdir -p " . storage_path('app/data') . "\n\n";
    
    echo "6. Run this script again to verify\n\n";
    
    echo "⚠️  SECURITY WARNING:\n";
    echo "   - Add storage/app/data/*.json to .gitignore\n";
    echo "   - Never commit service account files to git\n";
    echo "   - Keep this file secure on your server\n\n";
    
} else {
    echo "✅ Found Firebase Admin SDK service account!\n\n";
    
    foreach ($serviceAccountFiles as $file) {
        $fileName = basename($file);
        $fileSize = filesize($file);
        echo "   📄 {$fileName} (" . number_format($fileSize) . " bytes)\n";
        
        // Validate the service account file
        $serviceAccount = json_decode(file_get_contents($file), true);
        
        if ($serviceAccount && isset($serviceAccount['type']) && $serviceAccount['type'] === 'service_account') {
            echo "   ✅ Valid service account file\n";
            echo "   📧 Service account email: {$serviceAccount['client_email']}\n";
            echo "   🔑 Private key: Present\n";
            
            // Check if project_id matches
            if (isset($serviceAccount['project_id']) && $serviceAccount['project_id'] === $projectId) {
                echo "   ✅ Project ID matches: {$projectId}\n";
            } else {
                echo "   ⚠️  Project ID mismatch!\n";
                echo "      Service account: {$serviceAccount['project_id']}\n";
                echo "      google-services.json: {$projectId}\n";
            }
        } else {
            echo "   ⚠️  This doesn't look like a valid service account file\n";
        }
        echo "\n";
    }
}

echo "===========================================\n";
echo "   Configuration Summary\n";
echo "===========================================\n\n";

$hasProjectId = Setting::where('name', 'firebase_project_id')->exists();
$hasServiceAccount = !empty($serviceAccountFiles);

echo "✅ google-services.json: Present\n";
echo ($hasProjectId ? "✅" : "❌") . " firebase_project_id in database: " . ($hasProjectId ? $projectId : "Not set") . "\n";
echo ($hasServiceAccount ? "✅" : "❌") . " Firebase Admin SDK: " . ($hasServiceAccount ? "Present" : "Missing") . "\n";
echo "✅ Push notification templates: Enabled (by default)\n\n";

if ($hasProjectId && $hasServiceAccount) {
    echo "🎉 READY! Push notifications are fully configured!\n\n";
    
    echo "===========================================\n";
    echo "   Next Steps\n";
    echo "===========================================\n\n";
    
    echo "1. ✅ Backend is ready to send push notifications\n";
    echo "2. 📱 Ensure mobile apps are configured with google-services.json\n";
    echo "3. 🔔 Mobile apps must send device tokens (FCM tokens) to backend\n";
    echo "4. 💾 Device tokens stored in users.player_id column\n";
    echo "5. 🧪 Test by creating an appointment for a user with player_id\n\n";
    
    echo "💡 To test push notifications:\n";
    echo "   - User must have mobile app installed\n";
    echo "   - User must be logged in to app\n";
    echo "   - App must have sent FCM token to backend\n";
    echo "   - Check users.player_id is not null\n\n";
    
} else {
    echo "⚠️  Configuration incomplete!\n\n";
    
    if (!$hasServiceAccount) {
        echo "❌ Missing: Firebase Admin SDK service account\n";
        echo "   Follow the steps above to download it\n\n";
    }
}

echo "Done!\n\n";
