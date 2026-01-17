<?php

namespace App\Services;

use App\Models\Driver;
use App\Traits\DefaultAccessModelTrait;
use Exception;
use Illuminate\Support\Facades\Log;

class DriverService
{
    use DefaultAccessModelTrait;

    /**
     * @throws Exception
     */
    public function list(array $search = [])
    {
        try {
            $orderColumn = $search['order_column'] ?? 'id';
            $orderType   = $search['order_type'] ?? 'desc';
            $status      = $search['status'] ?? null;

            $query = Driver::query()
                ->when($status !== null, fn ($q) => $q->where('status', $status))
                ->orderBy($orderColumn, $orderType);

            return $query->get();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function store(array $data): Driver
    {
        try {
            $data['branch_id'] = $this->branch();
            return Driver::create($data);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function update(Driver $driver, array $data): Driver
    {
        try {
            $driver->update($data);
            return $driver->fresh();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Driver $driver): void
    {
        try {
            $driver->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }
}


