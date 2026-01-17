<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OnlineUserSampleExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect([
            [
                '+994501234567',
                'https://maps.google.com/?q=40.4093,49.8671',
            ],
        ]);
    }

    public function headings(): array
    {
        return ['whatsapp', 'location'];
    }
}


