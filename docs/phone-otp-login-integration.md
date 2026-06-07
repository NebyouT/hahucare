# Phone OTP Login Integration Guide

## Overview

This document covers the complete phone OTP (One-Time Password) login system for HahuCare, with both **Admin Panel** (`resources/views/auth/otp/`) and **Frontend Website** (`Modules/Frontend/Resources/views/auth/otp_*.blade.php`) implementations.

The system allows users to:
1. Enter their phone number
2. Receive a 6-digit OTP via SMS (AfroMessage API)
3. Verify the OTP
4. Auto-login if existing user, or register as a new user

---

## Architecture

### Files Involved

| Layer | File | Purpose |
|-------|------|---------|
| **Routes** | `routes/auth.php` | Web routes for OTP login |
| **Controller** | `app/Http/Controllers/Auth/OtpAuthController.php` | Handles all OTP logic |
| **Model** | `app/Models/OtpVerification.php` | OTP record model |
| **SMS Service** | `app/Services/AfroMessageService.php` | Sends SMS via AfroMessage API |
| **Mock Service** | `app/Services/MockSmsService.php` | Fallback SMS in dev environment |
| **Migration** | `database/migrations/2026_01_01_000001_create_otp_verifications_table.php` | Creates `otp_verifications` table |
| **Migration** | `database/migrations/2026_01_01_000002_fix_otp_column_size.php` | Fixes OTP column for hashed values |
| **Admin Views** | `resources/views/auth/otp/phone.blade.php` | Admin OTP phone input page |
| **Admin Views** | `resources/views/auth/otp/verify.blade.php` | Admin OTP verification page |
| **Admin Views** | `resources/views/auth/otp/register.blade.php` | Admin OTP registration page |
| **Frontend Views** | `Modules/Frontend/Resources/views/auth/otp_phone.blade.php` | Frontend phone input page |
| **Frontend Views** | `Modules/Frontend/Resources/views/auth/otp_verify.blade.php` | Frontend OTP verification page |
| **Frontend Views** | `Modules/Frontend/Resources/views/auth/otp_register.blade.php` | Frontend registration page |

---

## Database Schema

Table: `otp_verifications`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint, PK | Auto-increment ID |
| `phone` | string(20), indexed | Phone number (format: `251XXXXXXXXX`) |
| `otp` | string(255) | Hashed 6-digit OTP |
| `expires_at` | timestamp | OTP expiry (5 minutes from creation) |
| `is_verified` | boolean, default false | Whether OTP was successfully verified |
| `attempts` | integer, default 0 | Failed verification attempts (max 5) |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record update time |

---

## Route Configuration

Routes are defined in `routes/auth.php` under the `guest` middleware:

```php
Route::middleware('guest')->group(function () {
    Route::get('login/phone', [OtpAuthController::class, 'showPhoneForm'])->name('otp.phone.form');
    Route::post('login/phone/send-otp', [OtpAuthController::class, 'sendOtp'])->name('otp.send');
    Route::get('login/phone/verify', [OtpAuthController::class, 'showVerifyForm'])->name('otp.verify.form');
    Route::post('login/phone/verify', [OtpAuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('login/phone/resend', [OtpAuthController::class, 'resendOtp'])->name('otp.resend');
    Route::get('login/phone/register', [OtpAuthController::class, 'showRegisterForm'])->name('otp.register.form');
    Route::post('login/phone/register', [OtpAuthController::class, 'register'])->name('otp.register');
});
```

---

## Flow Diagram

```
[Phone Input] → [Send OTP] → [Verify OTP] → [User Exists?]
                                                  │
                                    ┌─────────────┴─────────────┐
                                    │ YES                       │ NO
                                    │                           │
                                    ▼                           ▼
                              [Auto Login]            [Registration Form]
                                                            │
                                                            ▼
                                                      [Create User]
                                                            │
                                                            ▼
                                                      [Auto Login]
```

---

## Full Integration Steps

### Step 1: Database Migration

Run the existing migration to create the `otp_verifications` table:

```bash
php artisan migrate
```

This runs:
- `2026_01_01_000001_create_otp_verifications_table.php`
- `2026_01_01_000002_fix_otp_column_size.php`

### Step 2: Controller Setup

The `OtpAuthController` handles all logic. The controller is already created at `app/Http/Controllers/Auth/OtpAuthController.php`.

Key methods:
- `showPhoneForm()` — Returns the phone input view
- `sendOtp(Request $request)` — Validates phone, generates OTP, stores hashed OTP, sends via SMS
- `showVerifyForm()` — Returns OTP input view (guarded by session)
- `verifyOtp(Request $request)` — Validates OTP, checks attempts/expiry, logs in existing user or redirects to registration
- `showRegisterForm()` — Returns registration form (guarded by session)
- `register(Request $request)` — Creates new user with auto-generated email, logs in
- `resendOtp(Request $request)` — Rate-limited OTP resend

