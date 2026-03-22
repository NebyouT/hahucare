# Google OAuth Setup Guide for HahuCare

## Problem
The Google OAuth integration for Google Meet is failing with a 500 error because the required environment variables are not configured.

## Error Message
```
Google OAuth credentials are not configured
Missing Google OAuth configuration: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT
```

## Solution

### Step 1: Get Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - Google Calendar API
   - Google+ API (for user info)
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure the OAuth consent screen if prompted
6. Choose "Web application" as the application type
7. Add authorized redirect URIs:
   ```
   https://yourdomain.com/app/auth/google/callback
   https://hahucare.com/app/auth/google/callback
   ```
8. Click "Create" and copy the Client ID and Client Secret

### Step 2: Configure Environment Variables

Add the following to your `.env` file on the server:

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT=https://hahucare.com/app/auth/google/callback
```

**Important:** Replace `your_client_id_here` and `your_client_secret_here` with the actual values from Google Cloud Console.

### Step 3: Clear Configuration Cache

After updating the `.env` file, run these commands on your server:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 4: Test the Integration

1. Log in as a doctor
2. Go to your profile page
3. Click "Connect Google Account" or the Google Meet integration button
4. You should be redirected to Google's authorization page
5. Grant the requested permissions
6. You should be redirected back to your profile with a success message

## Debugging

The system now has enhanced debugging. Check the Laravel logs for detailed information:

```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
- `Google OAuth Configuration Check` - Shows which variables are missing
- `Google OAuth Auth URL Generated Successfully` - Confirms the auth URL was created
- `Google OAuth Callback Started` - Shows callback processing
- `Google OAuth completed successfully` - Confirms successful authentication

## Common Issues

### Issue 1: "redirect_uri_mismatch" error
**Solution:** Make sure the redirect URI in your `.env` file exactly matches one of the authorized redirect URIs in Google Cloud Console.

### Issue 2: "invalid_client" error
**Solution:** Double-check that your Client ID and Client Secret are correct and haven't been regenerated.

### Issue 3: Still getting 500 error after configuration
**Solution:** 
1. Verify the `.env` file has been updated on the server (not just locally)
2. Run `php artisan config:clear` again
3. Check file permissions on the `.env` file
4. Verify the Google Cloud project has the Calendar API enabled

## Required Scopes

The integration requests the following scopes:
- `https://www.googleapis.com/auth/calendar.events` - For creating Google Meet events
- `https://www.googleapis.com/auth/userinfo.email` - For user identification

## Database Fields

The following fields are used to store Google OAuth data in the `users` table:
- `google_access_token` - JSON encoded access token
- `google_refresh_token` - Refresh token for renewing access
- `token_expires_at` - Timestamp when the token expires
- `is_telmet` - Flag indicating Google Meet is connected (1 = connected)

## Support

If you continue to experience issues after following these steps, check the detailed error logs and ensure all prerequisites are met.
