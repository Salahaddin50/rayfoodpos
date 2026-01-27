# 🔔 Notification System Improvements

## What Was Fixed

### Issue #2: Token Refresh Handling ✅

**Problem:** FCM tokens expire over time, causing notifications to fail silently.

**Solution Implemented:**
- Added `onTokenRefresh` listener in frontend (`BackendNavbarComponent.vue`)
- Automatically detects when FCM token changes/expires
- Sends refreshed token to backend immediately
- Logs refresh events for monitoring

**Location:** `resources/js/components/layouts/backend/BackendNavbarComponent.vue` (lines 317-337)

**How it works:**
1. Firebase SDK detects token needs refresh
2. Listener gets new token from FCM
3. New token automatically saved to database
4. User continues receiving notifications without interruption

---

### Issue #3: Error Logging & Invalid Token Cleanup ✅

**Problem:** Notification failures were silently ignored, invalid tokens accumulated in database.

**Solution Implemented:**

#### 1. Comprehensive Error Logging
- **Success/failure tracking** - Logs count of successful vs failed notifications
- **Detailed error info** - Logs FCM error codes, messages, and status codes
- **Token validation** - Identifies invalid/expired/unregistered tokens
- **Summary logging** - Shows batch results (total, success, failures, removed tokens)

#### 2. Automatic Invalid Token Cleanup
- **Detects invalid tokens** - Recognizes FCM error codes: NOT_FOUND, INVALID_ARGUMENT, UNREGISTERED
- **Auto-removal** - Removes invalid tokens from `users` table (both `web_token` and `device_token`)
- **Prevents retry** - Stops sending to known-bad tokens immediately

#### 3. Failed Notification Tracking
- **Database table** - New `failed_notifications` table tracks all failures
- **Useful for debugging** - See which notifications failed and why
- **Analytics ready** - Can analyze failure patterns

**Location:** `app/Services/FirebaseService.php`

---

## New Database Table

### `failed_notifications`

Tracks notification failures for debugging and analytics.

**Columns:**
- `id` - Auto-increment ID
- `token` - FCM token that failed (text)
- `title` - Notification title (string, nullable)
- `body` - Notification body (text, nullable)
- `topic` - Topic name like 'new-order-found' (string, nullable)
- `error_message` - Error from FCM API (text, nullable)
- `created_at` - When failure occurred
- `updated_at` - Last update

**Indexes:**
- `topic` - Fast filtering by notification type
- `created_at` - Fast date range queries

---

## Log Examples

### Success Log
```
[2026-01-27 16:50:00] local.INFO: FCM: Notification batch complete
{
    "topic": "new-order-found",
    "total": 5,
    "success": 4,
    "failed": 1,
    "invalid_tokens_removed": 1
}
```

### Invalid Token Warning
```
[2026-01-27 16:50:00] local.WARNING: FCM: Invalid/expired token detected
{
    "token": "dGhpcyBpcyBhIHRlc3...",
    "error": "NOT_FOUND",
    "message": "Requested entity was not found",
    "topic": "new-order-found"
}
```

### Error Log
```
[2026-01-27 16:50:00] local.ERROR: FCM: Notification send failed
{
    "token": "dGhpcyBpcyBhIHRlc3...",
    "error": "QUOTA_EXCEEDED",
    "message": "Quota exceeded for quota metric 'Requests' and limit 'Requests per minute'",
    "topic": "new-order-found",
    "status_code": 429
}
```

---

## How to Deploy Changes

### On Server (Production)

1. **Pull latest code:**
```bash
cd /var/www/rayyanscorner
git pull origin master
```

2. **Run migration:**
```bash
php artisan migrate
```

3. **Rebuild frontend:**
```bash
npm install
npm run build
```

4. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

5. **Restart services:**
```bash
systemctl restart php8.2-fpm
systemctl restart nginx
```

### On Local (Development)

```bash
git pull
npm install
npm run dev
php artisan migrate
```

---

## Testing the Improvements

### Test Token Refresh

1. Open admin panel in browser
2. Open browser console (F12)
3. Wait 5-10 seconds for Firebase init
4. You should see: `Token refreshed: ...` (when token changes)

### Test Error Logging

1. Create a test order
2. Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

You should see:
- Success count
- Any failures
- Invalid tokens removed

### Test Failed Notifications Table

Query the database:
```sql
SELECT * FROM failed_notifications ORDER BY created_at DESC LIMIT 10;
```

---

## Benefits

### For Users
✅ **Reliable notifications** - Tokens stay fresh automatically  
✅ **No interruption** - Token refresh happens in background  
✅ **Better delivery rate** - Invalid tokens removed immediately  

### For Admins
✅ **Full visibility** - Know exactly when/why notifications fail  
✅ **Easy debugging** - Check logs and `failed_notifications` table  
✅ **Automatic cleanup** - No manual token maintenance needed  
✅ **Performance tracking** - Monitor success/failure rates  

---

## Monitoring

### Key Logs to Watch

**Success rate:**
```bash
grep "FCM: Notification batch complete" storage/logs/laravel.log | tail -20
```

**Invalid tokens:**
```bash
grep "FCM: Invalid/expired token detected" storage/logs/laravel.log | tail -20
```

**Critical errors:**
```bash
grep "FCM: Critical error" storage/logs/laravel.log
```

### Database Queries

**Recent failures:**
```sql
SELECT topic, COUNT(*) as count, error_message
FROM failed_notifications
WHERE created_at > NOW() - INTERVAL 7 DAY
GROUP BY topic, error_message
ORDER BY count DESC;
```

**Failure rate by day:**
```sql
SELECT DATE(created_at) as date, COUNT(*) as failures
FROM failed_notifications
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

---

## Maintenance

### Cleanup Old Failed Notifications

Add to cron or run manually:

```bash
# Delete records older than 30 days
php artisan tinker
>>> DB::table('failed_notifications')->where('created_at', '<', now()->subDays(30))->delete();
```

Or create a scheduled command (optional):
```php
// In app/Console/Kernel.php
$schedule->command('notifications:cleanup')->daily();
```

---

## What's Next (Optional Improvements)

### 1. Retry Failed Notifications
- Implement exponential backoff retry
- Queue failed notifications for retry
- Max 3 retry attempts

### 2. Admin Dashboard Widget
- Show notification success rate
- List recent failures
- Graph of delivery trends

### 3. User Notification Preferences
- Let users opt-out of certain notification types
- Per-user notification settings

### 4. Batch Sending Optimization
- Send to multiple tokens in one API call
- Use FCM topics for role-based notifications
- Reduce API calls by 90%

---

## Summary

✅ **Token refresh** - Automatic, no user action needed  
✅ **Error logging** - Detailed logs in `storage/logs/laravel.log`  
✅ **Invalid token cleanup** - Automatic removal from database  
✅ **Failed notification tracking** - New `failed_notifications` table  
✅ **Production ready** - All changes tested, no breaking changes  

**No configuration needed - improvements work automatically!**
