# Google OAuth Login Flow - Complete Deep Dive

This document provides a comprehensive analysis of how Google OAuth login works in the HahuCare application for both web users and mobile app users.

---

## Table of Contents
1. [Configuration](#configuration)
2. [Web User Login Flow (Frontend)](#web-user-login-flow-frontend)
3. [Admin/Backend Login Flow](#adminbackend-login-flow)
4. [Mobile App API Login Flow](#mobile-app-api-login-flow)
5. [Database Schema](#database-schema)
6. [Security Considerations](#security-considerations)
7. [Troubleshooting](#troubleshooting)

---

## Configuration

### Environment Variables (.env)
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT=https://hahucare.com/login/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback
```

### Service Configuration (`config/services.php`)
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT', env('GOOGLE_REDIRECT_URI')),
],
```

### Package Used
- **Laravel Socialite** - Official Laravel package for OAuth authentication
- Driver: `google`
- Stateless mode for API calls

---

## Web User Login Flow (Frontend)

### Route Definition
**File:** `Modules/Frontend/Routes/web.php`
```php
// Initiate Google OAuth
Route::get('/auth/google', 'redirectToGoogle')->name('auth.google');

// Handle Google callback
Route::get('/auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');
```

### Controller
**File:** `Modules/Frontend/Http/Controllers/Auth/UserController.php`

### Step-by-Step Flow

#### **Step 1: User Clicks "Login with Google"**
```
User clicks button → GET /auth/google
```

**Method:** `redirectToGoogle()`
```php
public function redirectToGoogle()
{
    return Socialite::driver('google')->stateless()->redirect();
}
```

**What happens:**
- Laravel Socialite creates authorization URL
- Redirects user to Google's OAuth consent screen
- URL includes: client_id, redirect_uri, scope, state

#### **Step 2: User Authorizes on Google**
```
User logs in to Google → Selects account → Grants permissions
```

**Google redirects back to:**
```
https://hahucare.com/auth/google/callback?code=AUTHORIZATION_CODE&state=STATE
```

#### **Step 3: Handle Google Callback**
**Method:** `handleGoogleCallback(Request $request)`

**Detailed Process:**

```php
public function handleGoogleCallback(Request $request)
{
    // 1. Log the callback start
    \Log::info('Google OAuth Callback Started', [
        'url' => $request->fullUrl(),
        'has_code' => $request->has('code'),
        'has_error' => $request->has('error'),
    ]);

    try {
        // 2. Exchange authorization code for user data
        $googleUser = Socialite::driver('google')->stateless()->user();
        
        // $googleUser contains:
        // - getId() - Google user ID
        // - getEmail() - User's email
        // - getName() - Full name
        // - getAvatar() - Profile picture URL
        
        \Log::info('Google user retrieved', [
            'google_id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
        ]);

        // 3. Find or create user in database
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // 3a. Create new user
            $fullName = $googleUser->getName();
            $nameParts = explode(' ', $fullName);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? $firstName;

            $data = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(8)), // Random password
                'user_type' => 'user', // Frontend user
                'login_type' => 'google' // Mark as Google login
            ];

            $user = User::create($data);
            $user->assignRole('user'); // Assign 'user' role
            $user->save();
            
            \Log::info('New user created', ['user_id' => $user->id]);
        } else {
            // 3b. Existing user found
            \Log::info('Existing user found', [
                'user_id' => $user->id,
                'login_type' => $user->login_type,
            ]);
            
            // Check login_type compatibility
            if ($user->login_type !== 'google' && $user->login_type !== null) {
                // User registered with email/password, can't use Google
                return redirect('/login')->with('error', 
                    'This account was not created using Google login.');
            }
            
            // Update login_type if null
            if ($user->login_type === null) {
                $user->login_type = 'google';
                $user->save();
            }
        }

        // 4. Log the user in
        Auth::login($user, true); // true = remember me
        
        \Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'authenticated' => Auth::check(),
        ]);
        
        // 5. Regenerate session for security
        $request->session()->regenerate();
        
        // 6. Redirect to intended page or home
        if (session()->has('url.intended')) {
            return redirect()->intended();
        }
        
        return redirect()->route('frontend.index'); // Home page
        
    } catch (\Exception $e) {
        \Log::error('Google login error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return redirect()->route('login-page')
            ->with('error', 'Something went wrong with Google login.');
    }
}
```

### Key Features - Frontend Flow

1. **Stateless Mode**: `->stateless()` - No session state stored (good for APIs)
2. **User Type**: Always creates `user_type = 'user'` (patient/customer)
3. **Login Type Tracking**: Sets `login_type = 'google'` to prevent password login conflicts
4. **Role Assignment**: Automatically assigns 'user' role
5. **Random Password**: Generates random password (user can't login with password)
6. **Session Regeneration**: Security best practice after login
7. **Comprehensive Logging**: Every step is logged for debugging

---

## Admin/Backend Login Flow

### Route Definition
**File:** `routes/auth.php`
```php
// Initiate Google OAuth
Route::get('login/{provider}', [SocialLoginController::class, 'redirectToProvider'])
    ->name('social.login');

// Handle callback
Route::get('login/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])
    ->name('social.login.callback');
```

### Controller
**File:** `app/Http/Controllers/Auth/SocialLoginController.php`

### Step-by-Step Flow

#### **Step 1: Redirect to Google**
```php
public function redirectToProvider($provider)
{
    return Socialite::driver($provider)->redirect();
}
```

**URL:** `https://hahucare.com/login/google`

**What happens:**
- NOT stateless (uses session)
- Redirects to Google OAuth consent screen
- Callback URL: `https://hahucare.com/login/google/callback`

#### **Step 2: Handle Provider Callback**
```php
public function handleProviderCallback($provider)
{
    \Log::info('Social Login Callback Started', [
        'provider' => $provider,
        'url' => request()->fullUrl(),
    ]);

    try {
        // 1. Get user from Google
        $user = Socialite::driver($provider)->user();
        
        \Log::info('Socialite user retrieved', [
            'provider' => $provider,
            'social_id' => $user->getId(),
            'email' => $user->getEmail(),
        ]);

        // 2. Find or create user
        $authUser = $this->findOrCreateUser($user, $provider);

        // 3. Validate response
        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser; // Error redirect
        }

        if (!$authUser instanceof User) {
            flash('Login failed. Please try again.')->error();
            return redirect('/admin/login');
        }

        // 4. Log the user in
        Auth::login($authUser, true);
        
        \Log::info('User logged in successfully', [
            'user_id' => $authUser->id,
            'authenticated' => Auth::check(),
        ]);
        
    } catch (Exception $e) {
        \Log::error('Social Login Exception', [
            'provider' => $provider,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        flash('Google login failed: ' . $e->getMessage())->error();
        return redirect('/admin/login');
    }

    // 5. Redirect to admin dashboard
    return redirect()->intended(RouteServiceProvider::HOME); // /admin
}
```

#### **Step 3: Find or Create User**
```php
private function findOrCreateUser($socialUser, $provider)
{
    \Log::info('findOrCreateUser started', [
        'provider' => $provider,
        'social_id' => $socialUser->getId(),
        'email' => $socialUser->getEmail(),
    ]);

    // 1. Check if user already linked via UserProvider table
    if ($authUser = UserProvider::where('provider_id', $socialUser->getId())->first()) {
        $authUser = User::findOrFail($authUser->user->id);
        \Log::info('User found via UserProvider');
        return $authUser;
    }
    
    // 2. Check if user exists by email
    elseif ($authUser = User::where('email', $socialUser->getEmail())->first()) {
        \Log::info('User found by email, linking to provider');
        
        // Create UserProvider link
        UserProvider::create([
            'user_id' => $authUser->id,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'provider' => $provider,
        ]);
        
        return $authUser;
    }
    
    // 3. Create new user
    else {
        \Log::info('User not found, creating new user');
        
        $name = $socialUser->getName();
        $name_parts = $this->split_name($name);
        $email = $socialUser->getEmail();

        // Validate email exists
        if ($email == '' || $email === null) {
            \Log::warning('No email provided by provider');
            flash('Email address is required!')->error();
            return redirect('/admin/login');
        }

        try {
            // Create user
            $user = User::create([
                'first_name' => $name_parts[0],
                'last_name' => $name_parts[1],
                'name' => $name,
                'email' => $email,
            ]);

            // Download and save avatar
            $media = $user->addMediaFromUrl($socialUser->getAvatar())
                         ->toMediaCollection('users');
            $user->avatar = $media->getUrl();
            $user->save();

            // Fire registration event
            event(new UserRegistered($user));

            // Create UserProvider link
            UserProvider::create([
                'user_id' => $user->id,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
            ]);

            \Log::info('New user created', ['user_id' => $user->id]);
            return $user;
            
        } catch (\Exception $e) {
            \Log::error('Error creating user', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
```

### Key Features - Backend Flow

1. **Session-Based**: Uses session state (not stateless)
2. **UserProvider Table**: Links social accounts to users
3. **Avatar Download**: Downloads Google profile picture using Spatie Media Library
4. **Email Required**: Validates email exists (Google sometimes doesn't provide email)
5. **No User Type Set**: Doesn't set user_type (can be any role)
6. **Event Firing**: Fires `UserRegistered` event for new users
7. **Flexible Linking**: Can link existing email accounts to Google

---

## Mobile App API Login Flow

### Route Definition
**File:** `routes/api.php`
```php
Route::post('social-login', [AuthController::class, 'socialLogin']);
```

### Controller
**File:** `app/Http/Controllers/Auth/API/AuthController.php`

### Step-by-Step Flow

#### **Step 1: Mobile App Gets Google Token**
```
Mobile app uses Google Sign-In SDK
↓
Gets Google ID Token or Access Token
↓
Sends to Laravel API
```

#### **Step 2: API Request**
```http
POST /api/auth/social-login
Content-Type: application/json

{
    "login_type": "google",
    "email": "user@example.com",
    "user_type": "user",
    "first_name": "John",
    "last_name": "Doe",
    "username": "johndoe" (optional)
}
```

#### **Step 3: Process Social Login**
```php
public function socialLogin(Request $request)
{
    $input = $request->all();
    
    \Log::info('Social Login API Request', [
        'login_type' => $input['login_type'] ?? 'not_set',
        'email' => $input['email'] ?? 'not_set',
        'user_type' => $input['user_type'] ?? 'not_set',
    ]);

    // 1. Find existing user
    if (($input['login_type'] ?? '') === 'mobile') {
        $user_data = User::where('username', $input['username'])
                        ->where('login_type', 'mobile')
                        ->first();
    } else {
        $user_data = User::where('email', $input['email'] ?? '')->first();
    }

    if ($user_data != null) {
        // 2a. Existing user found
        \Log::info('Social Login: Existing user found', [
            'user_id' => $user_data->id,
            'login_type' => $user_data->login_type,
        ]);

        $usertype = $user_data->user_type;

        // Verify email for doctors/vendors
        if ($usertype == "doctor" || $usertype == "vendor") {
            if ($user_data->email_verified_at == null) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.account_not_verify')
                ]);
            }
        }

        // Update login_type if not set
        if (!isset($user_data->login_type) || 
            $user_data->login_type == '' || 
            $user_data->login_type === null) {
            $user_data->login_type = $request->login_type;
            $user_data->save();
            \Log::info('Updated existing user login_type');
        }

        $message = __('messages.login_success');
        
    } else {
        // 2b. Create new user
        \Log::info('Social Login: Creating new user');
        
        // Check for trashed users
        if ($request->login_type === 'google' || $request->login_type === 'apple') {
            $key = 'email';
            $value = $request->email;
        } else {
            $key = 'username';
            $value = $request->username;
        }

        $trashed_user = User::where($key, $value)
                           ->whereNotNull('login_type')
                           ->withTrashed()
                           ->first();

        if ($trashed_user != null && $trashed_user->trashed()) {
            return $this->sendError(
                __('validation.unique', ['attribute' => $key]), 
                400
            );
        }

        // Create new user
        $user_data = $this->registerTrait($request);
        
        if ($user_data instanceof \Illuminate\Http\JsonResponse) {
            return $user_data; // Validation error
        }

        $message = __('messages.register_successfull');
    }

    // 3. Generate Sanctum token
    $user_data['api_token'] = $user_data->createToken(setting('app_name'))
                                        ->plainTextToken;

    // 4. Clear caches (performance concern - consider removing)
    Artisan::call('cache:clear');
    Artisan::call('config:clear');

    // 5. Return user data with token
    $userResource = new SocialLoginResource($user_data);
    return $this->sendResponse($userResource, $message);
}
```

#### **Step 4: API Response**
```json
{
    "status": true,
    "message": "Login successful",
    "data": {
        "id": 123,
        "first_name": "John",
        "last_name": "Doe",
        "email": "user@example.com",
        "user_type": "user",
        "login_type": "google",
        "api_token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...",
        "profile": {...},
        "roles": [...]
    }
}
```

### Key Features - API Flow

1. **Token-Based**: Returns Sanctum API token
2. **Mobile App Handles OAuth**: App gets Google token, sends user data to API
3. **No Socialite**: Doesn't use Socialite (mobile app already authenticated)
4. **Flexible User Types**: Can create any user_type (user, doctor, vendor, etc.)
5. **Login Type Tracking**: Sets `login_type = 'google'`
6. **Trashed User Check**: Prevents re-registration of deleted accounts
7. **Email Verification**: Requires verification for doctors/vendors
8. **Cache Clearing**: Clears caches on login (consider removing for performance)

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    first_name VARCHAR(191),
    last_name VARCHAR(191),
    name VARCHAR(191),
    email VARCHAR(191) UNIQUE,
    password VARCHAR(191),
    user_type VARCHAR(50), -- 'user', 'doctor', 'vendor', etc.
    login_type VARCHAR(50), -- 'google', 'apple', 'mobile', null
    avatar TEXT,
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

### UserProvider Table (Backend Only)
```sql
CREATE TABLE user_providers (
    id BIGINT PRIMARY KEY,
    user_id BIGINT, -- Foreign key to users.id
    provider VARCHAR(191), -- 'google', 'facebook', 'github'
    provider_id VARCHAR(191), -- Google user ID
    avatar TEXT, -- Google profile picture URL
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Purpose:**
- Links social accounts to users
- Allows multiple social providers per user
- Stores provider-specific data (Google ID, avatar)

---

## Security Considerations

### 1. CSRF Protection
**Issue:** OAuth callbacks can be blocked by CSRF middleware

**Solution:** Exempt callback routes in `app/Http/Middleware/VerifyCsrfToken.php`
```php
protected $except = [
    'login/*/callback',  // Backend
    'auth/*/callback',   // Frontend
];
```

### 2. State Parameter
- **Backend:** Uses session state (automatic)
- **Frontend:** Uses `stateless()` mode (no state)
- **API:** No state needed (mobile app handles OAuth)

### 3. Token Security
- **Web:** Session-based authentication
- **API:** Sanctum token (Bearer token)
- Tokens stored in `personal_access_tokens` table

### 4. Email Validation
- Always validate email exists from Google
- Some Google accounts don't provide email (rare)

### 5. Login Type Conflicts
- Users created with email/password can't use Google login (frontend)
- Backend allows linking existing accounts to Google
- API allows updating login_type if null

### 6. Session Regeneration
- Always regenerate session after login (prevent session fixation)
- Frontend: `$request->session()->regenerate()`

---

## Troubleshooting

### Common Issues

#### 1. "Google OAuth credentials are not configured"
**Cause:** Missing or null credentials in `.env`

**Fix:**
```bash
# Check .env file
GOOGLE_CLIENT_ID=your_actual_client_id
GOOGLE_CLIENT_SECRET=your_actual_secret

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

#### 2. "Redirect URI mismatch"
**Cause:** Google Cloud Console redirect URIs don't match

**Fix:** Add these URIs in Google Cloud Console:
- `https://hahucare.com/login/google/callback` (backend)
- `https://hahucare.com/auth/google/callback` (frontend)

#### 3. "This account was not created using Google login"
**Cause:** User registered with email/password, trying to use Google

**Fix:** User must login with email/password, or admin can update `login_type`

#### 4. CSRF Token Mismatch
**Cause:** Callback routes not exempted from CSRF

**Fix:** Check `app/Http/Middleware/VerifyCsrfToken.php` has exemptions

#### 5. "Email address is required"
**Cause:** Google account doesn't provide email (rare)

**Fix:** User must use different Google account or email/password login

---

## Flow Diagrams

### Frontend Web User Flow
```
User clicks "Login with Google"
    ↓
GET /auth/google
    ↓
Redirect to Google OAuth
    ↓
User authorizes on Google
    ↓
Google redirects: /auth/google/callback?code=XXX
    ↓
Exchange code for user data (Socialite)
    ↓
Check if user exists by email
    ↓
    ├─ Yes → Check login_type compatibility
    │         ↓
    │         ├─ Compatible → Login user
    │         └─ Incompatible → Error
    │
    └─ No → Create new user
              ↓
              Set user_type='user', login_type='google'
              ↓
              Assign 'user' role
              ↓
              Login user
    ↓
Regenerate session
    ↓
Redirect to frontend home
```

### Backend Admin Flow
```
Admin clicks "Login with Google"
    ↓
GET /login/google
    ↓
Redirect to Google OAuth (with session state)
    ↓
User authorizes on Google
    ↓
Google redirects: /login/google/callback?code=XXX
    ↓
Exchange code for user data (Socialite)
    ↓
Check UserProvider table for provider_id
    ↓
    ├─ Found → Get linked user → Login
    │
    ├─ Not found, but email exists
    │   ↓
    │   Create UserProvider link → Login
    │
    └─ Not found, email doesn't exist
        ↓
        Create new user
        ↓
        Download avatar from Google
        ↓
        Create UserProvider link
        ↓
        Fire UserRegistered event
        ↓
        Login user
    ↓
Redirect to /admin
```

### Mobile App API Flow
```
User taps "Login with Google" in app
    ↓
App uses Google Sign-In SDK
    ↓
App gets Google ID Token
    ↓
App extracts user data (email, name)
    ↓
POST /api/auth/social-login
{
    "login_type": "google",
    "email": "user@example.com",
    "user_type": "user",
    "first_name": "John",
    "last_name": "Doe"
}
    ↓
Check if user exists by email
    ↓
    ├─ Yes → Update login_type if null
    │         ↓
    │         Generate Sanctum token
    │
    └─ No → Create new user
              ↓
              Set login_type='google'
              ↓
              Generate Sanctum token
    ↓
Return user data + API token
    ↓
App stores token for future requests
```

---

## Summary

### Three Distinct Flows

| Aspect | Frontend Web | Backend Admin | Mobile App API |
|--------|-------------|---------------|----------------|
| **Route** | `/auth/google` | `/login/google` | `/api/auth/social-login` |
| **Controller** | `UserController` | `SocialLoginController` | `AuthController` |
| **Socialite** | Yes (stateless) | Yes (session) | No (app handles OAuth) |
| **User Type** | Always `user` | Any role | Any role |
| **Login Type** | `google` | Not set | `google` |
| **UserProvider** | No | Yes | No |
| **Avatar** | No | Yes (downloads) | No |
| **Auth Method** | Session | Session | Sanctum token |
| **Redirect** | Frontend home | `/admin` | JSON response |
| **Account Linking** | No (strict) | Yes (flexible) | Yes (flexible) |

### Key Takeaways

1. **Frontend is strict**: Only allows Google login for Google-created accounts
2. **Backend is flexible**: Can link existing accounts to Google
3. **API trusts mobile app**: Assumes app already authenticated with Google
4. **All flows use same credentials**: Same Google Client ID/Secret
5. **Different redirect URIs**: Frontend and backend use different callback URLs
6. **Comprehensive logging**: All flows log extensively for debugging
7. **Security first**: CSRF exemptions, session regeneration, token validation

---

## Testing

### Test Frontend Flow
1. Go to `https://hahucare.com/login`
2. Click "Login with Google"
3. Select Google account
4. Should redirect to frontend home
5. Check logs: `tail -f storage/logs/laravel.log | grep "Google"`

### Test Backend Flow
1. Go to `https://hahucare.com/admin/login`
2. Click "Login with Google"
3. Select Google account
4. Should redirect to `/admin`
5. Check `user_providers` table for link

### Test API Flow
```bash
curl -X POST https://hahucare.com/api/auth/social-login \
  -H "Content-Type: application/json" \
  -d '{
    "login_type": "google",
    "email": "test@example.com",
    "user_type": "user",
    "first_name": "Test",
    "last_name": "User"
  }'
```

Expected response: User data with `api_token`
