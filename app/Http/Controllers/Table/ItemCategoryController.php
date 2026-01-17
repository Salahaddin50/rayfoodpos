<?php

namespace App\Http\Controllers\Table;


use App\Enums\Status;
use App\Http\Resources\ItemCategoryMenuResource;
use App\Models\ItemCategory;
use Exception;
use App\Http\Controllers\Controller;
use App\Services\ItemCategoryService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ItemCategoryResource;
use Illuminate\Support\Facades\Cache;
use Throwable;


class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private ItemCategoryService $itemCategoryService;

    public function __construct(ItemCategoryService $itemCategory)
    {
        $this->itemCategoryService = $itemCategory;
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $start = microtime(true);

            // Cache the menu category list (used by /menu and /online). Also include `sort` so the frontend
            // can sort items client-side without an expensive DB join.
            if ((int) $request->get('paginate', 0) === 0) {
                $cacheKey = 'table:item-category:' . md5($request->fullUrl());
                try {
                    $cacheHit = Cache::has($cacheKey);

                    $payload = Cache::remember($cacheKey, 300, function () use ($request) {
                        $itemCategoryArray = [];
                        $itemCategories    = $this->itemCategoryService->list($request);
                        $allCategory[]     = [
                            'id'     => 0,
                            'name'   => trans('all.label.all_items'),
                            'slug'   => 'all-items',
                            'description' => "",
                            'status' => Status::ACTIVE,
                            'sort'   => 0,
                            'thumb'  => asset("images/default/all-category.png"),
                            'cover'  => asset("images/default/all-category.png")
                        ];
                        foreach ($itemCategories as $itemCategory) {
                            $itemCategoryArray[] = [
                                'id'          => $itemCategory->id,
                                'name'        => $itemCategory->name,
                                'slug'        => $itemCategory->slug,
                                'description' => $itemCategory->description === null ? '' : $itemCategory->description,
                                'status'      => $itemCategory->status,
                                'sort'        => (int) ($itemCategory->sort ?? 0),
                                'thumb'       => $itemCategory->thumb,
                                'cover'       => $itemCategory->cover
                            ];
                        }
                        return ['data' => array_merge($allCategory, $itemCategoryArray)];
                    });

                    $durMs = (microtime(true) - $start) * 1000;
                    return response()
                        ->json($payload)
                        ->header('Cache-Control', 'public, max-age=300')
                        ->header('Server-Timing', 'app;dur=' . round($durMs, 2) . ', cache;desc=' . ($cacheHit ? 'hit' : 'miss'));
                } catch (Throwable $e) {
                    // Fail-safe: if cache isn't available, keep endpoint working.
                    $itemCategoryArray = [];
                    $itemCategories    = $this->itemCategoryService->list($request);
                    $allCategory[]     = [
                        'id'     => 0,
                        'name'   => trans('all.label.all_items'),
                        'slug'   => 'all-items',
                        'description' => "",
                        'status' => Status::ACTIVE,
                        'sort'   => 0,
                        'thumb'  => asset("images/default/all-category.png"),
                        'cover'  => asset("images/default/all-category.png")
                    ];
                    foreach ($itemCategories as $itemCategory) {
                        $itemCategoryArray[] = [
                            'id'          => $itemCategory->id,
                            'name'        => $itemCategory->name,
                            'slug'        => $itemCategory->slug,
                            'description' => $itemCategory->description === null ? '' : $itemCategory->description,
                            'status'      => $itemCategory->status,
                            'sort'        => (int) ($itemCategory->sort ?? 0),
                            'thumb'       => $itemCategory->thumb,
                            'cover'       => $itemCategory->cover
                        ];
                    }

                    $durMs = (microtime(true) - $start) * 1000;
                    return response()
                        ->json(['data' => array_merge($allCategory, $itemCategoryArray)])
                        ->header('Server-Timing', 'app;dur=' . round($durMs, 2) . ', cache;desc=disabled');
                }
            }

            $itemCategoryArray = [];
            $itemCategories    = $this->itemCategoryService->list($request);
            $allCategory[]     = [
                'id'     => 0,
                'name'   => trans('all.label.all_items'),
                'slug'   => 'all-items',
                'description' => "",
                'status' => Status::ACTIVE,
                'sort'   => 0,
                'thumb'  => asset("images/default/all-category.png"),
                'cover'  => asset("images/default/all-category.png")
            ];
            foreach ($itemCategories as $itemCategory) {
                $itemCategoryArray[] = [
                    'id'          => $itemCategory->id,
                    'name'        => $itemCategory->name,
                    'slug'        => $itemCategory->slug,
                    'description' => $itemCategory->description === null ? '' : $itemCategory->description,
                    'status'      => $itemCategory->status,
                    'sort'        => (int) ($itemCategory->sort ?? 0),
                    'thumb'       => $itemCategory->thumb,
                    'cover'       => $itemCategory->cover
                ];
            }
            $durMs = (microtime(true) - $start) * 1000;
            return response(['data' => array_merge($allCategory, $itemCategoryArray)])
                ->header('Server-Timing', 'app;dur=' . round($durMs, 2));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(ItemCategory $itemCategory): \Illuminate\Http\Response|ItemCategoryMenuResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new ItemCategoryMenuResource($this->itemCategoryService->show($itemCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
