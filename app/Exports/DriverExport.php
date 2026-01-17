<?php

namespace App\Exports;

use App\Services\DriverService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriverExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly DriverService $driverService, private readonly array $request = [])
    {
    }

    public function collection(): Collection
    {
        $rows = [];
        $drivers = $this->driverService->list($this->request);

        foreach ($drivers as $driver) {
            $rows[] = [
                $driver->name,
                $driver->transport_type,
                $driver->whatsapp,
                $driver->status,
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ['name', 'transport_type', 'whatsapp', 'status'];
    }
}


