# Google OAuth Testing Guide

This guide provides step-by-step instructions to test Google OAuth login for all three flows in your HahuCare application.

---

## Quick Start

### 1. Run the Complete Test Script
```bash
cd ~/public_html
php test_google_oauth_complete.php
```

This will:
- ✅ Check Google OAuth configuration
- ✅ Verify all routes are registered
- ✅ Check database schema
- ✅ Test controller instantiation
- ✅ Verify CSRF middleware exemptions
- ✅ Generate test URLs
- ✅ Show recent logs

### 2. Use the Web Test Interface
Open in browser: `https://hahucare.com/google_oauth_test_interface.php`

This provides:
- 🌐 Interactive testing interface
- 🔍 Configuration status
- 🧪 One-click test buttons
- 📝 API test form
- 🐛 Debug information

---

## Testing Each Flow

### Frontend Web User Login
**Purpose:** Test Google login for regular users (patients/customers)

#### Method 1: Web Interface
1. Open `https://hahucare.com/google_oauth_test_interface.php`
2. Click "Test Frontend Login"
3. Complete Google login in popup
4. Should redirect to frontend home page

#### Method 2: Manual Test
1. Go to `https://hahucare.com/login`
2. Click "Login with Google"
3. Select Google account
4. Should redirect to `https://hahucare.com/`

#### Expected Results:
- ✅ User is created with `user_type = 'user'`
- ✅ `login_type = 'google'` is set
- ✅ User gets 'user' role
- ✅ Session is created
- ✅ Redirected to frontend home

#### Check in Database:
```sql
SELECT id, email, user_type, login_type, created_at 
FROM users 
WHERE login_type = 'google' 
ORDER BY created_at DESC 
LIMIT 5;
```

---

### Backend Admin Login
**Purpose:** Test Google login for admin/doctor/vendor accounts

#### Method 1: Web Interface
1. Open `https://hahucare.com/google_oauth_test_interface.php`
2. Click "Test Backend Login"
3. Complete Google login in popup
4. Should redirect to `/admin`

#### Method 2: Manual Test
1. Go to `https://hahucare.com/admin/login`
2. Click "Login with Google"
3. Select Google account
4. Should redirect to admin dashboard

#### Expected Results:
- ✅ User is created (if new)
- ✅ Entry created in `user_providers` table
- ✅ Avatar downloaded from Google
- ✅ `UserRegistered` event fired
- ✅ Session is created
- ✅ Redirected to `/admin`

#### Check in Database:
```sql
-- Check users
SELECT id, email, user_type, login_type 
FROM users 
WHERE email = 'your-google-email@example.com';

-- Check UserProvider link
SELECT up.provider_id, up.provider, up.created_at
FROM user_providers up
JOIN users u ON up.user_id = u.id
WHERE u.email = 'your-google-email@example.com';
```

---

### Mobile App API Login
**Purpose:** Test Google login for mobile app via API

#### Method 1: Web Interface
1. Open `https://hahucare.com/google_oauth_test_interface.php`
2. Scroll to "API Test Form"
3. Fill in test data
4. Click "Test API Endpoint"

#### Method 2: Curl Command
```bash
curl -X POST https://hahucare.com/api/auth/social-login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login_type": "google",
    "email": "test.google.12345@example.com",
    "user_type": "user",
    "first_name": "Test",
    "last_name": "Google"
  }'
```

#### Expected Results:
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "id": 123,
    "email": "test.google.12345@example.com",
    "user_type": "user",
    "login_type": "google",
    "api_token": "1|abc123...",
    "first_name": "Test",
    "last_name": "Google"
  }
}
```

#### Check in Database:
```sql
SELECT id, email, user_type, login_type, created_at 
FROM users 
WHERE email = 'test.google.12345@example.com';
```

---

## Common Issues & Solutions

### Issue: "Google OAuth credentials are not configured"
**Solution:**
```bash
# Check .env file
cat .env | grep GOOGLE

