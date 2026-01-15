# Online Order System Implementation

## Overview
A new online ordering system has been successfully created, similar to the table/branch ordering system but designed for customers ordering from home. Users can select a branch, browse the menu, add items to cart, and place orders with their WhatsApp contact information.

## Features Implemented

### 1. Branch Selection Page (`/online`)
- Displays all available branches in a grid layout
- Shows branch details (name, address, phone, email)
- Users can select a branch to start ordering
- **Component**: `resources/js/components/online/OnlineBranchSelectionComponent.vue`

### 2. Online Menu Page (`/online/menu/:branchId`)
- Displays items filtered by selected branch
- Category navigation with swiper
- Veg/Non-veg filters
- List/Grid view toggle
- Same UI/UX as table ordering
- **Component**: `resources/js/components/online/OnlineMenuComponent.vue`

### 3. Search Functionality (`/online/search/:branchId`)
- Search items within selected branch
- **Component**: `resources/js/components/online/OnlineSearchItemComponent.vue`

### 4. Page Display (`/online/page/:branchId/:pageSlug`)
- Display custom pages (terms, privacy, etc.)
- **Component**: `resources/js/components/online/OnlinePageComponent.vue`

### 5. Checkout Page (`/online/checkout/:branchId`)
- Cart summary with all items
- Payment method selection (Cash/Card or Digital Payment)
- **WhatsApp Number Field** - Required field for customer contact
- Order placement
- **Component**: `resources/js/components/online/OnlineCheckoutComponent.vue`

### 6. Order Details Page (`/online/order/:branchId/:id`)
- Order confirmation
- Order status tracking with refresh button
- Payment information
- Branch contact details
- Order items list
- Receipt download option
- **Component**: `resources/js/components/online/OnlineOrderDetailsComponent.vue`

## Backend Changes

### Database Migration
**File**: `database/migrations/2026_01_15_000000_add_whatsapp_number_to_orders_table.php`
- Adds `whatsapp_number` field to `orders` table (VARCHAR 20, nullable)

**To run the migration:**
```bash
php artisan migrate
```

### Model Updates
1. **Order Model** (`app/Models/Order.php`)
   - Added `whatsapp_number` to `$fillable` array
   - Added `whatsapp_number` to `$casts` array

2. **FrontendOrder Model** (`app/Models/FrontendOrder.php`)
   - Added `whatsapp_number` to `$fillable` array
   - Added `whatsapp_number` to `$casts` array

### Validation Updates
**File**: `app/Http/Requests/TableOrderRequest.php`
- Changed `dining_table_id` from `required` to `nullable` (to support online orders without table)
- Added `whatsapp_number` validation rule: `['nullable', 'string', 'max:20']`

## Frontend Changes

### Router Configuration
**File**: `resources/js/router/modules/onlineOrderRoutes.js`
- Created new route module with 6 routes
- All routes use `isTable: true` meta to use table layout (navbar, footer, cart)

**Routes registered in**: `resources/js/router/index.js`

### State Management
**File**: `resources/js/store/modules/table/tableCart.js`
- Added `onlineBranchId` to state
- Added `initOnlineBranch` action to store selected branch
- Cart functionality reused from table ordering system

### Translations
**File**: `resources/js/languages/en.json`

Added new translation keys:
- `label.online_order`: "Online Order"
- `label.whatsapp_number`: "WhatsApp Number"
- `label.enter_whatsapp_number`: "Enter WhatsApp Number"
- `label.contact_information`: "Contact Information"
- `label.back_to_branches`: "Back to Branches"
- `label.change_branch`: "Change Branch"
- `message.select_branch_to_order`: "Select a branch to start ordering"
- `message.no_branches_available`: "No branches available at the moment"
- `message.order_confirm_online`: "Your online order has been confirmed. We will contact you via WhatsApp."
- `message.whatsapp_number_required`: "WhatsApp number is required"
- `message.something_went_wrong`: "Something went wrong. Please try again."
- `button.order_now`: "Order Now"

## Order Management

### Where Do Online Orders Appear?

