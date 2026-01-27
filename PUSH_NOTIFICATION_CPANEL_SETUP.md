# 🔔 Push Notification Setup Guide for cPanel

**Complete step-by-step guide to configure Firebase Push Notifications on your cPanel hosted HahuCare website.**

---

## 📋 Prerequisites

Before starting, make sure you have:

- ✅ Access to your cPanel hosting account
- ✅ Firebase Admin SDK service account JSON file (already downloaded)
- ✅ SSH or File Manager access to your hosting
- ✅ Database access (phpMyAdmin or similar)

---

## 🚀 Step-by-Step Setup

### **Step 1: Upload Firebase Service Account File**

#### **Option A: Using cPanel File Manager (Recommended)**

1. **Login to cPanel**
   - Go to your hosting cPanel URL
   - Enter your username and password

2. **Open File Manager**
   - Find "File Manager" in cPanel
   - Click to open

3. **Navigate to Storage Directory**
   ```
   public_html/storage/app/data/
   ```
   
   **If the `data` folder doesn't exist:**
   - Right-click in `storage/app/` folder
   - Select "Create New Folder"
   - Name it: `data`
   - Click "Create New Folder"

4. **Upload Firebase Service Account**
   - Click "Upload" button at the top
   - Select your file: `hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json`
   - Wait for upload to complete
   - Click "Go Back" to return to File Manager

5. **Verify Upload**
   - Navigate to `storage/app/data/`
   - You should see: `hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json`
   - File size should be around 2,382 bytes

#### **Option B: Using FTP/SFTP**

1. **Connect via FTP Client** (FileZilla, WinSCP, etc.)
   ```
   Host: your-domain.com
   Username: your-cpanel-username
   Password: your-cpanel-password
   Port: 21 (FTP) or 22 (SFTP)
   ```

2. **Navigate to Directory**
   ```
   /public_html/storage/app/data/
   ```

3. **Upload File**
   - Drag and drop: `hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json`
   - Ensure upload completes successfully

#### **Option C: Using SSH (If Available)**

```bash
# Connect to your server
ssh your-username@your-domain.com

# Navigate to project directory
cd public_html/storage/app

# Create data directory if it doesn't exist
mkdir -p data

# Upload file (use SCP from your local machine)
# From your local machine:
scp "C:\Users\HPENVY-17\Desktop\New folder\storage\app\data\hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json" your-username@your-domain.com:public_html/storage/app/data/
```

---

### **Step 2: Set Correct File Permissions**

**Important for security!**

1. **Using cPanel File Manager:**
   - Navigate to `storage/app/data/`
   - Right-click on the JSON file
   - Select "Change Permissions"
   - Set to: **600** (Owner: Read + Write)
   - Click "Change Permissions"

2. **Using SSH:**
   ```bash
   cd public_html/storage/app/data
   chmod 600 hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json
   ```

**Why 600?**
- Only the web server can read the file
- Prevents unauthorized access to your private keys
- Security best practice for sensitive credentials

---

### **Step 3: Configure Firebase Project ID in Database**

#### **Option A: Using phpMyAdmin (Recommended)**

1. **Open phpMyAdmin in cPanel**
   - Find "phpMyAdmin" in cPanel
   - Click to open

2. **Select Your Database**
   - Click on your HahuCare database name (left sidebar)

3. **Open Settings Table**
   - Find and click on `settings` table

4. **Check if Firebase Project ID Exists**
   - Click "Search" tab
   - In "name" field, enter: `firebase_project_id`
   - Click "Go"

5. **If Record Exists (Update It):**
   - Click "Edit" (pencil icon) on the row
   - Update the `val` field to: `hahucare-9fe67`
   - Update the `type` field to: `integration`
   - Click "Go" to save

6. **If Record Doesn't Exist (Create It):**
   - Click "Insert" tab
   - Fill in the fields:
     ```
     name: firebase_project_id
     val: hahucare-9fe67
     type: integration
     created_by: 1
     updated_by: 1
     created_at: (current timestamp)
     updated_at: (current timestamp)
     ```
   - Click "Go" to save

#### **Option B: Using SQL Query**

1. **Open phpMyAdmin SQL Tab**
   - Select your database
   - Click "SQL" tab at the top

2. **Run This Query:**
   ```sql
   -- Check if record exists
   SELECT * FROM settings WHERE name = 'firebase_project_id';
   
   -- If it exists, update it:
   UPDATE settings 
   SET val = 'hahucare-9fe67', 
       type = 'integration',
       updated_at = NOW()
   WHERE name = 'firebase_project_id';
   
   -- If it doesn't exist, insert it:
   INSERT INTO settings (name, val, type, created_by, updated_by, created_at, updated_at)
   VALUES ('firebase_project_id', 'hahucare-9fe67', 'integration', 1, 1, NOW(), NOW());
   ```

