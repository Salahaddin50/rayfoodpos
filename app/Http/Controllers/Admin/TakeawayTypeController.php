<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\TakeawayType;
use App\Exports\TakeawayTypeExport;
use App\Services\TakeawayTypeService;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\TakeawayTypeRequest;
use App\Http\Resources\TakeawayTypeResource;
use Illuminate\Routing\Controllers\Middleware;

class TakeawayTypeController extends AdminController
{
    private TakeawayTypeService $takeawayTypeService;

    public function __construct(TakeawayTypeService $takeawayType)
    {
        parent::__construct();
        $this->takeawayTypeService = $takeawayType;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:takeaway-types', only: ['export']),
            new Middleware('permission:takeaway_types_create', only: ['store']),
            new Middleware('permission:takeaway_types_edit', only: ['update']),
            new Middleware('permission:takeaway_types_delete', only: ['destroy']),
            new Middleware('permission:takeaway_types_show', only: ['show'])
        ];
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return TakeawayTypeResource::collection($this->takeawayTypeService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(
        TakeawayTypeRequest $request
    ): \Illuminate\Http\Response | TakeawayTypeResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TakeawayTypeResource($this->takeawayTypeService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(
        TakeawayType $takeawayType
    ): \Illuminate\Http\Response | TakeawayTypeResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TakeawayTypeResource($this->takeawayTypeService->show($takeawayType));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(
        TakeawayTypeRequest $request,
        TakeawayType $takeawayType
    ): \Illuminate\Http\Response | TakeawayTypeResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new TakeawayTypeResource($this->takeawayTypeService->update($request, $takeawayType));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(
        TakeawayType $takeawayType
    ): \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            $this->takeawayTypeService->destroy($takeawayType);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new TakeawayTypeExport($this->takeawayTypeService, $request), 'Takeaway-Types.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}

