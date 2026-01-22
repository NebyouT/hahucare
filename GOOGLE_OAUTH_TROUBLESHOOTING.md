# Google OAuth Error 400: redirect_uri_mismatch - Complete Troubleshooting

## Current Status
Still getting "Error 400: redirect_uri_mismatch" after initial fixes.

---

## 🔍 Step-by-Step Debugging

### Step 1: Check Laravel Logs (DO THIS FIRST)

I've added logging to see the exact redirect URI being sent. 

1. Clear cache first:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Try to connect Google account again (click "Connect Google Account")

3. Check the Laravel log file:
   ```bash
   # Windows PowerShell
   Get-Content storage\logs\laravel-2026-01-22.log -Tail 50
   
   # Or open the file directly
   # storage/logs/laravel-2026-01-22.log
   ```

4. Look for these log entries:
   ```
   [YYYY-MM-DD HH:MM:SS] local.INFO: Google OAuth Configuration: {"redirect_uri":"...","client_id":"...","has_secret":true}
   [YYYY-MM-DD HH:MM:SS] local.INFO: Google OAuth Auth URL: {"url":"..."}
   ```

5. **Copy the exact `redirect_uri` value from the log** - this is what we need to add to Google Cloud Console.

---

### Step 2: Verify .env File

Open your `.env` file and check these values:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

**Common Issues:**
- ❌ Values are empty
- ❌ Extra spaces before or after the values
- ❌ Wrong domain (http instead of https)
- ❌ Missing `/app` in the redirect URI

**Correct Format:**
```env
GOOGLE_CLIENT_ID=123456789-abcdefg.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-abcdefghijklmnop
GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback
```

---

### Step 3: Verify Google Cloud Console Configuration

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select project: **project-864058558222**
3. Go to **APIs & Services** → **Credentials**
4. Click on your OAuth 2.0 Client ID
5. Check **Authorized redirect URIs** section

**It should have EXACTLY:**
```
https://hahucare.com/app/callback
```

**Common Mistakes:**
- ❌ `http://hahucare.com/app/callback` (http instead of https)
- ❌ `https://hahucare.com/callback` (missing /app)
- ❌ `https://www.hahucare.com/app/callback` (has www)
- ❌ `https://hahucare.com/app/callback/` (trailing slash)

---

### Step 4: Check for Multiple OAuth Clients

In Google Cloud Console, you might have multiple OAuth 2.0 Client IDs.

1. Go to **APIs & Services** → **Credentials**
2. Check if you have multiple "OAuth 2.0 Client IDs"
3. Make sure you're editing the CORRECT one
4. The Client ID in Google Cloud Console should match the one in your `.env` file

**To verify:**
- Copy the Client ID from Google Cloud Console
- Compare with `GOOGLE_CLIENT_ID` in your `.env` file
- They must be EXACTLY the same

---

### Step 5: Check Application Type

In Google Cloud Console, when you created the OAuth Client:

1. Go to your OAuth 2.0 Client ID settings
2. Check **Application type**
3. It should be: **Web application**

If it's "Desktop app" or "Mobile app", that could cause issues.

---

### Step 6: Verify Authorized JavaScript Origins (Optional but Recommended)

In Google Cloud Console OAuth settings, also add:

**Authorized JavaScript origins:**
```
https://hahucare.com
```

This helps with CORS and other OAuth flows.

---

## 🔧 Common Solutions

### Solution 1: The redirect_uri in .env doesn't match Google Cloud Console

**Problem:** `.env` has one URL, Google Cloud Console has another.

**Fix:**
1. Check Laravel logs to see exact redirect_uri being sent
2. Add that EXACT URL to Google Cloud Console
3. Make sure `.env` and Google Cloud Console match perfectly

---

### Solution 2: Environment variables not loading

