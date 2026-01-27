# 🌐 Web Push Notification Setup Guide (Browser Notifications)

**Complete guide to enable browser push notifications for Admin, Doctors, and all web users.**

---

## 🎯 What You'll Get

After this setup, **all users accessing your website** will receive push notifications:

✅ **Admin users** - Get notified about new appointments, payments, etc.  
✅ **Doctors** - Get notified about patient appointments, check-ins  
✅ **Staff** - Get notified about their relevant events  
✅ **Patients** - Get notified when using the web portal  

**Works on:**
- 🖥️ Desktop browsers (Chrome, Firefox, Edge, Safari)
- 📱 Mobile browsers (Chrome, Safari on iOS/Android)
- ✅ Even when browser tab is closed (if browser is running)

---

## 📋 Prerequisites

- ✅ Your HahuCare website is already set up
- ✅ You have admin access to the website
- ✅ You can access the database (phpMyAdmin)

---

## 🚀 Step-by-Step Setup

### **Step 1: Create FREE OneSignal Account**

OneSignal is a free service for push notifications. You already have the code integrated!

1. **Go to OneSignal:**
   ```
   https://onesignal.com/
   ```

2. **Click "Sign Up"** (top right)
   - Use your email
   - Create a password
   - It's 100% FREE (no credit card needed)

3. **Verify your email** (check inbox)

---

### **Step 2: Create a New App in OneSignal**

1. **After login, click "New App/Website"**

2. **Choose Platform:**
   - Select **"Web Push"**
   - Click "Next"

3. **Configure Web Push:**
   
   **Site Setup:**
   - **Site Name:** `HahuCare`
   - **Site URL:** `https://your-actual-domain.com` (your website URL)
   - **Auto Resubscribe:** ✅ Enable (recommended)
   - **Default Notification Icon:** Upload your logo (optional)

4. **Choose Integration:**
   - Select **"Typical Site"** (not WordPress)
   - Click "Next"

5. **Permission Prompt:**
   - **Prompt Type:** Choose "Slide Prompt" (recommended)
   - **Prompt Message:** "Get notified about appointments, payments, and updates"
   - Click "Save"

6. **Complete Setup:**
   - Click "Done"
   - You'll see your dashboard

---

### **Step 3: Get Your OneSignal App ID**

1. **In OneSignal Dashboard:**
   - Click on **"Settings"** (left sidebar)
   - Click on **"Keys & IDs"**

