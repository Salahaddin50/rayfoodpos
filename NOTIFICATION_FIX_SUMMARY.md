# Notification Fix Summary

## Issues Identified and Fixed

### 1. **Test Notifications Work on Mobile but Not Desktop**
**Root Cause:** Service worker conflicts and inconsistent notification handling between mobile and desktop browsers.

**Fixes Applied:**
- Unregister all existing service workers before registering `firebase-messaging-sw.js` to prevent conflicts
- Use `reg.showNotification()` directly instead of postMessage for better desktop compatibility
- Added `requireInteraction: true` flag to keep notifications visible on desktop
- Added comprehensive console logging for debugging

### 2. **Notifications Not Pushed When Orders Are Placed**
**Root Cause:** Multiple potential issues in the notification flow from backend to frontend.

**Fixes Applied:**
- Enhanced logging in `OrderGotPushNotificationBuilder.php` to track notification sending
- Improved Firebase config synchronization between main app and service worker
- Fixed onMessage handler to properly show notifications on both mobile and desktop
- Added better error handling and fallback mechanisms

## Files Modified

### Frontend Files:
1. **BackendNavbarComponent.vue** - Main notification handling component
   - Improved service worker registration
   - Enhanced Firebase initialization with config sync to SW
   - Better onMessage handler with desktop support
   - Added comprehensive console logging

2. **NotificationComponent.vue** - Test notification page
   - Improved test notification to show both backend push and local notification
   - Better error messages for debugging

3. **firebase-messaging-sw.js** - Service worker for Firebase notifications
   - Added dynamic Firebase config support
   - Enhanced background message handling
   - Improved notification click navigation
   - Added extensive console logging

### Backend Files:
4. **OrderGotPushNotificationBuilder.php** - Order notification sender
   - Added detailed logging for token collection
   - Added logging for notification sending process
   - Added warning when no tokens are found

## Testing Instructions

### Step 1: Clear Everything
1. Open browser DevTools (F12)
2. Go to Application > Service Workers
3. Click "Unregister" on all service workers
4. Go to Application > Storage
5. Click "Clear site data"
6. Close all tabs of the application
7. Close browser completely

### Step 2: Fresh Start
1. Open browser and navigate to the admin panel
2. Log in with admin credentials
3. Open DevTools Console (F12 > Console)
4. Watch for Firebase initialization logs:
   ```
   Initializing Firebase messaging...
   Notification permission: granted/default/denied
   Firebase initialized successfully
   ```

### Step 3: Enable Notifications (if not already enabled)
1. Click on your profile picture (top right)
2. Click "Enable notifications" button
3. When browser asks for permission, click "Allow"
4. Watch console for registration logs:
   ```
   Starting token registration process...
   Found X existing service worker(s)
   Registering firebase-messaging-sw.js...
   Service worker ready
   Getting FCM token...
   FCM token received: ...
   Token saved successfully
   ```

### Step 4: Test Notifications
1. **Test via Settings Page:**
   - Go to Admin > Settings > Notifications
   - Click "Send test push" button
   - You should see:
     - Success message in the app
     - Browser notification popup (both local and from FCM)
   
2. **Test via Real Order:**
   - Create a test order from the table/online ordering interface
   - Watch the Laravel logs: `tail -f storage/logs/laravel.log`
   - Look for:
     ```
     OrderGotPushNotification: Starting notification process
     OrderGotPushNotification: Collected tokens
     OrderGotPushNotification: Sending notification
     FCM: Notification batch complete
     ```
   - You should receive a browser notification
   - Audio should play (if configured)
   - Order modal should appear

### Step 5: Check Console Logs

**On Desktop Browser:**
- Open DevTools Console (F12)
- Create an order and watch for:
  ```
  Foreground notification received: {notification: {...}, data: {...}}
  Topic name: new-order-found
  ```

**In Service Worker:**
- DevTools > Application > Service Workers > Click "firebase-messaging-sw.js"
- Watch the service worker console for:
  ```
  SW: Background message received: {...}
  SW: Notification clicked: {...}
  ```

