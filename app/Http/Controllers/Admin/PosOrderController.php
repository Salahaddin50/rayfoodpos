<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Order;
use App\Exports\OrderExport;
use App\Services\OrderService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Resources\SimpleOrderResource;
use App\Http\Resources\OrderDetailsResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Driver;
use Illuminate\Http\Request;


class PosOrderController extends AdminController
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        parent::__construct();
        $this->orderService = $order;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:pos-orders', only: ['index', 'export']),
            new Middleware('permission:pos_orders_show', only: ['show']),
            new Middleware('permission:pos_orders_delete', only: ['destroy']),
            new Middleware('permission:pos_orders_edit', only: ['changeStatus', 'changePaymentStatus', 'assignDriver']),
        ];
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return SimpleOrderResource::collection($this->orderService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        Order $order
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->show($order, false));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        Order $order
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->orderService->destroy($order);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return Excel::download(new OrderExport($this->orderService, $request), 'Online-Order.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeStatus(
        Order $order,
        OrderStatusRequest $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->changeStatus($order, false, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changePaymentStatus(
        Order $order,
        PaymentStatusRequest $request
    ): \Illuminate\Http\Response | OrderDetailsResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new OrderDetailsResource($this->orderService->changePaymentStatus($order, false, $request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function assignDriver(
        Order $order,
        Request $request
    ): \Illuminate\Http\Response | \Illuminate\Http\JsonResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $request->validate([
                'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            ]);

            if ((int) $order->status !== OrderStatus::DELIVERED) {
                return response(['status' => false, 'message' => 'Driver can be assigned only when order is delivered.'], 422);
            }

            // POS Orders screen is for POS source; allow driver only for takeaway/online flows.
            if (!in_array((int) $order->order_type, [OrderType::TAKEAWAY, OrderType::DELIVERY], true)) {
                return response(['status' => false, 'message' => 'Driver can be assigned only for takeaway or delivery orders.'], 422);
            }

            $driverId = $request->input('driver_id');
            if ($driverId) {
                // Apply BranchScope: this will return null if driver isn't in this branch.
                $driver = Driver::find($driverId);
                if (!$driver) {
                    return response(['status' => false, 'message' => 'Driver not found.'], 422);
                }
                $order->driver_id = $driver->id;
            } else {
                $order->driver_id = null;
            }

            $order->save();
            $order->load('driver');

            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $order->id,
                    'driver_id' => $order->driver_id,
                    'driver_name' => $order->driver?->name,
                ],
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
