<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriverSampleExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            [
                'John Doe',
                'Car',
                '+994501234567',
                1,
            ],
        ]);
    }

    public function headings(): array
    {
        return ['name', 'transport_type', 'whatsapp', 'status'];
    }
}