**Important:** The controller uses sessions to track the flow:
- `session('otp_phone')` — Stores phone number during verification
- `session('otp_verified')` — Set to `true` after OTP verification for registration step

### Step 3: Route Registration

Ensure the routes in `routes/auth.php` are loaded. They are automatically loaded by Laravel's `RouteServiceProvider` from `routes/auth.php` if you have:

```php
// In routes/web.php or RouteServiceProvider
require __DIR__.'/auth.php';
```

### Step 4: Create Views

#### Admin Panel Views (using Blade components)

Uses `x-auth-layout`, `x-auth-card`, `x-auth-validation-errors` components.

**phone.blade.php** — `/login/phone`
- Phone number input with `+251` prefix
- Form POST to `route('otp.send')`
- JS strips non-numeric chars and leading `0`

**verify.blade.php** — `/login/phone/verify`
- 6-digit OTP input
- Shows phone number from session
- Form POST to `route('otp.verify')`
- Resend OTP button → `route('otp.resend')`
- Change phone number link → `route('otp.phone.form')`

**register.blade.php** — `/login/phone/register`
- First name (required), Last name (required)
- Email (optional — auto-generated if empty)
- Gender dropdown, Date of birth
- Form POST to `route('otp.register')`

#### Frontend Views (using frontend layout)

Same flow but styled with the frontend auth layout (`frontend::layouts.auth_layout`).

Views: `otp_phone.blade.php`, `otp_verify.blade.php`, `otp_register.blade.php`

### Step 5: SMS Service Configuration

The AfroMessage service sends OTP SMS. Configure in `.env`:

```env
AFROMESSAGE_TOKEN=your_api_token_here
AFROMESSAGE_IDENTIFIER_ID=your_identifier_id
AFROMESSAGE_SENDER=HahuCare
AFROMESSAGE_BASE_URL=https://api.afromessage.com/api/send
```

**How it works:**
- `AfroMessageService::sendOtp($phone, $otp)` → formats message, calls `sendSms()`
- Phone numbers are normalized to Ethiopian format (`+251XXXXXXXXX`)
- In `local` environment, falls back to `MockSmsService` if API is unreachable
- `MockSmsService` logs the OTP to the console and Laravel log

### Step 6: Add Entry Points on Login Pages

#### Admin Login Page (`resources/views/auth/login.blade.php`)

Add a button linking to the OTP phone form:

```blade
<div class="text-center mb-3">
    <a href="{{ route('otp.phone.form') }}" class="btn btn-outline-success w-100">
        <i class="fas fa-mobile-alt me-2"></i>{{ __('Login with Phone Number') }}
    </a>
</div>
```

#### Frontend Login Page (`Modules/Frontend/Resources/views/auth/login.blade.php`)

```blade
<div class="d-flex justify-content-center mt-4">
    <a href="{{ route('otp.phone.form') }}" class="btn btn-outline-success w-100" style="border-radius: 8px; padding: 12px;">
        <i class="ph ph-phone me-2"></i>{{ __("Login with Phone Number") }}
    </a>
</div>
<div class="text-center mt-2 mb-3">
    <small class="text-muted">{{ __("Get OTP on your phone to login") }}</small>
</div>
```

---

## Security

| Measure | Implementation |
|---------|---------------|
| **OTP Hashing** | `Hash::make($otp)` before storage, `Hash::check()` on verification |
| **Rate Limiting** | 1-minute cooldown between OTP requests per phone number |
| **OTP Expiry** | 5-minute TTL (`expires_at`) |
| **Max Attempts** | 5 failed attempts → OTP record deleted, user must re-request |
| **Session Guard** | Registration page only accessible if `otp_verified` session flag set |
| **Phone Normalization** | Consistent phone format (`251XXXXXXXXX`) for storage and lookup |

---

## Adding a Demo Login for Phone OTP

To add demo/quick-login functionality (like the admin email demo accounts):

### 1. In `OtpAuthController::sendOtp()`, detect demo phone numbers:

```php
// In sendOtp() after generating $otp
$isDemo = false;
if (config('app.env') !== 'production' && in_array($phone, ['251911111111', '251922222222'])) {
    $isDemo = true;
    Log::info('Demo OTP login', ['phone' => $phone, 'otp' => $otp]);
}

// Skip sending SMS for demo numbers
if (!$isDemo) {
    $result = $this->smsService->sendOtp($phone, $otp);
    // ... error handling
}
```

