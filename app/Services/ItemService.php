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
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Item::with('media', 'category', 'tax')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
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

    public function simpleList(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Item::with('media', 'category', 'offer', 'variations.itemAttribute', 'extras', 'addons')
                ->withCount('orders')
                ->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "item_category_id") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
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
