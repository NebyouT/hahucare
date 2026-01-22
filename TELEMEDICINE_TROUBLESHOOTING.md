# Telemedicine Troubleshooting Guide

## Problem: "Meeting link not available" Error

When clicking the video icon, you get an alert saying "Meeting link not available". This means the Google Meet link was not generated during appointment booking.

---

## Diagnostic SQL Queries

Run these queries in your database to check the setup:

### 1. Check Google Meet Settings
```sql
SELECT name, val, type 
FROM settings 
WHERE name IN ('google_meet_method', 'google_clientid', 'google_secret_key', 'is_zoom')
ORDER BY name;
```

**Expected Results:**
- `google_meet_method` = `1` (enabled)
- `google_clientid` = `your-client-id.apps.googleusercontent.com` (not empty)
- `google_secret_key` = `your-secret-key` (not empty)
- `is_zoom` = `0` (disabled, to prioritize Google Meet)

---

### 2. Check Service Video Consultancy Setting
```sql
SELECT id, name, is_video_consultancy, status 
FROM clinics_services 
WHERE is_video_consultancy = 1;
```

**Expected Results:**
- Should show services with `is_video_consultancy = 1`
- These are the services that support telemedicine

---

### 3. Check Doctor's Google OAuth Token
```sql
SELECT id, email, first_name, last_name, 
       CASE 
           WHEN google_access_token IS NOT NULL THEN 'Connected'
           ELSE 'Not Connected'
       END as google_status
FROM users 
WHERE user_type = 'doctor';
```

**Expected Results:**
- Doctors should have `google_status = 'Connected'`
- If "Not Connected", doctor needs to authorize Google Calendar access

---

### 4. Check Existing Appointments for Meet Links
```sql
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    cs.name as service_name,
    cs.is_video_consultancy,
    CASE 
        WHEN a.meet_link IS NOT NULL THEN 'Has Link'
        ELSE 'No Link'
    END as meet_link_status,
    a.meet_link,
    a.start_video_link,
    a.join_video_link
FROM appointments a
LEFT JOIN clinics_services cs ON a.service_id = cs.id
WHERE cs.is_video_consultancy = 1
ORDER BY a.id DESC
LIMIT 10;
```

**Expected Results:**
- Appointments with video consultancy should have `meet_link_status = 'Has Link'`
- If "No Link", the meet link was not generated

---

## Common Issues and Solutions

### Issue 1: Google Meet Not Enabled
**Symptom:** `google_meet_method = 0` or NULL

**Solution:**
```sql
-- Enable Google Meet
UPDATE settings SET val = '1' WHERE name = 'google_meet_method';
```

Or run the database seeder:
```bash
php artisan db:seed --class=SettingSeeder
```

---

### Issue 2: Missing Google OAuth Credentials
**Symptom:** `google_clientid` or `google_secret_key` is empty

**Solution:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create OAuth 2.0 credentials
3. Update settings table:
```sql
UPDATE settings SET val = 'YOUR_CLIENT_ID' WHERE name = 'google_clientid';
UPDATE settings SET val = 'YOUR_SECRET_KEY' WHERE name = 'google_secret_key';
```

**See:** `GOOGLE_MEET_SETUP.md` for detailed instructions

---

### Issue 3: Doctor Not Connected to Google
**Symptom:** Doctor's `google_access_token` is NULL

**Solution:**
1. Doctor logs into backend
2. Goes to Profile/Settings
3. Clicks "Connect Google Account" button
4. Authorizes Google Calendar access
5. System stores access token

**Note:** Each doctor must connect their own Google account individually.

---

### Issue 4: Service Not Configured for Video Consultancy
**Symptom:** Service has `is_video_consultancy = 0`

**Solution:**
1. Admin goes to Services management
2. Edit the service
3. Enable "Video Consultancy" checkbox
4. Save

Or via SQL:
```sql
-- Enable video consultancy for a service
UPDATE clinics_services SET is_video_consultancy = 1 WHERE id = YOUR_SERVICE_ID;
```

---

### Issue 5: Old Appointments Without Meet Links
**Symptom:** Appointments booked before Google Meet was configured don't have links

**Solution:**
Old appointments won't automatically get meet links. Options:
1. **Cancel and rebook** the appointment
2. **Manually generate** meet link (requires custom script)
3. **Use Zoom** as fallback if configured

---

## Step-by-Step Setup Checklist

### ✅ Admin Setup
- [ ] Run database seeder: `php artisan db:seed --class=SettingSeeder`
- [ ] Verify Google Meet enabled in settings table
- [ ] Add Google OAuth credentials to settings
- [ ] Enable video consultancy on services

### ✅ Doctor Setup
- [ ] Each doctor connects their Google account
- [ ] Verify `google_access_token` is stored in users table
- [ ] Test by booking a test appointment

### ✅ Service Setup
- [ ] Create or edit service
- [ ] Enable "Video Consultancy" checkbox
- [ ] Save and verify `is_video_consultancy = 1`

### ✅ Testing
- [ ] Book new appointment with video consultancy service
- [ ] Check appointment has `meet_link` in database
- [ ] Go to "My Appointments" page
- [ ] See green video icon
- [ ] Click icon - popup should open with Google Meet link

---

## Logs to Check

### Laravel Logs
```bash
# Check for errors during meet link generation
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i "google\|meet\|video"
```

**Look for:**
- "Doctor not found for Google Meet link generation"
- "Google Meet credentials not configured"
- "Doctor does not have Google access token"
- "Google access token expired"
- "Error generating Google Meet link"

---

## Quick Test Script

Create a test file to verify setup:

```php
// test-google-meet.php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;
use App\Models\User;

echo "=== Google Meet Configuration Test ===\n\n";

// Check settings
echo "1. Checking Google Meet Settings:\n";
$settings = Setting::whereIn('name', ['google_meet_method', 'google_clientid', 'google_secret_key'])
    ->pluck('val', 'name');

echo "   - google_meet_method: " . ($settings['google_meet_method'] ?? 'NOT SET') . "\n";
echo "   - google_clientid: " . (empty($settings['google_clientid']) ? 'NOT SET' : 'SET ✓') . "\n";
echo "   - google_secret_key: " . (empty($settings['google_secret_key']) ? 'NOT SET' : 'SET ✓') . "\n\n";

// Check doctors
echo "2. Checking Doctors with Google OAuth:\n";
$doctors = User::where('user_type', 'doctor')->get();
foreach ($doctors as $doctor) {
    $hasToken = !empty($doctor->google_access_token);
    echo "   - {$doctor->first_name} {$doctor->last_name} ({$doctor->email}): " 
         . ($hasToken ? 'Connected ✓' : 'NOT CONNECTED ✗') . "\n";
}

echo "\n=== Test Complete ===\n";
```

Run: `php test-google-meet.php`

---

## Contact Support

If issues persist after following this guide:
1. Check Laravel logs for specific errors
2. Verify Google Cloud Console OAuth settings
3. Ensure redirect URIs are configured correctly
4. Test with a fresh appointment booking

---

**Last Updated:** January 22, 2026
