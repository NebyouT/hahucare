<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StarPayController extends Controller
{
    /**
     * Initialize StarPay Payment
     */
    public function starPayPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');
        $starPaySecretKey = GetpaymentMethod('starpay_secretkey');
        $starPayMerchantId = GetpaymentMethod('starpay_merchant_id');
        $starPayMode = GetpaymentMethod('starpay_mode') ?? 'sandbox';

        // Determine API base URL based on mode
        $apiBaseUrl = $starPayMode === 'production' 
            ? 'https://starpay.starpayethiopia.com/v1/starpay-api'
            : 'https://starpayqa.starpayethiopia.com/v1/starpay-api';

        $txRef = 'starpay_' . time() . '_' . ($paymentData['id'] ?? '0');
        $currency = strtoupper(GetcurrentCurrency() ?? 'ETB');

        // Build items array for StarPay
        $items = [];
        if (!empty($paymentData['service_id'])) {
            $items[] = [
                'productId' => $paymentData['service_id'],
                'quantity' => 1,
                'item_name' => $paymentData['description'] ?? 'Appointment Payment',
                'unit_price' => (float) number_format($price, 2, '.', ''),
            ];
        } else {
            $items[] = [
                'productId' => uniqid(),
                'quantity' => 1,
                'item_name' => $paymentData['description'] ?? 'Appointment Payment',
                'unit_price' => (float) number_format($price, 2, '.', ''),
            ];
        }

        $payload = [
            'amount' => (float) number_format($price, 2, '.', ''),
            'description' => $paymentData['description'] ?? 'Appointment Payment',
            'currency' => $currency,
            'customerName' => optional(auth()->user())->first_name . ' ' . optional(auth()->user())->last_name ?? $paymentData['user_name'] ?? 'Patient',
            'customerPhoneNumber' => optional(auth()->user())->phone ?? $paymentData['phone'] ?? '+251911234567',
            'callbackURL' => $baseURL . '/payment/success?gateway=starpay&tx_ref=' . $txRef . '&appointment_id=' . ($paymentData['id'] ?? ''),
            'redirectUrl' => $baseURL . '/payment/success?gateway=starpay&tx_ref=' . $txRef . '&appointment_id=' . ($paymentData['id'] ?? ''),
            'api_secret' => $starPaySecretKey,
            'items' => $items,
            'metadata' => [
                'appointment_id' => $paymentData['id'] ?? null,
                'clinic_id' => $paymentData['clinic_id'] ?? null,
                'doctor_id' => $paymentData['doctor_id'] ?? null,
                'service_id' => $paymentData['service_id'] ?? null,
                'advance_payment_status' => $paymentData['advance_payment_status'] ?? 0,
                'advance_paid_amount' => $paymentData['advance_paid_amount'] ?? 0,
                'remaining_payment_amount' => $paymentData['remaining_payment_amount'] ?? 0,
                'appointment_date' => $paymentData['appointment_date'] ?? null,
                'appointment_time' => $paymentData['appointment_time'] ?? null,
                'type' => $paymentData['type'] ?? 'appointment',
            ],
        ];

        // Add customer email if available
        if (optional(auth()->user())->email || !empty($paymentData['email'])) {
            $payload['customerEmail'] = optional(auth()->user())->email ?? $paymentData['email'];
        }

        $response = Http::withHeaders([
            'x-api-secret' => $starPaySecretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($apiBaseUrl . '/trdp/order', $payload);

        if (!$response->successful()) {
            Log::error('StarPay initialize failed', ['response' => $response->body(), 'payload' => $payload]);
            return response()->json(['error' => 'Failed to initialize payment.'], 500);
        }

        $body = $response->json();
        $paymentUrl = data_get($body, 'data.payment_url')
            ?: data_get($body, 'data.url');

        if (!$paymentUrl) {
            Log::error('StarPay missing payment url', ['body' => $body]);
            return response()->json(['error' => 'Payment did not return a payment url.'], 500);
        }

        // Save tx_ref to AppointmentTransaction so callback can look it up
        try {
            if (!empty($paymentData['id'])) {
                \Modules\Appointment\Models\AppointmentTransaction::updateOrCreate(
                    ['appointment_id' => $paymentData['id']],
                    ['external_transaction_id' => $txRef, 'transaction_type' => 'starpay']
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to persist StarPay tx_ref', ['error' => $e->getMessage()]);
        }

        Log::info('StarPay initialize response', ['tx_ref' => $txRef, 'body' => $body]);

        return response()->json([
            'success' => true,
            'redirect' => $paymentUrl,
            'tx_ref' => $txRef,
            'raw' => $body,
        ]);
    }

    /**
     * Verify Payment (Callback Handler)
     */
    public function handleStarPaySuccess(Request $request)
    {
        Log::info('StarPay success callback received', $request->all());

        $reference = $request->input('tx_ref')
            ?: $request->input('order_id')
            ?: $request->input('billRefNo');

        $appointmentId = $request->input('appointment_id') ?? $request->query('appointment_id');

        // Fallback if reference missing
        if (!$reference && $appointmentId) {
            $tx = \Modules\Appointment\Models\AppointmentTransaction::where('appointment_id', $appointmentId)->first();
            $reference = $tx->external_transaction_id ?? null;
        }

        if (!$reference) {
            Log::warning('StarPay callback missing reference');
            return redirect('/')->with('error', 'Payment reference is missing.');
        }

        // Get StarPay credentials
        $starPaySecretKey = GetpaymentMethod('starpay_secretkey');
        $starPayMode = GetpaymentMethod('starpay_mode') ?? 'sandbox';

        // Determine API base URL based on mode
        $apiBaseUrl = $starPayMode === 'production' 
            ? 'https://starpay.starpayethiopia.com/v1/starpay-api'
            : 'https://starpayqa.starpayethiopia.com/v1/starpay-api';

        // Verify payment with StarPay
        $verifyUrl = $apiBaseUrl . '/trdp/verify';

        $response = Http::withHeaders([
            'x-api-secret' => $starPaySecretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($verifyUrl, ['orderId' => $reference]);

        if (!$response->successful()) {
            Log::error('StarPay verify failed', ['reference' => $reference, 'response' => $response->body()]);
            return redirect('/')->with('error', 'Payment verification failed.');
        }

        $body = $response->json();
        Log::info('StarPay verify response', ['reference' => $reference, 'body' => $body]);

        $data = data_get($body, 'data');

        // Validate success status
        $status = strtolower(data_get($data, 'status', ''));
        $successValues = ['paid', 'success', 'successful', 'completed'];

        if (!in_array($status, $successValues)) {
            return redirect('/')->with('error', 'Payment not successful: ' . $status);
        }

        // Metadata Handle
        $metadata = data_get($data, 'metadata', []);
        $appointmentIdResolved = $metadata['appointment_id'] ?? $appointmentId ?? null;

        // Final fallback: lookup by tx_ref
        if (!$appointmentIdResolved) {
            $tx = \Modules\Appointment\Models\AppointmentTransaction::where('external_transaction_id', $reference)->first();
            $appointmentIdResolved = $tx->appointment_id ?? null;
        }

        // Full payment or advance payment
        $isAdvance = isset($metadata['advance_payment_status']) && $metadata['advance_payment_status'] == 1;
        $paymentStatus = $isAdvance ? 0 : 1;

        // Fetch appointment from DB to get date/time and fill any missing metadata
        $appointment = \Modules\Appointment\Models\Appointment::find($appointmentIdResolved);

        $paymentData = [
            'id' => $appointmentIdResolved,
            'appointment_id' => $appointmentIdResolved,
            'transaction_type' => 'starpay',
            'external_transaction_id' => $reference,
            'payment_status' => $paymentStatus,
            'amountTotal' => (float) data_get($data, 'amount'),
            'currency' => strtoupper(data_get($data, 'currency', 'ETB')),
            'clinic_id' => $metadata['clinic_id'] ?? optional($appointment)->clinic_id,
            'doctor_id' => $metadata['doctor_id'] ?? optional($appointment)->doctor_id,
            'service_id' => $metadata['service_id'] ?? optional($appointment)->service_id,
            'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
            'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
            'remaining_payment_amount' => $metadata['remaining_payment_amount'] ?? 0,
            'appointment_date' => optional($appointment)->appointment_date ?? '',
            'appointment_time' => optional($appointment)->appointment_time ?? '',
            'tip' => 0,
            'metadata' => $metadata,
            'type' => $metadata['type'] ?? 'appointment',
        ];

        Log::info('StarPay paymentData before savePayment', $paymentData);

        // Direct DB update
        try {
            \Modules\Appointment\Models\AppointmentTransaction::updateOrCreate(
                ['appointment_id' => $appointmentIdResolved],
                [
                    'transaction_type' => 'starpay',
                    'external_transaction_id' => $reference,
                    'payment_status' => $paymentStatus,
                    'advance_payment_status' => $isAdvance ? 1 : 0,
                ]
            );
            Log::info('StarPay: direct DB payment_status update done', [
                'appointment_id' => $appointmentIdResolved,
                'payment_status' => $paymentStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('StarPay: direct DB update failed', ['error' => $e->getMessage()]);
        }

        // Full savePayment() for commissions / wallet history
        try {
            $controller = app(\Modules\Frontend\Http\Controllers\AppointmentController::class);
            $controller->savePayment($paymentData);
            Log::info('StarPay: savePayment() completed successfully', ['appointment_id' => $appointmentIdResolved]);
        } catch (\Exception $e) {
            Log::error('StarPay: savePayment() failed (payment already marked paid above)', [
                'error' => $e->getMessage(),
                'appointment_id' => $appointmentIdResolved,
            ]);
        }

        try {
            $controller = $controller ?? app(\Modules\Frontend\Http\Controllers\AppointmentController::class);
            return $controller->handlePaymentSuccess($paymentData);
        } catch (\Exception $e) {
            Log::error('StarPay: handlePaymentSuccess() failed', ['error' => $e->getMessage()]);
            return redirect()->route('appointment-list')->with('success', 'Payment confirmed successfully.');
        }
    }

    /**
     * StarPay Webhook
     */
    public function handleStarPayWebhook(Request $request)
    {
        Log::info('StarPay webhook received', $request->all());

        $billRefNo = data_get($request->all(), 'billRefNo')
            ?: data_get($request->all(), 'order_id');

        if (!$billRefNo) {
            return response()->json(['error' => 'Reference missing'], 400);
        }

        return response()->json(['status' => 'received'], 200);
    }
}
