# Campaign System Security Documentation

## 🔒 Security Measures Implemented

### 1. **Rate Limiting (Per Phone Number)**
- **Join Campaign**: Maximum 3 attempts per hour per phone number
- **Progress Check**: Maximum 20 checks per hour per phone number
- Prevents automated abuse via Postman/scripts

**Implementation**: `CampaignRateLimiter` middleware

### 2. **Rate Limiting (Per IP Address)**
- **Join Campaign**: Maximum 10 requests per minute per IP
- **Progress Check**: Maximum 30 requests per minute per IP
- Prevents distributed attacks from multiple phone numbers

**Implementation**: Laravel's built-in `throttle` middleware

### 3. **Server-Side Campaign Assignment**
Users **CANNOT** specify which campaign they're enrolled in via API:
- Campaign is determined from `online_users.campaign_id` (server-side)
- No `campaign_id` parameter accepted in order requests
- Prevents users from claiming rewards they didn't earn

### 4. **Campaign Completion Tracking**
- Once a campaign is completed, it's recorded in `campaign_completions` table
- Users cannot rejoin completed campaigns (permanent block)
- Prevents repeatedly earning rewards from the same campaign

### 5. **One Campaign at a Time**
- Users can only be enrolled in ONE item-based campaign at a time
- Prevents using the same orders for multiple campaigns
- Must complete current campaign before joining another

### 6. **Order Counting from Join Date**
- Only orders placed AFTER `campaign_joined_at` count
- Orders from previous campaigns don't carry over
- Prevents backdating or retroactive campaign claims

### 7. **Campaign Date Validation**
- Cannot join campaigns before `start_date`
- Cannot join campaigns after `end_date`
- Server-side validation of campaign status (ACTIVE check)

### 8. **Request Logging**
All campaign actions are logged with:
- IP address
- User agent
- Phone number
- Timestamp
- Campaign ID
- Branch ID

**Purpose**: Audit trail for investigating suspicious activity

### 9. **Input Validation & Sanitization**
- Phone number normalization (removes special characters)
- Branch ID must exist in database
- Campaign ID must exist and be active
- Request validation via `FormRequest` classes

### 10. **Database Constraints**
- Unique constraint on `campaign_completions` (campaign_id + branch_id + whatsapp)
- Foreign key constraints prevent invalid references
- Index on frequently queried columns for performance

---

## 🚫 What Users CANNOT Do

### ❌ Cannot Manipulate Campaign Enrollment
```bash
# This will NOT work - campaign_id is ignored
curl -X POST https://rayyanscorner.az/api/table/dining-order \
  -d "campaign_id=5" \
  -d "campaign_redeem=true"
```
**Why**: Campaign is determined from `online_users` table, not from request

### ❌ Cannot Bypass Rate Limits
```bash
# After 3 join attempts within 1 hour:
curl -X POST https://rayyanscorner.az/api/frontend/campaign/join
# Response: 429 Too Many Requests
# "Too many campaign join attempts. Please try again in 1 hour."
```

### ❌ Cannot Rejoin Completed Campaigns
```bash
# User completed "Buy 2 Get 1 Free Pizza"
# Tries to join again:
curl -X POST https://rayyanscorner.az/api/frontend/campaign/join \
  -d "campaign_id=1"
# Response: 422
# "You have already completed this campaign and cannot rejoin it."
```

### ❌ Cannot Join Multiple Campaigns Simultaneously
```bash
# User is in "Buy 2 Get 1 Pizza" campaign
# Tries to join "Buy 3 Get 1 Burger":
curl -X POST https://rayyanscorner.az/api/frontend/campaign/join \
  -d "campaign_id=2"
# Response: 422
# "You are already enrolled in Buy 2 Get 1 Pizza. Complete it first..."
```

### ❌ Cannot Count Orders Before Join Date
```bash
# User joined campaign on Jan 31
# Has orders from Jan 20 (before join)
# Those orders will NOT count towards campaign progress
```

