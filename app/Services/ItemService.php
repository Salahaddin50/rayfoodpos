<?php

namespace App\Services;


use Exception;
use App\Enums\Ask;
use App\Models\Item;
use App\Enums\Status;
use Illuminate\Support\Str;
use App\Models\ItemVariation;
use App\Http\Requests\ItemRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\ChangeImageRequest;

class ItemService
{
    public $item;
    protected $itemFilter = [
        'name',
        'slug',
        'item_category_id',
        'price',
        'is_featured',
        'item_type',
        'tax_id',
        'status',
        'order',
        'description',
        'except'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $branchId    = $request->get('branch_id');
            $statusFilter = $requests['status'] ?? null;
            unset($requests['branch_id'], $requests['status']);
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            // Include `offer` to avoid N+1 queries and to keep `item.offer` available for list UIs.
            $query = Item::with('media', 'category', 'tax', 'offer')
                ->when($branchId, function ($q) use ($branchId) {
                    $q->with(['branchItemStatuses' => function ($sub) use ($branchId) {
                        $sub->where('branch_id', $branchId);
                    }]);
                })
                ->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('items.id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id") {
                                $query->where('items.item_category_id', $request);
                            } else {
                                $query->where('items.' . $key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->when($statusFilter !== null, function ($q) use ($statusFilter, $branchId) {
                // Apply status filter using branch override when a branch is selected.
                $q->where(function ($sub) use ($statusFilter, $branchId) {
                    if ($branchId) {
                        $sub->whereHas('branchItemStatuses', function ($bis) use ($branchId, $statusFilter) {
                            $bis->where('branch_id', $branchId)->where('status', $statusFilter);
                        })->orWhere(function ($fallback) use ($branchId, $statusFilter) {
                            // No override for this branch, fall back to global item status
                            $fallback->whereDoesntHave('branchItemStatuses', function ($bis) use ($branchId) {
                                $bis->where('branch_id', $branchId);
                            })->where('items.status', $statusFilter);
                        });
                    } else {
                        $sub->where('items.status', $statusFilter);
                    }
                });
            });

            // Special ordering: allow ordering items by their category's sort value
            // (used by POS /menu and Online menu so items appear grouped by category order).
            if ($orderColumn === 'category_sort') {
                $query->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
                    ->select('items.*')
                    ->orderBy('item_categories.sort', $orderType)
                    ->orderBy('items.id', 'asc');
            } else {
                $query->orderBy($orderColumn, $orderType);
            }

            return $query->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function simpleList(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $branchId    = $request->get('branch_id');
            $lite        = (int) $request->get('lite', 0) === 1;
            $statusFilter = $requests['status'] ?? null;
            unset($requests['branch_id'], $requests['status']);
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            // "lite" mode is used by POS menu grids and similar pages:
            // keep the list payload small and avoid loading heavy relations.
            $with = $lite
                ? ['media', 'category', 'tax', 'offer']
                : ['media', 'category', 'tax', 'offer', 'variations.itemAttribute', 'extras', 'addons'];

            $query = Item::with($with)
                ->when(!$lite, function ($q) {
                    $q->withCount('orders');
                })
                ->when($branchId, function ($q) use ($branchId) {
                    $q->with(['branchItemStatuses' => function ($sub) use ($branchId) {
                        $sub->where('branch_id', $branchId);
                    }]);
                })
                // Join categories for category-based sorting
                ->when($orderColumn === 'category_sort', function ($q) {
                    $q->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
                      ->select('items.*');
                })
                ->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('items.id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id") {
                                $query->where('items.' . $key, $request);
                            } else {
                                $query->where('items.' . $key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->when($statusFilter !== null, function ($q) use ($statusFilter, $branchId) {
                // Apply status filter using branch override when a branch is selected.
                $q->where(function ($sub) use ($statusFilter, $branchId) {
                    if ($branchId) {
                        $sub->whereHas('branchItemStatuses', function ($bis) use ($branchId, $statusFilter) {
                            $bis->where('branch_id', $branchId)->where('status', $statusFilter);
                        })->orWhere(function ($fallback) use ($branchId, $statusFilter) {
                            // No override for this branch, fall back to global item status
                            $fallback->whereDoesntHave('branchItemStatuses', function ($bis) use ($branchId) {
                                $bis->where('branch_id', $branchId);
                            })->where('items.status', $statusFilter);
                        });
                    } else {
                        $sub->where('items.status', $statusFilter);
                    }
                });
            });

            // Order by category sort, then item id
            if ($orderColumn === 'category_sort') {
                $query->orderBy('item_categories.sort', 'asc')
                    ->orderBy('items.id', 'asc');
            } else {
                $query->orderBy($orderColumn, $orderType);
            }

            return $query->$method($methodValue);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * Save branch-specific status override for an item.
     *
     * @throws Exception
     */
    public function saveBranchItemStatus(int $branchId, Item $item, int $status): void
    {
        try {
            $existing = DB::table('branch_item_statuses')
                ->where('branch_id', $branchId)
                ->where('item_id', $item->id)
                ->first();
            
            if ($existing) {
                DB::table('branch_item_statuses')
                    ->where('branch_id', $branchId)
                    ->where('item_id', $item->id)
                    ->update([
                        'status' => $status,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('branch_item_statuses')->insert([
                    'branch_id' => $branchId,
                    'item_id' => $item->id,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->item = Item::create($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $this->item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $this->item->variations()->createMany(json_decode($request->variations, true));
                }
            });
            return $this->item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemRequest $request, Item $item): Item
    {
        try {
            DB::transaction(function () use ($request, $item) {
                $item->update($request->validated() + ['slug' => Str::slug($request->name)]);
                if ($request->image) {
                    $item->addMedia($request->image)->toMediaCollection('item');
                }
                if ($request->variations) {
                    $variationIdsArray    = [];
                    $variationDeleteArray = [];
                    $oldVariations        = $item->variations->pluck('id')->toArray();
                    foreach (json_decode($request->variations, true) as $variation) {
                        if (isset($variation['id'])) {
                            $variationIdsArray[] = $variation['id'];
                            ItemVariation::where('id', $variation['id'])->update([
                                'name'             => $variation['name'],
                                'price' => $variation['price'],
                            ]);
                        } else {
                            $item->variations()->create($variation);
                        }
                    }

                    if ($variationIdsArray) {
                        foreach ($oldVariations as $oldVariation) {
                            if (!in_array($oldVariation, $variationIdsArray)) {
                                $variationDeleteArray[] = $oldVariation;
                            }
                        }
                    }

                    if ($variationDeleteArray) {
                        ItemVariation::whereIn('id', $variationDeleteArray)->delete();
                    }
                }
            });
            return Item::find($item->id);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item)
    {
        try {
            DB::transaction(function () use ($item) {
                $item->variations()->delete();
                $item->extras()->delete();
                $item->addons()->delete();
                $item->delete();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Item $item): Item
    {
        try {
            return $item->load('media', 'category', 'tax', 'offer', 'addons', 'variations', 'extras');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function changeImage(ChangeImageRequest $request, Item $item): Item
    {
        try {
            if ($request->image) {
                $item->clearMediaCollection('item');
                $item->addMedia($request->image)->toMediaCollection('item');
            }
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function featuredItems()
    {
        try {
            return Item::with('media','category','offer')->where(['is_featured' => Ask::YES, 'status' => Status::ACTIVE])->inRandomOrder()->limit(8)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function mostPopularItems()
    {
        try {
            return Item::with('media', 'category','offer')->withCount('orders')->where(['status' => Status::ACTIVE])->orderBy('orders_count', 'desc')->limit(6)->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function itemReport(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';

            // Simpler query that groups by item ID and a hash of variations/extras
            // This avoids complex expressions in GROUP BY
            $query = DB::table('order_items')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
                ->select(
                    'items.id as item_id',
                    'items.name as item_name',
                    'items.item_type',
                    'item_categories.name as category_name',
                    // Calculate average unit price for this grouping
                    DB::raw('ROUND(AVG(CASE 
                        WHEN order_items.total_price > 0 AND order_items.quantity > 0 
                        THEN order_items.total_price / order_items.quantity 
                        ELSE items.price 
                    END), 2) as unit_price'),
                    // Hash of variations+extras for grouping
                    DB::raw('MD5(CONCAT(
                        COALESCE(order_items.item_variations, \'\'), 
                        \'|\', 
                        COALESCE(order_items.item_extras, \'\')
                    )) as options_key'),
                    DB::raw('MIN(order_items.item_variations) as item_variations'),
                    DB::raw('MIN(order_items.item_extras) as item_extras'),
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(CASE 
                        WHEN order_items.total_price > 0 
                        THEN order_items.total_price 
                        ELSE items.price * order_items.quantity 
                    END) as total_income'),
                    DB::raw('MIN(orders.order_datetime) as first_order_date')
                );

            // Apply date filtering
            if (isset($requests['from_date']) && !empty($requests['from_date']) && 
                isset($requests['to_date']) && !empty($requests['to_date'])) {
                $first_date = date('Y-m-d', strtotime($requests['from_date']));
                $last_date  = date('Y-m-d', strtotime($requests['to_date']));
                $query->whereDate('orders.order_datetime', '>=', $first_date)
                      ->whereDate('orders.order_datetime', '<=', $last_date);
            }

            // Apply filters
            if (isset($requests['name']) && !empty($requests['name'])) {
                $query->where('items.name', 'like', '%' . $requests['name'] . '%');
            }

            if (isset($requests['item_category_id']) && !empty($requests['item_category_id'])) {
                $query->where('items.item_category_id', $requests['item_category_id']);
            }

            if (isset($requests['item_type']) && !empty($requests['item_type'])) {
                $query->where('items.item_type', $requests['item_type']);
            }

            // Simple GROUP BY - just item and options hash
            $query->groupBy(
                'items.id',
                'items.name',
                'items.item_type',
                'items.price',
                'item_categories.name',
                DB::raw('MD5(CONCAT(
                    COALESCE(order_items.item_variations, \'\'), 
                    \'|\', 
                    COALESCE(order_items.item_extras, \'\')
                ))')
            )
            ->orderByDesc('total_income');

            $result = $method === 'paginate' ? $query->paginate($methodValue) : $query->get();
            
            Log::info('ItemReport Success: Returned ' . (is_countable($result) ? count($result) : $result->count()) . ' rows');
            
            return $result;
        } catch (Exception $exception) {
            Log::error('ItemReport Error: ' . $exception->getMessage());
            Log::error('ItemReport Trace: ' . $exception->getTraceAsString());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    public function itemDetails(Item $item)
    {
        return $item->load('media', 'category', 'tax', 'offer', 'addons', 'variations', 'extras');
    }
}
