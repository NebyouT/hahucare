<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageService
{
    protected ?string $apiToken;
    protected ?string $identifierId;
    protected string $baseUrl;
    protected string $sender;

    public function __construct()
    {
        $this->apiToken = config('services.afromessage.token') ?? '';
        $this->identifierId = config('services.afromessage.identifier_id') ?? '';
        $this->baseUrl = config('services.afromessage.base_url') ?? 'https://api.afromessage.com/api/send';
        $this->sender = config('services.afromessage.sender') ?? 'HahuCare';
    }

    /**
     * Send SMS via AfroMessage API
     *
     * @param string $phone Phone number (with or without country code)
     * @param string $message Message content
     * @return array
     */
    public function sendSms(string $phone, string $message): array
    {
        try {
            // Normalize phone number to Ethiopian format
            $phone = $this->normalizePhoneNumber($phone);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get($this->baseUrl, [
                'from' => $this->identifierId,
                'sender' => $this->sender,
                'to' => $phone,
                'message' => $message,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['acknowledge']) && $result['acknowledge'] === 'success') {
                Log::info('AfroMessage SMS sent successfully', [
                    'phone' => $phone,
                    'response' => $result
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $result
                ];
            }

            Log::error('AfroMessage SMS failed', [
                'phone' => $phone,
                'response' => $result
            ]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to send SMS',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('AfroMessage SMS exception', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            // Check if it's a DNS/network error and use mock service in development
            if (config('app.env') === 'local' && (
                str_contains($e->getMessage(), 'Could not resolve host') ||
                str_contains($e->getMessage(), 'cURL error 6')
            )) {
                Log::info('Using Mock SMS Service due to network issues', ['phone' => $phone]);
                $mockService = new MockSmsService();
                return $mockService->sendSms($phone, $message);
            }

            return [
                'success' => false,
                'message' => 'SMS service error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Send OTP SMS
     *
     * @param string $phone Phone number
     * @param string $otp OTP code
     * @return array
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $message = "Your HahuCare verification code is: {$otp}. This code expires in 5 minutes. Do not share this code with anyone.";
        
        return $this->sendSms($phone, $message);
    }

    /**
     * Normalize phone number to Ethiopian format (+251)
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhoneNumber(string $phone): string
    {
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

    /**
     * Get normalized phone for storage (without +)
     *
     * @param string $phone
     * @return string
     */
    public static function normalizeForStorage(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 251, keep as is
        if (str_starts_with($phone, '251')) {
            return $phone;
        }
        // If starts with 0, replace with 251
        if (str_starts_with($phone, '0')) {
            return '251' . substr($phone, 1);
        }
        // If starts with 9, add 251
        if (str_starts_with($phone, '9') && strlen($phone) === 9) {
            return '251' . $phone;
        }
        
        return $phone;
    }
}
