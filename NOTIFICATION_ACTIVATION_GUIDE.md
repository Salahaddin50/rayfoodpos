# 🔔 Push Notification Activation Guide

## Overview
Your app uses **Firebase Cloud Messaging (FCM)** for push notifications. This guide shows you how to activate it.

---

## What You Need

### 1. Firebase Account (Free)
- Go to: https://console.firebase.google.com
- Create account if you don't have one

### 2. Files You'll Need to Download
- Firebase Web Config (JSON with API keys)
- Service Account JSON file

---

## Step-by-Step Activation

### Step 1: Create Firebase Project

1. Go to https://console.firebase.google.com
2. Click **"Add project"** or **"Create a project"**
3. Enter project name: `rayyanscorner-notifications` (or any name)
4. Disable Google Analytics (not needed for notifications)
5. Click **"Create project"**

### Step 2: Add Web App to Firebase

1. In your Firebase project, click the **web icon** (`</>`)
2. Register app:
   - **App nickname**: `RayyanCorner Admin`
   - Check **"Also set up Firebase Hosting"** → NO
3. Copy the Firebase config (looks like this):

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyBLVn...",
  authDomain: "rayyanscorner.firebaseapp.com",
  projectId: "rayyanscorner-123",
  storageBucket: "rayyanscorner-123.appspot.com",
  messagingSenderId: "1068326850326",
  appId: "1:1068326850326:web:abc123",
  measurementId: "G-ABC123"
};
```

**Save this — you'll need it in Step 5!**

### Step 3: Enable Cloud Messaging

1. In Firebase Console, go to **Project Settings** (gear icon)
2. Go to **Cloud Messaging** tab
3. Scroll down to **"Web Push certificates"**
4. Click **"Generate key pair"**
5. Copy the **VAPID key** (starts with `B...`)

**Save this VAPID key — you'll need it in Step 5!**

### Step 4: Create Service Account

1. Still in **Project Settings**, go to **"Service accounts"** tab
2. Click **"Generate new private key"**
3. Click **"Generate key"** (downloads a JSON file)
4. **Save this JSON file securely!**

Example filename: `rayyanscorner-firebase-adminsdk-abc123.json`

### Step 5: Configure in Admin Panel

1. Log in to your admin panel: `https://rayyanscorner.az/admin/login`
2. Go to **Settings → Notification**
3. Fill in these fields from your Firebase config (Step 2):

| Field in Admin Panel | Value from Firebase Config |
|---------------------|----------------------------|
| **API Key** | `apiKey` |
| **Auth Domain** | `authDomain` |
| **Project ID** | `projectId` |
| **Storage Bucket** | `storageBucket` |
| **Messaging Sender ID** | `messagingSenderId` |
| **App ID** | `appId` |
| **Measurement ID** | `measurementId` |
| **VAPID Key** | From Step 3 (starts with `B...`) |

4. **Upload Service Account JSON File**:
   - Click **"Choose File"**
   - Select the JSON file from Step 4
   - Click **"Upload"** or **"Save"**

### Step 6: Update Service Worker (Important!)

Your service worker has hardcoded config. You need to update it:

**On your server**, edit the file:

```bash
ssh root@167.71.51.100
cd /var/www/rayyanscorner
nano public/firebase-messaging-sw.js
```

Replace the hardcoded config (lines 3-11) with YOUR Firebase config from Step 2:

```javascript
let config = {
    apiKey: "YOUR_API_KEY_FROM_STEP_2",
    authDomain: "YOUR_AUTH_DOMAIN",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_STORAGE_BUCKET",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID",
    measurementId: "YOUR_MEASUREMENT_ID",
};
```

Save and exit (`Ctrl+O`, `Enter`, `Ctrl+X`).

### Step 7: Test Notifications

1. Log in to admin panel
2. Open browser console (F12 → Console tab)
3. You should see a notification permission popup
4. Click **"Allow"**
5. Check console — you should see: `Token saved successfully` or similar

### Step 8: Test with Real Order

1. Create a test order (POS, online, or table order)
2. All admins/branch managers should receive a push notification
3. Check if notification appears on:
   - Desktop browser (Chrome, Firefox, Edge)
   - Mobile browser (if logged in)

---

## Who Receives Notifications?

When a **new order is created**, notifications are sent to:

✅ **All Admins** (branch_id = 0)  
✅ **Branch Admins** (for that specific branch)  
✅ **Branch Managers** (for that specific branch)  

❌ Regular users/customers do NOT receive order notifications.

---

## Troubleshooting

### Issue: No notification permission popup
**Solution:**
- Clear browser cache and cookies
- Reload page
- Check browser settings → Notifications → Ensure site is not blocked

### Issue: Permission granted but no notifications
**Solution:**
1. Check browser console for errors
2. Verify Firebase config in admin panel is correct
3. Check `public/firebase-messaging-sw.js` has correct config
4. Verify service account JSON file uploaded successfully

### Issue: Notifications work on some browsers but not others
**Solution:**
- Some browsers (Safari, older browsers) have limited FCM support
- Best support: Chrome, Firefox, Edge (latest versions)
- Mobile: Chrome Android, Firefox Android

### Issue: "Service worker not registered"
**Solution:**
```bash
# Clear service worker cache
# In browser console:
navigator.serviceWorker.getRegistrations().then(registrations => {
  registrations.forEach(registration => registration.unregister());
});
# Then reload page
```

### Issue: Token not saved in database
**Solution:**
- Check if user is logged in
- Check `storage/logs/laravel.log` for errors
- Verify API route `/api/frontend/device-token/web` is working

---

## Verification Checklist

After setup, verify:

- [ ] Firebase project created
- [ ] Web app added to Firebase
- [ ] Cloud Messaging enabled
- [ ] VAPID key generated
- [ ] Service account JSON downloaded
- [ ] All settings filled in admin panel
- [ ] Service worker config updated with YOUR credentials
- [ ] Notification permission granted in browser
- [ ] Token saved to database (check browser console)
- [ ] Test order created and notification received

---

## Security Notes

⚠️ **IMPORTANT:**
- Never commit service account JSON to git
- Keep VAPID private key secure
- Rotate service account keys periodically
- The hardcoded config in `firebase-messaging-sw.js` should eventually be made dynamic

---

## What Happens Next?

Once configured:
1. **Every new order** triggers a push notification
2. Notifications work even when browser is closed (if service worker is active)
3. Users can click notification to open the admin panel
4. Notifications include order details (title, description)

---

## Cost

✅ **FREE** - Firebase Cloud Messaging is free for unlimited notifications

---

## Need Help?

If you get stuck:
1. Check browser console for errors (F12)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Check Firebase Console → Cloud Messaging → Reports
4. Verify all credentials are correct

---

## Summary

**3 Key Files to Configure:**
1. Admin Panel → Settings → Notification (Firebase config)
2. Admin Panel → Upload service account JSON file
3. Server → `public/firebase-messaging-sw.js` (update hardcoded config)

That's it! 🎉
