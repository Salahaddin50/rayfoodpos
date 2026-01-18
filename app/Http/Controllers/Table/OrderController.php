<?php

namespace App\Http\Controllers\Table;


use App\Http\Controllers\Controller;
use App\Http\Requests\TableOrderRequest;
use App\Models\FrontendOrder;
use App\Models\Order;
use Exception;
use App\Services\OrderService;
use App\Http\Resources\OrderDetailsResource;
use App\Support\WhatsAppNormalizer;


class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $order)
    {
        $this->orderService = $order;
    }

    public function store(TableOrderRequest $request): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($this->orderService->tableOrderStore($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(FrontendOrder $frontendOrder): \Illuminate\Http\Response|OrderDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new OrderDetailsResource($frontendOrder);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function trackByWhatsApp(\Illuminate\Http\Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $phoneNumber = $request->get('phone_number');
            
            if (!$phoneNumber) {
                return response(['status' => false, 'message' => 'Phone number is required'], 422);
            }

            // Normalize phone number using the same normalizer used when storing orders
            $normalized = WhatsAppNormalizer::normalize($phoneNumber);
            
            if ($normalized === '') {
                return response(['status' => false, 'message' => 'Invalid phone number'], 422);
            }

            // Search for orders with this WhatsApp number from last 2 hours
            $twoHoursAgo = now()->subHours(2);
            
            // Generate all possible formats the number could be stored in (raw input might not be normalized)
            // Also include the original input in case it was stored exactly as entered
            $possibleFormats = [
                $normalized,  // +994503531437
                str_replace('+994', '+9940', $normalized),  // +9940503531437
                str_replace('+', '', $normalized),  // 994503531437
                str_replace('+994', '9940', $normalized),  // 9940503531437
                $phoneNumber,  // Original input (could be +9940503531437 or +994503531437 or any format)
            ];
            
            // Remove duplicates and empty values
            $possibleFormats = array_filter(array_unique($possibleFormats));
            
            // Extract digits only from normalized number for comparison
            $normalizedDigits = preg_replace('/\D+/', '', $normalized);
            
            // Use withoutGlobalScope since this is a public frontend endpoint and we want to search all branches
            $orders = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->with(['diningTable', 'branch'])
                ->where(function($query) use ($possibleFormats, $normalizedDigits) {
                    // Try exact matches first
                    $query->whereIn('whatsapp_number', $possibleFormats);
                    
                    // Also try matching by extracting digits from DB values (for cases with spaces, dashes, etc.)
                    if ($normalizedDigits && strlen($normalizedDigits) >= 9) {
                        // MySQL REGEXP_REPLACE (MySQL 8.0+) - fallback to simpler comparison if not available
                        $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(whatsapp_number, ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", ['%' . $normalizedDigits . '%']);
                    }
                })
                ->where('order_datetime', '>=', $twoHoursAgo)
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', '')
                ->orderBy('order_datetime', 'desc')
                ->get();

            // Format orders for response
            $formattedOrders = $orders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_serial_no' => $order->order_serial_no,
                    'order_datetime' => \App\Libraries\AppLibrary::datetime($order->order_datetime),
                    'total_currency_price' => \App\Libraries\AppLibrary::currencyAmountFormat($order->total),
                    'branch_id' => $order->branch_id,
                    'dining_table_id' => $order->dining_table_id,
                    'dining_table_slug' => $order->diningTable?->slug,
                    'is_table_order' => $order->dining_table_id !== null,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $formattedOrders,
                'debug' => [
                    'normalized' => $normalized,
                    'possible_formats' => array_values($possibleFormats),
                    'two_hours_ago' => $twoHoursAgo->toDateTimeString(),
                    'orders_found' => $orders->count()
                ]
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}