# If missing, add credentials:
GOOGLE_CLIENT_ID=your_actual_client_id
GOOGLE_CLIENT_SECRET=your_actual_client_secret
GOOGLE_REDIRECT=https://hahucare.com/login/google/callback
GOOGLE_REDIRECT_URI=https://hahucare.com/auth/google/callback

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Issue: "redirect_uri_mismatch"
**Solution:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Go to APIs & Services → Credentials
4. Find your OAuth 2.0 Client ID
5. Add these Authorized redirect URIs:
   - `https://hahucare.com/login/google/callback`
   - `https://hahucare.com/auth/google/callback`

### Issue: CSRF Token Mismatch
**Solution:**
Check `app/Http/Middleware/VerifyCsrfToken.php` has:
```php
protected $except = [
    'login/*/callback',
    'auth/*/callback',
];
```

### Issue: "This account was not created using Google login"
**Cause:** User registered with email/password, trying Google login
**Solution:** User must use email/password login, or admin can update `login_type`

### Issue: No email from Google
**Cause:** Some Google accounts don't provide email
**Solution:** Use different Google account with email

---

## Testing Checklist

### Pre-Testing Checklist
- [ ] Google OAuth credentials configured in `.env`
- [ ] Redirect URIs added to Google Cloud Console
- [ ] CSRF middleware exemptions in place
- [ ] Caches cleared
- [ ] Test scripts deployed

### Frontend Testing Checklist
- [ ] Login button redirects to Google
- [ ] Google consent screen appears
- [ ] User can select account
- [ ] Callback receives authorization code
- [ ] User created/found correctly
- [ ] Session established
- [ ] Redirected to frontend home
- [ ] User has 'user' role
- [ ] login_type = 'google'

### Backend Testing Checklist
- [ ] Login button redirects to Google
- [ ] Google consent screen appears
- [ ] User can select account
- [ ] Callback receives authorization code
- [ ] User created/found correctly
- [ ] UserProvider entry created
- [ ] Avatar downloaded
- [ ] Session established
- [ ] Redirected to `/admin`

### API Testing Checklist
- [ ] API endpoint accepts POST request
- [ ] Request data validated
- [ ] User created/found correctly
- [ ] Sanctum token generated
- [ ] Response includes user data
- [ ] Response includes API token
- [ ] Token works for authenticated requests

### Post-Testing Checklist
- [ ] Check database for created users
- [ ] Verify UserProvider entries (backend)
- [ ] Check Laravel logs for errors
- [ ] Test with different user types
- [ ] Test error scenarios
- [ ] Verify token authentication works

---

## Debugging

### Check Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log | grep -i "google\|oauth"

# Recent Google/OAuth logs
grep -i "google\|oauth" storage/logs/laravel.log | tail -20

# All logs from last hour
find storage/logs -name "*.log" -mmin -60 -exec tail -20 {} \;
```

### Check Database
```sql
-- Recent Google users
SELECT id, email, user_type, login_type, created_at 
FROM users 
WHERE login_type = 'google' 
ORDER BY created_at DESC;

-- UserProvider links
SELECT u.email, up.provider, up.provider_id, up.created_at
FROM user_providers up
JOIN users u ON up.user_id = u.id
WHERE up.provider = 'google'
ORDER BY up.created_at DESC;

-- Failed login attempts (check logs)
-- Look for "Social Login Exception" in logs
```

### Check Configuration
```php
// Test script output
php test_google_oauth_complete.php

// Check service config
php artisan tinker
>>> config('services.google')
>>> env('GOOGLE_CLIENT_ID')
>>> env('GOOGLE_CLIENT_SECRET')
```

### Check Routes
```bash
# List all routes
php artisan route:list | grep -i google

# Check specific route
php artisan route:list | grep "/auth/google"
php artisan route:list | grep "/login/google"
```

---

## Performance Testing

### Load Testing API
```bash
# Install ab (Apache Bench) if needed
sudo apt-get install apache2-utils

# Test API endpoint (100 requests, 10 concurrent)
ab -n 100 -c 10 -p api_test.json -T application/json \
   https://hahucare.com/api/auth/social-login