### Step 6: Check Backend Logs

Check Laravel logs for detailed notification flow:
```bash
tail -f storage/logs/laravel.log | grep -i "notification\|fcm"
```

Look for:
- `OrderGotPushNotification: Starting notification process`
- `OrderGotPushNotification: Collected tokens` (should show token count > 0)
- `FCM: Notification batch complete` (should show success count > 0)

## Common Issues and Solutions

### Issue: "No device token found"
**Solution:** User needs to enable notifications first via profile dropdown.

### Issue: Notifications work on mobile but not desktop
**Possible Causes:**
1. Desktop browser has stricter notification permissions
2. Browser is in "Do Not Disturb" mode
3. System notifications are disabled
4. Browser notifications are blocked at OS level

**Solution:** Check browser settings > Notifications and ensure the site is allowed.

### Issue: Service worker registration fails
**Possible Causes:**
1. HTTPS required (except localhost)
2. Path issues with service worker file
3. CORS issues

**Solution:** Ensure app is served over HTTPS and service worker file is accessible at `/firebase-messaging-sw.js`.

### Issue: Token registration succeeds but notifications don't arrive
**Possible Causes:**
1. Firebase config mismatch between frontend and backend
2. Invalid VAPID key
3. Firebase project doesn't have Cloud Messaging enabled

**Solution:** 
1. Verify all Firebase config fields in Admin > Settings > Notifications
2. Ensure VAPID key is correct
3. Check Firebase Console > Project Settings > Cloud Messaging

### Issue: Backend shows "No tokens found"
**Possible Causes:**
1. Users haven't enabled notifications
2. Database migration for `web_tokens` column not run

**Solution:**
```bash
php artisan migrate
```
Then have all users enable notifications again via profile dropdown.

## Architecture Overview

### Notification Flow for New Orders:

1. **Order Created** → `OrderService::tableOrderStore()`
2. **Event Dispatched** → `SendOrderGotPush::dispatch(['order_id' => $orderId])`
3. **Listener Triggered** → `SendOrderGotPushNotification::handle()`
4. **Notification Builder** → `OrderGotPushNotificationBuilder::send()`
   - Collects tokens from admins and branch managers
   - Sends to Firebase Cloud Messaging
5. **FCM Delivers** → Firebase sends to all registered tokens
6. **Frontend Receives:**
   - **Background:** Service worker `onBackgroundMessage()` shows notification
   - **Foreground:** `BackendNavbarComponent` `onMessage()` shows notification + plays audio + shows modal

### Token Management:

- **Single Token:** Stored in `users.web_token` (backward compatibility)
- **Multiple Tokens:** Stored in `users.web_tokens` (JSON array)
- **Support:** A user can have notifications on multiple devices (desktop + mobile)

## Debugging Commands

### Check User Tokens:
```bash
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> echo "web_token: " . $user->web_token . "\n";
>>> echo "web_tokens: " . json_encode($user->web_tokens) . "\n";
```

### Check Firebase Settings:
```bash
php artisan tinker
>>> $settings = Dipokhalder\Settings\Facades\Settings::group('notification')->all();
>>> print_r(array_keys($settings));
```

### Manually Test FCM:
```bash
php artisan tinker
>>> $firebase = new App\Services\FirebaseService();
>>> $user = App\Models\User::find(1); // Your admin user
>>> $data = (object)['title' => 'Test', 'description' => 'Manual test notification'];
>>> $firebase->sendNotification($data, [$user->web_token], 'test');
```

## Next Steps

1. **Test thoroughly on both desktop and mobile**
2. **Monitor logs during testing**
3. **Check browser console for any errors**
4. **Verify all admins have enabled notifications**
5. **Test with real order creation**

## Notes

- All changes maintain backward compatibility
- Console logs can be removed in production if needed
- The fix supports multiple browsers: Chrome, Firefox, Edge, Safari (desktop and mobile)
- Notifications require HTTPS in production (localhost works without HTTPS for testing)
