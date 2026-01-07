<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\Source;
use App\Enums\PosPaymentMethod;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO')) {
            Order::insert([
                [
                    'order_serial_no'    => date('dmy') . '1',
                    'token'              => "0101",
                    'user_id'            => 2,
                    'branch_id'          => 1,
                    'subtotal'           => 18.730000,
                    'discount'           => 0.000000,
                    'delivery_charge'    => 0.000000,
                    'total_tax'          => 0.425000,
                    'total'              => 18.730000,
                    'pos_payment_method' => PosPaymentMethod::CASH,
                    'order_type'         => OrderType::TAKEAWAY,
                    'order_datetime'     => now(),
                    'delivery_time'      => '20:30 - 21:00',
                    'preparation_time'   => 30,
                    'is_advance_order'   => 10,
                    'payment_method'     => 1,
                    'payment_status'     => 5,
                    'status'             => OrderStatus::DELIVERED,
                    'delivery_boy_id'    => null,
                    'reason'             => null,
                    'dining_table_id'    => null,
                    'source'             => Source::POS,
                    'created_at'         => now(),
                    'updated_at'         => now()
                ],
                [
                    'order_serial_no'    => date('dmy') . '2',
                    'token'              => "0102",
                    'user_id'            => 3,
                    'branch_id'          => 1,
                    'subtotal'           => 8.500000,
                    'discount'           => 0.000000,
                    'delivery_charge'    => 0.000000,
                    'total_tax'          => 0.000000,
                    'total'              => 8.500000,
                    'pos_payment_method' => PosPaymentMethod::CASH,
                    'order_type'         => OrderType::DINING_TABLE,
                    'order_datetime'     => now(),
                    'delivery_time'      => '20:30 - 21:00',
                    'preparation_time'   => 30,
                    'is_advance_order'   => 10,
                    'payment_method'     => 1,
                    'payment_status'     => 5,
                    'status'             => OrderStatus::ACCEPT,
                    'delivery_boy_id'    => null,
                    'reason'             => null,
                    'dining_table_id'    => 1,
                    'source'             => Source::POS,
                    'created_at'         => now(),
                    'updated_at'         => now()
                ],
            ]);
        }
    }
}