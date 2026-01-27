# 🔑 Google OAuth Credentials for Production

**Add these to your production `.env` file on cPanel.**

---

## ✅ Your Google OAuth Credentials

```env
# Google OAuth for Doctor Calendar Integration
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

---

## ⚠️ Important: Fix Redirect URIs

I noticed your credentials have **different redirect URIs**. For HahuCare to work correctly, both should be:

```
https://hahucare.com/app/auth/google/callback
```

**Your current values:**
- `GOOGLE_REDIRECT=https://hahucare.com/login/google/callback` ❌ Wrong
- `GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback` ❌ Wrong

**Correct values:**
- `GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback` ✅
- `GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback` ✅

---

## 🚀 Deploy to Production (3 Steps)

### **Step 1: Update Google Cloud Console**

**IMPORTANT:** Make sure your Google Cloud Console has the correct redirect URI.

1. Go to: **https://console.cloud.google.com/**
2. **APIs & Services** → **Credentials**
3. Click on your OAuth 2.0 Client ID: `864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com`
4. Under **Authorized redirect URIs**, make sure you have:
   ```
   https://hahucare.com/app/auth/google/callback
   ```
5. **Remove** any incorrect URIs like:
   - `https://hahucare.com/login/google/callback`
   - `https://hahucare.com/app/callback`
6. Click **Save**

---

### **Step 2: Add to Production .env**

**Using cPanel:**

1. **Login to cPanel**
2. **File Manager** → Navigate to `public_html/`
3. Find and **Edit** the `.env` file
4. **Add these lines** (copy-paste exactly):

```env
# Google OAuth for Doctor Calendar Integration
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

5. **Save** the file

---

### **Step 3: Clear Cache on Production**

**Option A - Using SSH:**
```bash
cd public_html
php artisan config:clear
php artisan cache:clear
```

**Option B - Using Web Browser (No SSH):**

Create file: `clear_google_oauth_cache.php` in `public_html/`

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Artisan::call('config:clear');
echo "✅ Config cleared<br>";

Artisan::call('cache:clear');
echo "✅ Cache cleared<br>";

echo "<h2>✅ Google OAuth is now configured!</h2>";
echo "<p><strong>DELETE THIS FILE NOW for security!</strong></p>";
```

Access: `https://hahucare.com/clear_google_oauth_cache.php`

**Delete the file immediately after running!**

---

## 🧪 Test the Integration

1. **Clear browser cache:**
   - `Ctrl + Shift + Delete` (Windows)
   - `Cmd + Shift + Delete` (Mac)
   - Clear everything

2. **Login to your website:**
   ```
   https://hahucare.com/app/login
   ```

3. **Go to Profile → Google Calendar**

4. **Click "Connect Google Account"**

5. **Should redirect to Google login** (no error!)

6. **Login with your Google account**

7. **Allow permissions**

8. **Should redirect back to HahuCare** with success message ✅

---

## 🔍 Verify It's Working

### **Check 1: No Console Errors**

1. Press `F12` (Developer Tools)
2. Go to "Console" tab
3. Click "Connect Google Account"
4. Should see: Redirect to Google (no 500 error)

### **Check 2: Database Has Token**

After connecting, check database:
```sql
SELECT id, full_name, email, google_access_token, is_telmet 
FROM users 
WHERE email = 'your-doctor-email@example.com';
```

Should show:
- `google_access_token`: Has JSON value (not NULL)
- `is_telmet`: 1

### **Check 3: Can Create Google Meet Links**

1. Create a new appointment
2. Select "Google Meet" as meeting type
3. Should generate Google Meet link automatically
4. Event should appear in Google Calendar

---

## 📋 Deployment Checklist

- [ ] Updated Google Cloud Console redirect URI to: `https://hahucare.com/app/auth/google/callback`
- [ ] Removed incorrect redirect URIs from Google Console
- [ ] Saved changes in Google Console
- [ ] Edited production `.env` file in cPanel
- [ ] Added all 5 Google OAuth variables to `.env`
- [ ] Saved `.env` file
- [ ] Cleared config cache on production
- [ ] Cleared application cache on production
- [ ] Cleared browser cache
- [ ] Tested: Clicked "Connect Google Account"
- [ ] Success: Redirected to Google login (no error)
- [ ] Success: Connected and token saved in database
- [ ] Success: Can create Google Meet links

---

## 🔒 Security Notes

**Keep These Credentials Secret:**
- ✅ Never commit to Git (already in `.gitignore`)
- ✅ Never share publicly
- ✅ Only add to production `.env` file
- ✅ Delete any test scripts after use

**Authorized Domains in Google Console:**

Make sure your Google Cloud Console project has:
- **Authorized JavaScript origins:** `https://hahucare.com`
- **Authorized redirect URIs:** `https://hahucare.com/app/auth/google/callback`

---

## ❌ Common Issues & Solutions

### **Issue 1: "redirect_uri_mismatch" error**

**Cause:** Redirect URI in `.env` doesn't match Google Console

**Solution:**
1. Check Google Console has: `https://hahucare.com/app/auth/google/callback`
2. Check `.env` has: `GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback`
3. Both must match exactly (including https, no trailing slash)

### **Issue 2: Still getting "missing client identifier"**

**Cause:** Cache not cleared or `.env` not saved

**Solution:**
1. Verify credentials are in production `.env` (not local)
2. Clear cache: `php artisan config:clear`
3. Restart PHP-FPM (if available)
4. Clear browser cache completely

### **Issue 3: "invalid_client" error**

**Cause:** Wrong Client ID or Secret

**Solution:**
1. Verify Client ID matches Google Console exactly
2. Verify Client Secret matches Google Console exactly
3. No extra spaces or quotes in `.env`

---

## 🎉 What This Enables

After this fix, doctors can:

✅ **Connect Google Calendar** from their profile  
✅ **Sync appointments** to Google Calendar automatically  
✅ **Create Google Meet links** for virtual appointments  
✅ **Manage calendar events** from HahuCare  
✅ **Receive calendar notifications** from Google  

---

## 📞 Support

If you still have issues after following all steps:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console (F12) for JavaScript errors
3. Verify all 5 environment variables are set correctly
4. Ensure Google Cloud Console redirect URI matches exactly

---

**Your credentials are ready to deploy!** Just follow the 3 steps above and test. 🚀
