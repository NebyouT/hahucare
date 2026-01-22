# Google Meet Telemedicine Service Setup Guide

## Overview
This guide will help you configure Google Meet integration for telemedicine appointments in your HahuCare/KiviCare healthcare management system.

---

## Prerequisites

1. **Google Cloud Account** - You need a Google Cloud Platform account
2. **Google Workspace** (optional but recommended) - For better Google Meet features
3. **Admin/Vendor Access** - You need admin privileges to configure settings
4. **Doctor Google Account** - Each doctor needs a Google account to generate Meet links

---

## Part 1: Google Cloud Console Setup

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a project"** → **"New Project"**
3. Enter project name: `HahuCare-Telemedicine` (or your preferred name)
4. Click **"Create"**

### Step 2: Enable Google Calendar API

1. In your project, go to **"APIs & Services"** → **"Library"**
2. Search for **"Google Calendar API"**
3. Click on it and press **"Enable"**

### Step 3: Configure OAuth Consent Screen

1. Go to **"APIs & Services"** → **"OAuth consent screen"**
2. Select **"External"** (or "Internal" if you have Google Workspace)
3. Click **"Create"**

**Fill in the required fields:**
- **App name:** HahuCare Telemedicine
- **User support email:** Your email
- **Developer contact email:** Your email
- **App logo:** (Optional) Upload your clinic logo
- **Application home page:** Your website URL
- **Authorized domains:** Add your domain (e.g., `hahucare.com`)

4. Click **"Save and Continue"**

### Step 4: Add Scopes

1. Click **"Add or Remove Scopes"**
2. Add the following scope:
   - `https://www.googleapis.com/auth/calendar.events`
3. Click **"Update"** → **"Save and Continue"**

### Step 5: Add Test Users (if using External)

1. Click **"Add Users"**
2. Add email addresses of doctors who will use Google Meet
3. Click **"Save and Continue"**

### Step 6: Create OAuth 2.0 Credentials

1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"Create Credentials"** → **"OAuth client ID"**
3. Select **"Web application"**
4. **Name:** HahuCare Web Client

**Configure Authorized redirect URIs:**
```
https://yourdomain.com/auth/google/callback
http://localhost:8000/auth/google/callback (for testing)
```

5. Click **"Create"**
6. **IMPORTANT:** Copy the **Client ID** and **Client Secret** - you'll need these!

---

## Part 2: Database Configuration

### Option A: Using Database Seeder (Fresh Installation)

1. Open terminal in your project directory
2. Run the seeder:
```bash
php artisan db:seed --class=SettingSeeder
```

This will create the following settings:
- `google_meet_method` = 1 (enabled)
- `google_clientid` = (empty - you need to fill this)
- `google_secret_key` = (empty - you need to fill this)
- `google_meet_event_title` = Template for event titles
- `google_meet_content` = Template for event descriptions

### Option B: Manual Database Entry (Existing Installation)

Run these SQL queries in your database:

```sql
-- Enable Google Meet
INSERT INTO settings (name, val, type, created_at, updated_at) 
VALUES ('google_meet_method', '1', 'integaration', NOW(), NOW());

-- Add Client ID (replace YOUR_CLIENT_ID with actual value)
INSERT INTO settings (name, val, type, created_at, updated_at) 
VALUES ('google_clientid', 'YOUR_CLIENT_ID', 'google_meet_method', NOW(), NOW());

-- Add Client Secret (replace YOUR_CLIENT_SECRET with actual value)
INSERT INTO settings (name, val, type, created_at, updated_at) 
VALUES ('google_secret_key', 'YOUR_CLIENT_SECRET', 'google_meet_method', NOW(), NOW());

-- Add Event Title Template
INSERT INTO settings (name, val, type, created_at, updated_at) 
VALUES ('google_meet_event_title', 'Medical Appointment - {{service_name}}', 'google_meet_method', NOW(), NOW());

-- Add Event Description Template
INSERT INTO settings (name, val, type, created_at, updated_at) 
VALUES ('google_meet_content', 'You have an appointment with {{doctor_name}} at {{clinic_name}} on {{appointment_date}} at {{appointment_time}} for {{service_name}}.', 'google_meet_method', NOW(), NOW());

-- Disable Zoom (optional)
UPDATE settings SET val = '0' WHERE name = 'is_zoom';
```

