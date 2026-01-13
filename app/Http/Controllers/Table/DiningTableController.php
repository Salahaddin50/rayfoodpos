<?php

namespace App\Http\Controllers\Table;


use App\Http\Controllers\Controller;
use App\Models\FrontendDiningTable;
use Exception;
use App\Services\DiningTableService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\DiningTableResource;
use App\Models\DiningTable;
use Illuminate\Support\Str;

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

    public function show(string $slug): DiningTableResource|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $raw = urldecode($slug);
            $slugified = Str::slug($raw);
            // Handle slugs where numbers are attached to words (e.g. "masa4" vs "masa-4")
            $spacedAlphaNumeric = preg_replace('/([\pL])([0-9]+)/u', '$1 $2', $raw);
            $slugifiedAlphaNumeric = Str::slug($spacedAlphaNumeric);

            $table = FrontendDiningTable::query()
                ->where('slug', $raw)
                ->orWhere('slug', $slugified)
                ->orWhere('slug', $slugifiedAlphaNumeric)
                ->orWhere('name', $raw)
                ->first();

            if (!$table) {
                return response(['success' => false, 'message' => 'The specified URL cannot be found.'], 404);
            }

            return new DiningTableResource($table);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
