<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Item;
use App\Models\User;
use App\Models\Order;
use App\Models\OnlineUser;
use App\Enums\CampaignType;
use App\Enums\Status as AppStatus;
use App\Enums\TaxType;
use App\Models\Address;
use App\Enums\OrderType;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\OrderAddress;
use Illuminate\Http\Request;
use App\Libraries\AppLibrary;
use App\Models\FrontendOrder;
use App\Models\PaymentGateway;
use App\Events\SendOrderGotSms;
use App\Events\SendOrderGotMail;
use App\Events\SendOrderGotPush;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PosOrderRequest;
use App\Http\Requests\TableOrderRequest;
use App\Libraries\QueryExceptionLibrary;
use Dipokhalder\Settings\Facades\Settings;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Requests\TableOrderTokenRequest;
use App\Services\OnlineUserService;
use App\Support\WhatsAppNormalizer;
use App\Traits\DefaultAccessModelTrait;

class OrderService
{
    use DefaultAccessModelTrait;
    
    public object $order;
    protected array $orderFilter = [
        'order_serial_no',
        'user_id',
        'branch_id',
        'total',
        'order_type',
        'order_datetime',
        'payment_method',
        'payment_status',
        'status',
        'delivery_boy_id',
        'source'
    ];

    protected array $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            // Get the selected branch for strict filtering
            $selectedBranchId = $this->branch();
            Log::info('SalesReport list - Selected Branch ID: ' . $selectedBranchId . ', User ID: ' . Auth::id());
            
