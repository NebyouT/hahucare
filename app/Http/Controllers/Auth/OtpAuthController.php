<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Trait\AuthTrait;
use App\Models\OtpVerification;
use App\Models\User;
use App\Services\AfroMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtpAuthController extends Controller
{
    use AuthTrait;

    protected AfroMessageService $smsService;

    public function __construct(AfroMessageService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show phone number input form
     */
    public function showPhoneForm()
    {
        return view('frontend::auth.otp_phone');
    }

    /**
     * Send OTP to phone number
     */
    public function sendOtp(Request $request)
    {
        Log::info('[OTP:Login] === sendOtp START ===', [
            'raw_phone_input' => $request->phone,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'phone' => 'required|string|min:9|max:15',
        ]);

        $phone = AfroMessageService::normalizeForStorage($request->phone);
        Log::info('[OTP:Login] Phone normalized for storage', [
            'raw_input' => $request->phone,
            'normalized' => $phone,
        ]);

        // Check for existing valid OTP and rate limiting
        $existingOtp = OtpVerification::where('phone', $phone)
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        if ($existingOtp) {
            Log::warning('[OTP:Login] Rate limited - OTP requested too soon', [
                'phone' => $phone,
                'last_otp_created' => $existingOtp->created_at,
                'seconds_ago' => now()->diffInSeconds($existingOtp->created_at),
            ]);
            return back()->withErrors([
                'phone' => __('Please wait 1 minute before requesting a new OTP.')
            ])->withInput();
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Log::info('[OTP:Login] OTP generated', [
            'phone' => $phone,
            'otp_length' => strlen($otp),
        ]);

        // Delete old OTPs for this phone
        $deletedCount = OtpVerification::where('phone', $phone)->delete();
        Log::info('[OTP:Login] Old OTPs cleaned up', [
            'phone' => $phone,
            'deleted_count' => $deletedCount,
        ]);

        // Create new OTP record
        $otpRecord = OtpVerification::create([
            'phone' => $phone,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
        ]);
        Log::info('[OTP:Login] OTP record created in DB', [
            'phone' => $phone,
            'record_id' => $otpRecord->id ?? null,
            'expires_at' => $otpRecord->expires_at ?? null,
        ]);

        // Send OTP via AfroMessage
        Log::info('[OTP:Login] Calling AfroMessageService->sendOtp()', ['phone' => $phone]);
        $result = $this->smsService->sendOtp($phone, $otp);
        Log::info('[OTP:Login] AfroMessageService->sendOtp() returned', [
            'phone' => $phone,
            'success' => $result['success'],
            'message' => $result['message'],
            'has_data' => isset($result['data']),
        ]);

        if (!$result['success']) {
            Log::error('[OTP:Login] OTP send FAILED - returning error to user', [
                'phone' => $phone,
                'error_message' => $result['message'],
                'response_data' => $result['data'] ?? null,
            ]);
            return back()->withErrors([
                'phone' => __('Failed to send OTP. Please try again.')
            ])->withInput();
        }

        // Store phone in session for verification step
        session(['otp_phone' => $phone]);
        Log::info('[OTP:Login] === sendOtp SUCCESS === Redirecting to verify form', [
            'phone' => $phone,
        ]);

        return redirect()->route('otp.verify.form')
            ->with('success', __('OTP sent successfully to your phone.'));
    }

    /**
     * Show OTP verification form
     */
    public function showVerifyForm()
    {
        if (!session('otp_phone')) {
            return redirect()->route('otp.phone.form');
        }

        return view('frontend::auth.otp_verify', [
            'phone' => session('otp_phone')
        ]);
    }

    /**
     * Verify OTP and login/register user
     */
    public function verifyOtp(Request $request)
    {
        Log::info('[OTP:Verify] === verifyOtp START ===', [
            'ip' => $request->ip(),
        ]);

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $phone = session('otp_phone');

        if (!$phone) {
            Log::warning('[OTP:Verify] No phone in session - session expired');
            return redirect()->route('otp.phone.form')
                ->withErrors(['phone' => __('Session expired. Please enter your phone number again.')]);
        }

        Log::info('[OTP:Verify] Phone from session', ['phone' => $phone]);

        // Find valid OTP
        $otpRecord = OtpVerification::validForPhone($phone)->first();

        if (!$otpRecord) {
            Log::warning('[OTP:Verify] No valid OTP record found', ['phone' => $phone]);
            return back()->withErrors([
                'otp' => __('OTP expired or invalid. Please request a new one.')
            ]);
        }

        Log::info('[OTP:Verify] OTP record found', [
            'phone' => $phone,
            'record_id' => $otpRecord->id,
            'attempts' => $otpRecord->attempts ?? 0,
            'expires_at' => $otpRecord->expires_at,
            'created_at' => $otpRecord->created_at,
        ]);

        // Check max attempts
        if ($otpRecord->maxAttemptsReached()) {
            Log::warning('[OTP:Verify] Max attempts reached', [
                'phone' => $phone,
                'attempts' => $otpRecord->attempts,
            ]);
            $otpRecord->delete();
            return redirect()->route('otp.phone.form')
                ->withErrors(['phone' => __('Too many failed attempts. Please request a new OTP.')]);
        }

        // Verify OTP
        if (!Hash::check($request->otp, $otpRecord->otp)) {
            $otpRecord->incrementAttempts();
            Log::warning('[OTP:Verify] OTP mismatch - invalid code entered', [
                'phone' => $phone,
                'attempts_now' => $otpRecord->attempts,
            ]);
            return back()->withErrors([
                'otp' => __('Invalid OTP. Please try again. Attempts remaining: ') . (5 - $otpRecord->attempts)
            ]);
        }

        // OTP is valid - mark as verified
        $otpRecord->markAsVerified();
        Log::info('[OTP:Verify] OTP verified successfully', ['phone' => $phone]);

        // Check if user exists with this phone
        $user = User::where('mobile', $phone)->first();

        if ($user) {
            Log::info('[OTP:Verify] Existing user found - logging in', [
                'phone' => $phone,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ]);
            // User exists - log them in
            Auth::login($user, true);
            
            // Clear session
            session()->forget('otp_phone');
            $otpRecord->delete();

            // Clear caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');

            Log::info('[OTP:Verify] === verifyOtp SUCCESS === User logged in', [
                'user_id' => $user->id,
            ]);
            return redirect('/home')->with('success', __('Login successful!'));
        }

        // User doesn't exist - show registration form
        Log::info('[OTP:Verify] No user found for phone - redirecting to registration', [
            'phone' => $phone,
        ]);
        session(['otp_verified' => true]);
        
        return redirect()->route('otp.register.form');
    }

    /**
     * Show registration form for new users
     */
    public function showRegisterForm()
    {
        if (!session('otp_verified') || !session('otp_phone')) {
            return redirect()->route('otp.phone.form');
        }

        return view('frontend::auth.otp_register', [
            'phone' => session('otp_phone')
        ]);
    }

    /**
     * Register new user after OTP verification
     */
    public function register(Request $request)
    {
        if (!session('otp_verified') || !session('otp_phone')) {
            return redirect()->route('otp.phone.form')
                ->withErrors(['phone' => __('Session expired. Please verify your phone again.')]);
        }

        $phone = session('otp_phone');

        $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => 'nullable|email|max:191|unique:users,email',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ]);

        // Generate email if not provided
        $email = $request->email;
        if (empty($email)) {
            // Use phone number as email domain
            $phoneForEmail = preg_replace('/[^0-9]/', '', $phone);
            // If starts with 251, remove it for cleaner email
            if (str_starts_with($phoneForEmail, '251')) {
                $phoneForEmail = '0' . substr($phoneForEmail, 3);
            }
            $email = $phoneForEmail . '@hahucare.com';
        }

        // Check if email already exists (in case auto-generated email conflicts)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            // Add random suffix to make unique
            $email = preg_replace('/@/', '_' . Str::random(4) . '@', $email);
        }

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $email,
            'mobile' => $phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'password' => Hash::make(Str::random(32)), // Random password since using OTP
            'user_type' => 'user',
            'status' => 1,
            'email_verified_at' => now(), // Phone verified = email verified
        ]);

        // Assign user role
        $user->assignRole('user');

        // Create wallet for user if Wallet module exists
        if (class_exists('Modules\Wallet\Models\Wallet')) {
            \Modules\Wallet\Models\Wallet::create([
                'title' => $user->first_name . ' ' . $user->last_name,
                'user_id' => $user->id,
                'amount' => 0
            ]);
        }

        // Generate 2FA QR code
        $user->qr_image = $this->multiFactorAuth($user);
        $user->save();

        // Clear caches
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');

        // Log in the user
        Auth::login($user, true);

        // Clear session
        session()->forget(['otp_phone', 'otp_verified']);

        // Delete OTP record
        OtpVerification::where('phone', $phone)->delete();

        return redirect('/home')->with('success', __('Registration successful! Welcome to HahuCare.'));
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        Log::info('[OTP:Resend] === resendOtp START ===', ['ip' => $request->ip()]);

        $phone = session('otp_phone');

        if (!$phone) {
            Log::warning('[OTP:Resend] No phone in session - redirecting to phone form');
            return redirect()->route('otp.phone.form');
        }

        Log::info('[OTP:Resend] Phone from session', ['phone' => $phone]);

        // Rate limiting - check last OTP sent time
        $lastOtp = OtpVerification::where('phone', $phone)
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        if ($lastOtp) {
            Log::warning('[OTP:Resend] Rate limited', [
                'phone' => $phone,
                'last_created' => $lastOtp->created_at,
            ]);
            return back()->withErrors([
                'otp' => __('Please wait 1 minute before requesting a new OTP.')
            ]);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Log::info('[OTP:Resend] New OTP generated', ['phone' => $phone]);

        // Delete old OTPs
        $deletedCount = OtpVerification::where('phone', $phone)->delete();
        Log::info('[OTP:Resend] Old OTPs deleted', ['phone' => $phone, 'deleted_count' => $deletedCount]);

        // Create new OTP record
        OtpVerification::create([
            'phone' => $phone,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP
        Log::info('[OTP:Resend] Calling AfroMessageService->sendOtp()', ['phone' => $phone]);
        $result = $this->smsService->sendOtp($phone, $otp);
        Log::info('[OTP:Resend] AfroMessageService result', [
            'phone' => $phone,
            'success' => $result['success'],
            'message' => $result['message'],
        ]);

        if (!$result['success']) {
            Log::error('[OTP:Resend] Resend FAILED', [
                'phone' => $phone,
                'error' => $result['message'],
            ]);
            return back()->withErrors([
                'otp' => __('Failed to send OTP. Please try again.')
            ]);
        }

        Log::info('[OTP:Resend] === resendOtp SUCCESS ===', ['phone' => $phone]);
        return back()->with('success', __('New OTP sent successfully.'));
    }
}
