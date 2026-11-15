<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldOrders extends Command
{
    protected $signature = 'orders:delete-old {date}';
    protected $description = 'Delete all orders before a specific date';

    public function handle()
    {
        $date = $this->argument('date');
        
        // Validate date format
        try {
            $dateObj = new \DateTime($date);
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM-DD format.');
            return 1;
        }

        $this->info("Checking orders before {$date}...");

        // Count orders to be deleted
        $orderCount = Order::withoutGlobalScopes()
            ->where('order_datetime', '<', $date)
            ->count();

        if ($orderCount === 0) {
            $this->info('No orders found before this date.');
            return 0;
        }

        $this->warn("Found {$orderCount} orders to delete.");
        
        if (!$this->confirm('Do you want to proceed with deletion?')) {
            $this->info('Deletion cancelled.');
            return 0;
        }

        $this->info('Deleting orders and related records...');
        
        DB::beginTransaction();
        
        try {
            // Get order IDs to delete
            $orderIds = Order::withoutGlobalScopes()
                ->where('order_datetime', '<', $date)
                ->pluck('id')
                ->toArray();

            if (empty($orderIds)) {
                $this->info('No orders to delete.');
                DB::rollBack();
                return 0;
            }

            // Delete related records
            $this->info('Deleting order items...');
            $orderItemsDeleted = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->delete();
            $this->info("Deleted {$orderItemsDeleted} order items.");

            $this->info('Deleting order addresses...');
            $addressesDeleted = DB::table('order_addresses')
                ->whereIn('order_id', $orderIds)
                ->delete();
            $this->info("Deleted {$addressesDeleted} order addresses.");

            $this->info('Deleting transactions...');
            $transactionsDeleted = DB::table('transactions')
                ->whereIn('order_id', $orderIds)
                ->delete();
            $this->info("Deleted {$transactionsDeleted} transactions.");

            // Delete orders
            $this->info('Deleting orders...');
            $ordersDeleted = Order::withoutGlobalScopes()
                ->where('order_datetime', '<', $date)
                ->delete();
            $this->info("Deleted {$ordersDeleted} orders.");

            DB::commit();
            
            $this->info('✅ Successfully deleted all old orders and related records!');
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
