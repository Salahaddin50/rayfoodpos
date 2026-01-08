# Icon Storage & Menu Linking Guide

## 📍 **Where Icons Are Stored**

Icons are stored as a custom icon font library called **"Lab"**:
- **Location**: `public/themes/default/fonts/lab/`
- **Files**: 
  - `lab.css` - Full CSS with all icon classes
  - `lab.min.css` - Minified version
  - Font files: `lab.woff2`, `lab.woff`, `lab.ttf`, `lab.eot`, `lab.svg`

## 🔗 **How Icons Are Linked to Menu Sections**

### 1. **Database Storage**
- **Table**: `menus`
- **Column**: `icon` (string)
- **Format**: Icon classes like `lab lab-bag-line`, `lab lab-dashboard`, etc.
- Example: `icon = 'lab lab-bag-line'`

### 2. **Vue Component Rendering**
- **File**: `resources/js/components/layouts/backend/BackendMenuComponent.vue`
- **How it works**:
  ```vue
  <i class="text-sm" :class="menu.icon"></i>
  ```
- The component reads the `icon` field from the menu object and applies it as a CSS class

### 3. **Menu Seeder**
- **File**: `database/seeders/MenuTableSeeder.php`
- Defines icons when seeding menus:
  ```php
  'icon' => 'lab lab-bag-line',
  ```

### 4. **Fallback Icons**
- **Location**: `BackendMenuComponent.vue` (lines 60-63)
- Provides fallback icons if database icon is missing:
  ```javascript
  const fallbackIcons = {
      dining_tables: "lab lab-reserve-line",
      takeaway: "lab lab-bag-line",
      takeaway_types: "lab lab-bag-line",
  };
  ```

## ✅ **Changes Made: Takeaway Parent Menu**

### **What Was Changed**

1. **Menu Structure Updated**:
   - **Before**: "Takeaway Types" was a standalone menu item
   - **After**: "Takeaway" is now a parent menu with "Takeaway Types" as a child

2. **Files Modified**:
   - ✅ `database/seeders/MenuTableSeeder.php` - Updated menu structure
   - ✅ `resources/js/components/layouts/backend/BackendMenuComponent.vue` - Updated pinned order and fallback icons

3. **Migration Created**:
   - ✅ `database/migrations/2026_01_10_000000_create_takeaway_parent_menu.php` - Converts existing menu structure

### **New Menu Structure**

```
📦 Takeaway (parent menu)
  └── 🛍️ Takeaway Types (child menu)
```

## 🚀 **How to Apply Changes**

### **Step 1: Run the Migration**
```bash
php artisan migrate
```

This will:
- Create a new parent "Takeaway" menu
- Convert existing "Takeaway Types" to be a child of "Takeaway"

### **Step 2: Clear Cache** (if needed)
```bash
php artisan cache:clear
php artisan config:clear
```

### **Step 3: Refresh Browser**
- Press `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
- Or logout and login again

## 📋 **Available Icon Classes**

Common icon classes used in menus:
- `lab lab-dashboard` - Dashboard icon
- `lab lab-items` - Items icon
- `lab lab-reserve-line` - Dining tables icon
- `lab lab-bag-line` - Takeaway/bag icon
- `lab lab-pos` - POS icon
- `lab lab-offers` - Offers icon
- `lab lab-settings` - Settings icon
- `lab lab-transactions` - Transactions icon
- And many more in `public/themes/default/fonts/lab/lab.css`

## 🎯 **Menu Priority Order**

Current top menu order (by priority):
1. Dashboard (priority: 10)
2. Items (priority: 15)
3. Dining Tables (priority: 20)
4. **Takeaway (priority: 21)** ← New parent menu
   - Takeaway Types (priority: 21, child)
5. Pos & Orders (priority: 100)

## 📝 **Notes**

- Icons use the Lab icon font library
- All icons are prefixed with `lab` or `lab-`
- Parent menus have `url = '#'` and `parent = 0`
- Child menus have `parent = <parent_id>` and their actual URL
- Language translations are in `resources/js/languages/` (en.json, az.json, ru.json)

