<?php

/**
 * Test Firebase Push Notification
 * 
 * This script tests sending a push notification via Firebase FCM
 * to verify the configuration is working correctly.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Setting;
use Google\Client as Google_Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

echo "\n===========================================\n";
echo "   Firebase Push Notification Test\n";
echo "===========================================\n\n";

// Check Firebase configuration
echo "🔍 Checking Firebase configuration...\n\n";

$projectId = Setting::where('name', 'firebase_project_id')->first();

if (!$projectId) {
    echo "❌ ERROR: firebase_project_id not found in settings!\n";
    echo "   Run: php setup_firebase_push.php\n\n";
    exit(1);
}

echo "✅ Firebase Project ID: {$projectId->val}\n";

// Check for service account
$directory = storage_path('app/data');
$credentialsFiles = File::glob($directory . '/*.json');

if (empty($credentialsFiles)) {
    echo "❌ ERROR: No Firebase Admin SDK service account found!\n";
    echo "   Expected location: {$directory}/*.json\n\n";
    exit(1);
}

echo "✅ Service Account: " . basename($credentialsFiles[0]) . "\n\n";

// Check for users with device tokens
echo "🔍 Looking for users with device tokens (player_id)...\n\n";

$usersWithTokens = User::whereNotNull('player_id')
    ->where('player_id', '!=', '')
    ->get();

if ($usersWithTokens->isEmpty()) {
    echo "⚠️  No users found with device tokens (player_id)!\n\n";
    echo "📱 To receive push notifications, users need to:\n";
    echo "   1. Install your mobile app (Flutter)\n";
    echo "   2. Log in to the app\n";
    echo "   3. App sends FCM device token to backend\n";
    echo "   4. Token stored in users.player_id column\n\n";
    
    echo "🧪 Creating a TEST push notification anyway...\n";
    echo "   (It won't be delivered since no devices are registered)\n\n";
    
    // Use a test topic instead
    $testTopic = 'test_topic';
    $testUser = User::first();
    
    if (!$testUser) {
        echo "❌ No users found in database!\n\n";
        exit(1);
    }
    
    echo "📝 Test Details:\n";
    echo "   Topic: {$testTopic}\n";
    echo "   User: {$testUser->full_name} (ID: {$testUser->id})\n\n";
    
} else {
    echo "✅ Found " . $usersWithTokens->count() . " user(s) with device tokens:\n\n";
    
    foreach ($usersWithTokens as $user) {
        $tokenPreview = substr($user->player_id, 0, 30) . '...';
        echo "   👤 {$user->full_name} (ID: {$user->id})\n";
        echo "      Token: {$tokenPreview}\n\n";
    }
    
    $testUser = $usersWithTokens->first();
    echo "📤 Will send test notification to: {$testUser->full_name}\n\n";
}

// Get access token
echo "🔑 Getting Firebase access token...\n";

try {
    $client = new Google_Client();
    $client->setAuthConfig($credentialsFiles[0]);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    
    $token = $client->fetchAccessTokenWithAssertion();
    $accessToken = $token['access_token'];
    
    echo "✅ Access token obtained\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR getting access token: {$e->getMessage()}\n\n";
    exit(1);
}

// Prepare test notification
$testTitle = "🧪 Test Notification from HahuCare";
$testBody = "This is a test push notification. If you see this, Firebase push notifications are working correctly!";
$testTopic = 'user_' . $testUser->id;

echo "📋 Notification Details:\n";
echo "   Title: {$testTitle}\n";
echo "   Body: {$testBody}\n";
echo "   Topic: {$testTopic}\n\n";

// Build FCM message
$message = [
    "message" => [
        "topic" => $testTopic,
        "notification" => [
            "title" => $testTitle,
            "body" => $testBody,
        ],
        "data" => [
            "type" => "test_notification",
            "test" => "true",
            "timestamp" => date('Y-m-d H:i:s'),
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        ],
        "android" => [
            "priority" => "high",
            "notification" => [
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "sound" => "default",
            ],
        ],
        "apns" => [
            "payload" => [
                "aps" => [
                    "sound" => "default",
                    "badge" => 1,
                ],
            ],
        ],
    ],
];

echo "🚀 Sending push notification to Firebase...\n\n";

// Send to Firebase FCM API
$url = 'https://fcm.googleapis.com/v1/projects/' . $projectId->val . '/messages:send';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "===========================================\n";
echo "   RESULTS\n";
echo "===========================================\n\n";

echo "📊 HTTP Status Code: {$httpCode}\n\n";

if ($curlError) {
    echo "❌ cURL Error: {$curlError}\n\n";
} else {
    echo "📄 Firebase Response:\n";
    $responseData = json_decode($response, true);
    echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($httpCode == 200) {
        echo "✅ SUCCESS! Push notification sent to Firebase!\n\n";
        
        if ($usersWithTokens->isEmpty()) {
            echo "⚠️  Note: No devices registered, so notification won't be delivered\n";
            echo "   But the Firebase API is working correctly!\n\n";
        } else {
            echo "📱 The notification should appear on the user's device shortly\n";
            echo "   (if the app is installed and user is subscribed to topic: {$testTopic})\n\n";
        }
        
        echo "💡 What happens next:\n";
        echo "   1. Firebase received the notification\n";
        echo "   2. Firebase will deliver to devices subscribed to: {$testTopic}\n";
        echo "   3. Mobile app will display the notification\n";
        echo "   4. User can tap to open the app\n\n";
        
    } else {
        echo "❌ FAILED! Firebase returned an error\n\n";
        
        if (isset($responseData['error'])) {
            echo "Error Details:\n";
            echo "   Code: " . ($responseData['error']['code'] ?? 'N/A') . "\n";
            echo "   Message: " . ($responseData['error']['message'] ?? 'N/A') . "\n";
            echo "   Status: " . ($responseData['error']['status'] ?? 'N/A') . "\n\n";
        }
        
        echo "🔍 Common Issues:\n";
        echo "   - Service account doesn't have FCM permissions\n";
        echo "   - Project ID mismatch\n";
        echo "   - Firebase Cloud Messaging API not enabled\n";
        echo "   - Invalid topic name\n\n";
    }
}

echo "===========================================\n";
echo "   Summary\n";
echo "===========================================\n\n";

echo "Configuration:\n";
echo "   ✅ Firebase Project ID: {$projectId->val}\n";
echo "   ✅ Service Account: Present\n";
echo "   ✅ Access Token: Obtained\n";
echo "   " . ($usersWithTokens->isEmpty() ? "⚠️ " : "✅") . " Users with tokens: " . $usersWithTokens->count() . "\n";
echo "   " . ($httpCode == 200 ? "✅" : "❌") . " Firebase API: " . ($httpCode == 200 ? "Working" : "Error") . "\n\n";

if ($httpCode == 200) {
    echo "🎉 Push notification system is working!\n\n";
    
    echo "Next steps:\n";
    echo "   1. Ensure mobile apps subscribe to user topics (user_{id})\n";
    echo "   2. Test by creating a real appointment\n";
    echo "   3. Check Laravel logs: storage/logs/laravel.log\n";
    echo "   4. Monitor Firebase Console for delivery stats\n\n";
} else {
    echo "⚠️  Push notification system needs attention\n\n";
    echo "   Check the error details above and fix the issue\n\n";
}

echo "Done!\n\n";
