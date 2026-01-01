<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Mock SMS Service for Development/Testing
 * 
 * This service simulates SMS sending when the real AfroMessage API
 * is not available due to network issues or for testing purposes.
 */
class MockSmsService
{
    /**
     * Send SMS (mock implementation)
     *
     * @param string $phone Phone number
     * @param string $message Message content
     * @return array
     */
    public function sendSms(string $phone, string $message): array
    {
        // Log the SMS that would have been sent
        Log::info('Mock SMS sent', [
            'phone' => $phone,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Simulate successful response
        return [
            'success' => true,
            'message' => 'SMS sent successfully (Mock)',
            'data' => [
                'acknowledge' => 'success',
                'response' => [
                    'message' => 'Mock SMS delivery successful',
                    'phone' => $phone,
                    'mock' => true
                ]
            ]
        ];
    }

    /**
     * Send OTP SMS (mock implementation)
     *
     * @param string $phone Phone number
     * @param string $otp OTP code
     * @return array
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $message = "Your HahuCare verification code is: {$otp}. This code expires in 5 minutes. Do not share this code with anyone.";
        
        // Log the OTP that would have been sent
        Log::info('Mock OTP SMS sent', [
            'phone' => $phone,
            'otp' => $otp,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Display OTP in console for development
        echo "\n=== MOCK SMS SERVICE ===\n";
        echo "Phone: {$phone}\n";
        echo "OTP: {$otp}\n";
        echo "Message: {$message}\n";
        echo "========================\n\n";

        return [
            'success' => true,
            'message' => 'OTP SMS sent successfully (Mock)',
            'data' => [
                'acknowledge' => 'success',
                'response' => [
                    'message' => 'Mock OTP delivery successful',
                    'phone' => $phone,
                    'otp' => $otp,
                    'mock' => true
                ]
            ]
        ];
    }
}
