# 🔧 Troubleshoot: Google Credentials Not Being Read

**Problem:** Logs show `"redirect_uri":null,"client_id":null,"has_secret":false`

This means Laravel **cannot read** the Google credentials from your `.env` file.

---

## 🔍 Diagnostic Tool

**Upload this file to your server to diagnose the issue:**

1. Upload `verify_google_env.php` to `public_html/`
2. Visit: `https://hahucare.com/verify_google_env.php`
3. It will show you exactly what's wrong
4. **Delete the file after checking!**

---

## ❌ Common Causes & Solutions

### **Cause 1: Cache Not Cleared**

**The Problem:**
Laravel caches configuration. Even after adding credentials to `.env`, Laravel uses the old cached config.

**The Solution:**

**SSH:**
```bash
cd public_html
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

**No SSH? Create:** `force_clear_cache.php` in `public_html/`

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear all caches
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('route:clear');
Artisan::call('view:clear');

// Rebuild config cache
Artisan::call('config:cache');

echo "✅ All caches cleared and rebuilt!<br>";
echo "<strong>DELETE THIS FILE NOW!</strong>";
```

Visit: `https://hahucare.com/force_clear_cache.php`  
Then **delete it!**

---

### **Cause 2: Syntax Error in .env File**

**The Problem:**
Extra spaces, quotes, or line breaks prevent Laravel from reading the values.

**Wrong:**
```env
GOOGLE_CLIENT_ID = "864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET = "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"
```

**Correct:**
```env
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

**Rules:**
- ❌ NO spaces around `=`
- ❌ NO quotes around values
- ❌ NO extra line breaks between lines
- ✅ Each variable on its own line

**The Solution:**

1. Open `.env` in File Manager
2. Find the Google lines
3. Delete them
4. Re-add them correctly:

```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

5. Save
6. Clear cache again

---

### **Cause 3: Wrong .env File Edited**

**The Problem:**
You might have edited a different `.env` file (backup, local copy, etc.)

**The Solution:**

1. In cPanel File Manager, make sure you're in `public_html/`
2. Enable "Show Hidden Files" (Settings button, top right)
3. Look for `.env` file (starts with a dot)
4. Right-click → Edit
5. Scroll to the bottom
6. Verify the Google lines are there
7. If not, add them again

**Correct path:** `/home/hahucaxq/public_html/.env`

---

### **Cause 4: File Not Saved Properly**

**The Problem:**
Changes weren't saved or were lost.

**The Solution:**

1. Open `.env` again
2. Add the 5 Google lines
3. Click **"Save Changes"** button (top right)
4. Wait for confirmation message
5. Click **"Close"**
6. **Reopen the file** to verify changes are there

---

### **Cause 5: File Permissions**

**The Problem:**
`.env` file has wrong permissions and can't be read.

**The Solution:**

1. In File Manager, right-click `.env`
2. Click **"Change Permissions"**
3. Set to: **644**
   - Owner: Read + Write
   - Group: Read
   - World: Read
4. Click **"Change Permissions"**

---

## ✅ Step-by-Step Fix

### **Step 1: Verify .env File**

1. cPanel → File Manager
2. Navigate to `public_html/`
3. Enable "Show Hidden Files"
4. Open `.env` file
5. Scroll to bottom
6. Look for these lines:

```env
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

7. If not there or wrong, **delete and re-add them**
8. Make sure:
   - No spaces around `=`
   - No quotes
   - No extra line breaks
9. **Save Changes**
10. Close and **reopen to verify**

---

### **Step 2: Force Clear All Caches**

**SSH:**
```bash
cd /home/hahucaxq/public_html
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

**No SSH:**
Use the `force_clear_cache.php` script above.

---

### **Step 3: Verify Credentials Are Loaded**

Upload and run `verify_google_env.php` to check if credentials are now being read.

---

### **Step 4: Test Again**

1. Clear browser cache completely
2. Close and reopen browser
3. Go to: https://hahucare.com/app/login
4. Login
5. Profile → Google Calendar
6. Click "Connect Google Account"
7. Should work now! ✅

---

## 🔍 Manual Verification

**Check if .env has the credentials:**

1. SSH to server or use File Manager
2. Run: `cat /home/hahucaxq/public_html/.env | grep GOOGLE`
3. Should show:
```
GOOGLE_ACTIVE=true
GOOGLE_CLIENT_ID=864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/app/auth/google/callback
```

---

## 📋 Checklist

- [ ] Opened correct `.env` file in `public_html/`
- [ ] Verified Google credentials are in the file
- [ ] No syntax errors (spaces, quotes, line breaks)
- [ ] Saved the file properly
- [ ] Reopened file to verify changes persisted
- [ ] Cleared config cache
- [ ] Cleared application cache
- [ ] Rebuilt config cache
- [ ] Ran verification script
- [ ] Credentials showing as "SET" in verification
- [ ] Cleared browser cache
- [ ] Tested: Connect Google Account works

---

## 🆘 Still Not Working?

If you've done all the above and it still shows `null`:

1. **Check PHP version:** Must be PHP 7.4 or higher
2. **Check file encoding:** `.env` must be UTF-8, no BOM
3. **Check for hidden characters:** Copy `.env` content to notepad, remove all Google lines, re-add them fresh
4. **Restart PHP-FPM:** In cPanel → MultiPHP Manager → Restart
5. **Check Laravel logs:** `storage/logs/laravel.log` for other errors

---

**The key issue is that Laravel cannot read the `.env` file. Follow the steps above to diagnose and fix it!** 🚀
