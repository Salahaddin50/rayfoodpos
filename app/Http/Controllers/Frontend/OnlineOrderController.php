<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\TableOrderRequest;
use App\Services\OrderService;
use Exception;

class OnlineOrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(TableOrderRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Force order type to ONLINE
            $request->merge(['order_type' => OrderType::ONLINE]);
            
            $this->orderService->tableOrderStore($request);
            return response()->json([
                'status' => true,
                'message' => trans('all.message.order_create')
            ], 201);
        } catch (Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
    }
}