### Option C: Using Admin Panel

1. Login as **Admin**
2. Go to **Settings** → **Integrations** → **Telemed Service**
3. Enable **Google Meet**
4. Enter your **Google Client ID**
5. Enter your **Google Client Secret**
6. Customize event templates (optional)
7. Click **Save**

---

## Part 3: Doctor Google Account Connection

Each doctor must connect their Google account to generate Meet links.

### Step 1: Doctor Authorization Flow

**Backend Implementation (Already included in the code):**

The system needs a route for doctors to authorize their Google account. Add this route if not present:

```php
// In routes/web.php or routes/api.php
Route::get('/auth/google/connect', [DoctorController::class, 'connectGoogle'])->middleware('auth');
Route::get('/auth/google/callback', [DoctorController::class, 'handleGoogleCallback'])->middleware('auth');
```

### Step 2: Create Doctor Authorization Controller Methods

Create these methods in your Doctor controller:

```php
use Google\Client as GoogleClient;

public function connectGoogle(Request $request)
{
    $settings = Setting::whereIn('name', ['google_clientid', 'google_secret_key'])
        ->pluck('val', 'name');
    
    $client = new GoogleClient([
        'client_id' => $settings['google_clientid'],
        'client_secret' => $settings['google_secret_key'],
        'redirect_uri' => route('google.callback'),
        'access_type' => 'offline',
        'prompt' => 'consent',
        'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
    ]);
    
    $authUrl = $client->createAuthUrl();
    return redirect($authUrl);
}

public function handleGoogleCallback(Request $request)
{
    $code = $request->get('code');
    
    $settings = Setting::whereIn('name', ['google_clientid', 'google_secret_key'])
        ->pluck('val', 'name');
    
    $client = new GoogleClient([
        'client_id' => $settings['google_clientid'],
        'client_secret' => $settings['google_secret_key'],
        'redirect_uri' => route('google.callback'),
    ]);
    
    $token = $client->fetchAccessTokenWithAuthCode($code);
    
    if (isset($token['error'])) {
        return redirect()->back()->with('error', 'Failed to connect Google account');
    }
    
    // Save token to doctor's record
    auth()->user()->update([
        'google_access_token' => json_encode($token)
    ]);
    
    return redirect()->route('doctor.dashboard')->with('success', 'Google account connected successfully');
}
```

### Step 3: Add Google Access Token Column to Users Table

Run this migration:

```bash
php artisan make:migration add_google_access_token_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->text('google_access_token')->nullable()->after('remember_token');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('google_access_token');
    });
}
```

Run the migration:
```bash
php artisan migrate
```

---

## Part 4: Enable Telemedicine for Services

### Step 1: Configure Clinic Services

1. Login as **Vendor** or **Admin**
2. Go to **Clinics** → **Services**
3. Edit a service
4. Enable **"Video Consultancy"** checkbox (`is_video_consultancy = 1`)
5. Save the service

### Step 2: Verify Service Configuration

Check in database:
```sql
SELECT id, name, is_video_consultancy FROM clinics_services WHERE is_video_consultancy = 1;
```

---

## Part 5: Testing the Integration

### Test Checklist:

1. **✓ Google Cloud Project Created**
2. **✓ Google Calendar API Enabled**
3. **✓ OAuth Consent Screen Configured**
4. **✓ OAuth Credentials Created**
5. **✓ Database Settings Updated**
6. **✓ Doctor Connected Google Account**
7. **✓ Service Marked as Video Consultancy**

### Test Appointment Booking:

1. Login as **Patient**
2. Book an appointment with a service that has video consultancy enabled
3. Select a doctor who has connected their Google account
4. Complete the booking
5. Check the appointment details - you should see:
   - `meet_link` field populated with Google Meet URL
   - `start_video_link` (for doctor)
   - `join_video_link` (for patient)

### Verify in Database:

```sql
SELECT id, user_id, doctor_id, meet_link, start_video_link, join_video_link 
FROM appointments 
WHERE meet_link IS NOT NULL 
ORDER BY id DESC 
LIMIT 5;
```

