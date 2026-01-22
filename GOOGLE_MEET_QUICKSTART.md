# Google Meet Telemedicine - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### 1. Google Cloud Console (2 minutes)
1. Go to https://console.cloud.google.com/
2. Create new project: "HahuCare-Telemedicine"
3. Enable **Google Calendar API**
4. Create **OAuth 2.0 Client ID** (Web application)
5. Copy **Client ID** and **Client Secret**

### 2. Database Configuration (1 minute)

**Option A - Run Seeder:**
```bash
php artisan db:seed --class=SettingSeeder
```

**Option B - SQL Query:**
```sql
INSERT INTO settings (name, val, type, created_at, updated_at) VALUES
('google_meet_method', '1', 'integaration', NOW(), NOW()),
('google_clientid', 'YOUR_CLIENT_ID_HERE', 'google_meet_method', NOW(), NOW()),
('google_secret_key', 'YOUR_CLIENT_SECRET_HERE', 'google_meet_method', NOW(), NOW());
```

Replace `YOUR_CLIENT_ID_HERE` and `YOUR_CLIENT_SECRET_HERE` with actual values from Google Cloud Console.

### 3. Database Migration (1 minute)

Add Google access token column to users table:

```bash
php artisan make:migration add_google_access_token_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->text('google_access_token')->nullable();
    });
}
```

```bash
php artisan migrate
```

### 4. Doctor Authorization (1 minute per doctor)

Each doctor needs to connect their Google account:

1. Doctor logs in
2. Goes to Settings → Connect Google Account
3. Authorizes the application
4. Google access token is saved automatically

---

## ✅ Verification Checklist

- [ ] Google Calendar API enabled in Google Cloud Console
- [ ] OAuth 2.0 credentials created
- [ ] `google_clientid` setting configured in database
- [ ] `google_secret_key` setting configured in database
- [ ] `google_meet_method` setting = 1 (enabled)
- [ ] `google_access_token` column added to users table
- [ ] Doctor has connected their Google account
- [ ] Service has `is_video_consultancy = 1`

---

## 🧪 Test It

1. Login as patient
2. Book appointment with video consultancy service
3. Check appointment record for `meet_link` field
4. Should contain: `https://meet.google.com/xxx-xxxx-xxx`

**Verify in database:**
```sql
SELECT id, meet_link, start_video_link, join_video_link 
FROM appointments 
WHERE meet_link IS NOT NULL 
ORDER BY id DESC LIMIT 1;
```

---

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| No meet link generated | Check doctor has `google_access_token` in users table |
| "Credentials not configured" | Verify `google_clientid` and `google_secret_key` in settings |
| "Access token expired" | Doctor needs to reconnect Google account |
| Service not creating link | Check `is_video_consultancy = 1` for the service |

---

## 📝 Key Settings

| Setting Name | Value | Description |
|--------------|-------|-------------|
| `google_meet_method` | 1 | Enable Google Meet (0 = disabled) |
| `google_clientid` | Your Client ID | From Google Cloud Console |
| `google_secret_key` | Your Client Secret | From Google Cloud Console |
| `is_zoom` | 0 | Disable Zoom (if using Google Meet) |

---

## 🔗 Important URLs

- **Google Cloud Console:** https://console.cloud.google.com/
- **OAuth Consent Screen:** https://console.cloud.google.com/apis/credentials/consent
- **Credentials:** https://console.cloud.google.com/apis/credentials
- **Full Documentation:** See `GOOGLE_MEET_SETUP.md`

---

## 💡 Tips

1. **Use HTTPS in production** - Google OAuth requires secure connections
2. **Add test users** - Add doctor emails in OAuth consent screen during testing
3. **Monitor logs** - Check `storage/logs/laravel.log` for debugging
4. **Customize templates** - Edit event title/description in settings table

---

## 🎯 What Happens When Appointment is Booked?

1. ✓ System checks if service has video consultancy enabled
2. ✓ System checks if Google Meet is enabled (`google_meet_method = 1`)
3. ✓ System retrieves doctor's Google access token
4. ✓ System creates Google Calendar event with Meet link
5. ✓ Meet link is saved to appointment (`meet_link`, `start_video_link`, `join_video_link`)
6. ✓ Both doctor and patient receive the Meet link

---

**Need Help?** Check the full setup guide: `GOOGLE_MEET_SETUP.md`
