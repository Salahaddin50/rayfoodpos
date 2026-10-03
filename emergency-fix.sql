# Emergency SQL fix for ItemService.php
# Simplified version without LEFT JOINs to fix 422 error

# Replace the itemReport query in ItemService.php around line 400-490
# with this simpler version:

$query = DB::table('order_items')
    ->join('items', 'order_items.item_id', '=', 'items.id')
    ->join('orders', 'order_items.order_id', '=', 'orders.id')
    ->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
    ->select(
        'items.id as item_id',
        'items.name as item_name',
        'items.item_type',
        'item_categories.name as category_name',
        'orders.order_type',
        // Calculate average unit price for this grouping
        DB::raw('ROUND(AVG(CASE 
            WHEN order_items.total_price > 0 AND order_items.quantity > 0 
            THEN order_items.total_price / order_items.quantity 
            ELSE items.price 
        END), 2) as unit_price'),
        // Hash of variations+extras for grouping
        DB::raw('MD5(CONCAT(
            COALESCE(order_items.item_variations, \'\'), 
            \'|\', 
            COALESCE(order_items.item_extras, \'\'),
            \'|\',
            COALESCE(orders.order_type, \'\')
        )) as options_key'),
        DB::raw('MIN(order_items.item_variations) as item_variations'),
        DB::raw('MIN(order_items.item_extras) as item_extras'),
        DB::raw('SUM(order_items.quantity) as total_quantity'),
        DB::raw('SUM(CASE 
            WHEN order_items.total_price > 0 
            THEN order_items.total_price 
            ELSE items.price * order_items.quantity 
        END) as total_income'),
        DB::raw('MIN(orders.order_datetime) as first_order_date'),
        DB::raw('GROUP_CONCAT(DISTINCT orders.order_serial_no ORDER BY orders.order_serial_no SEPARATOR \', \') as order_numbers')
    );

// GROUP BY without the problematic LEFT JOINs
$query->groupBy(
    'items.id',
    'items.name',
    'items.item_type',
    'items.price',
    'item_categories.name',
    'orders.order_type',
    DB::raw('MD5(CONCAT(
        COALESCE(order_items.item_variations, \'\'), 
        \'|\', 
        COALESCE(order_items.item_extras, \'\'),
        \'|\',
        COALESCE(orders.order_type, \'\')
    ))')
);