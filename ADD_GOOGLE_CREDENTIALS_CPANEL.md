# 🚀 Add Google OAuth Credentials to cPanel (Step-by-Step)

**The error is still happening because the credentials aren't on your production server yet.**

---

## 📋 Credentials to Add

Copy these 5 lines (you'll paste them into your production `.env` file):

```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

---

## 🔧 Step-by-Step: Add to Production .env

### **Step 1: Login to cPanel**

1. Go to your hosting provider's cPanel login page
2. Enter your cPanel username and password
3. Click "Login"

---

### **Step 2: Open File Manager**

1. In cPanel, find the **"Files"** section
2. Click **"File Manager"**
3. A new tab will open with your files

---

### **Step 3: Navigate to public_html**

1. In File Manager, you'll see folders on the left
2. Click on **"public_html"** folder
3. This is where your website files are

---

### **Step 4: Find and Edit .env File**

1. In the `public_html` folder, look for a file named **`.env`**
   - **Note:** Files starting with `.` are hidden by default
   - If you don't see it, click **"Settings"** (top right) → Check **"Show Hidden Files"** → Click "Save"

2. **Right-click** on the `.env` file

3. Click **"Edit"**

4. A popup may ask about encoding - click **"Edit"** again

5. The file will open in a text editor

---

### **Step 5: Add Google Credentials**

1. **Scroll to the bottom** of the `.env` file

2. **Add a blank line** at the end

3. **Copy and paste** these 5 lines:

```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

4. **Make sure:**
   - No extra spaces before or after the `=` sign
   - No quotes around the values
   - Each line is on its own line

5. Click **"Save Changes"** (top right)

6. Click **"Close"** to exit the editor

---

### **Step 6: Clear Cache**

**You MUST clear the cache for Laravel to pick up the new credentials.**

**Option A - If you have SSH access:**

1. Open **Terminal** in cPanel (under "Advanced" section)
2. Run these commands:
```bash
cd public_html
php artisan config:clear
php artisan cache:clear
```

**Option B - If you DON'T have SSH (use this method):**

1. In File Manager, go back to `public_html` folder

2. Click **"+ File"** (top left) to create a new file

3. Name it: `clear_cache.php`

4. Click **"Create New File"**

5. **Right-click** on `clear_cache.php` → **"Edit"**

6. **Paste this code:**

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

echo "<h2 style='color: green;'>✅ Cache Cleared Successfully!</h2>";
echo "<p><strong style='color: red;'>IMPORTANT: Delete this file now for security!</strong></p>";
echo "<p>Go back to File Manager and delete clear_cache.php</p>";
```

7. Click **"Save Changes"** → **"Close"**

8. **Open your browser** and go to:
   ```
   https://hahucare.com/clear_cache.php
   ```

9. You should see: "✅ Cache Cleared Successfully!"

10. **IMPORTANT:** Go back to File Manager and **DELETE** the `clear_cache.php` file
    - Right-click on `clear_cache.php` → **"Delete"** → Confirm

---

### **Step 7: Update Google Cloud Console**

**IMPORTANT:** Make sure Google Console has the correct redirect URI.

1. Go to: **https://console.cloud.google.com/**

2. Select your project

3. Go to: **APIs & Services** → **Credentials**

4. Click on your OAuth 2.0 Client ID:
   ```
   864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
   ```

5. Under **"Authorized redirect URIs"**, make sure you have:
   ```
   https://hahucare.com/app/auth/google/callback
   ```

6. If it's not there, click **"+ ADD URI"** and add it

7. **Remove** any incorrect URIs like:
   - `https://hahucare.com/login/google/callback`
   - `https://hahucare.com/app/callback`

8. Click **"Save"**

---

## 🧪 Test It Works

1. **Clear your browser cache:**
   - Press `Ctrl + Shift + Delete` (Windows)
   - Press `Cmd + Shift + Delete` (Mac)
   - Select "All time"
   - Check "Cached images and files"
   - Click "Clear data"

2. **Close and reopen your browser**

3. **Go to your website:**
   ```
   https://hahucare.com/app/login
   ```

4. **Login with your account**

5. **Go to Profile → Google Calendar**

6. **Click "Connect Google Account"**

7. **You should be redirected to Google login page** (no error!)

8. **Login with your Google account**

9. **Allow the permissions**

10. **You should be redirected back to HahuCare** with success message ✅

---

## ✅ Verification Checklist

After completing all steps, verify:

- [ ] Logged into cPanel
- [ ] Opened File Manager
- [ ] Found `.env` file in `public_html/`
- [ ] Edited `.env` file
- [ ] Added all 5 Google credential lines
- [ ] Saved `.env` file
- [ ] Cleared cache (via SSH or clear_cache.php)
- [ ] Deleted clear_cache.php (if used)
- [ ] Updated Google Cloud Console redirect URI
- [ ] Saved Google Console changes
- [ ] Cleared browser cache
- [ ] Tested: Clicked "Connect Google Account"
- [ ] Success: Redirected to Google (no 500 error)
- [ ] Success: Connected and returned to HahuCare

---

## 🔍 How to Verify Credentials Are Set

**Check if credentials are in .env:**

1. In File Manager, open `.env` file again
2. Scroll to the bottom
3. You should see the 5 Google lines you added
4. If not there, add them again and save

**Check if cache is cleared:**

1. Create a test file: `check_config.php` in `public_html/`
2. Add this code:
```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h2>Google OAuth Configuration Check</h2>";
echo "GOOGLE_CLIENT_ID: " . (env('GOOGLE_CLIENT_ID') ? '✅ SET' : '❌ NOT SET') . "<br>";
echo "GOOGLE_CLIENT_SECRET: " . (env('GOOGLE_CLIENT_SECRET') ? '✅ SET' : '❌ NOT SET') . "<br>";
echo "GOOGLE_REDIRECT: " . (env('GOOGLE_REDIRECT') ?: '❌ NOT SET') . "<br>";
echo "<p><strong>Delete this file after checking!</strong></p>";
```
3. Visit: `https://hahucare.com/check_config.php`
4. Should show all as "✅ SET"
5. Delete the file after checking

---

## ❌ Troubleshooting

### **Still getting "missing client identifier" error?**

**Possible causes:**

1. **Credentials not saved in .env**
   - Solution: Open `.env` again, verify the 5 lines are there

2. **Cache not cleared**
   - Solution: Run clear_cache.php again or use SSH commands

3. **Wrong .env file edited**
   - Solution: Make sure you edited the `.env` in `public_html/`, not any other folder

4. **Extra spaces or quotes**
   - Solution: Make sure no spaces around `=` and no quotes around values

### **Getting "redirect_uri_mismatch" error?**

**Cause:** Google Console redirect URI doesn't match

**Solution:**
- Google Console must have: `https://hahucare.com/app/auth/google/callback`
- `.env` must have: `GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback`
- Both must match exactly

---

## 🎉 Success!

Once you complete all steps and test successfully, you'll be able to:

✅ Connect Google Calendar  
✅ Sync appointments to Google Calendar  
✅ Create Google Meet links  
✅ Manage calendar events from HahuCare  

---

**The key is making sure the credentials are in the production `.env` file and the cache is cleared!** 🚀
