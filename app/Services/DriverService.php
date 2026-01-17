<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Driver;
use App\Support\WhatsAppNormalizer;
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
                // Backward-compat: some environments have legacy drivers.status=1 meaning "active".
                ->when($status !== null, function ($q) use ($status) {
                    if ((int) $status === Status::ACTIVE) {
                        return $q->whereIn('status', [1, Status::ACTIVE]);
                    }
                    return $q->where('status', $status);
                })
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
            if (!isset($data['status']) || $data['status'] === null || $data['status'] === '') {
                $data['status'] = Status::ACTIVE;
            }
            if (isset($data['whatsapp'])) {
                $data['whatsapp'] = WhatsAppNormalizer::normalize($data['whatsapp']);
            }
            $exists = Driver::where('branch_id', $data['branch_id'])
                ->where('whatsapp', $data['whatsapp'])
                ->exists();
            if ($exists) {
                throw new Exception("WhatsApp number already exists", 422);
            }
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
            if (isset($data['whatsapp'])) {
                $data['whatsapp'] = WhatsAppNormalizer::normalize($data['whatsapp']);
                $dup = Driver::where('branch_id', $driver->branch_id)
                    ->where('whatsapp', $data['whatsapp'])
                    ->where('id', '!=', $driver->id)
                    ->exists();
                if ($dup) {
                    throw new Exception("WhatsApp number already exists", 422);
                }
            }
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


