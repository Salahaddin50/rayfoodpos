<?php

namespace App\Services;

use Exception;
use App\Enums\Ask;
use Carbon\Carbon;
use App\Models\Item;
use App\Enums\Source;
use App\Enums\Status;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Log;
use App\Libraries\QueryExceptionLibrary;

class OrderStatusScreenOrderService
{
    public object $order;
    protected array $orderFilter = [
        'order_serial_no',
        'branch_id',
        'order_type',
        'status',
        'kitchen_status',
        'source'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            return Order::with(['diningTable', 'takeawayType'])
                // OSS should include token-based orders AND dining-table orders (which may not have tokens).
                ->where(function ($query) {
                    $query->whereNotNull('token')
                        ->orWhereNotNull('dining_table_id');
                })
                ->whereIn('status', [OrderStatus::PREPARING, OrderStatus::PREPARED])
                ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereDate('order_datetime', Carbon::today())->where('is_advance_order', Ask::NO);
                })->orWhere(function ($subQuery) {
                    $subQuery->where('is_advance_order', Ask::YES)->where('order_datetime', '<', Carbon::today());
                });
            })
                // Priority: online (no token) first, then token orders.
                ->orderByRaw('CASE WHEN token IS NULL OR token = "" THEN 0 ELSE 1 END ASC')
                ->orderByRaw('CAST(token AS UNSIGNED) ASC')
                ->orderBy('order_datetime', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function mostPopularItems()
    {
        try {
            return Item::with('media', 'category', 'offer')->withCount('orders')->where(['status' => Status::ACTIVE])->orderBy('orders_count', 'desc')->limit(9)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}