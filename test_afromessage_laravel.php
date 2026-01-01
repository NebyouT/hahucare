<?php

/**
 * AfroMessage SMS API Test Script (Laravel Integration)
 * 
 * This script tests the AfroMessage SMS integration using Laravel's
 * service container and configuration.
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AfroMessageService;

echo "=== AfroMessage Laravel Service Test ===\n";
echo "Testing AfroMessage integration through Laravel service\n";
echo "========================================\n\n";

// Test phone number
$testPhone = '0912946688';

// Test message
$testMessage = 'Hello! This is a test message from HahuCare SMS system. Your OTP integration is working correctly. Time: ' . date('Y-m-d H:i:s');

echo "📱 Test Phone: {$testPhone}\n";
echo "💬 Test Message: {$testMessage}\n\n";

try {
    // Create AfroMessage service instance
    $smsService = new AfroMessageService();
    
    echo "✅ AfroMessage service created successfully\n\n";
    
    // Test configuration
    echo "=== Configuration Check ===\n";
    $config = config('services.afromessage');
    
    echo "API Token: " . (empty($config['token']) ? '❌ NOT SET' : '✅ SET (' . strlen($config['token']) . ' chars)') . "\n";
    echo "Identifier ID: " . (empty($config['identifier_id']) ? '❌ NOT SET' : '✅ SET (' . $config['identifier_id'] . ')') . "\n";
    echo "Base URL: " . ($config['base_url'] ?? 'NOT SET') . "\n";
    echo "Sender: " . ($config['sender'] ?? 'NOT SET') . "\n\n";
    
    if (empty($config['token']) || empty($config['identifier_id'])) {
        echo "❌ ERROR: Missing required configuration. Please set AFROMESSAGE_API_TOKEN and AFROMESSAGE_IDENTIFIER_ID in .env file\n";
        exit(1);
    }
    
    // Test phone number normalization
    echo "=== Phone Number Normalization Test ===\n";
    $normalizedPhone = AfroMessageService::normalizeForStorage($testPhone);
    echo "Original: {$testPhone}\n";
    echo "Normalized: {$normalizedPhone}\n\n";
    
    // Test SMS sending (this will make actual API call)
    echo "=== SMS Sending Test ===\n";
    echo "🚀 Attempting to send SMS...\n";
    
    $result = $smsService->sendSms($testPhone, $testMessage);
    
    echo "📊 Result:\n";
    echo "Success: " . ($result['success'] ? '✅ YES' : '❌ NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    
    if (isset($result['data'])) {
        echo "Response Data:\n";
        print_r($result['data']);
    }
    
    if ($result['success']) {
        echo "\n🎉 SMS sent successfully!\n";
        echo "Check your phone {$testPhone} for the test message.\n";
    } else {
        echo "\n❌ SMS sending failed.\n";
        echo "This could be due to:\n";
        echo "- Invalid API credentials\n";
        echo "- Network connectivity issues\n";
        echo "- Invalid phone number\n";
        echo "- AfroMessage service issues\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";

// Additional OTP-specific test
echo "\n=== OTP SMS Test ===\n";
try {
    $testOtp = '123456';
    echo "🔐 Testing OTP SMS with code: {$testOtp}\n";
    
    $smsService = new AfroMessageService();
    $otpResult = $smsService->sendOtp($testPhone, $testOtp);
    
    echo "📊 OTP Result:\n";
    echo "Success: " . ($otpResult['success'] ? '✅ YES' : '❌ NO') . "\n";
    echo "Message: " . $otpResult['message'] . "\n";
    
    if ($otpResult['success']) {
        echo "\n🎉 OTP SMS sent successfully!\n";
        echo "The OTP login system should work correctly.\n";
    }
    
} catch (Exception $e) {
    echo "❌ OTP Test ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Environment Information ===\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Environment: " . config('app.env') . "\n";
echo "Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
echo "==============================\n";