### 2. In `OtpAuthController::verifyOtp()`, accept a hardcoded OTP for demo:

```php
// Before the Hash::check()
$isDemo = false;
$demoOtp = '123456';
if (config('app.env') !== 'production' && $request->otp === $demoOtp) {
    $isDemo = true;
    // Check if the phone has an OTP record (even expired)
    $otpRecord = OtpVerification::where('phone', $phone)->latest()->first();
    if (!$otpRecord) {
        return back()->withErrors(['otp' => __('Please request an OTP first.')]);
    }
}

// Then modify the Hash::check condition:
if (!$isDemo && !Hash::check($request->otp, $otpRecord->otp)) {
    // ... error handling
}
```

### 3. In the phone input view, add a demo dropdown:

```blade
@if (setting('is_dummy_credentials'))
    <div class="mt-4">
        <h6 class="text-center border-top py-3">{{ __('messages.lbl_demo_account') }}</h6>
        <select name="demo_phone" id="demoPhone" class="form-control"
                onchange="document.getElementById('phone').value = this.value">
            <option value="">{{ __('Select demo account') }}</option>
            <option value="911111111">Demo User 1</option>
            <option value="922222222">Demo User 2</option>
        </select>
        <small class="text-muted d-block mt-1">OTP: <strong>123456</strong></small>
    </div>
@endif
```

---

## Mobile App API Integration

The current phone OTP flow is **session-based (web only)**. For mobile app integration, create dedicated API endpoints:

### Suggested API Endpoints

```php
// routes/api.php
Route::post('phone/send-otp', [ApiOtpController::class, 'sendOtp']);
Route::post('phone/verify-otp', [ApiOtpController::class, 'verifyOtp']);
```

### API Controller Approach

```php
class ApiOtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|min:9|max:15']);

        $phone = AfroMessageService::normalizeForStorage($request->phone);

        // Rate limit check
        $existingOtp = OtpVerification::where('phone', $phone)
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        if ($existingOtp) {
            return response()->json([
                'status' => false,
                'message' => 'Please wait 1 minute before requesting a new OTP.'
            ], 429);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::where('phone', $phone)->delete();

        OtpVerification::create([
            'phone' => $phone,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
        ]);

        $result = app(AfroMessageService::class)->sendOtp($phone, $otp);

        if (!$result['success']) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP.'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $phone = AfroMessageService::normalizeForStorage($request->phone);

        $otpRecord = OtpVerification::validForPhone($phone)->first();

        if (!$otpRecord) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired or invalid. Please request a new one.'
            ], 400);
        }

        if ($otpRecord->maxAttemptsReached()) {
            $otpRecord->delete();
            return response()->json([
                'status' => false,
                'message' => 'Too many failed attempts. Please request a new OTP.'
            ], 429);
        }

        if (!Hash::check($request->otp, $otpRecord->otp)) {
            $otpRecord->incrementAttempts();
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP. Attempts remaining: ' . (5 - $otpRecord->attempts)
            ], 400);
        }

        $otpRecord->markAsVerified();

        $user = User::where('mobile', $phone)->first();

        if ($user) {
            $otpRecord->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful.',
                'token' => $token,
                'user' => new RegisterResource($user),
                'is_new_user' => false,
            ]);
        }

        // New user — return token for registration completion
        $tempToken = Str::random(60);
        Cache::put('otp_temp_token_' . $phone, $tempToken, now()->addMinutes(15));

        return response()->json([
            'status' => true,
            'message' => 'Phone verified. Please complete registration.',
            'temp_token' => $tempToken,
            'is_new_user' => true,
        ]);
    }
}
```

---

## Troubleshooting

| Issue | Cause | Fix |
|-------|-------|-----|
| OTP not sending | API token not configured | Check `AFROMESSAGE_TOKEN` in `.env` |
| Phone number format error | Invalid format accepted | Ensure `normalizeForStorage()` is called |
| Session expired during registration | Session timeout | Increase session lifetime in `config/session.php` |
| "Phone already taken" | Duplicate mobile number | Check `users.mobile` unique constraint |
| OTP not matching | OTP column too short for hash | Run migration `2026_01_01_000002_fix_otp_column_size.php` |

---

## Testing

```bash
# Run migrations
php artisan migrate

# In local environment, OTPs are logged to:
# - Laravel log (storage/logs/laravel.log)
# - Console output (MockSmsService)

# Test the full flow:
# 1. Visit /login/phone
# 2. Enter phone 911111111
# 3. Check logs for OTP
# 4. Enter OTP on verify page
# 5. Complete registration or auto-login
```
