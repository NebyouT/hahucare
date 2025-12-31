<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChapaController extends Controller
{
    /**
     * Initialize Chapa Payment
     */
    public function ChapaPayment(Request $request, $paymentData, $price)
    {
        $baseURL = url('/');
        $chapaSecretKey = config('services.chapa.secret') ?? env('CHAPA_SECRET_KEY');

        $txRef = 'chapa_' . time() . '_' . ($paymentData['id'] ?? '0');
        $currency = strtoupper(GetcurrentCurrency() ?? 'ETB');

        $payload = [
            'amount' => (float) number_format($price, 2, '.', ''),
            'currency' => $currency,
            'email' => optional(auth()->user())->email ?? $paymentData['email'] ?? null,
            'first_name' => optional(auth()->user())->first_name ?? $paymentData['user_name'] ?? null,
            'last_name' => optional(auth()->user())->last_name ?? $paymentData['user_name'] ?? null,
            'tx_ref' => $txRef,
            'callback_url' => $baseURL . '/payment/success?gateway=chapa',
            'return_url' => $baseURL . '/payment/success?gateway=chapa',
            'description' => $paymentData['description'] ?? 'Appointment Payment',
            'metadata' => [
                'appointment_id' => $paymentData['id'] ?? null,
                'clinic_id' => $paymentData['clinic_id'] ?? null,
                'doctor_id' => $paymentData['doctor_id'] ?? null,
                'service_id' => $paymentData['service_id'] ?? null,
                'advance_payment_status' => $paymentData['advance_payment_status'] ?? 0,
                'advance_paid_amount' => $paymentData['advance_paid_amount'] ?? 0,
                'remaining_payment_amount' => $paymentData['remaining_payment_amount'] ?? 0,
                'type' => $paymentData['type'] ?? 'appointment',
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $chapaSecretKey,
            'Accept' => 'application/json',
        ])->post('https://api.chapa.co/v1/transaction/initialize', $payload);

        if (!$response->successful()) {
            Log::error('Chapa initialize failed', ['response' => $response->body(), 'payload' => $payload]);
            return response()->json(['error' => 'Failed to initialize payment.'], 500);
        }

        $body = $response->json();
        $checkoutUrl = data_get($body, 'data.checkout_url')
            ?: data_get($body, 'data.url')
            ?: data_get($body, 'data.payment_url');

        if (!$checkoutUrl) {
            Log::error('Chapa missing checkout url', ['body' => $body]);
            return response()->json(['error' => 'Payment did not return a checkout url.'], 500);
        }

        // Save tx_ref to DB
        try {
            if (!empty($paymentData['id'])) {
                \Modules\Appointment\Models\AppointmentTransaction::updateOrCreate(
                    ['appointment_id' => $paymentData['id']],
                    ['external_transaction_id' => $txRef]
                );

                \Modules\Appointment\Models\Appointment::where('id', $paymentData['id'])
                    ->update(['external_transaction_id' => $txRef]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to persist Chapa tx_ref', ['error' => $e->getMessage()]);
        }

        Log::info('Chapa initialize response', ['tx_ref' => $txRef, 'body' => $body]);

        return response()->json([
            'success' => true,
            'redirect' => $checkoutUrl,
            'tx_ref' => $txRef,
            'raw' => $body,
        ]);
    }





    /**
     * Verify Payment (Callback Handler)
     */
    public function handleChapaSuccess(Request $request)
    {
        Log::info('Chapa success callback received', $request->all());

        $reference = $request->input('reference')
            ?: $request->input('tx_ref')
            ?: $request->input('tran_ref');

        $appointmentId = $request->input('appointment_id') ?? $request->query('appointment_id');

        // Fallback if reference missing
        if (!$reference && $appointmentId) {
            $tx = \Modules\Appointment\Models\AppointmentTransaction::where('appointment_id', $appointmentId)->first();
            $reference = $tx->external_transaction_id ?? null;
        }

        if (!$reference) {
            Log::warning('Chapa callback missing reference');
            return redirect('/')->with('error', 'Payment reference is missing.');
        }

        // Verify payment with Chapa
        $chapaSecretKey = config('services.chapa.secret') ?? env('CHAPA_SECRET_KEY');
        $verifyUrl = "https://api.chapa.co/v1/transaction/verify/{$reference}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $chapaSecretKey,
            'Accept' => 'application/json',
        ])->get($verifyUrl);

        if (!$response->successful()) {
            Log::error('Chapa verify failed', ['reference' => $reference, 'response' => $response->body()]);
            return redirect('/')->with('error', 'Payment verification failed.');
        }

        $body = $response->json();
        Log::info('Chapa verify response', ['reference' => $reference, 'body' => $body]);

        $data = data_get($body, 'data');

        // Validate success statuses
        $status = strtolower(data_get($data, 'status', ''));
        $successValues = ['success', 'successful', 'paid', 'completed', 'ok', 'true'];

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

        $paymentData = [
            'id' => $appointmentIdResolved,
            'transaction_type' => 'chapa',
            'external_transaction_id' => $reference,
            'payment_status' => $paymentStatus,
            'amountTotal' => (float) data_get($data, 'amount'),
            'currency' => strtoupper(data_get($data, 'currency', 'ETB')),
            'clinic_id' => $metadata['clinic_id'] ?? null,
            'doctor_id' => $metadata['doctor_id'] ?? null,
            'service_id' => $metadata['service_id'] ?? null,
            'advance_payment_status' => $metadata['advance_payment_status'] ?? 0,
            'advance_paid_amount' => $metadata['advance_paid_amount'] ?? 0,
            'remaining_payment_amount' => $metadata['remaining_payment_amount'] ?? 0,
            'metadata' => $metadata,
            'type' => $metadata['type'] ?? 'appointment',
        ];

        // Save reference again safely
        try {
            \Modules\Appointment\Models\AppointmentTransaction::updateOrCreate(
                ['appointment_id' => $paymentData['id']],
                ['external_transaction_id' => $reference]
            );
        } catch (\Exception $e) {
            Log::warning('Unable to persist reference after verify', ['error' => $e->getMessage()]);
        }

        // Call AppointmentController → savePayment()
        try {
            $controller = app(\Modules\Frontend\Http\Controllers\AppointmentController::class);
            $controller->savePayment($paymentData);

            return $controller->handlePaymentSuccess($paymentData);
        } catch (\Exception $e) {
            Log::error('Error saving Chapa payment', ['error' => $e->getMessage()]);
            return redirect('/')->with('error', 'Payment processing failed.');
        }
    }





    /**
     * Chapa Webhook
     */
    public function handleChapaWebhook(Request $request)
    {
        Log::info('Chapa webhook received', $request->all());

        $reference = data_get($request->all(), 'data.reference')
            ?: data_get($request->all(), 'data.tx_ref');

        if (!$reference) {
            return response()->json(['error' => 'Reference missing'], 400);
        }

        return response()->json(['status' => 'received'], 200);
    }
}
