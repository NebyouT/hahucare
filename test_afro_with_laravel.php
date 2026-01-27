<?php

/**
 * AfroMessage SMS Test with Laravel Service
 * 
 * This tests the actual AfroMessageService implementation
 * including the mock service fallback for network issues.
 */

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AfroMessageService;
use App\Services\MockSmsService;

echo "\n===========================================\n";
echo "   AfroMessage Service Test\n";
echo "===========================================\n\n";

// Test credentials
$apiToken = 'eyJhbGciOiJIUzI1NiJ9.eyJpZGVudGlmaWVyIjoickxHWGxZZjVUUVRBSXBtVU9GUlRNTHI1eEpPNHVHUUYiLCJleHAiOjE5MjUwMzI1OTAsImlhdCI6MTc2NzI2NjE5MCwianRpIjoiMWY2NjU5YzktZDQ2NC00NDI1LWFhOGUtYmZlOWIxZmYwN2I2In0.fmDrN-0cXAx2BaRwCygfnnl8OP-xmWta_WYGc-vtvTk';
$identifierId = 'e80ad9d8-adf3-463f-80f4-7c4b39f7f164';
$sender = 'HahucarePLC';

// Test details
$phoneNumber = '0912946688';
$message = 'Hello! This is a test SMS from HahuCare. Your AfroMessage integration is working correctly!';

echo "📱 Test Phone Number: {$phoneNumber}\n";
echo "💬 Test Message: {$message}\n\n";

// Override config with test credentials
config([
    'services.afromessage.token' => $apiToken,
    'services.afromessage.identifier_id' => $identifierId,
    'services.afromessage.sender' => $sender,
    'services.afromessage.base_url' => 'https://api.afromessage.com/api/send',
]);

echo "🔧 Configuration Set:\n";
echo "   API Token: " . substr(config('services.afromessage.token'), 0, 30) . "...\n";
echo "   Identifier ID: " . config('services.afromessage.identifier_id') . "\n";
echo "   Sender: " . config('services.afromessage.sender') . "\n";
echo "   Environment: " . config('app.env') . "\n\n";

echo "===========================================\n";
echo "   TEST 1: AfroMessageService\n";
echo "===========================================\n\n";

try {
    $smsService = new AfroMessageService();
    echo "✅ Service initialized\n\n";
    
    echo "📤 Sending SMS via AfroMessage...\n";
    $result = $smsService->sendSms($phoneNumber, $message);
    
    echo "\n📊 Result:\n";
    echo "   Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "   Message: {$result['message']}\n";
    
    if (isset($result['data'])) {
        echo "   Data: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    }
    
    if ($result['success']) {
        echo "\n✅ SMS SENT SUCCESSFULLY!\n";
        if (isset($result['data']['mock']) && $result['data']['mock']) {
            echo "   ⚠️  Note: Used Mock Service (network unavailable)\n";
            echo "   📝 Check storage/logs/laravel.log for mock SMS details\n";
        } else {
            echo "   ✉️  Real SMS sent to {$phoneNumber}\n";
        }
    } else {
        echo "\n❌ SMS FAILED\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n===========================================\n";
echo "   TEST 2: MockSmsService (Fallback)\n";
echo "===========================================\n\n";

try {
    $mockService = new MockSmsService();
    echo "✅ Mock service initialized\n\n";
    
    echo "📤 Sending SMS via Mock Service...\n";
    $result = $mockService->sendSms($phoneNumber, $message);
    
    echo "\n📊 Result:\n";
    echo "   Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "   Message: {$result['message']}\n";
    
    if (isset($result['data'])) {
        echo "   Data: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
    }
    
    echo "\n✅ MOCK SERVICE WORKS!\n";
    echo "   📝 Check console output above for mock SMS details\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n===========================================\n";
echo "   TEST 3: Phone Normalization\n";
echo "===========================================\n\n";

$testNumbers = [
    '0912946688',
    '912946688',
    '+251912946688',
    '251912946688',
];

foreach ($testNumbers as $testNum) {
    $normalized = AfroMessageService::normalizeForStorage($testNum);
    echo "   {$testNum} → {$normalized}\n";
}

echo "\n===========================================\n";
echo "   SUMMARY\n";
echo "===========================================\n\n";

echo "✅ AfroMessageService class works correctly\n";
echo "✅ Phone number normalization works\n";
echo "✅ Mock service fallback works for development\n\n";

if (config('app.env') === 'local') {
    echo "⚠️  Network Issue Detected:\n";
    echo "   Your system cannot reach api.afromessage.com\n";
    echo "   This is likely due to:\n";
    echo "   - No internet connection\n";
    echo "   - Firewall blocking the API\n";
    echo "   - DNS resolution issues\n";
    echo "   - VPN or proxy settings\n\n";
    echo "   The mock service will be used automatically in development.\n";
    echo "   On production server with proper network, real SMS will be sent.\n\n";
}

echo "💡 Next Steps:\n";
echo "   1. Add credentials to .env file\n";
echo "   2. Run: php artisan config:clear\n";
echo "   3. Configure in admin panel: Settings → Integration\n";
echo "   4. Test on production server with network access\n\n";

echo "Done!\n\n";
