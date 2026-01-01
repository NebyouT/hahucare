<?php

/**
 * AfroMessage SMS API Test Script
 * 
 * This script tests the AfroMessage SMS API integration
 * by sending a test message to the specified phone number.
 */

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// AfroMessage API Configuration from .env
$apiToken = $_ENV['AFROMESSAGE_API_TOKEN'] ?? '';
$identifierId = $_ENV['AFROMESSAGE_IDENTIFIER_ID'] ?? '';
$baseUrl = $_ENV['AFROMESSAGE_BASE_URL'] ?? 'https://api.afromessage.com/api/send';
$sender = $_ENV['AFROMESSAGE_SENDER'] ?? 'HahuCare';

// Test phone number
$testPhone = '0912946688';

// Test message
$testMessage = 'Hello! This is a test message from HahuCare SMS system. Your OTP integration is working correctly. Time: ' . date('Y-m-d H:i:s');

echo "=== AfroMessage SMS API Test ===\n";
echo "Phone: {$testPhone}\n";
echo "Message: {$testMessage}\n";
echo "API URL: {$baseUrl}\n";
echo "Sender: {$sender}\n";
echo "================================\n\n";

// Validate configuration
if (empty($apiToken)) {
    echo "❌ ERROR: AFROMESSAGE_API_TOKEN is not set in .env file\n";
    exit(1);
}

if (empty($identifierId)) {
    echo "❌ ERROR: AFROMESSAGE_IDENTIFIER_ID is not set in .env file\n";
    exit(1);
}

echo "✅ Configuration loaded successfully\n\n";

/**
 * Normalize phone number to Ethiopian format (+251)
 */
function normalizePhoneNumber($phone) {
    // Remove all non-numeric characters except +
    $phone = preg_replace('/[^0-9+]/', '', $phone);

    // If starts with 0, replace with +251
    if (str_starts_with($phone, '0')) {
        $phone = '+251' . substr($phone, 1);
    }
    // If starts with 251 without +, add +
    elseif (str_starts_with($phone, '251')) {
        $phone = '+' . $phone;
    }
    // If starts with 9 (just the local part), add +251
    elseif (str_starts_with($phone, '9') && strlen($phone) === 9) {
        $phone = '+251' . $phone;
    }
    // If doesn't start with +, assume it needs +251
    elseif (!str_starts_with($phone, '+')) {
        $phone = '+251' . $phone;
    }

    return $phone;
}

// Normalize the phone number
$normalizedPhone = normalizePhoneNumber($testPhone);
echo "📱 Normalized phone: {$normalizedPhone}\n\n";

// Prepare API request
$params = [
    'from' => $identifierId,
    'sender' => $sender,
    'to' => $normalizedPhone,
    'message' => $testMessage,
];

// Build query string
$queryString = http_build_query($params);
$fullUrl = $baseUrl . '?' . $queryString;

echo "🚀 Sending SMS...\n";
echo "URL: {$fullUrl}\n\n";

// Initialize cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $fullUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_SSL_VERIFYPEER => false, // For testing only
    CURLOPT_VERBOSE => true,
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Handle cURL errors
if ($error) {
    echo "❌ cURL Error: {$error}\n";
    exit(1);
}

echo "📊 HTTP Status Code: {$httpCode}\n";
echo "📄 Response: {$response}\n\n";

// Parse response
$responseData = json_decode($response, true);

if ($httpCode === 200) {
    if ($responseData && isset($responseData['acknowledge']) && $responseData['acknowledge'] === 'success') {
        echo "✅ SUCCESS: SMS sent successfully!\n";
        echo "📋 Response Details:\n";
        print_r($responseData);
    } else {
        echo "⚠️  WARNING: Request succeeded but response indicates failure\n";
        echo "📋 Response Details:\n";
        print_r($responseData);
    }
} else {
    echo "❌ FAILED: HTTP {$httpCode}\n";
    echo "📋 Response Details:\n";
    print_r($responseData);
}

echo "\n=== Test Complete ===\n";

// Additional debugging information
echo "\n=== Debug Information ===\n";
echo "Environment Variables:\n";
echo "- AFROMESSAGE_API_TOKEN: " . (empty($apiToken) ? 'NOT SET' : 'SET (' . strlen($apiToken) . ' chars)') . "\n";
echo "- AFROMESSAGE_IDENTIFIER_ID: " . (empty($identifierId) ? 'NOT SET' : $identifierId) . "\n";
echo "- AFROMESSAGE_BASE_URL: " . $baseUrl . "\n";
echo "- AFROMESSAGE_SENDER: " . $sender . "\n";
echo "========================\n";