2. **Copy Your App ID:**
   - You'll see: **OneSignal App ID**
   - It looks like: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
   - **Copy this ID** (you'll need it in the next step)

   **Example:**
   ```
   12345678-abcd-1234-efgh-123456789012
   ```

---

### **Step 4: Configure OneSignal in Your Database**

**Using phpMyAdmin:**

1. **Open phpMyAdmin** in cPanel

2. **Select your HahuCare database**

3. **Click "SQL" tab**

4. **Run this query** (replace `YOUR_APP_ID` with the ID you copied):

```sql
-- Set your OneSignal App ID
UPDATE settings 
SET val = 'YOUR_APP_ID_HERE' 
WHERE name = 'onesignal_app_id';

-- Enable OneSignal web push notifications
UPDATE settings 
SET val = '1' 
WHERE name = 'is_one_signal_notification';
```

**Example with real App ID:**
```sql
UPDATE settings 
SET val = '12345678-abcd-1234-efgh-123456789012' 
WHERE name = 'onesignal_app_id';

UPDATE settings 
SET val = '1' 
WHERE name = 'is_one_signal_notification';
```

5. **Click "Go"** to execute

6. **Verify it worked:**
```sql
SELECT name, val 
FROM settings 
WHERE name IN ('onesignal_app_id', 'is_one_signal_notification');
```

**Expected Result:**
```
onesignal_app_id: 12345678-abcd-1234-efgh-123456789012
is_one_signal_notification: 1
```

---

### **Step 5: Clear Laravel Cache**

**Important:** Clear cache so Laravel picks up the new settings.

**Option A - Using SSH/Terminal:**
```bash
cd public_html
php artisan config:clear
php artisan cache:clear
```

**Option B - Using Web Browser (No SSH):**

Create file: `clear_cache.php` in `public_html/`

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
echo "<h2>Done! Delete this file now.</h2>";
```

Access: `https://your-domain.com/clear_cache.php`

**Delete the file after running!**

---

### **Step 6: Upload OneSignal SDK Files (Important!)**

OneSignal requires two files in your website root:

1. **In OneSignal Dashboard:**
   - Go to **Settings → Web Configuration**
   - Download these files:
     - `OneSignalSDKWorker.js`
     - `OneSignalSDKUpdaterWorker.js`

2. **Upload to Your Website:**
   - Using cPanel File Manager
   - Navigate to: `public_html/`
   - Upload both `.js` files to the root directory

**File locations should be:**
```
public_html/
├── OneSignalSDKWorker.js
├── OneSignalSDKUpdaterWorker.js
└── index.php
```

**Alternative:** OneSignal may auto-generate these. Check if they already exist.

---

### **Step 7: Test Web Push Notifications**

1. **Clear Browser Cache:**
   - Press `Ctrl + Shift + Delete` (Windows)
   - Press `Cmd + Shift + Delete` (Mac)
   - Clear "Cached images and files"

2. **Visit Your Website:**
   ```
   https://your-domain.com/app/login
   ```

3. **Login as Admin**

4. **You Should See:**
   - A notification permission prompt
   - Either from browser or OneSignal slide prompt
   - Click **"Allow"** or **"Subscribe"**

5. **Check OneSignal Dashboard:**
   - Go to OneSignal → Audience
   - You should see 1 subscriber (you!)

6. **Test Notification:**
   - In OneSignal Dashboard
   - Click **"Messages"** → **"New Push"**
   - Create a test message
   - Send to "All Users"
   - You should receive it in your browser!

---

## 🧪 Testing with Real Events

### **Test 1: Create an Appointment**

1. Login to admin panel
2. Create a new appointment
3. **You should receive a browser push notification!**

### **Test 2: Update Appointment**

1. Change appointment status
2. **You should receive a notification!**

### **Test 3: Multiple Users**

1. Have a doctor login
2. They should see the permission prompt
3. After allowing, they'll receive notifications too

---

## 📊 What Notifications Are Sent?

Web push notifications are sent for these events:

### **For Admin:**
- ✅ New appointment created
- ✅ Payment received
- ✅ Appointment cancelled
- ✅ Low medicine stock
- ✅ New user registration
- ✅ Service requests

### **For Doctors:**
- ✅ New appointment assigned to them
- ✅ Patient checked in (waiting)
- ✅ Appointment cancelled
- ✅ Payment received for their service
- ✅ Prescription requests

### **For Patients:**
- ✅ Appointment confirmed
- ✅ Appointment reminder
- ✅ Prescription ready
- ✅ Payment confirmation
- ✅ Appointment rescheduled

---

## 🔧 How It Works Technically

### **When User Visits Website:**

1. **OneSignal SDK loads** (from your settings)
2. **Asks for permission** (browser prompt)
3. **User clicks "Allow"**
4. **OneSignal generates a unique ID** (web_player_id)
5. **Saved to database** (`users.web_player_id` column)

### **When Event Occurs:**

1. **Appointment created** (for example)
2. **CommonNotification triggered**
3. **Checks notification templates** (PUSH_NOTIFICATION enabled)
4. **Sends to OneSignal API**
5. **OneSignal delivers to user's browser**
6. **User sees notification** (even if tab is closed!)

---

## 🔍 Troubleshooting

### **Problem: No permission prompt appears**

**Solutions:**
1. Clear browser cache
2. Check OneSignal App ID is correct in database
3. Check `is_one_signal_notification = 1` in database
4. Verify SDK files uploaded to root directory
5. Check browser console for errors (F12)

### **Problem: Permission prompt appears but no notifications**

**Solutions:**
1. Check OneSignal dashboard → Audience (are users subscribed?)
2. Verify notification templates have `PUSH_NOTIFICATION = '1'`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test sending from OneSignal dashboard directly

### **Problem: "Service Worker registration failed"**

**Solutions:**
1. Ensure `OneSignalSDKWorker.js` is in root directory
2. Check file permissions (644)
3. Verify HTTPS is enabled (required for web push)
4. Check browser console for exact error

### **Problem: Notifications work in OneSignal but not from app**

**Solutions:**
1. Check `users.web_player_id` is being saved
2. Verify route `backend.update-player-id` exists
3. Check CSRF token is valid
4. Look for JavaScript errors in console

---

## 🔒 Security & Privacy

### **User Privacy:**
- ✅ Users must explicitly allow notifications
- ✅ Users can unsubscribe anytime (browser settings)
- ✅ No personal data sent to OneSignal (only user ID)

### **Best Practices:**
- ✅ Don't spam users with too many notifications
- ✅ Make notifications relevant and useful
- ✅ Respect user's notification preferences
- ✅ Provide clear opt-out instructions

---

## 📱 Browser Compatibility

| Browser | Desktop | Mobile | Notes |
|---------|---------|--------|-------|
| Chrome | ✅ | ✅ | Full support |
| Firefox | ✅ | ✅ | Full support |
| Edge | ✅ | ✅ | Full support |
| Safari | ✅ | ✅ | Requires macOS 10.14+ |
| Opera | ✅ | ✅ | Full support |
| Brave | ✅ | ✅ | Full support |

---

## 💰 OneSignal Pricing

**FREE Plan Includes:**
- ✅ Unlimited push notifications
- ✅ Unlimited subscribers
- ✅ All core features
- ✅ Perfect for your needs!

**No credit card required!**

---

## 📋 Quick Checklist

- [ ] Created OneSignal account
- [ ] Created new Web Push app in OneSignal
- [ ] Copied OneSignal App ID
- [ ] Updated `onesignal_app_id` in database
- [ ] Set `is_one_signal_notification = 1` in database
- [ ] Cleared Laravel cache
- [ ] Uploaded SDK worker files to root
- [ ] Tested permission prompt appears
- [ ] Subscribed to notifications
- [ ] Received test notification
- [ ] Created real appointment and got notified

---

## 🎉 You're Done!

Once all steps are complete:

✅ **All web users** will receive browser push notifications  
✅ **Works alongside** mobile push, SMS, and email  
✅ **Automatic** for all appointment events  
✅ **FREE** forever with OneSignal  

---

## 📞 Support Resources

**OneSignal Documentation:**
- https://documentation.onesignal.com/docs/web-push-quickstart

**OneSignal Dashboard:**
- https://app.onesignal.com/

**Test Notifications:**
- OneSignal Dashboard → Messages → New Push

---

## 🔄 Complete Notification System

Your HahuCare now has **4 notification channels**:

| Channel | Platform | Status | Configuration |
|---------|----------|--------|---------------|
| **Web Push** | Browser | ✅ Ready | OneSignal App ID |
| **Mobile Push** | Mobile Apps | ✅ Ready | Firebase FCM |
| **SMS** | All Platforms | ✅ Ready | AfroMessage |
| **Email** | All Platforms | ✅ Ready | SMTP Settings |

**All channels work simultaneously!** When an appointment is created:
- 🌐 Web push → Browser notification
- 📱 Mobile push → Phone notification
- 💬 SMS → Text message
- 📧 Email → Email inbox

---

**Last Updated:** January 23, 2026  
**Project:** HahuCare  
**OneSignal:** Free Plan  
**Firebase:** hahucare-9fe67