### ❌ Cannot Join Before Campaign Starts
```bash
# Campaign starts Feb 5, today is Jan 31
curl -X POST https://rayyanscorner.az/api/frontend/campaign/join \
  -d "campaign_id=3"
# Response: 422
# "Campaign will start in 5 days."
```

---

## ✅ What Users CAN Do (Legitimate Use)

1. **View Active Campaigns** (unlimited, read-only)
2. **Join ONE Active Campaign** (3 attempts per hour)
3. **Check Progress** (20 times per hour)
4. **Redeem Free Item** (when entitled, automatic)
5. **Complete Campaign & Join New One** (after completion)

---

## 📊 Monitoring & Detection

### Suspicious Activity Indicators

Check logs for:
```bash
# Find users hitting rate limits
grep "Campaign rate limit exceeded" storage/logs/laravel.log

# Find failed join attempts
grep "Campaign join attempt" storage/logs/laravel.log | grep -v "Successfully"

# Find unusual redemption patterns
grep "Campaign redemption attempt" storage/logs/laravel.log
```

### Database Queries for Auditing

```sql
-- Find users who joined multiple campaigns (should be impossible)
SELECT whatsapp, COUNT(DISTINCT campaign_id) as campaign_count
FROM campaign_completions
WHERE branch_id = 1
GROUP BY whatsapp
HAVING campaign_count > 1;

-- Find duplicate redemptions (should be prevented)
SELECT whatsapp_number, campaign_id, COUNT(*) as redemptions
FROM orders
WHERE campaign_redeem_free_item_id IS NOT NULL
GROUP BY whatsapp_number, campaign_id
HAVING redemptions > 1;

-- Find orders with mismatched campaign assignments
SELECT o.id, o.whatsapp_number, o.campaign_id, ou.campaign_id as user_campaign
FROM orders o
LEFT JOIN online_users ou ON o.whatsapp_number = ou.whatsapp AND o.branch_id = ou.branch_id
WHERE o.campaign_id IS NOT NULL 
  AND o.campaign_id != ou.campaign_id;
```

---

## 🛡️ Admin Best Practices

1. **Monitor Logs Daily**: Check for rate limit violations
2. **Review Completions**: Ensure no suspicious patterns
3. **Set Realistic Limits**: Adjust `required_purchases` appropriately
4. **Campaign Duration**: Don't make campaigns too long (increases abuse risk)
5. **Test Before Launch**: Create test campaign and verify all security measures

---

## 🔧 Configuration

### Rate Limit Adjustments

Edit `app/Http/Middleware/CampaignRateLimiter.php`:

```php
$limits = [
    'join' => [
        'max' => 3,      // Change this (attempts per period)
        'decay' => 3600, // Change this (period in seconds)
    ],
    'progress' => [
        'max' => 20,     // Change this
        'decay' => 3600, // Change this
    ],
];
```

### IP Rate Limit Adjustments

Edit `routes/api.php`:

```php
->middleware('throttle:10,1'); // 10 requests per 1 minute
```

---

## ⚠️ Known Limitations

1. **Phone Number Spoofing**: Users with multiple phone numbers can join multiple times (mitigated by rate limiting + IP tracking)
2. **VPN/Proxy Users**: IP-based rate limiting less effective (still have phone-based limits)
3. **Shared Devices**: Family members sharing device tracked by same IP (phone number limits still apply)

---

## 📝 Summary

The campaign system is secured against:
- ✅ Postman/API manipulation
- ✅ Automated scripts/bots
- ✅ Retroactive campaign claims
- ✅ Multiple campaign enrollment
- ✅ Campaign reward stacking
- ✅ Rejoining completed campaigns
- ✅ Brute force attacks

**Conclusion**: Normal users through the website interface will have a smooth experience, while malicious actors using Postman or automation tools will be blocked by multiple layers of protection.
