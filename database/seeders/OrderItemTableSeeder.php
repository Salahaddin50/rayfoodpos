<?php

namespace Database\Seeders;

use App\Enums\TaxType;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;
use App\Models\OrderItem;

class OrderItemTableSeeder extends Seeder
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
            OrderItem::insert([
                [
                    'order_id'             => 1,
                    'branch_id'            => 1,
                    'item_id'              => 6,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 5.230000,
                    'item_variations'      => '[{"id":9,"item_id":6,"item_attribute_id":"1","variation_name":"Size","name":"Regular"}]',
                    'item_extras'          => '[{"id":3,"item_id":6,"name":"Add Onion"},{"id":4,"item_id":6,"name":"Add Patty"}]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 1.500000,
                    'total_price'          => 6.730000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 1,
                    'branch_id'            => 1,
                    'item_id'              => 48,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 1.500000,
                    'item_variations'      => '[]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 1.500000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 1,
                    'branch_id'            => 1,
                    'item_id'              => 18,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'VAT',
                    'tax_rate'             => 5,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.225000,
                    'price'                => 3.500000,
                    'item_variations'      => '[{"id":34,"item_id":18,"item_attribute_id":"1","variation_name":"Size","name":"Large - 10"}]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 1.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 4.500000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 1,
                    'branch_id'            => 1,
                    'item_id'              => 53,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 2.000000,
                    'item_variations'      => '[]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 2.000000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 1,
                    'branch_id'            => 1,
                    'item_id'              => 33,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'VAT',
                    'tax_rate'             => 5,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.200000,
                    'price'                => 4.000000,
                    'item_variations'      => '[{"id":76,"item_id":33,"item_attribute_id":"2","variation_name":"Quantity Choice","name":"Pack of 6"}]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 4.000000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 2,
                    'branch_id'            => 1,
                    'item_id'              => 14,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 2.500000,
                    'item_variations'      => '[{"id":25,"item_id":14,"item_attribute_id":"1","variation_name":"Size","name":"Regular"}]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 2.500000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 2,
                    'branch_id'            => 1,
                    'item_id'              => 53,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 2.000000,
                    'item_variations'      => '[]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 2.000000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 2,
                    'branch_id'            => 1,
                    'item_id'              => 44,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 1.000000,
                    'item_variations'      => '[{"id":80,"item_id":44,"item_attribute_id":"1","variation_name":"Size","name":"Regular"}]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 1.000000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 2,
                    'branch_id'            => 1,
                    'item_id'              => 43,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 1.500000,
                    'item_variations'      => '[]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 1.500000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
                [
                    'order_id'             => 2,
                    'branch_id'            => 1,
                    'item_id'              => 45,
                    'quantity'             => 1,
                    'discount'             => 0.000000,
                    'tax_name'             => 'NO-VAT',
                    'tax_rate'             => 0,
                    'tax_type'             => TaxType::PERCENTAGE,
                    'tax_amount'           => 0.000000,
                    'price'                => 1.500000,
                    'item_variations'      => '[]',
                    'item_extras'          => '[]',
                    'item_variation_total' => 0.000000,
                    'item_extra_total'     => 0.000000,
                    'total_price'          => 1.500000,
                    'instruction'          => '',
                    'created_at'           => now(),
                    'updated_at'           => now()
                ],
            ]);
        }
    }
}