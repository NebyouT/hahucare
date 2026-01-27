<?php

/**
 * Direct AfroMessage SMS Test (No Laravel Dependencies)
 * 
 * This script tests AfroMessage API directly without Laravel
 * to verify the credentials and SMS sending functionality.
 */

echo "\n===========================================\n";
echo "   AfroMessage Direct API Test\n";
echo "===========================================\n\n";

// Your AfroMessage credentials
$apiToken = 'eyJhbGciOiJIUzI1NiJ9.eyJpZGVudGlmaWVyIjoickxHWGxZZjVUUVRBSXBtVU9GUlRNTHI1eEpPNHVHUUYiLCJleHAiOjE5MjUwMzI1OTAsImlhdCI6MTc2NzI2NjE5MCwianRpIjoiMWY2NjU5YzktZDQ2NC00NDI1LWFhOGUtYmZlOWIxZmYwN2I2In0.fmDrN-0cXAx2BaRwCygfnnl8OP-xmWta_WYGc-vtvTk';
$identifierId = 'e80ad9d8-adf3-463f-80f4-7c4b39f7f164';
$sender = 'HahucarePLC';
$baseUrl = 'https://api.afromessage.com/api/send';

// Test details
$phoneNumber = '0912946688';
$message = 'Hello! This is a test SMS from HahuCare. Your AfroMessage integration is working correctly!';

echo "📱 Test Phone Number: {$phoneNumber}\n";
echo "💬 Test Message: {$message}\n\n";

echo "🔧 Configuration:\n";
echo "   API Token: " . substr($apiToken, 0, 30) . "...\n";
echo "   Identifier ID: {$identifierId}\n";
echo "   Sender: {$sender}\n";
echo "   Base URL: {$baseUrl}\n\n";

// Normalize phone number to Ethiopian format
function normalizePhoneNumber($phone) {
    // Remove all non-numeric characters except +
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // If starts with 0, replace with +251
    if (substr($phone, 0, 1) === '0') {
        $phone = '+251' . substr($phone, 1);
    }
    // If starts with 251 without +, add +
    elseif (substr($phone, 0, 3) === '251') {
        $phone = '+' . $phone;
    }
    // If starts with 9 (just the local part), add +251
    elseif (substr($phone, 0, 1) === '9' && strlen($phone) === 9) {
        $phone = '+251' . $phone;
    }
    // If doesn't start with +, assume it needs +251
    elseif (substr($phone, 0, 1) !== '+') {
        $phone = '+251' . $phone;
    }
    
    return $phone;
}

$normalizedPhone = normalizePhoneNumber($phoneNumber);
echo "📞 Normalized Phone: {$normalizedPhone}\n\n";

echo "🚀 Sending SMS via AfroMessage API...\n";
echo "   Please wait...\n\n";

// Prepare the request
$url = $baseUrl . '?' . http_build_query([
    'from' => $identifierId,
    'sender' => $sender,
    'to' => $normalizedPhone,
    'message' => $message,
]);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiToken,
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "===========================================\n";
echo "   API RESPONSE\n";
echo "===========================================\n\n";

echo "📊 HTTP Status Code: {$httpCode}\n\n";

if ($curlError) {
    echo "❌ cURL Error: {$curlError}\n\n";
} else {
    echo "📄 Raw Response:\n";
    echo $response . "\n\n";
    
    // Try to decode JSON response
    $result = json_decode($response, true);
    
    if ($result) {
        echo "📋 Parsed Response:\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
        
        // Check for success
        if (isset($result['acknowledge']) && $result['acknowledge'] === 'success') {
            echo "✅ SUCCESS! SMS sent successfully!\n\n";
            echo "✉️  The SMS should arrive at {$phoneNumber} shortly.\n";
            echo "   Please check the phone to confirm delivery.\n\n";
        } else {
            echo "❌ FAILED! SMS was not sent successfully.\n\n";
            
            if (isset($result['message'])) {
                echo "   Error Message: {$result['message']}\n\n";
            }
            
            echo "🔍 Troubleshooting:\n";
            echo "   1. Verify API Token is correct and not expired\n";
            echo "   2. Check Identifier ID matches your account\n";
            echo "   3. Ensure you have sufficient SMS credits\n";
            echo "   4. Verify sender name is approved\n";
            echo "   5. Check phone number format\n\n";
        }
    } else {
        echo "⚠️  Could not parse JSON response\n\n";
    }
}

echo "===========================================\n";
echo "   Test Complete\n";
echo "===========================================\n\n";

echo "💡 Next Steps:\n";
echo "   1. If successful, add these credentials to your .env file\n";
echo "   2. Clear Laravel config cache: php artisan config:clear\n";
echo "   3. Configure in admin panel: Settings → Integration\n";
echo "   4. Enable SMS for notification templates\n\n";

echo "Done!\n\n";
