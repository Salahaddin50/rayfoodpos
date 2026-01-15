# Troubleshooting 422 Error on Online Orders

## Error Description
When placing an online order, you're getting a 422 (Unprocessable Content) error from `/api/table/dining-order`.

## Common Causes & Solutions

### 1. Missing Default Customer

The most common cause is that the default customer (Walking Customer) doesn't exist in your database.

**Check if the default customer exists:**
```sql
SELECT * FROM users WHERE username = 'default-customer';
```

**If it doesn't exist, create it:**
```sql
INSERT INTO users (name, email, username, phone, country_code, password, role_id, branch_id, status, is_guest, created_at, updated_at)
VALUES (
    'Walking Customer',
    'walking@customer.com',
    'default-customer',
    '1234567890',
    '+1',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    3, -- Customer role ID (adjust if needed)
    1, -- Default branch ID (adjust to your first branch ID)
    5, -- Active status
    0, -- Not a guest
    NOW(),
    NOW()
);
```

**Note:** Adjust the `role_id` and `branch_id` values according to your database:
- `role_id`: Should be the ID of the "Customer" role (usually 3)
- `branch_id`: Should be a valid branch ID from your `branches` table

### 2. Invalid Customer ID

The code uses `customer_id: 2` by default. If user ID 2 doesn't exist:

**Check if user ID 2 exists:**
```sql
SELECT * FROM users WHERE id = 2;
```

**If not, the system will fall back to the default customer (above).**

### 3. Invalid Branch ID

Make sure the branch you're selecting actually exists.

**Check branches:**
```sql
SELECT id, name FROM branches WHERE status = 5; -- 5 = Active
```

### 4. Database Migration Not Run

Make sure you've run the migration to add the `whatsapp_number` field:

```bash
php artisan migrate
```

**Verify the field exists:**
```sql
DESCRIBE orders;
-- Look for 'whatsapp_number' column
```

### 5. Check Browser Console

After rebuilding the frontend, check the browser console for the detailed error:

```bash
npm run build
```

Then in your browser:
1. Open Developer Tools (F12)
2. Go to Console tab
3. Try placing an order
4. Look for the console.log output showing what data is being sent
5. Look for the error response details

## Debugging Steps

1. **Check the browser console** - The updated code now logs the order data being submitted
2. **Check Laravel logs** - Look at `storage/logs/laravel.log` for detailed error messages
3. **Check database** - Verify the default customer exists
4. **Test with a table order** - If table orders work but online orders don't, the issue is specific to online order handling

## Quick Fix Script

Create a file `fix-default-customer.php` in your project root:

```php
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

// Get the customer role
$customerRole = Role::where('name', 'Customer')->first();
if (!$customerRole) {
    echo "Error: Customer role not found!\n";
    exit(1);
}

// Get the first active branch
$branch = Branch::where('status', 5)->first();
if (!$branch) {
    echo "Error: No active branch found!\n";
    exit(1);
}

// Check if default customer exists
$defaultCustomer = User::where('username', 'default-customer')->first();

if ($defaultCustomer) {
    echo "Default customer already exists (ID: {$defaultCustomer->id})\n";
} else {
    // Create default customer
    $defaultCustomer = User::create([
        'name' => 'Walking Customer',
        'email' => 'walking@customer.com',
        'username' => 'default-customer',
        'phone' => '1234567890',
        'country_code' => '+1',
        'password' => Hash::make('password'),
        'role_id' => $customerRole->id,
        'branch_id' => $branch->id,
        'status' => 5, // Active
        'is_guest' => 0,
    ]);
    
    echo "Default customer created successfully (ID: {$defaultCustomer->id})\n";
}

echo "Done!\n";
```

Run it:
```bash
php fix-default-customer.php
```

## Expected Console Output

After the fix, when you place an order, you should see in the browser console:

```
Submitting order with data: {
    branch_id: 1,
    customer_id: 2,
    whatsapp_number: "+1234567890",
    subtotal: 25.50,
    total: "25.50"
}
```

If you still see errors, check the response in the Network tab for more details.

## Still Having Issues?

1. Check the exact error message in the browser Network tab:
   - Open Developer Tools (F12)
   - Go to Network tab
   - Try placing an order
   - Click on the failed `dining-order` request
   - Look at the Response tab for the exact error message

2. Share the error message for further assistance.

