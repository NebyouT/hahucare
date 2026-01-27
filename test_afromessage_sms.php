<?php

/**
 * AfroMessage SMS Test Script
 * 
 * This script tests the AfroMessage SMS integration
 * by sending a test SMS to the specified phone number.
 * 
 * Usage: php test_afromessage_sms.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\AfroMessageService;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n===========================================\n";
echo "   AfroMessage SMS Integration Test\n";
echo "===========================================\n\n";

// Test phone number
$testPhoneNumber = '0912946688';
$testMessage = 'Hello! This is a test SMS from HahuCare using AfroMessage integration. If you receive this, the integration is working correctly.';

echo "📱 Test Phone Number: {$testPhoneNumber}\n";
echo "💬 Test Message: {$testMessage}\n\n";

// Display current configuration
echo "🔧 Current Configuration:\n";
echo "   API Token: " . (env('AFROMESSAGE_API_TOKEN') ? substr(env('AFROMESSAGE_API_TOKEN'), 0, 20) . '...' : 'NOT SET') . "\n";
echo "   Identifier ID: " . (env('AFROMESSAGE_IDENTIFIER_ID') ?: 'NOT SET') . "\n";
echo "   Sender: " . (env('AFROMESSAGE_SENDER') ?: 'NOT SET') . "\n";
echo "   Base URL: " . (env('AFROMESSAGE_BASE_URL') ?: 'NOT SET') . "\n\n";

// Check if credentials are configured
if (empty(env('AFROMESSAGE_API_TOKEN')) || empty(env('AFROMESSAGE_IDENTIFIER_ID'))) {
    echo "❌ ERROR: AfroMessage credentials not configured in .env file!\n";
    echo "   Please set the following in your .env file:\n";
    echo "   - AFROMESSAGE_API_TOKEN\n";
    echo "   - AFROMESSAGE_IDENTIFIER_ID\n";
    echo "   - AFROMESSAGE_SENDER (optional, defaults to 'HahuCare')\n\n";
    exit(1);
}

echo "🚀 Initializing AfroMessage Service...\n";

try {
    // Create AfroMessage service instance
    $smsService = new AfroMessageService();
    
    echo "✅ Service initialized successfully\n\n";
    
    echo "📤 Sending test SMS...\n";
    echo "   This may take a few seconds...\n\n";
    
    // Send the test SMS
    $result = $smsService->sendSms($testPhoneNumber, $testMessage);
    
    // Display results
    echo "===========================================\n";
    echo "   TEST RESULTS\n";
    echo "===========================================\n\n";
    
    if ($result['success']) {
        echo "✅ SUCCESS! SMS sent successfully\n\n";
        echo "📊 Response Details:\n";
        echo json_encode($result['data'], JSON_PRETTY_PRINT) . "\n\n";
        echo "✉️  The SMS should arrive at {$testPhoneNumber} shortly.\n";
        echo "   Please check the phone to confirm delivery.\n\n";
    } else {
        echo "❌ FAILED! SMS could not be sent\n\n";
        echo "📊 Error Details:\n";
        echo "   Message: {$result['message']}\n";
        if (isset($result['data'])) {
            echo "   Response: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
        
        // Provide troubleshooting tips
        echo "🔍 Troubleshooting Tips:\n";
        echo "   1. Verify your API Token is correct\n";
        echo "   2. Check your Identifier ID is valid\n";
        echo "   3. Ensure you have sufficient SMS credits\n";
        echo "   4. Verify the phone number format is correct\n";
        echo "   5. Check your internet connection\n\n";
    }
    
} catch (\Exception $e) {
    echo "===========================================\n";
    echo "   EXCEPTION OCCURRED\n";
    echo "===========================================\n\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n\n";
    
    echo "🔍 Stack Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
}

echo "===========================================\n";
echo "   Test Complete\n";
echo "===========================================\n\n";

// Check Laravel logs
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    echo "💡 TIP: Check Laravel logs for more details:\n";
    echo "   {$logFile}\n\n";
}

echo "Done!\n\n";