### Check Logs:

Monitor Laravel logs for any errors:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- ✓ "Google Meet link generated successfully"
- ✗ "Doctor not found for Google Meet link generation"
- ✗ "Google Meet credentials not configured"
- ✗ "Doctor does not have Google access token"

---

## Part 6: Troubleshooting

### Issue 1: "Google Meet credentials not configured"

**Solution:**
- Verify `google_clientid` and `google_secret_key` are set in settings table
- Check they match your Google Cloud Console credentials

### Issue 2: "Doctor does not have Google access token"

**Solution:**
- Doctor needs to connect their Google account first
- Visit `/auth/google/connect` route
- Complete OAuth authorization flow

### Issue 3: "Access token expired"

**Solution:**
- The system automatically refreshes tokens using refresh_token
- If refresh fails, doctor needs to reconnect their account
- Ensure `access_type` is set to `'offline'` in OAuth config

### Issue 4: Meet link not generated

**Solution:**
1. Check service has `is_video_consultancy = 1`
2. Check `google_meet_method` setting is `1`
3. Verify doctor has valid Google access token
4. Check Laravel logs for specific error messages

### Issue 5: "Invalid redirect_uri"

**Solution:**
- Add your callback URL to Google Cloud Console
- Authorized redirect URIs must match exactly
- Include both production and development URLs

---

## Part 7: Environment Variables (Optional)

You can also configure via `.env` file:

```env
# Google Meet Configuration
GOOGLE_MEET_ENABLED=true
GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google/callback
```

---

## Part 8: Security Best Practices

1. **Never commit credentials to Git**
   - Add `.env` to `.gitignore` (already done)
   - Use environment variables for sensitive data

2. **Use HTTPS in production**
   - Google OAuth requires HTTPS for redirect URIs
   - Configure SSL certificate for your domain

3. **Rotate credentials periodically**
   - Generate new OAuth credentials every 6-12 months
   - Update in database/settings

4. **Limit API scopes**
   - Only request `calendar.events` scope
   - Don't request unnecessary permissions

5. **Monitor API usage**
   - Check Google Cloud Console for quota usage
   - Set up alerts for unusual activity

---

## Part 9: Available Template Variables

Customize event titles and descriptions using these variables:

- `{{appointment_date}}` - Appointment date
- `{{appointment_time}}` - Appointment time
- `{{patient_name}}` - Patient full name
- `{{doctor_name}}` - Doctor full name
- `{{clinic_name}}` - Clinic name
- `{{service_name}}` - Service name

**Example:**
```
Title: Appointment with Dr. {{doctor_name}} - {{service_name}}
Description: Dear {{patient_name}}, you have an appointment at {{clinic_name}} on {{appointment_date}} at {{appointment_time}}.
```

---

## Part 10: API Response Structure

When Google Meet link is successfully generated:

```json
{
  "status": true,
  "meet_link": "https://meet.google.com/abc-defg-hij",
  "start_url": "https://meet.google.com/abc-defg-hij",
  "join_url": "https://meet.google.com/abc-defg-hij"
}
```

The appointment record is updated with:
```php
[
    'meet_link' => 'https://meet.google.com/abc-defg-hij',
    'start_video_link' => 'https://meet.google.com/abc-defg-hij',
    'join_video_link' => 'https://meet.google.com/abc-defg-hij'
]
```

---

## Support & Additional Resources

- **Google Calendar API Documentation:** https://developers.google.com/calendar
- **Google OAuth 2.0 Guide:** https://developers.google.com/identity/protocols/oauth2
- **Laravel Google API Client:** https://github.com/googleapis/google-api-php-client

---

## Summary

Your Google Meet telemedicine service is now configured! The system will:

1. ✓ Automatically generate Google Meet links when appointments are booked
2. ✓ Create calendar events in doctor's Google Calendar
3. ✓ Send Meet links to both doctor and patient
4. ✓ Handle token refresh automatically
5. ✓ Log all activities for debugging

**Next Steps:**
1. Test with a real appointment booking
2. Train doctors on connecting their Google accounts
3. Monitor logs for any issues
4. Customize email templates to include Meet links

---

**Last Updated:** January 2026
**Version:** 1.0
