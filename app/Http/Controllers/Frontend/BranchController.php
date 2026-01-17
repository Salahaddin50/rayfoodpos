<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Branch;
use Exception;
use App\Services\BranchService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\BranchResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BranchController extends Controller
{
    public BranchService $branchService;

    public function __construct(BranchService $branch)
    {
        $this->branchService = $branch;
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\JsonResponse | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $start = microtime(true);

            // Cache non-paginated branch lists briefly (used by online branch selector/menu).
            if ((int) $request->get('paginate', 0) === 0) {
                $cacheKey = 'frontend:branch:' . md5($request->fullUrl());
                try {
                    $cacheHit = Cache::has($cacheKey);
                    $payload = Cache::remember($cacheKey, 300, function () use ($request) {
                        return BranchResource::collection($this->branchService->list($request))
                            ->response()
                            ->getData(true);
                    });

                    $durMs = (microtime(true) - $start) * 1000;
                    return response()
                        ->json($payload)
                        ->header('Cache-Control', 'public, max-age=300')
                        ->header('Server-Timing', 'app;dur=' . round($durMs, 2) . ', cache;desc=' . ($cacheHit ? 'hit' : 'miss'));
                } catch (Throwable $e) {
                    $res = BranchResource::collection($this->branchService->list($request));
                    $durMs = (microtime(true) - $start) * 1000;
                    return $res->additional([])->response()->withHeaders([
                        'Server-Timing' => 'app;dur=' . round($durMs, 2) . ', cache;desc=disabled'
                    ]);
                }
            }

            $res = BranchResource::collection($this->branchService->list($request));
            $durMs = (microtime(true) - $start) * 1000;
            return $res->additional([])->response()->withHeaders([
                'Server-Timing' => 'app;dur=' . round($durMs, 2)
            ]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Branch $branch) : BranchResource | \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new BranchResource($this->branchService->show($branch));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
