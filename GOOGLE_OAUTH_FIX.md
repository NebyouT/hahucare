# Google OAuth Error 400: invalid_request - FIXED

## Problem
**Error:** "Missing required parameter: redirect_uri"

**Cause:** The code was using `env('GOOGLE_REDIRECT')` but your `.env` file has `GOOGLE_REDIRECT_URI`.

---

## ✅ What I Fixed

### 1. Updated SettingController.php
Changed from `env('GOOGLE_REDIRECT')` to `env('GOOGLE_REDIRECT_URI')` in two places:
- `googleId()` method (line 227)
- `handleGoogleCallback()` method (line 242)

Also added the required Calendar scope for Google Meet.

---

## 🔧 Setup Instructions

### Step 1: Update Your .env File

Open your `.env` file and update these values:

```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=YOUR_CLIENT_ID_HERE.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=YOUR_SECRET_KEY_HERE
GOOGLE_REDIRECT_URI=https://hahucare.com/callback
```

**Important:** Replace:
- `YOUR_CLIENT_ID_HERE` with your actual Google Client ID
- `YOUR_SECRET_KEY_HERE` with your actual Google Client Secret
- `https://hahucare.com/callback` with your actual domain

---

### Step 2: Configure Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project (project-864058558222)
3. Go to **APIs & Services** → **Credentials**
4. Click on your OAuth 2.0 Client ID
5. Under **Authorized redirect URIs**, add:
   ```
   https://hahucare.com/callback
   ```
6. Click **Save**

**Note:** Make sure the redirect URI in Google Cloud Console **exactly matches** what you put in your `.env` file.

---

### Step 3: Enable Required APIs

In Google Cloud Console, enable these APIs:
1. **Google Calendar API** (for creating meet links)
2. **Google+ API** or **People API** (for user info)

Go to: **APIs & Services** → **Library** → Search and enable each API

---

### Step 4: Update Database Settings

Run this SQL to add your Google credentials to the database:

```sql
-- Update Google Client ID
UPDATE settings 
SET val = 'YOUR_CLIENT_ID_HERE.apps.googleusercontent.com' 
WHERE name = 'google_clientid';

-- Update Google Client Secret
UPDATE settings 
SET val = 'YOUR_SECRET_KEY_HERE' 
WHERE name = 'google_secret_key';

-- Enable Google Meet
UPDATE settings 
SET val = '1' 
WHERE name = 'google_meet_method';
```

---

### Step 5: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 🧪 Test the Connection

### For Doctors to Connect Google Account:

1. Doctor logs into backend
2. Goes to Settings or Profile
3. Clicks "Connect Google Account" button
4. Should redirect to Google OAuth consent screen
5. Doctor authorizes the app
6. Should redirect back to your site with success message
7. Check database:
   ```sql
   SELECT id, email, google_access_token 
   FROM users 
   WHERE user_type = 'doctor';
   ```
   The `google_access_token` should now have a value.

---

## 📋 Complete .env Configuration

Here's what your Google section should look like:

```env
# Google OAuth for Telemedicine
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=123456789-abcdefghijklmnop.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-abcdefghijklmnopqrstuvwx
GOOGLE_REDIRECT_URI=https://hahucare.com/callback
```

---

## 🔍 Troubleshooting

### Error: "redirect_uri_mismatch"
**Solution:** Make sure the redirect URI in `.env` **exactly matches** what's in Google Cloud Console.
- Check for trailing slashes
- Check http vs https
- Check domain spelling

### Error: "access_denied"
**Solution:** User cancelled the authorization. Try again.

### Error: "invalid_client"
**Solution:** 
- Check `GOOGLE_CLIENT_ID` is correct
- Check `GOOGLE_CLIENT_SECRET` is correct
- Make sure you're using credentials from the correct Google Cloud project

---

## 📝 What Changed in the Code

### Before:
```php
'redirect_uri' => env('GOOGLE_REDIRECT'),  // ❌ Wrong variable name
```

### After:
```php
'redirect_uri' => env('GOOGLE_REDIRECT_URI'),  // ✅ Correct variable name
```

Also added Calendar scope:
```php
'scopes' => ['https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/userinfo.email'],
```

---

## 🎯 Next Steps After Fixing OAuth

Once doctors can connect their Google accounts:

1. ✅ Doctor connects Google account
2. ✅ Enable video consultancy on services
3. ✅ Patient books appointment with video service
4. ✅ Google Meet link is automatically generated
5. ✅ Patient clicks video icon to join meeting

---

## 📞 Support

If you still get errors:
1. Check Laravel logs: `storage/logs/laravel-YYYY-MM-DD.log`
2. Check browser console for JavaScript errors
3. Verify all environment variables are set correctly
4. Make sure Google Cloud Console settings match exactly

---

**Last Updated:** January 22, 2026  
**Status:** ✅ FIXED