3. **Verify the Record**
   ```sql
   SELECT * FROM settings WHERE name = 'firebase_project_id';
   ```
   
   **Expected Result:**
   ```
   name: firebase_project_id
   val: hahucare-9fe67
   type: integration
   ```

---

### **Step 4: Verify Push Notification Templates Are Enabled**

1. **Open phpMyAdmin SQL Tab**

2. **Check Notification Templates:**
   ```sql
   SELECT id, name, channels 
   FROM notification_templates 
   LIMIT 5;
   ```

3. **Verify PUSH_NOTIFICATION is Enabled:**
   - Look at the `channels` column
   - It should contain JSON like:
     ```json
     {
       "IS_MAIL": "0",
       "PUSH_NOTIFICATION": "1",
       "IS_SMS": "1",
       "IS_WHATSAPP": "0"
     }
     ```

4. **If PUSH_NOTIFICATION is "0", Enable It:**
   ```sql
   -- Enable push notifications for all templates
   UPDATE notification_templates 
   SET channels = JSON_SET(channels, '$.PUSH_NOTIFICATION', '1')
   WHERE JSON_EXTRACT(channels, '$.PUSH_NOTIFICATION') = '0';
   ```

5. **Verify All Templates:**
   ```sql
   SELECT 
       name,
       JSON_EXTRACT(channels, '$.PUSH_NOTIFICATION') as push_enabled
   FROM notification_templates;
   ```
   
   **All should show:** `push_enabled = "1"`

---

### **Step 5: Upload google-services.json (Optional but Recommended)**

This file is useful for reference and future mobile app updates.

1. **Using cPanel File Manager:**
   - Navigate to `public_html/` (root directory)
   - Upload `google-services.json`
   - **Important:** This file is safe to upload (contains public info)

2. **Verify Upload:**
   - File should be at: `public_html/google-services.json`

---

### **Step 6: Security - Update .gitignore**

**Important:** Prevent accidental exposure of private keys!

1. **Open .gitignore File**
   - Navigate to `public_html/`
   - Right-click `.gitignore`
   - Select "Edit"

2. **Add This Line at the End:**
   ```
   # Firebase Admin SDK service account (contains private keys)
   storage/app/data/*.json
   ```

3. **Save the File**

---

### **Step 7: Test Push Notification Configuration**

#### **Option A: Using SSH (Recommended)**

```bash
# Connect to your server
ssh your-username@your-domain.com

# Navigate to project directory
cd public_html

# Run setup verification script
php setup_firebase_push.php

# Run push notification test
php test_push_notification.php
```

#### **Option B: Using cPanel Terminal (If Available)**

1. **Open Terminal in cPanel**
   - Find "Terminal" in cPanel
   - Click to open

2. **Navigate and Test:**
   ```bash
   cd public_html
   php setup_firebase_push.php
   php test_push_notification.php
   ```

#### **Option C: Using Web Browser (Create Test Endpoint)**

If you don't have SSH access, create a temporary test file:

1. **Create Test File:**
   - In File Manager, navigate to `public_html/`
   - Create new file: `test_firebase.php`
   - Add this content:
     ```php
     <?php
     require __DIR__ . '/vendor/autoload.php';
     $app = require_once __DIR__ . '/bootstrap/app.php';
     $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
     $kernel->bootstrap();
     
     use App\Models\Setting;
     use Illuminate\Support\Facades\File;
     
     echo "<h1>Firebase Push Notification Test</h1>";
     
     // Check Project ID
     $projectId = Setting::where('name', 'firebase_project_id')->first();
     echo "<p><strong>Project ID:</strong> " . ($projectId ? $projectId->val : "NOT FOUND") . "</p>";
     
     // Check Service Account
     $files = File::glob(storage_path('app/data/*.json'));
     echo "<p><strong>Service Account:</strong> " . (count($files) > 0 ? "✅ Found" : "❌ Not Found") . "</p>";
     
     if (count($files) > 0) {
         echo "<p><strong>File:</strong> " . basename($files[0]) . "</p>";
     }
     
     echo "<h2>Status: " . ($projectId && count($files) > 0 ? "✅ READY" : "❌ INCOMPLETE") . "</h2>";
     ?>
     ```

2. **Access in Browser:**
   ```
   https://your-domain.com/test_firebase.php
   ```

3. **Expected Output:**
   ```
   Firebase Push Notification Test
   Project ID: hahucare-9fe67
   Service Account: ✅ Found
   File: hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json
   Status: ✅ READY
   ```

4. **Delete Test File After Verification:**
   - Remove `test_firebase.php` for security

---

### **Step 8: Clear Laravel Cache**

**Important:** Ensure Laravel picks up the new configuration.

#### **Using SSH/Terminal:**
```bash
cd public_html

# Clear config cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear view cache
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### **Using Web Browser (If No SSH):**

Create a temporary file `clear_cache.php`:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Artisan::call('config:clear');
echo "✅ Config cache cleared<br>";

Artisan::call('cache:clear');
echo "✅ Application cache cleared<br>";

Artisan::call('view:clear');
echo "✅ View cache cleared<br>";

echo "<h2>✅ All caches cleared!</h2>";
```

