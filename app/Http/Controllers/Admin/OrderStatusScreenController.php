<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Http\Resources\OSSPopularItemResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\OSSOrderDetailsResource;
use App\Services\OrderStatusScreenOrderService;

class OrderStatusScreenController extends AdminController
{
    private OrderStatusScreenOrderService $orderStatusScreenOrderService;

    public function __construct(OrderStatusScreenOrderService $orderStatusScreenOrderService)
    {
        parent::__construct();
        $this->orderStatusScreenOrderService = $orderStatusScreenOrderService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:order-status-screen', only: ['index', 'mostPopularItems']),
        ];
    }

    public function index(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return OSSOrderDetailsResource::collection($this->orderStatusScreenOrderService->list());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function mostPopularItems(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return OSSPopularItemResource::collection($this->orderStatusScreenOrderService->mostPopularItems());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