# api_test.json content:
{
  "login_type": "google",
  "email": "load.test.123@example.com",
  "user_type": "user",
  "first_name": "Load",
  "last_name": "Test"
}
```

### Monitor Performance
```bash
# Check response times
curl -w "@curl-format.txt" -X POST \
  -H "Content-Type: application/json" \
  -d '{"login_type":"google","email":"test@example.com","user_type":"user","first_name":"Test","last_name":"User"}' \
  https://hahucare.com/api/auth/social-login

# curl-format.txt:
      time_namelookup:  %{time_namelookup}\n
         time_connect:  %{time_connect}\n
      time_appconnect:  %{time_appconnect}\n
     time_pretransfer:  %{time_pretransfer}\n
        time_redirect:  %{time_redirect}\n
   time_starttransfer:  %{time_starttransfer}\n
                      ----------\n
           time_total:  %{time_total}\n
```

---

## Security Testing

### Test OAuth Security
1. **State Parameter:** Verify state parameter is used (backend)
2. **CSRF Protection:** Ensure callback routes are exempted
3. **Token Validation:** Check Sanctum tokens are valid
4. **Session Security:** Verify session regeneration
5. **Input Validation:** Test with malicious input

### Test API Security
```bash
# Test without required fields
curl -X POST https://hahucare.com/api/auth/social-login \
  -H "Content-Type: application/json" \
  -d '{"login_type":"google"}'

# Test with invalid email
curl -X POST https://hahucare.com/api/auth/social-login \
  -H "Content-Type: application/json" \
  -d '{"login_type":"google","email":"invalid-email"}'

# Test SQL injection attempts
curl -X POST https://hahucare.com/api/auth/social-login \
  -H "Content-Type: application/json" \
  -d '{"login_type":"google","email":"test\'; DROP TABLE users; --"}'
```

---

## Mobile App Integration

### Android/iOS Testing
1. **Google Sign-In SDK:** Configure with your Client ID
2. **Token Handling:** Securely store returned token
3. **API Integration:** Use `/api/auth/social-login` endpoint
4. **Error Handling:** Handle OAuth errors gracefully
5. **Token Refresh:** Implement token refresh if needed

### Sample Mobile Code
```javascript
// React Native Example
import { GoogleSignin } from '@react-native-google-signin/google-signin';

GoogleSignin.configure({
  webClientId: 'YOUR_GOOGLE_CLIENT_ID',
});

// Sign in
const signIn = async () => {
  try {
    await GoogleSignin.hasPlayServices();
    const userInfo = await GoogleSignin.signIn();
    
    // Send to your API
    const response = await fetch('https://hahucare.com/api/auth/social-login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        login_type: 'google',
        email: userInfo.user.email,
        first_name: userInfo.user.givenName,
        last_name: userInfo.user.familyName,
        user_type: 'user'
      })
    });
    
    const data = await response.json();
    // Store token and user data
  } catch (error) {
    console.error(error);
  }
};
```

---

## Production Deployment

### Before Going Live
- [ ] Test with real Google accounts
- [ ] Verify SSL certificate is valid
- [ ] Check redirect URIs in production
- [ ] Monitor error logs
- [ ] Set up log monitoring alerts
- [ ] Test with mobile apps
- [ ] Document the flows for your team

### Monitoring
```bash
# Set up log monitoring
tail -f storage/logs/laravel.log | grep -E "(Google|OAuth|error|exception)" |

# Monitor API response times
curl -o /dev/null -s -w "%{time_total}\n" https://hahucare.com/api/auth/social-login

# Check database growth
SELECT COUNT(*) as google_users FROM users WHERE login_type = 'google';
```

---

## Conclusion

After following this guide, you should have:
- ✅ Fully tested Google OAuth for all three flows
- ✅ Verified configuration and routes
- ✅ Confirmed database operations
- ✅ Tested error scenarios
- ✅ Validated security measures
- ✅ Documented the process

If you encounter any issues:
1. Check the troubleshooting section
2. Review Laravel logs
3. Verify Google Cloud Console settings
4. Test with the provided scripts

The Google OAuth implementation should now be ready for production use! 🎉
