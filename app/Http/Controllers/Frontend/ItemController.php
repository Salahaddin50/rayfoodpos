<?php

namespace App\Http\Controllers\Frontend;


use Exception;
use App\Models\Item;
use App\Services\ItemService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\NormalItemResource;
use App\Http\Resources\SimpleItemResource;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ItemController extends Controller
{

    public ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index(
        PaginateRequest $request
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $start = microtime(true);

            if ((int) $request->get('lite', 0) === 1) {
                // Lite list is used by menu grids (online/table) to keep payload small.
                // Full item data is fetched via `frontend/item/details/:id` when needed.
                // Biggest perf win without changing UI: cache the heavy "lite + non-paginated" menu list briefly.
                if ((int) $request->get('paginate', 0) === 0) {
                    $cacheKey = 'frontend:item:lite:' . md5($request->fullUrl());
                    try {
                        $cacheHit = Cache::has($cacheKey);
                        $payload = Cache::remember($cacheKey, 60, function () use ($request) {
                            return SimpleItemResource::collection($this->itemService->list($request))
                                ->response()
                                ->getData(true);
                        });

                        $durMs = (microtime(true) - $start) * 1000;
                        return response()
                            ->json($payload)
                            ->header('Cache-Control', 'public, max-age=60')
                            ->header('Server-Timing', 'app;dur=' . round($durMs, 2) . ', cache;desc=' . ($cacheHit ? 'hit' : 'miss'));
                    } catch (Throwable $e) {
                        // Fail-safe: don't break menu loading if cache is unavailable.
                        $res = SimpleItemResource::collection($this->itemService->list($request));
                        $durMs = (microtime(true) - $start) * 1000;
                        return $res->additional([])->response()->withHeaders([
                            'Server-Timing' => 'app;dur=' . round($durMs, 2) . ', cache;desc=disabled'
                        ]);
                    }
                }

                $res = SimpleItemResource::collection($this->itemService->list($request));
                $durMs = (microtime(true) - $start) * 1000;
                return $res->additional([])->response()->withHeaders([
                    'Server-Timing' => 'app;dur=' . round($durMs, 2)
                ]);
            }

            $res = NormalItemResource::collection($this->itemService->list($request));
            $durMs = (microtime(true) - $start) * 1000;
            return $res->additional([])->response()->withHeaders([
                'Server-Timing' => 'app;dur=' . round($durMs, 2)
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function featuredItems(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return NormalItemResource::collection($this->itemService->featuredItems());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function mostPopularItems(): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return NormalItemResource::collection($this->itemService->mostPopularItems());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function itemDetails(Item $item)
    {
        try {
            return new NormalItemResource($this->itemService->itemDetails($item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