            return Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('branch_id', '=', $selectedBranchId)
                ->with('transaction', 'orderItems', 'branch', 'user', 'diningTable', 'takeawayType', 'driver')
                ->where(function ($query) use ($requests) {
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = Date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = Date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('order_datetime', '>=', $first_date)->whereDate(
                        'order_datetime',
                        '<=',
                        $last_date
                    );
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        if ($key === "status") {
                            $query->where($key, (int)$request);
                        } else if ($key === 'payment_method') {
                            if ((int)$request > 0) {
                                if ((int)$request === 1) {
                                    $query->where('payment_method', 1)->where('pos_payment_method', null)->whereDoesntHave('transaction');
                                } else {
                                    $paymentGateway = PaymentGateway::findOrFail((int)$request);
                                    $query->whereHas('transaction', function ($q) use ($paymentGateway) {
                                        $q->where('payment_method', $paymentGateway->slug);
                                    });
                                }
                            } else {
                                $query->where('pos_payment_method', abs((int)$request));
                            }
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
                            }
                        }
                    }
                }

                // Add condition for "exceptSource"
                if (isset($requests['exceptSource'])) {
                    $query->where('source', '!=', $requests['exceptSource']);
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function userOrder(PaginateRequest $request, User $user)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::with('transaction', 'orderItems', 'branch', 'user')->where('order_type', "!=", OrderType::POS)->where(function ($query) use ($requests, $user) {
                $query->where('user_id', $user->id);
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('status', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function deliveredOrder(PaginateRequest $request, User $user)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            return Order::where('delivery_boy_id', $user->id)->where('order_type', "!=", OrderType::POS)->where(
                function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->orderFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                        if (in_array($key, $this->exceptFilter)) {
                            $explodes = explode('|', $request);
                            if (is_array($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('status', '!=', $explode);
                                }
                            }
                        }
                    }
                }
            )->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function myOrderStore(OrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $this->order = Order::create(
                    $request->validated() + [
                        'user_id'          => Auth::user()->id,
                        'status'           => OrderStatus::PENDING,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('site')->get('site_food_preparation_time')
                    ]
                );

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                // If redeem is requested and available, add free item line with 0 price
                if (!empty($this->order->campaign_redeem_free_item_id)) {
                    $freeItem = Item::find($this->order->campaign_redeem_free_item_id);
                    if ($freeItem) {
                        OrderItem::create([
                            'order_id'             => $this->order->id,
                            'branch_id'            => $this->order->branch_id,
                            'item_id'              => $freeItem->id,
                            'quantity'             => 1,
                            'discount'             => 0,
                            'tax_name'             => null,
                            'tax_rate'             => 0,
                            'tax_type'             => TaxType::FIXED,
                            'tax_amount'           => 0,
                            'price'                => 0,
                            'item_variations'      => json_encode([]),
                            'item_extras'          => json_encode([]),
                            'instruction'          => 'Campaign free item',
                            'item_variation_total' => 0,
                            'item_extra_total'     => 0,
                            'total_price'          => 0,
                        ]);
                    }
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                $this->order->save();

                if ($request->address_id) {
                    $address = Address::find($request->address_id);
                    if ($address) {
                        OrderAddress::create([
                            'order_id'  => $this->order->id,
                            'user_id'   => Auth::user()->id,
                            'label'     => $address->label,
                            'address'   => $address->address,
                            'apartment' => $address->apartment,
                            'latitude'  => $address->latitude,
                            'longitude' => $address->longitude
                        ]);
                    }
                }
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function posOrderUpdate(PosOrderRequest $request, Order $order): object
    {
        try {
            DB::transaction(function () use ($request, $order) {
                $validatedData = $request->validated();
                
                // Keep the original order_serial_no
                $orderSerialNo = $order->order_serial_no;
                
                // Update order
                $order->update(
                    $validatedData + [
                        'user_id'          => $request->customer_id,
                        'token'            => $request->token,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                    ]
                );
                
                // Restore order_serial_no (in case it was changed)
                $order->order_serial_no = $orderSerialNo;
                
                // Delete old order items
                OrderItem::where('order_id', $order->id)->delete();
                
                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $order->total_tax = $totalTax;
                $currentTime = Carbon::now();
                $endTime = $currentTime->copy()->addMinutes((int) Settings::group('site')->get('site_food_preparation_time'));
                $start = $currentTime->format('H:i');
                $end = $endTime->format('H:i');
                $order->delivery_time = "$start - $end";
                $order->save();
                
                $this->order = $order;
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function posOrderStore(PosOrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                $validatedData = $request->validated();

                $this->order = Order::create(
                    $validatedData + [
                        'user_id'          => $request->customer_id,
                        'status'           => OrderStatus::ACCEPT,
                        'token'            => $request->token,
                        'payment_status'   => PaymentStatus::PAID,
                        'order_datetime'   => date('Y-m-d H:i:s'),
                        'preparation_time' => Settings::group('order_setup')->get('order_setup_food_preparation_time')
                    ]
                );

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $items        = Item::get()->pluck('tax_id', 'id');
                $taxes        = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }


                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                $currentTime = Carbon::now();
                $endTime = $currentTime->copy()->addMinutes((int) Settings::group('site')->get('site_food_preparation_time'));
                $start = $currentTime->format('H:i');
                $end = $endTime->format('H:i');
                $this->order->delivery_time   = "$start - $end";
                $this->order->save();
            });
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function tableOrderStore(TableOrderRequest $request): object
    {
        try {
            DB::transaction(function () use ($request) {
                // Security: Validate customer exists and use fallback
                $customer = User::find($request->customer_id);
                if (!$customer) {
                    $customer = User::where('username', 'default-customer')->first();
                }
                if (!$customer) {
                    throw new Exception("Default customer not found. Please create/restore a customer user with username 'default-customer' (Walking Customer).", 422);
                }
                
                // Security: Validate branch exists and is active
                $branch = \App\Models\Branch::where('id', $request->branch_id)
                    ->where('status', \App\Enums\Status::ACTIVE)
                    ->first();
                if (!$branch) {
                    throw new Exception("Selected branch is not available for orders at this time.", 422);
                }
                
                // Calculate distance once (reused for both validation and saving)
                $calculatedDistance = null;
                if ($request->location_url) {
                    $calculatedDistance = $this->calculateDistanceFromLocation($request->location_url, $branch->id);
                    
                    // Security: Validate delivery radius if branch has max_delivery_radius set
                    if ($branch->max_delivery_radius && $calculatedDistance !== null && $calculatedDistance > $branch->max_delivery_radius) {
                        throw new Exception(
                            "Your delivery location is outside our service radius. Maximum delivery distance for this branch is {$branch->max_delivery_radius} km.",
                            422
                        );
                    }
                }

                $validatedData = $request->validated();

                // Normalize whatsapp_number early so we can reliably link OnlineUser + Campaign.
                $normalizedWhatsApp = null;
                if (!empty($validatedData['whatsapp_number'])) {
                    $normalized = WhatsAppNormalizer::normalize($validatedData['whatsapp_number']);
                    $normalizedWhatsApp = $normalized !== '' ? $normalized : null;
                    if ($normalizedWhatsApp) {
                        $validatedData['whatsapp_number'] = $normalizedWhatsApp;
                    }
                }
                
                // Ensure pickup_option is included if present in request
                $pickupOption = $request->input('pickup_option');
                if (!empty($pickupOption) && is_string($pickupOption)) {
                    $validatedData['pickup_option'] = $pickupOption;
                } elseif ($pickupOption !== null) {
                    $validatedData['pickup_option'] = $pickupOption;
                }

                // Check if pickup_option column exists in database before including it
                // This prevents errors if migration hasn't been run yet
                $orderData = $validatedData + [
                    'user_id'          => $customer->id,
                    'status'           => OrderStatus::PENDING,
                    'order_datetime'   => date('Y-m-d H:i:s'),
                    'preparation_time' => Settings::group('site')->get('site_food_preparation_time')
                ];

                /**
                 * Campaign application (online checkout only)
                 * - One campaign at a time (from online_users.campaign_id)
                 * - Percentage campaigns auto-apply discount
                 * - Item campaigns allow user-controlled redeem (campaign_redeem=true)
                 */
                $campaignRedeemRequested = (bool) ($request->input('campaign_redeem') ?? false);
                if ($normalizedWhatsApp) {
                    $onlineUser = OnlineUser::withoutGlobalScopes()
                        ->where('branch_id', $request->branch_id)
                        ->where('whatsapp', $normalizedWhatsApp)
                        ->whereNotNull('campaign_id')
                        ->with(['campaign.freeItem'])
                        ->first();

                    if ($onlineUser && $onlineUser->campaign) {
                        $campaign = $onlineUser->campaign;

                        // Validate campaign is active + within time window (if set)
                        $isActive = ((int) $campaign->status === (int) AppStatus::ACTIVE);
                        $now = now();
                        $inStart = (!$campaign->start_date) || $campaign->start_date <= $now;
                        $inEnd = (!$campaign->end_date) || $campaign->end_date >= $now;

                        if ($isActive && $inStart && $inEnd) {
                            $orderData['campaign_id'] = $campaign->id;
                            $orderData['campaign_snapshot'] = [
                                'id' => $campaign->id,
                                'name' => $campaign->name,
                                'type' => (int) $campaign->type,
                                'discount_value' => (float) $campaign->discount_value,
                                'required_purchases' => (int) ($campaign->required_purchases ?? 0),
                                'free_item_id' => $campaign->free_item_id,
                                'start_date' => $campaign->start_date?->toDateTimeString(),
                                'end_date' => $campaign->end_date?->toDateTimeString(),
                            ];

                            // Percentage campaign: auto-apply discount on subtotal
                            if ((int) $campaign->type === (int) CampaignType::PERCENTAGE) {
                                $percent = (float) $campaign->discount_value;
                                if ($percent > 0) {
                                    $campaignDiscount = round(((float) $request->subtotal) * ($percent / 100), 6);
                                    $campaignDiscount = max(0, min((float) $request->subtotal, $campaignDiscount));

                                    $orderData['campaign_discount'] = $campaignDiscount;
                                    $orderData['discount'] = ((float) ($orderData['discount'] ?? 0)) + $campaignDiscount;
                                    $orderData['total'] = max(
                                        0,
                                        ((float) $request->subtotal) - ((float) $orderData['discount']) + ((float) ($orderData['delivery_charge'] ?? 0))
                                    );
                                }
                            }

                            // Item campaign: redeem free item if requested and reward is available
                            if ((int) $campaign->type === (int) CampaignType::ITEM && $campaignRedeemRequested) {
                                $requiredPurchases = (int) ($campaign->required_purchases ?? 0);
                                $requiredPurchases = $requiredPurchases > 0 ? $requiredPurchases : 8;

                                // Count completed orders in campaign window
                                $ordersQuery = Order::withoutGlobalScopes(\App\Models\Scopes\BranchScope::class)
                                    ->where('branch_id', $request->branch_id)
                                    ->where('whatsapp_number', $normalizedWhatsApp)
                                    ->whereIn('status', [OrderStatus::DELIVERED]);

                                if ($campaign->start_date) {
                                    $ordersQuery->where('created_at', '>=', $campaign->start_date);
                                }
                                if ($campaign->end_date) {
                                    $ordersQuery->where('created_at', '<=', $campaign->end_date);
                                }

                                $completedCount = (int) $ordersQuery->count();
                                $earnedRewards = (int) floor($completedCount / $requiredPurchases);

                                // Already redeemed rewards (stored on orders)
                                $redeemedCount = (int) Order::withoutGlobalScopes(\App\Models\Scopes\BranchScope::class)
                                    ->where('branch_id', $request->branch_id)
                                    ->where('campaign_id', $campaign->id)
                                    ->where('whatsapp_number', $normalizedWhatsApp)
                                    ->whereNotNull('campaign_redeem_free_item_id')
                                    ->whereIn('status', [OrderStatus::DELIVERED])
                                    ->count();

                                $available = max(0, $earnedRewards - $redeemedCount);

                                if ($available > 0 && $campaign->free_item_id) {
                                    $orderData['campaign_redeem_free_item_id'] = (int) $campaign->free_item_id;
                                }
                            }
                        }
                    }
                }
                
                // Only include pickup_option if column exists (cached check)
                static $pickupColumnExists = null;
                if ($pickupColumnExists === null) {
                    $pickupColumnExists = Schema::hasColumn('orders', 'pickup_option');
                }
                if (isset($orderData['pickup_option']) && !$pickupColumnExists) {
                    unset($orderData['pickup_option']);
                }

                $this->order = FrontendOrder::create($orderData);

                $i            = 0;
                $totalTax     = 0;
                $itemsArray   = [];
                $requestItems = json_decode($request->items);
                $requestItemIds = collect($requestItems)->pluck('item_id')->toArray();
                
                // Security: Server-side price validation - fetch only needed items
                $calculatedSubtotal = 0;
                $dbItems = Item::whereIn('id', $requestItemIds)->get()->keyBy('id');
                $items = $dbItems->pluck('tax_id', 'id');
                $taxes = AppLibrary::pluck(Tax::get(), 'obj', 'id');

                if (!blank($requestItems)) {
                    foreach ($requestItems as $item) {
                        // Security: Validate item exists and get real price from database
                        if (!isset($dbItems[$item->item_id])) {
                            throw new Exception("Invalid item in order: Item ID {$item->item_id} does not exist.", 422);
                        }
                        $dbItem = $dbItems[$item->item_id];
                        
                        // Calculate expected price (base + variations + extras)
                        $expectedItemPrice = $dbItem->price;
                        $expectedVariationTotal = $item->item_variation_total ?? 0;
                        $expectedExtraTotal = $item->item_extra_total ?? 0;
                        $expectedTotalPrice = ($expectedItemPrice + $expectedVariationTotal + $expectedExtraTotal) * $item->quantity;
                        
                        // Allow small tolerance for floating-point rounding (0.02 per item)
                        $priceTolerance = 0.02 * $item->quantity;
                        if (abs($expectedTotalPrice - $item->total_price) > $priceTolerance) {
                            throw new Exception("Price validation failed for item: {$dbItem->name}. Please refresh and try again.", 422);
                        }
                        
                        $calculatedSubtotal += $item->total_price;
                        
                        $taxId          = isset($items[$item->item_id]) ? $items[$item->item_id] : 0;
                        $taxName        = isset($taxes[$taxId]) ? $taxes[$taxId]->name : null;
                        $taxRate        = isset($taxes[$taxId]) ? $taxes[$taxId]->tax_rate : 0;
                        $taxType        = isset($taxes[$taxId]) ? $taxes[$taxId]->type : TaxType::FIXED;
                        $taxPrice       = $taxType === TaxType::FIXED ? $taxRate : ($item->total_price * $taxRate) / 100;
                        $itemsArray[$i] = [
                            'order_id'             => $this->order->id,
                            'branch_id'            => $item->branch_id,
                            'item_id'              => $item->item_id,
                            'quantity'             => $item->quantity,
                            'discount'             => (float)$item->discount,
                            'tax_name'             => $taxName,
                            'tax_rate'             => $taxRate,
                            'tax_type'             => $taxType,
                            'tax_amount'           => $taxPrice,
                            'price'                => $item->item_price,
                            'item_variations'      => json_encode($item->item_variations),
                            'item_extras'          => json_encode($item->item_extras),
                            'instruction'          => $item->instruction,
                            'item_variation_total' => $item->item_variation_total,
                            'item_extra_total'     => $item->item_extra_total,
                            'total_price'          => $item->total_price,
                        ];
                        $totalTax       = $totalTax + $taxPrice;
                        $i++;
                    }
                }
                
                // Security: Validate subtotal matches calculated items total
                $subtotalTolerance = 0.10; // Allow 10 cents tolerance for entire order
                if (abs($calculatedSubtotal - $request->subtotal) > $subtotalTolerance) {
                    throw new Exception("Order total validation failed. Please refresh and try again.", 422);
                }

                if (!blank($itemsArray)) {
                    OrderItem::insert($itemsArray);
                }

                $this->order->order_serial_no = date('dmy') . $this->order->id;
                $this->order->total_tax       = $totalTax;
                
                // Save pre-calculated distance (calculated during validation)
                if ($calculatedDistance !== null) {
                    $this->order->distance = $calculatedDistance;
                }
                
                $currentTime = Carbon::now();
                $endTime = $currentTime->copy()->addMinutes((int) Settings::group('site')->get('site_food_preparation_time'));
                $start = $currentTime->format('H:i');
                $end = $endTime->format('H:i');
                $this->order->delivery_time   = "$start - $end";
                $this->order->save();
            });

            // Persist WhatsApp/location into online_users for quick lookup in Admin → Users → Online Users.
            // Includes dining-table orders as well if whatsapp_number is provided.
            app(OnlineUserService::class)->upsertFromOrder($this->order);
            
            // Dispatch notifications after transaction commits to prevent checkout failure when notification services fail.
            // IMPORTANT: Some providers (e.g., Twilio) can throw TypeError (which is not an Exception), so we catch Throwable.
            try {
                SendOrderGotMail::dispatch(['order_id' => $this->order->id]);
                SendOrderGotSms::dispatch(['order_id' => $this->order->id]);
                SendOrderGotPush::dispatch(['order_id' => $this->order->id]);
            } catch (\Throwable $e) {
                Log::warning("Order notification failed but order was created successfully: " . $e->getMessage());
            }
            
            return $this->order;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Order $order, $auth = false): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    return $order;
                } else {
                    return [];
                }
            } else {
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function orderDetails(User $user, Order $order): Order|array
    {
        try {
            if ($order->user_id == $user->id) {
                return $order->load('transaction', 'orderItems', 'branch', 'user');
            } else {
                return [];
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function changeStatus(Order $order, $auth = false, OrderStatusRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    if ($request->reason) {
                        $order->reason = $request->reason;
                    }

                    if ($request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED) {
                        if ($order->transaction) {
                            app(PaymentService::class)->cashBack(
                                $order,
                                'credit',
                                rand(111111111111111, 99999999999999)
                            );
                        }
                    }
                    $order->status = $request->status;
                    $order->save();
                }
            } else {
                if ($request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED) {
                    $request->validate([
                        'reason' => 'required|max:700',
                    ]);

                    if ($request->reason) {
                        $order->reason = $request->reason;
                    }

                    if ($order->transaction) {
                        app(PaymentService::class)->cashBack(
                            $order,
                            'credit',
                            rand(111111111111111, 99999999999999)
                        );
                    }
                }
                $order->status = $request->status;
                $order->save();
            }
            return $order;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changePaymentStatus(Order $order, $auth = false, PaymentStatusRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $order->payment_status = $request->payment_status;
                    $order->save();
                    return $order;
                } else {
                    return [];
                }
            } else {
                $order->payment_status = $request->payment_status;
                $order->save();
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }


    public function tokenCreate(Order $order, $auth = false, TableOrderTokenRequest $request): Order|array
    {
        try {
            if ($auth) {
                if ($order->user_id == Auth::user()->id) {
                    $order->token = $request->token;
                    $order->save();
                    return $order;
                } else {
                    return [];
                }
            } else {
                $order->token = $request->token;
                $order->save();
                return $order;
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                $order->address()?->delete();
                $order->orderItems()?->delete();
                $order->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }


    public function salesReportOverview(Request $request)
    {
        try {
            $requests    = $request->all();
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_by') ?? 'desc';

            // Get the selected branch for strict filtering
            $selectedBranchId = $this->branch();
            Log::info('SalesReport overview - Selected Branch ID: ' . $selectedBranchId . ', User ID: ' . Auth::id());
            
            $orders = Order::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('branch_id', '=', $selectedBranchId)
                ->with('transaction', 'orderItems')
                ->where(function ($query) use ($requests) {
                if (isset($requests['from_date']) && isset($requests['to_date'])) {
                    $first_date = Date('Y-m-d', strtotime($requests['from_date']));
                    $last_date  = Date('Y-m-d', strtotime($requests['to_date']));
                    $query->whereDate('order_datetime', '>=', $first_date)->whereDate(
                        'order_datetime',
                        '<=',
                        $last_date
                    );
                }
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->orderFilter)) {
                        if ($key === "status") {
                            $query->where($key, (int)$request);
                        } else if ($key === 'payment_method') {
                            if ((int)$request > 0) {
                                if ((int)$request === 1) {
                                    $query->where('payment_method', 1)->where('pos_payment_method', null)->whereDoesntHave('transaction');
                                } else {
                                    $paymentGateway = PaymentGateway::findOrFail((int)$request);
                                    $query->whereHas('transaction', function ($q) use ($paymentGateway) {
                                        $q->where('payment_method', $paymentGateway->slug);
                                    });
                                }
                            } else {
                                $query->where('pos_payment_method', abs((int)$request));
                            }
                        } else if ($key === 'source') {
                            $query->where($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('order_type', '!=', $explode);
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->get();
            $salesReportArray = [];

            $salesReportArray['total_orders'] = $orders->count();
            $salesReportArray['total_earnings'] = AppLibrary::currencyAmountFormat($orders->sum('total'));
            $salesReportArray['total_discounts'] = AppLibrary::currencyAmountFormat($orders->sum('discount'));
            $salesReportArray['total_delivery_charges'] = AppLibrary::currencyAmountFormat($orders->sum('delivery_charge'));

            return $salesReportArray;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * Calculate distance from location URL to branch using Haversine formula
     * 
     * @param string $locationUrl Google Maps URL format: https://www.google.com/maps?q=lat,lng
     * @param int $branchId Branch ID
     * @return float|null Distance in kilometers, or null if calculation fails
     */
    protected function calculateDistanceFromLocation(string $locationUrl, int $branchId): ?float
    {
        try {
            // Extract coordinates from location URL
            if (!preg_match('/q=([\d.-]+),([\d.-]+)/', $locationUrl, $matches)) {
                return null;
            }

            $deliveryLat = (float) $matches[1];
            $deliveryLng = (float) $matches[2];

            // Get branch coordinates
            $branch = \App\Models\Branch::find($branchId);
            if (!$branch || !$branch->latitude || !$branch->longitude) {
                return null;
            }

            $branchLat = (float) $branch->latitude;
            $branchLng = (float) $branch->longitude;

            // Haversine formula
            $R = 6371; // Earth radius in kilometers
            $dLat = deg2rad($deliveryLat - $branchLat);
            $dLng = deg2rad($deliveryLng - $branchLng);
            
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($branchLat)) * cos(deg2rad($deliveryLat)) *
                 sin($dLng / 2) * sin($dLng / 2);
            
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $R * $c;

            return round($distance, 2);
        } catch (\Exception $e) {
            \Log::warning('Distance calculation failed: ' . $e->getMessage());
            return null;
        }
    }
}
