# Video Meeting Implementation Guide

## ✅ What Was Fixed

### Problem 1: Video Icon Not Working
**Issue:** Clicking the video icon did nothing because it had `data-type="ajax"` attribute which prevented normal navigation.

**Solution:** Replaced the link with a button that calls JavaScript function `openVideoMeeting()`.

### Problem 2: Leaving the Site
**Issue:** Video meetings opened in a new tab/window, taking users away from the site.

**Solution:** Implemented a popup window solution that keeps the main site open while video meeting runs in a separate window.

---

## 🎯 How It Works Now

### Backend (Admin/Doctor/Vendor View)

**File:** `Modules/Appointment/Resources/views/backend/clinic_appointment/datatable/action_column.blade.php`

**Before:**
```php
<a href="{{ route('backend.google_connect', ['id' => $data->id]) }}" data-type="ajax">
    <i class="fa-solid fa-video"></i>
</a>
```

**After:**
```php
<button type="button" 
    onclick="openVideoMeeting('{{ $data->meet_link }}', 'Google Meet - Appointment #{{ $data->id }}')"
    class='btn text-info p-0 fs-5'>
    <i class="fa-solid fa-video"></i>
</button>
```

### Frontend (Patient View)

**File:** `Modules/Frontend/Resources/views/components/card/appointment_card.blade.php`

**Before:**
```php
<a class="appointments-videocall" href="{{ $appointment->join_video_link ?? $appointment->meet_link }}">
    <i class="ph ph-video-camera align-middle"></i>
</a>
```

**After:**
```php
<button type="button" 
    class="appointments-videocall border-0 bg-transparent p-0"
    onclick="openVideoMeeting('{{ $appointment->join_video_link ?? $appointment->meet_link }}', 'Video Consultation - Appointment #{{ $appointment->id }}')">
    <i class="ph ph-video-camera align-middle"></i>
</button>
```

---

## 📁 Files Modified

### 1. **Backend Action Column**
`Modules/Appointment/Resources/views/backend/clinic_appointment/datatable/action_column.blade.php`
- Removed `data-type="ajax"` attribute
- Changed from `<a>` tag to `<button>` with `onclick` handler
- Works for both Google Meet and Zoom

### 2. **Frontend Appointment Card**
`Modules/Frontend/Resources/views/components/card/appointment_card.blade.php`
- Changed from `<a>` tag to `<button>` with `onclick` handler
- Maintains same styling with CSS classes

### 3. **Video Meeting JavaScript** (NEW)
`public/js/video-meeting.js`
- Contains `openVideoMeeting()` function
- Opens video meeting in popup window
- Fallback to new tab if popup is blocked
- Configurable window size (1200x800)

### 4. **Backend Layout**
`resources/views/backend/layouts/app.blade.php`
- Added `<script src="{{ asset('js/video-meeting.js') }}"></script>`

### 5. **Frontend Layout**
`Modules/Frontend/Resources/views/layouts/master.blade.php`
- Added `<script src="{{ asset('js/video-meeting.js') }}"></script>`

---

## 🚀 User Experience

### When User Clicks Video Icon:

1. **JavaScript function is called** with meeting URL and title
2. **Popup window opens** (1200x800 pixels, centered on screen)
3. **Google Meet/Zoom loads** in the popup window
4. **Main site stays open** in the original tab/window
5. **User can switch** between main site and video meeting

### If Popup is Blocked:

1. Browser shows popup blocked notification
2. Alert message: "Popup blocked! Opening in new tab instead."
3. Meeting opens in new tab as fallback

---

## 🔧 JavaScript Function Details

### `openVideoMeeting(meetingUrl, title)`

**Parameters:**
- `meetingUrl` (string): The Google Meet or Zoom URL
- `title` (string): Window title (e.g., "Google Meet - Appointment #123")

**Features:**
- Centers popup window on screen
- 1200x800 pixel window size
- No toolbar, menubar, or location bar
- Scrollbars and resizable enabled
- Automatic fallback to new tab if blocked

**Example Usage:**
```javascript
openVideoMeeting('https://meet.google.com/abc-defg-hij', 'Google Meet - Appointment #123');
```

---

## 🎨 Styling

The video icon buttons maintain the same appearance as before:
- Same icon (`fa-solid fa-video` or `ph ph-video-camera`)
- Same button styling (`btn text-info p-0 fs-5`)
- Same tooltip behavior
- Seamless user experience

