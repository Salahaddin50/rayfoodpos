<?php

namespace App\Exports;

use App\Models\OnlineUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OnlineUserExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        $rows = [];

        foreach (OnlineUser::query()->orderByDesc('last_order_at')->get() as $u) {
            $rows[] = [
                $u->whatsapp,
                $u->location,
                optional($u->last_order_at)->toDateTimeString(),
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ['whatsapp', 'location', 'last_order_at'];
    }
}


