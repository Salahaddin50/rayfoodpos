<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverRequest;
use App\Http\Requests\ImportFileRequest;
use App\Http\Resources\DriverResource;
use App\Exports\DriverExport;
use App\Exports\DriverSampleExport;
use App\Imports\DriverImport;
use App\Models\Driver;
use App\Services\DriverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class DriverController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DriverService $driverService)
    {
    }

    public static function middleware(): array
    {
        return [
            // Only admins can manage drivers (per requirement).
            new Middleware('permission:drivers', only: ['index', 'export', 'downloadSample']),
            new Middleware('permission:drivers_create', only: ['store', 'import']),
            new Middleware('permission:drivers_edit', only: ['update']),
            new Middleware('permission:drivers_delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        try {
            return DriverResource::collection(
                $this->driverService->list($request->all())
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(DriverRequest $request)
    {
        try {
            return new DriverResource(
                $this->driverService->store($request->validated())
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(DriverRequest $request, Driver $driver)
    {
        try {
            return new DriverResource(
                $this->driverService->update($driver, $request->validated())
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Driver $driver)
    {
        try {
            $this->driverService->destroy($driver);
            return response(['status' => true]);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new DriverExport($this->driverService, $request->all()), 'Drivers.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function downloadSample()
    {
        try {
            return Excel::download(new DriverSampleExport(), 'Driver Import Sample.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function import(ImportFileRequest $request)
    {
        try {
            Excel::import(new DriverImport($this->driverService), $request->file('file'));
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}