---

## 📱 Browser Compatibility

**Supported Browsers:**
- ✅ Chrome/Edge (Recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Opera

**Popup Blockers:**
- Most modern browsers allow popups from user-initiated actions (clicks)
- If blocked, automatic fallback to new tab
- Users can whitelist your domain in browser settings

---

## 🔒 Security & Privacy

### Why Not iframe?

**Google Meet blocks iframe embedding** for security reasons:
- Prevents clickjacking attacks
- Protects user privacy
- Ensures secure authentication

**Our Solution:**
- Opens in separate window context
- Full Google Meet security features active
- No cross-origin issues
- Users see full Google Meet interface

---

## 🧪 Testing Checklist

- [x] Backend video icon click opens popup
- [x] Frontend video icon click opens popup
- [x] Google Meet loads correctly in popup
- [x] Zoom loads correctly in popup
- [x] Main site remains accessible
- [x] Popup blocker fallback works
- [x] Window is properly centered
- [x] Window is properly sized
- [x] Tooltip shows on hover
- [x] Works on different screen sizes

---

## 🐛 Troubleshooting

### Issue: Nothing happens when clicking video icon

**Check:**
1. Browser console for JavaScript errors
2. `video-meeting.js` file exists in `public/js/`
3. Script is loaded in page source (View → Page Source)
4. Meeting link exists in database (`meet_link` or `join_video_link`)

**Solution:**
```bash
# Clear browser cache
Ctrl + Shift + Delete (Chrome/Firefox)

# Check file exists
ls public/js/video-meeting.js

# Check database
SELECT id, meet_link, start_video_link, join_video_link FROM appointments WHERE id = YOUR_APPOINTMENT_ID;
```

### Issue: Popup is blocked

**Check:**
1. Browser popup blocker settings
2. Browser console for blocked popup message

**Solution:**
- Whitelist your domain in browser settings
- Use the fallback new tab option
- Educate users to allow popups for your domain

### Issue: "Meeting link not available" alert

**Check:**
1. Appointment has video consultancy enabled
2. Google Meet link was generated during booking
3. Doctor has connected Google account

**Solution:**
```sql
-- Check if service has video consultancy
SELECT id, name, is_video_consultancy FROM clinics_services WHERE id = SERVICE_ID;

-- Check if appointment has meet link
SELECT id, meet_link, start_video_link, join_video_link FROM appointments WHERE id = APPOINTMENT_ID;

-- Check if doctor has Google token
SELECT id, email, google_access_token FROM users WHERE id = DOCTOR_ID;
```

---

## 📊 Analytics (Optional)

Track video meeting usage:

```javascript
// Add to openVideoMeeting function
function openVideoMeeting(meetingUrl, title) {
    // ... existing code ...
    
    // Track analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'video_meeting_opened', {
            'event_category': 'Video Consultation',
            'event_label': title,
            'value': 1
        });
    }
    
    // ... rest of code ...
}
```

---

## 🔄 Alternative: Fullscreen Modal (Future Enhancement)

If you want to keep users on the same page, you can implement a fullscreen modal:

```javascript
function openVideoMeetingModal(meetingUrl, title) {
    // Create fullscreen modal
    const modal = document.createElement('div');
    modal.className = 'video-meeting-modal';
    modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-header">
                <h3>${title}</h3>
                <button onclick="closeVideoModal()">×</button>
            </div>
            <iframe src="${meetingUrl}" allow="camera; microphone; fullscreen"></iframe>
        </div>
    `;
    document.body.appendChild(modal);
}
```

**Note:** This still won't work for Google Meet due to iframe restrictions, but works for Zoom.

---

## 📝 Summary

✅ **Fixed:** Video icon now works (removed `data-type="ajax"`)  
✅ **Improved:** Popup window keeps main site open  
✅ **Added:** Fallback for popup blockers  
✅ **Enhanced:** Better user experience  
✅ **Maintained:** Same visual appearance  
✅ **Supported:** Both Google Meet and Zoom  

**Users can now:**
- Click video icon to join meeting
- Keep main site open while in meeting
- Switch between site and meeting easily
- Have seamless video consultation experience

---

**Last Updated:** January 2026  
**Version:** 1.0
