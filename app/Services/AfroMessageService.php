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

        Log::info('[AfroSMS] Service initialized', [
            'base_url' => $this->baseUrl,
            'sender' => $this->sender,
            'identifier_id' => $this->identifierId ? substr($this->identifierId, 0, 8) . '...' : 'EMPTY',
            'api_token_set' => !empty($this->apiToken),
            'api_token_length' => strlen($this->apiToken ?? ''),
        ]);
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
        $originalPhone = $phone;
        Log::info('[AfroSMS] sendSms called', [
            'original_phone' => $originalPhone,
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 50) . '...',
        ]);

        try {
            // Normalize phone number to Ethiopian format
            $phone = $this->normalizePhoneNumber($phone);
            Log::info('[AfroSMS] Phone normalized', [
                'original' => $originalPhone,
                'normalized' => $phone,
            ]);

            $requestParams = [
                'from' => $this->identifierId,
                'sender' => $this->sender,
                'to' => $phone,
                'message' => $message,
            ];

            Log::info('[AfroSMS] Sending HTTP request', [
                'url' => $this->baseUrl,
                'method' => 'GET',
                'params' => array_merge($requestParams, ['message' => substr($message, 0, 50) . '...']),
                'auth_header' => 'Bearer ' . substr($this->apiToken ?? '', 0, 10) . '...',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->get($this->baseUrl, $requestParams);

            $result = $response->json();

            Log::info('[AfroSMS] HTTP response received', [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'response_body' => $result,
            ]);

            if ($response->successful() && isset($result['acknowledge']) && $result['acknowledge'] === 'success') {
                Log::info('[AfroSMS] SMS sent successfully', [
                    'phone' => $phone,
                    'acknowledge' => $result['acknowledge'] ?? null,
                    'response_id' => $result['response']['id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $result
                ];
            }

            Log::error('[AfroSMS] SMS sending failed - API returned error', [
                'phone' => $phone,
                'http_status' => $response->status(),
                'acknowledge' => $result['acknowledge'] ?? 'missing',
                'error_message' => $result['message'] ?? 'No message in response',
                'full_response' => $result,
            ]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to send SMS',
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('[AfroSMS] Exception during SMS send', [
                'phone' => $phone,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace_summary' => array_slice(array_map(fn($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?'), $e->getTrace()), 0, 5),
            ]);

            // Check if it's a DNS/network error and use mock service in development
            if (config('app.env') === 'local' && (
                str_contains($e->getMessage(), 'Could not resolve host') ||
                str_contains($e->getMessage(), 'cURL error 6')
            )) {
                Log::info('[AfroSMS] Falling back to MockSmsService (local env, network error)', ['phone' => $phone]);
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
        Log::info('[AfroSMS:OTP] Sending OTP', [
            'phone' => $phone,
            'otp_length' => strlen($otp),
        ]);

        $message = "Your HahuCare verification code is: {$otp}. This code expires in 5 minutes. Do not share this code with anyone.";
        
        $result = $this->sendSms($phone, $message);

        Log::info('[AfroSMS:OTP] OTP send result', [
            'phone' => $phone,
            'success' => $result['success'],
            'message' => $result['message'],
        ]);

        return $result;
    }

    /**
     * Normalize phone number to Ethiopian format (+251)
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        $original = $phone;

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

        Log::debug('[AfroSMS] normalizePhoneNumber', [
            'input' => $original,
            'output' => $phone,
        ]);

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