**Online orders will appear in the "Table Orders" section** in the admin panel.

**Reasoning:**
1. Online orders use the same backend structure as table orders (`OrderType::DINING_TABLE`)
2. They are stored in the same `orders` table
3. They use the same API endpoints (`table/dining-order`)
4. The admin can distinguish online orders by:
   - The presence of `whatsapp_number` field
   - The absence of `dining_table_id` (will be null for online orders)
   - Order source tracking

### Distinguishing Online Orders from Table Orders

In the admin panel, you can identify online orders by:
1. **WhatsApp Number**: Online orders have a WhatsApp number, table orders don't
2. **Table Name**: Online orders won't have a table assigned
3. **Order Source**: Track the source field to differentiate

### Recommended Enhancement (Optional)
Consider adding a filter in the Table Orders admin panel to:
- Show "All Orders"
- Show "Table Orders Only" (where `dining_table_id` is not null)
- Show "Online Orders Only" (where `whatsapp_number` is not null)

## Testing Checklist

1. ✅ Navigate to `/online`
2. ✅ Select a branch
3. ✅ Browse menu items by category
4. ✅ Add items to cart
5. ✅ Go to checkout
6. ✅ Enter WhatsApp number
7. ✅ Select payment method
8. ✅ Place order
9. ✅ View order confirmation
10. ✅ Check order appears in admin "Table Orders" section

## Files Created

### Vue Components (6 files)
1. `resources/js/components/online/OnlineBranchSelectionComponent.vue`
2. `resources/js/components/online/OnlineMenuComponent.vue`
3. `resources/js/components/online/OnlineSearchItemComponent.vue`
4. `resources/js/components/online/OnlinePageComponent.vue`
5. `resources/js/components/online/OnlineCheckoutComponent.vue`
6. `resources/js/components/online/OnlineOrderDetailsComponent.vue`

### Router
7. `resources/js/router/modules/onlineOrderRoutes.js`

### Database Migration
8. `database/migrations/2026_01_15_000000_add_whatsapp_number_to_orders_table.php`

## Files Modified

### Backend (3 files)
1. `app/Http/Requests/TableOrderRequest.php` - Added WhatsApp validation
2. `app/Models/Order.php` - Added WhatsApp field
3. `app/Models/FrontendOrder.php` - Added WhatsApp field

### Frontend (6 files)
4. `resources/js/router/index.js` - Registered online routes
5. `resources/js/store/modules/table/tableCart.js` - Added online branch support
6. `resources/js/languages/en.json` - Added translations
7. `resources/js/components/layouts/table/TableNavBarComponent.vue` - Made route-aware for online/table orders
8. `resources/js/components/layouts/table/TableFooterComponent.vue` - Made route-aware for online/table orders
9. `resources/js/components/layouts/table/TableCartComponent.vue` - Made route-aware for online/table orders

## Important Technical Notes

### Shared Layout Components
The online ordering system reuses the table ordering layout components (navbar, footer, cart sidebar). These components have been updated to be "route-aware" - they automatically detect whether they're being used for online orders or table orders and adjust their navigation accordingly:

- **TableNavBarComponent**: Logo links to appropriate home, search navigates to correct route
- **TableFooterComponent**: Page links use correct route parameters
- **TableCartComponent**: Checkout button routes to correct checkout page

This approach maintains code reusability while supporting both ordering modes seamlessly.

## Next Steps

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Build frontend assets:**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

3. **Test the online ordering flow:**
   - Visit `http://your-domain.com/online`
   - Complete a test order

4. **Optional Enhancements:**
   - Add a filter in admin panel to distinguish online vs table orders
   - Add email notifications for online orders
   - Add SMS notifications via WhatsApp API
   - Create a separate "Online Orders" menu item in admin (if desired)

## Notes

- Online orders use the same order type as table orders (`DINING_TABLE`)
- The `dining_table_id` will be `null` for online orders
- The `whatsapp_number` field is the key identifier for online orders
- All existing table order functionality (status updates, payment, etc.) works for online orders
- The system reuses the existing cart, payment, and order management infrastructure