Access: `https://your-domain.com/clear_cache.php`

**Delete after use!**

---

## 🧪 Testing Push Notifications

### **Test 1: Configuration Check**

Run this SQL query in phpMyAdmin:

```sql
SELECT 
    (SELECT COUNT(*) FROM settings WHERE name = 'firebase_project_id') as has_project_id,
    (SELECT val FROM settings WHERE name = 'firebase_project_id') as project_id,
    (SELECT COUNT(*) FROM notification_templates WHERE JSON_EXTRACT(channels, '$.PUSH_NOTIFICATION') = '1') as push_enabled_templates;
```

**Expected Result:**
```
has_project_id: 1
project_id: hahucare-9fe67
push_enabled_templates: 23 (or more)
```

### **Test 2: File Verification**

Check if the service account file exists:

1. **cPanel File Manager:**
   - Navigate to: `storage/app/data/`
   - Verify file exists: `hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json`
   - Check size: ~2,382 bytes

2. **SSH:**
   ```bash
   ls -lh storage/app/data/*.json
   ```

### **Test 3: Create Real Appointment**

1. **Login to Admin Panel**
2. **Create New Appointment**
3. **Check Laravel Logs:**
   - Navigate to: `storage/logs/laravel.log`
   - Look for Firebase API responses
   - Should see HTTP 200 responses

---

## 🔒 Security Checklist

- ✅ Firebase service account file has 600 permissions
- ✅ `storage/app/data/*.json` added to .gitignore
- ✅ Test files deleted after verification
- ✅ Service account file NOT in public directory
- ✅ File is in `storage/app/data/` (not accessible via web)

---

## 📱 Mobile App Integration

After backend setup, your mobile app team needs to:

1. **Initialize Firebase in Flutter App**
2. **Get FCM Device Token**
3. **Send Token to Backend API**
4. **Subscribe to User Topics:** `user_{id}`

**Backend API Endpoint Needed:**
```php
// routes/api.php
Route::post('/save-device-token', function(Request $request) {
    $user = auth()->user();
    $user->player_id = $request->player_id;
    $user->save();
    return response()->json(['success' => true]);
})->middleware('auth:sanctum');
```

---

## 🎯 Quick Reference

### **File Locations:**
```
public_html/
├── storage/app/data/
│   └── hahucare-9fe67-firebase-adminsdk-fbsvc-1f1fc7cdd0.json  ← Service Account
├── google-services.json  ← Mobile App Config (optional)
├── setup_firebase_push.php  ← Setup Script
└── test_push_notification.php  ← Test Script
```

### **Database Settings:**
```sql
Table: settings
Row: name = 'firebase_project_id'
     val = 'hahucare-9fe67'
     type = 'integration'
```

### **Notification Templates:**
```sql
Table: notification_templates
All rows: channels->PUSH_NOTIFICATION = '1'
```

---

## ❓ Troubleshooting

### **Problem: "Service account not found"**
**Solution:**
- Verify file is in `storage/app/data/`
- Check file name matches exactly
- Ensure file permissions are correct (600)

### **Problem: "Project ID not found"**
**Solution:**
- Run SQL query to insert/update `firebase_project_id`
- Clear config cache: `php artisan config:clear`

### **Problem: "Push notifications not sending"**
**Solution:**
- Check notification templates have `PUSH_NOTIFICATION = '1'`
- Verify users have `player_id` (device tokens)
- Check Laravel logs: `storage/logs/laravel.log`

### **Problem: "Permission denied"**
**Solution:**
- Set file permissions to 600
- Ensure web server can read the file
- Check directory permissions (755 for directories)

---

## ✅ Final Verification

Run this checklist:

- [ ] Firebase service account uploaded to `storage/app/data/`
- [ ] File permissions set to 600
- [ ] `firebase_project_id` in database = `hahucare-9fe67`
- [ ] Push notification templates enabled (PUSH_NOTIFICATION = '1')
- [ ] `.gitignore` updated with `storage/app/data/*.json`
- [ ] Laravel cache cleared
- [ ] Test script shows "✅ READY"
- [ ] Test notification sent successfully (HTTP 200)

---

## 🎉 You're Done!

Your push notification system is now configured on cPanel!

**What happens next:**
1. ✅ Backend automatically sends push notifications for appointments
2. 📱 Mobile apps receive notifications (once they send device tokens)
3. 📊 Monitor in Firebase Console for delivery stats
4. 📝 Check Laravel logs for debugging

**Support:**
- Laravel Logs: `storage/logs/laravel.log`
- Firebase Console: https://console.firebase.google.com/project/hahucare-9fe67
- Test Script: `php test_push_notification.php`

---

**Last Updated:** January 23, 2026
**Project:** HahuCare
**Firebase Project:** hahucare-9fe67
