<?php

namespace App\Services;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TakeawayTypeRequest;
use App\Models\TakeawayType;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Libraries\QueryExceptionLibrary;

class TakeawayTypeService
{
    protected array $takeawayTypeFilter = [
        'name',
        'branch_id',
        'status'
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
            $orderColumn = $request->get('order_column') ?? 'sort_order';
            $orderType   = $request->get('order_type') ?? 'asc';

            return TakeawayType::with('branch')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->takeawayTypeFilter)) {
                        if ($key == "branch_id") {
                            $query->where($key, $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
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
    public function store(TakeawayTypeRequest $request)
    {
        try {
            $slug = Str::slug($request->name);
            return TakeawayType::create($request->validated() + ['slug' => $slug]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(TakeawayTypeRequest $request, TakeawayType $takeawayType)
    {
        try {
            $slug = Str::slug($request->name);
            return tap($takeawayType)->update($request->validated() + ['slug' => $slug]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(TakeawayType $takeawayType): void
    {
        try {
            $takeawayType->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(TakeawayType $takeawayType): TakeawayType
    {
        try {
            return $takeawayType;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}



