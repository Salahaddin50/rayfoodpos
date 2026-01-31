<?php
/**
 * Campaign Order Checker
 * Upload this to server and run: php check_campaign_orders.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$phone = '+994503531437';
$branchId = 1;
$campaignId = 2; // Buy 8 get 1 free

echo "=== Campaign Order Checker ===\n\n";
echo "Phone: $phone\n";
echo "Branch ID: $branchId\n";
echo "Campaign ID: $campaignId\n\n";

// Get campaign details
$campaign = DB::table('campaigns')->where('id', $campaignId)->first();
if (!$campaign) {
    echo "Campaign not found!\n";
    exit(1);
}

echo "Campaign: {$campaign->name}\n";
echo "Required Purchases: {$campaign->required_purchases}\n";
echo "Start Date: {$campaign->start_date}\n";
echo "End Date: {$campaign->end_date}\n\n";

// Normalize phone
echo "Searching for phone variations:\n";
$phoneVariations = [
    $phone,
    str_replace('+', '', $phone),
    substr($phone, -9), // Last 9 digits
];
foreach ($phoneVariations as $v) {
    echo "  - $v\n";
}
echo "\n";

// Check all orders for this phone
echo "=== ALL ORDERS (any status) ===\n";
$allOrders = DB::table('orders')
    ->where('branch_id', $branchId)
    ->where(function($query) use ($phone) {
        $query->where('whatsapp_number', 'LIKE', '%' . substr($phone, -9))
              ->orWhere('whatsapp_number', $phone);
    })
    ->orderBy('order_datetime', 'desc')
    ->get(['id', 'order_serial_no', 'whatsapp_number', 'status', 'order_datetime']);

echo "Found " . $allOrders->count() . " total orders:\n";
foreach ($allOrders as $order) {
    $statusName = [
        1 => 'PENDING',
        4 => 'ACCEPT',
        7 => 'PREPARING',
        8 => 'PREPARED',
        10 => 'OUT_FOR_DELIVERY',
        13 => 'DELIVERED',
        16 => 'CANCELED',
        19 => 'REJECTED',
    ][$order->status] ?? 'UNKNOWN';
    
    echo sprintf(
        "  #%d (%s) - WhatsApp: %s, Status: %d (%s), DateTime: %s\n",
        $order->id,
        $order->order_serial_no,
        $order->whatsapp_number,
        $order->status,
        $statusName,
        $order->order_datetime
    );
}
echo "\n";

// Check completed orders (campaign eligible)
echo "=== CAMPAIGN-ELIGIBLE ORDERS ===\n";
$completedStatuses = [4, 7, 8, 10, 13]; // ACCEPT, PREPARING, PREPARED, OUT_FOR_DELIVERY, DELIVERED

$eligibleOrders = DB::table('orders')
    ->where('branch_id', $branchId)
    ->where(function($query) use ($phone) {
        $query->where('whatsapp_number', 'LIKE', '%' . substr($phone, -9))
              ->orWhere('whatsapp_number', $phone);
    })
    ->whereIn('status', $completedStatuses);

// Apply campaign date filters
if ($campaign->start_date) {
    $eligibleOrders->where('order_datetime', '>=', $campaign->start_date);
}
if ($campaign->end_date) {
    $eligibleOrders->where('order_datetime', '<=', $campaign->end_date . ' 23:59:59');
}

$eligibleOrders = $eligibleOrders->orderBy('order_datetime', 'desc')
    ->get(['id', 'order_serial_no', 'whatsapp_number', 'status', 'order_datetime']);

echo "Found " . $eligibleOrders->count() . " eligible orders (within campaign period & completed status):\n";
foreach ($eligibleOrders as $order) {
    $statusName = [
        4 => 'ACCEPT',
        7 => 'PREPARING',
        8 => 'PREPARED',
        10 => 'OUT_FOR_DELIVERY',
        13 => 'DELIVERED',
    ][$order->status];
    
    echo sprintf(
        "  #%d (%s) - Status: %d (%s), DateTime: %s\n",
        $order->id,
        $order->order_serial_no,
        $order->status,
        $statusName,
        $order->order_datetime
    );
}

echo "\n=== SUMMARY ===\n";
echo "Total orders (all status): " . $allOrders->count() . "\n";
echo "Campaign-eligible orders: " . $eligibleOrders->count() . "\n";
echo "Required for reward: {$campaign->required_purchases}\n";
echo "Rewards earned: " . floor($eligibleOrders->count() / $campaign->required_purchases) . "\n";
echo "Progress: " . ($eligibleOrders->count() % $campaign->required_purchases) . " / {$campaign->required_purchases}\n";
