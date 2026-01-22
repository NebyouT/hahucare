# Google OAuth Deployment Fix

## 🔴 **Problem Found**

Your `deploy.php` script was **not compiling JavaScript assets** during deployment. It only did:
- `git pull`
- `php artisan migrate`
- `php artisan optimize:clear`

This means the updated `GoogleAuth.vue` component was deployed to production, but the old compiled JavaScript (`profile-vue.min.js`) was still being served.

---

## ✅ **Fix Applied**

Updated `deploy.php` to include:
```php
npm install &&
npm run build &&
```

Now the deployment process will:
1. Pull latest code from Git
2. Install npm dependencies
3. **Compile JavaScript assets** (including GoogleAuth.vue)
4. Run migrations
5. Clear Laravel cache

---

## 🎯 **Deploy to Production Now**

### **Step 1: Commit and Push Changes**

```bash
git add .
git commit -m "Fix Google OAuth: Switch to server-side flow and update deploy script"
git push origin master
```

**Files that will be committed:**
- `resources/js/Profile/SectionPages/GoogleAuth.vue` (fixed OAuth flow)
- `app/Http/Controllers/Backend/SettingController.php` (updated callback handler)
- `deploy.php` (added asset compilation)

---

### **Step 2: Trigger Deployment**

Access your deployment URL:
```
https://hahucare.com/deploy.php?key=DEPLOY_SECRET_123
```

This will:
- Pull the latest code
- Install npm packages
- **Compile the new JavaScript with fixed OAuth flow**
- Run migrations
- Clear caches

---

### **Step 3: Verify Deployment**

Check the deployment log:
```
/home/hahucaxq/public_html/deploy.log
```

Look for successful output from `npm run build`.

---

### **Step 4: Test on Production**

1. Go to `https://hahucare.com`
2. Login as doctor
3. Go to Profile page
4. Click "Connect Google Account"
5. Should redirect to Google OAuth ✅
6. Authorize the app ✅
7. Redirect back with success message ✅

---

## 📋 **What Changed**

### **Before Deployment**
- ❌ `GoogleAuth.vue` used JavaScript SDK with `redirect_uri: "postmessage"`
- ❌ Google rejected with `invalid_client` error
- ❌ Deploy script didn't compile assets

### **After Deployment**
- ✅ `GoogleAuth.vue` uses server-side OAuth via `/auth/google`
- ✅ Uses configured redirect URI: `https://hahucare.com/app/callback`
- ✅ Deploy script compiles assets automatically
- ✅ Google OAuth works correctly

---

## 🔧 **Future Deployments**

From now on, every deployment will automatically:
1. Pull latest code
2. Install dependencies
3. **Compile JavaScript assets**
4. Run migrations
5. Clear caches

So any future Vue.js component changes will be properly compiled and deployed.

---

## 📝 **Summary of All Changes**

### **1. GoogleAuth.vue**
- Removed JavaScript SDK (`vue3-google-login`)
- Now uses server-side OAuth redirect flow
- Calls `/auth/google` endpoint
- Redirects to Google, then back to `/app/callback`

### **2. SettingController.php**
- Updated `handleGoogleCallback` to properly store tokens
- Saves access token, refresh token, expiration
- Sets `is_telmet = 1` flag
- Redirects to profile with success message

### **3. deploy.php**
- Added `npm install` to install dependencies
- Added `npm run build` to compile assets
- Ensures JavaScript changes are deployed

---

## ✅ **Expected Result**

After deployment:
1. ✅ Doctor can connect Google account
2. ✅ No more `invalid_client` error
3. ✅ Tokens stored in database
4. ✅ Can create appointments with Google Meet links
5. ✅ Patients can join video consultations

---

**Last Updated:** January 22, 2026  
**Status:** ✅ READY TO DEPLOY
