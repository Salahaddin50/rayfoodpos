<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Requests\PaginateRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Services\LanguageService;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Throwable;

class LanguageController extends Controller
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\JsonResponse | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            if ((int) $request->get('paginate', 0) === 0) {
                $cacheKey = 'frontend:language:list:' . md5($request->fullUrl());
                try {
                    $payload = Cache::remember($cacheKey, 600, function () use ($request) {
                        return LanguageResource::collection($this->languageService->list($request))
                            ->response()
                            ->getData(true);
                    });

                    return response()
                        ->json($payload)
                        ->header('Cache-Control', 'public, max-age=600');
                } catch (Throwable $e) {
                    // fall through
                }
            }

            return LanguageResource::collection($this->languageService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Language $language) : \Illuminate\Http\Response | \Illuminate\Http\JsonResponse | LanguageResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $cacheKey = 'frontend:language:show:' . $language->id;
            try {
                $payload = Cache::remember($cacheKey, 600, function () use ($language) {
                    return (new LanguageResource($this->languageService->show($language)))
                        ->response()
                        ->getData(true);
                });

                return response()
                    ->json($payload)
                    ->header('Cache-Control', 'public, max-age=600');
            } catch (Throwable $e) {
                return new LanguageResource($this->languageService->show($language));
            }
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
