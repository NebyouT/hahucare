# Google OAuth JavaScript SDK Configuration Fix

## 🔴 Error: `invalid_client` with `redirect_uri: postmessage`

Your Vue.js component uses the **Google JavaScript SDK** (popup flow) with `redirect_uri: "postmessage"`, but your OAuth client might not be properly configured for this flow.

---

## 🎯 **Solution: Configure OAuth Client for JavaScript SDK**

### **Step 1: Go to Google Cloud Console**

1. Visit [Google Cloud Console](https://console.cloud.google.com/)
2. Select project: **project-864058558222**
3. Go to **APIs & Services** → **Credentials**

---

### **Step 2: Edit Your OAuth 2.0 Client ID**

1. Click on your OAuth client: `864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com`
2. Verify **Application type** is **Web application**
3. Under **Authorized JavaScript origins**, make sure you have:
   ```
   https://hahucare.com
   ```
4. Under **Authorized redirect URIs**, you should have:
   ```
   https://hahucare.com/app/callback
   https://hahucare.com/login/google/callback
   ```
5. Click **Save**

---

### **Step 3: Configure OAuth Consent Screen**

1. Go to **APIs & Services** → **OAuth consent screen**
2. Make sure the app is configured:
   - **App name**: hahucare (or your app name)
   - **User support email**: Your email
   - **Developer contact information**: Your email
3. Under **Scopes**, add:
   - `https://www.googleapis.com/auth/userinfo.email`
   - `https://www.googleapis.com/auth/userinfo.profile`
   - `https://www.googleapis.com/auth/calendar.events`
4. Click **Save and Continue**

---

### **Step 4: Verify API is Enabled**

1. Go to **APIs & Services** → **Library**
2. Search for **Google Calendar API**
3. Make sure it's **Enabled**
4. Also enable **People API** (for user info)

---

### **Step 5: Wait for Propagation**

Google changes can take **5-10 minutes** to propagate.

---

## 🔧 **Alternative Solution: Use Server-Side Flow Instead**

If the JavaScript SDK continues to have issues, we can modify the Vue component to use the server-side OAuth flow (which we already configured).

This would involve:
1. Redirecting to `/app/auth/google` endpoint
2. Using the `SettingController::googleId` method
3. Handling the callback at `/app/callback`

Let me know if you want to switch to this approach.

---

## 🧪 **Test After Configuration**

1. Wait 5-10 minutes after saving Google Cloud Console changes
2. Clear browser cache (Ctrl+Shift+Delete)
3. Try connecting Google account again
4. Should work now ✅

---

## 📋 **Why This Happens**

The Vue.js component (`GoogleAuth.vue`) uses:
```javascript
redirect_uri: 'postmessage'
```

This is a special redirect URI for the **Google JavaScript SDK** (popup flow). It requires:
- ✅ Authorized JavaScript origins configured
- ✅ OAuth consent screen configured
- ✅ Correct scopes enabled
- ✅ APIs enabled (Calendar API, People API)

---

## 🔍 **If Still Not Working**

Try these debugging steps:

1. **Check browser console for CORS errors**
2. **Verify JavaScript origins** in Google Cloud Console
3. **Check OAuth consent screen** is published (or in testing with your email added)
4. **Wait longer** - Google can take up to 1 hour to propagate changes
5. **Try incognito window** to avoid cache issues

---

**Last Updated:** January 22, 2026
