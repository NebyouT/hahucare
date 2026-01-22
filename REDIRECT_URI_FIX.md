# Google OAuth Redirect URI Mismatch - QUICK FIX

## 🔴 Error
**Error 400: redirect_uri_mismatch**

"This app's request is invalid. You can't sign in because this app sent an invalid request."

---

## ✅ THE SOLUTION

The route is defined with `/app` prefix in your routes file (line 97 in web.php):

```php
Route::group(['prefix' => 'app', 'middleware' => ['auth', 'auth_check']], function () {
    // ...
    Route::get('callback', [SettingController::class, 'handleGoogleCallback']);
});
```

This means the **actual callback URL** is:

```
https://hahucare.com/app/callback
```

NOT `https://hahucare.com/callback`

---

## 🔧 Fix in Google Cloud Console

### Step 1: Go to Google Cloud Console
1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project: **project-864058558222**
3. Go to **APIs & Services** → **Credentials**

### Step 2: Update OAuth 2.0 Client ID
1. Click on your OAuth 2.0 Client ID
2. Under **Authorized redirect URIs**, add:
   ```
   https://hahucare.com/app/callback
   ```
3. **Remove** the old one if you have: `https://hahucare.com/callback` (without /app)
4. Click **Save**

---

## 🔧 Fix in Your .env File

Update your `.env` file to match:

```env
GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback
```

**Note the `/app` in the URL!**

---

## ⚠️ IMPORTANT: Both Must Match EXACTLY

Your redirect URI must be **EXACTLY the same** in:
1. ✅ `.env` file → `GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback`
2. ✅ Google Cloud Console → Authorized redirect URIs → `https://hahucare.com/app/callback`

**Check for:**
- Correct protocol (https vs http)
- Correct domain (hahucare.com)
- Correct path (`/app/callback` NOT `/callback`)
- No trailing slash
- No extra spaces

---

## 🧪 Test After Fixing

1. Clear Laravel cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Doctor logs into backend
3. Goes to Settings/Profile
4. Clicks "Connect Google Account"
5. Should redirect to Google OAuth screen ✅
6. Doctor authorizes
7. Should redirect back to `https://hahucare.com/app/callback` ✅
8. Success message shown ✅

---

## 📋 Complete Configuration

### .env File:
```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=YOUR_SECRET_KEY
GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback
```

### Google Cloud Console → Authorized redirect URIs:
```
https://hahucare.com/app/callback
```

---

## 🔍 If Still Not Working

### Check 1: Verify the exact URL being used
Add this temporarily to `SettingController.php` line 227:

```php
public function googleId(Request $request)
{
    $redirectUri = env('GOOGLE_REDIRECT_URI');
    \Log::info('Google OAuth Redirect URI: ' . $redirectUri);
    
    $setting = Setting::where('type', 'google_meet_method')->where('name', 'google_clientid')->first();
    $client = new Client([
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'redirect_uri' => $redirectUri,
        'scopes' => ['https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/userinfo.email'],
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ]);
    $authUrl = $client->createAuthUrl();
    return response()->json($authUrl);
}
```

Then check `storage/logs/laravel-YYYY-MM-DD.log` for the logged redirect URI.

### Check 2: Verify Google Cloud Console settings
1. Make sure you're editing the correct OAuth Client ID
2. Make sure you saved the changes
3. Wait 1-2 minutes for Google to propagate changes

### Check 3: Clear browser cache
- Clear browser cookies and cache
- Try in incognito/private window

---

## 📝 Summary

**The Problem:** Route has `/app` prefix, so callback URL is `/app/callback` not just `/callback`

**The Fix:** Add `https://hahucare.com/app/callback` to Google Cloud Console Authorized redirect URIs

**The Result:** Doctor can successfully connect Google account ✅

---

**Last Updated:** January 22, 2026  
**Status:** ✅ SOLUTION PROVIDED