**Problem:** Changes to `.env` file not taking effect.

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Restart your web server if using php artisan serve
```

---

### Solution 3: Using wrong OAuth Client ID

**Problem:** Multiple OAuth clients in Google Cloud Console, using wrong one.

**Fix:**
1. In Google Cloud Console, note the Client ID you're configuring
2. Copy it to your `.env` file as `GOOGLE_CLIENT_ID`
3. Make sure they match exactly

---

### Solution 4: Domain mismatch (www vs non-www)

**Problem:** Your site uses `www.hahucare.com` but redirect URI uses `hahucare.com` (or vice versa).

**Fix:**
Check your actual site URL:
```bash
# In your browser, check the URL when logged into backend
# Is it https://hahucare.com or https://www.hahucare.com?
```

Update `.env` to match:
```env
# If your site is www.hahucare.com
GOOGLE_REDIRECT_URI=https://www.hahucare.com/app/callback

# If your site is hahucare.com (no www)
GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback
```

---

### Solution 5: HTTP vs HTTPS mismatch

**Problem:** Site uses HTTPS but redirect URI configured for HTTP.

**Fix:**
Make sure both use `https://`:
```env
GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback
```

And in Google Cloud Console:
```
https://hahucare.com/app/callback
```

---

## 📋 Complete Checklist

Run through this checklist:

### .env File
- [ ] `GOOGLE_CLIENT_ID` is filled in
- [ ] `GOOGLE_CLIENT_SECRET` is filled in
- [ ] `GOOGLE_REDIRECT_URI=https://hahucare.com/app/callback`
- [ ] No extra spaces in values
- [ ] Using `https://` not `http://`
- [ ] Has `/app/callback` not just `/callback`

### Google Cloud Console
- [ ] Correct project selected (project-864058558222)
- [ ] OAuth 2.0 Client ID exists
- [ ] Application type is "Web application"
- [ ] Authorized redirect URIs includes: `https://hahucare.com/app/callback`
- [ ] Client ID matches `.env` file
- [ ] Changes saved (click Save button)

### Laravel
- [ ] Ran `php artisan config:clear`
- [ ] Ran `php artisan cache:clear`
- [ ] Checked Laravel logs for exact redirect_uri being sent
- [ ] Web server restarted if needed

### Domain
- [ ] Confirmed actual site URL (with or without www)
- [ ] Redirect URI matches actual site URL
- [ ] Using HTTPS not HTTP

---

## 🧪 Test Procedure

1. **Clear everything:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

2. **Check logs are working:**
   ```bash
   # Delete old log file to start fresh
   del storage\logs\laravel-2026-01-22.log
   ```

3. **Try to connect:**
   - Doctor logs into backend
   - Clicks "Connect Google Account"

4. **Check logs immediately:**
   ```bash
   Get-Content storage\logs\laravel-2026-01-22.log
   ```

5. **Look for:**
   ```
   Google OAuth Configuration: {"redirect_uri":"EXACT_URL_HERE",...}
   ```

6. **Copy that EXACT URL to Google Cloud Console**

---

## 🎯 What the Logs Will Tell You

After you try to connect and check the logs, you'll see something like:

```
[2026-01-22 16:30:00] local.INFO: Google OAuth Configuration: {
    "redirect_uri": "https://hahucare.com/app/callback",
    "client_id": "123456789-abcdef.apps.googleusercontent.com",
    "has_secret": true
}
```

**This tells you:**
1. ✅ The exact redirect_uri being sent to Google
2. ✅ The client_id being used
3. ✅ Whether the secret is configured

**Use this information to:**
1. Add the exact redirect_uri to Google Cloud Console
2. Verify the client_id matches in Google Cloud Console
3. Confirm all values are loading from .env

---

## 📞 Next Steps

1. **Run the test procedure above**
2. **Check the Laravel logs**
3. **Copy the exact redirect_uri from the logs**
4. **Paste it into Google Cloud Console Authorized redirect URIs**
5. **Try again**

If still not working after this, share:
- The exact redirect_uri from the logs
- Screenshot of Google Cloud Console OAuth settings
- Your current `.env` values (hide the secret)

---

**Last Updated:** January 22, 2026
