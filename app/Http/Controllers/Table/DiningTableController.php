<?php

namespace App\Http\Controllers\Table;


use App\Http\Controllers\Controller;
use App\Models\FrontendDiningTable;
use Exception;
use App\Services\DiningTableService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\DiningTableResource;
use App\Models\DiningTable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class DiningTableController extends Controller
{
    private DiningTableService $diningTableService;

    public function __construct(DiningTableService $diningTable)
    {
        $this->diningTableService = $diningTable;
    }

    public function index(PaginateRequest $request) : \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return DiningTableResource::collection($this->diningTableService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(string $slug): DiningTableResource|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $raw = urldecode($slug);
            $cacheKey = 'table:dining-table:' . md5(mb_strtolower($raw));

            try {
                if (Cache::has($cacheKey)) {
                    $payload = Cache::get($cacheKey);
                    if ($payload === null) {
                        return response(['success' => false, 'message' => 'The specified URL cannot be found.'], 404);
                    }

                    return response()
                        ->json($payload)
                        ->header('Cache-Control', 'public, max-age=300');
                }
            } catch (Throwable $e) {
                // continue without cache
            }

            $table = $this->resolveTable($raw);
            if (!$table) {
                try {
                    Cache::put($cacheKey, null, 60);
                } catch (Throwable $e) {
                }

                return response(['success' => false, 'message' => 'The specified URL cannot be found.'], 404);
            }

            $resource = new DiningTableResource($table);

            try {
                $payload = $resource->response()->getData(true);
                Cache::put($cacheKey, $payload, 300);

                return response()
                    ->json($payload)
                    ->header('Cache-Control', 'public, max-age=300');
            } catch (Throwable $e) {
                return $resource;
            }
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    private function resolveTable(string $raw): ?FrontendDiningTable
    {
        $slugified = Str::slug($raw);
        // Handle slugs where numbers are attached to words (e.g. "masa4" vs "masa-4")
        $spacedAlphaNumeric = preg_replace('/([\pL])([0-9]+)/u', '$1 $2', $raw);
        $slugifiedAlphaNumeric = Str::slug($spacedAlphaNumeric);

        $candidates = array_values(array_unique(array_filter([
            $raw,
            $slugified,
            $slugifiedAlphaNumeric,
            // Common transliteration/typo variants we've seen in production data/QR codes:
            str_replace('filiali', 'filial', $raw),
            str_replace('filiali', 'filial', $slugified),
            str_replace('filiali', 'filial', $slugifiedAlphaNumeric),
            str_replace('filial', 'filiali', $raw),
            str_replace('filial', 'filiali', $slugified),
            str_replace('filial', 'filiali', $slugifiedAlphaNumeric),
        ])));

        return FrontendDiningTable::query()
            ->whereIn('slug', $candidates)
            ->orWhere('name', $raw)
            ->first();
    }
}
