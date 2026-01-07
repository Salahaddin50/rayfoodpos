# 🎫 Automatic Token Generation System

## Overview
An automatic token generation system has been implemented with shift reset functionality. Tokens are sequential numbers that reset daily at midnight.

---

## ✨ Features

### 1. **Auto-Generated Sequential Tokens**
- Format: `T-001`, `T-002`, `T-003`, etc.
- Branch-specific counters
- Daily automatic reset at midnight
- Customizable prefix and padding

### 2. **Manual Entry Still Works**
- Users can still type tokens manually
- Auto-generate button is optional
- No breaking changes to existing functionality

### 3. **Shift Reset**
- Automatic daily reset at 00:01 (midnight + 1 minute)
- Manual reset command available
- Keeps today's counter when resetting

---

## 🎯 How It Works

### **POS Screen**
1. Open POS
2. Add items to cart
3. See **"Token No"** field with **"Auto"** button
4. Click **"Auto"** button to generate next token automatically
5. Or type manually as before

### **Token Format**
- **Default**: `T-001`, `T-002`, `T-003`
- **Customizable**: Change prefix to `A-`, `P-`, etc.
- **Padding**: Adjust number length (001, 0001, etc.)

---

## ⚙️ Configuration

### **Settings (Site Settings Page)**
```
site_auto_token_enabled = 1 (enabled)
site_token_prefix = "T"
site_token_padding = 3
```

### **Database Table**
- `token_counters` - Tracks counters per branch per day
- Columns: `branch_id`, `shift_date`, `counter`, `prefix`

---

## 🔄 Shift Reset Commands

### **Automatic Reset (Daily at Midnight)**
```bash
# Runs automatically via Laravel scheduler
# Configured in app/Console/Kernel.php
# Resets all old shift counters, keeps today's
```

### **Manual Reset Commands**

**Reset all old shifts (keeps today):**
```bash
php artisan tokens:reset-shift --keep-today
```

**Reset specific date:**
```bash
php artisan tokens:reset-shift --date=2026-01-06
```

**Reset all (including today):**
```bash
php artisan tokens:reset-shift
```

---

## 📊 How Counters Work

### **Example Flow (Branch ID = 1)**

**Day 1 (2026-01-07):**
- First order: `T-001`
- Second order: `T-002`
- Third order: `T-003`
- ... continues

**Day 2 (2026-01-08) - After Midnight:**
- Counters reset automatically
- First order: `T-001` (starts over)
- Second order: `T-002`

### **Multi-Branch Support**
- **Branch 1**: `T-001`, `T-002`, `T-003`
- **Branch 2**: `T-001`, `T-002`, `T-003`
- Each branch has its own counter

---

## 🛠️ Technical Implementation

### **Files Created/Modified**

**Backend:**
1. `database/migrations/2026_01_07_190000_create_token_counters_table.php` - Database table
2. `app/Models/TokenCounter.php` - Model
3. `app/Services/TokenService.php` - Business logic
4. `app/Http/Controllers/Admin/TokenController.php` - API endpoints
5. `app/Console/Commands/ResetTokenCounters.php` - Shift reset command
6. `app/Console/Kernel.php` - Scheduled task
7. `routes/api.php` - API routes added
8. `database/seeders/SiteTableSeeder.php` - Default settings

**Frontend:**
9. `resources/js/store/modules/token.js` - Vuex store
10. `resources/js/store/index.js` - Store registration
11. `resources/js/components/admin/pos/PosComponent.vue` - Auto button

---

## 🔌 API Endpoints

### **Generate Next Token**
```http
POST /api/admin/token/generate
{
  "branch_id": 1
}

Response:
{
  "status": true,
  "data": {
    "token": "T-001",
    "counter": 1
  }
}
```

### **Get Current Counter**
```http
GET /api/admin/token/current-counter?branch_id=1

Response:
{
  "status": true,
  "data": {
    "counter": 25
  }
}
```

---

## ✅ Testing

### **Test Auto-Generation:**
1. Go to POS
2. Click "Auto" button multiple times
3. Verify tokens increment: T-001, T-002, T-003

### **Test Manual Entry:**
1. Type "999" in token field
2. Create order successfully
3. Verify manual entry still works

### **Test Shift Reset:**
```bash
php artisan tokens:reset-shift --keep-today
# Verify: old counters deleted, today's counter kept
```

### **Test Multi-Branch:**
1. Switch to different branch
2. Generate token
3. Verify each branch has separate counter

---

## 🔒 Backward Compatibility

✅ **No Breaking Changes**
- Manual token entry still works
- Existing orders unaffected
- Token field remains optional (can be empty)
- Auto-generate is optional (use button or type manually)

---

## 📋 Laravel Scheduler Setup

**For automatic daily reset to work, ensure Laravel scheduler is running:**

### **Option 1: Add to Cron (Production)**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### **Option 2: Run Manually (Development)**
```bash
php artisan schedule:work
```

### **Option 3: Windows Task Scheduler**
Create a scheduled task to run:
```
php artisan schedule:run
```
Every minute.

---

## 🎨 Customization

### **Change Token Prefix**
```php
// In database or settings page
Settings::group('site')->set(['site_token_prefix' => 'ORDER']);
// Result: ORDER-001, ORDER-002
```

### **Change Number Padding**
```php
Settings::group('site')->set(['site_token_padding' => 4]);
// Result: T-0001, T-0002
```

### **Disable Auto-Generation**
```php
Settings::group('site')->set(['site_auto_token_enabled' => 0]);
// Auto button will be hidden
```

---

## 📞 Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Verify migration ran: `php artisan migrate:status`
3. Verify settings exist: `Settings::group('site')->get('site_auto_token_enabled')`
4. Test token generation API directly

---

## 🎉 Summary

✅ Automatic sequential token generation  
✅ Daily shift reset at midnight  
✅ Manual entry still works  
✅ No breaking changes  
✅ Multi-branch support  
✅ Customizable format  
✅ Laravel scheduler integration  

**The token system is now fully automated while preserving all existing functionality!**

