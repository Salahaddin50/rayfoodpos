# Push Notification System Analysis

## Overview
Your application uses **Firebase Cloud Messaging (FCM)** for push notifications. The system supports both **web** and **mobile** device tokens.

---

## Architecture Flow

### 1. **Frontend Setup (Web Browser)**
**Location:** `resources/js/components/layouts/backend/BackendNavbarComponent.vue`

**How it works:**
- After 5 seconds delay, Firebase is initialized with settings from database
- Requests browser notification permission
- Gets FCM token using VAPID key
- Sends token to backend: `POST /api/frontend/device-token/web`
- Token is stored in `users.web_token` field
- Listens for incoming messages using `onMessage()` handler

**Key Code:**
```javascript
// Lines 274-317
- Initializes Firebase with settings
- Requests notification permission
- Gets FCM token
- Sends token to backend
- Handles incoming messages
```

### 2. **Service Worker (Background Notifications)**
**Location:** `public/firebase-messaging-sw.js`

**Purpose:** Handles notifications when browser tab is closed/background

**How it works:**
- Registers as service worker
- Listens for background messages
- Displays notification even when app is not active
- Uses Firebase SDK v8.10.1

**Note:** ⚠️ **HARDCODED CONFIG** - Contains hardcoded Firebase credentials that should be moved to settings

### 3. **Backend Token Storage**
**Location:** 
- Controller: `app/Http/Controllers/Frontend/TokenStoreController.php`
- Service: `app/Services/TokenStoreService.php`

**How it works:**
- Receives token from frontend
- Stores in `users.web_token` (for web) or `users.device_token` (for mobile)
- Requires authentication (`auth:sanctum` middleware)

### 4. **Notification Sending**
**Location:** `app/Services/FirebaseService.php`

**How it works:**
- Uses Firebase Admin SDK (v1 API)
- Requires service account JSON file
- Gets OAuth2 access token
- Sends to FCM API endpoint
- Supports both web and mobile tokens

**Key Features:**
- Sends to multiple tokens in a loop
- Includes notification title, body, image
- Sets urgency to "high" for web push
- Uses topic name for filtering

### 5. **Order Notification Trigger**
**Location:** `app/Services/OrderGotPushNotificationBuilder.php`

**When triggered:** When a new order is created

**Who receives:**
- All Admins (branch_id = 0) - Web & Mobile
- Branch Admins (matching order branch) - Web & Mobile  
- Branch Managers (matching order branch) - Web & Mobile

**Flow:**
1. Order created → `OrderService.php` dispatches `SendOrderGotPush` event
2. Event triggers `SendOrderGotPushNotification` listener
3. Listener calls `OrderGotPushNotificationBuilder`
4. Builder collects all relevant tokens
5. Calls `FirebaseService->sendNotification()`

---

## Configuration Requirements

### Firebase Settings (Stored in Database)
Required settings in `notification` group:
- `notification_fcm_api_key`
- `notification_fcm_auth_domain`
- `notification_fcm_project_id`
- `notification_fcm_storage_bucket`
- `notification_fcm_messaging_sender_id`
- `notification_fcm_app_id`
- `notification_fcm_measurement_id`
- `notification_fcm_public_vapid_key` (for web)
- `notification_fcm_json_file` (service account file)

### Service Account File
- Must be uploaded via admin panel
- Stored in `storage/app/public/`
- Used for OAuth2 authentication with Firebase

---

## Issues & Recommendations

### 🔴 Critical Issues

1. **Hardcoded Firebase Config in Service Worker**
   - **File:** `public/firebase-messaging-sw.js`
   - **Problem:** Contains hardcoded Firebase credentials
   - **Risk:** Cannot change Firebase project without code changes
   - **Solution:** Generate service worker dynamically or use environment variables

2. **No Token Refresh Handling**
   - **Problem:** FCM tokens can expire/invalidate
   - **Risk:** Users stop receiving notifications
   - **Solution:** Implement token refresh listener and update backend

3. **No Error Handling for Failed Sends**
   - **File:** `app/Services/FirebaseService.php` line 61
   - **Problem:** Errors are silently caught and ignored
   - **Risk:** No visibility into notification failures
   - **Solution:** Log errors and implement retry mechanism

### ⚠️ Important Issues

4. **No Token Validation**
   - **Problem:** Invalid/expired tokens are not cleaned up
   - **Risk:** API calls fail for invalid tokens
   - **Solution:** Validate tokens and remove invalid ones from database

5. **Synchronous Token Collection**
   - **File:** `OrderGotPushNotificationBuilder.php`
   - **Problem:** Multiple database queries executed sequentially
   - **Risk:** Performance issues with many users
   - **Solution:** Use single query with OR conditions

6. **No Notification Preferences**
   - **Problem:** Users cannot opt-out of specific notification types
   - **Solution:** Add user notification preferences table

7. **Service Worker Version**
   - **Problem:** Using Firebase SDK v8.10.1 (older version)
   - **Solution:** Consider upgrading to v9+ (modular SDK)

### 💡 Recommendations

8. **Add Notification History**
   - Track sent notifications in database
   - Useful for debugging and analytics

9. **Batch Token Sending**
   - FCM supports sending to multiple tokens in one request
   - Currently sending one-by-one (inefficient)

10. **Add Notification Click Handling**
    - Track when users click notifications
    - Redirect to specific order/page

11. **Test Notification Feature**
    - Add admin panel button to send test notification
    - Useful for testing configuration

12. **Token Expiration Management**
    - Implement periodic cleanup of expired tokens
    - Check token validity before sending

---

## Testing Checklist

- [ ] Verify Firebase config is loaded from database
- [ ] Test notification permission request
- [ ] Verify token is saved to database
- [ ] Test foreground notification (app open)
- [ ] Test background notification (app closed)
- [ ] Test notification when service worker is active
- [ ] Verify notifications work for all user roles
- [ ] Test with multiple branches
- [ ] Verify notification appears for new orders
- [ ] Check notification sound/audio plays
- [ ] Verify notification click redirects correctly

---

## Security Considerations

1. **Service Account File**
   - Keep secure, never commit to git
   - Restrict file permissions
   - Rotate keys periodically

2. **VAPID Key**
   - Keep private key secure
   - Public key can be exposed (used in frontend)

3. **Token Storage**
   - Tokens are user-specific
   - Ensure proper authentication before token storage

---

## Performance Optimization

1. **Current:** Sends notifications one-by-one in loop
2. **Better:** Use FCM batch API or send to topic
3. **Best:** Use FCM topic subscriptions for role-based notifications

---

## Monitoring & Debugging

**Logs to Check:**
- `storage/logs/laravel.log` - Backend errors
- Browser Console - Frontend errors
- Firebase Console - Delivery reports
- Service Worker Console - Background message handling

**Common Issues:**
- Token not saved → Check authentication
- Permission denied → User blocked notifications
- No notification → Check service worker registration
- Invalid token → Token expired, needs refresh

---

## Next Steps

1. **Immediate:** Fix hardcoded service worker config
2. **Short-term:** Add token refresh handling
3. **Medium-term:** Implement error logging and retry mechanism
4. **Long-term:** Add notification preferences and analytics
