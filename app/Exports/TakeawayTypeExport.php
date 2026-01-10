<?php

namespace App\Exports;

use App\Http\Requests\PaginateRequest;
use App\Services\TakeawayTypeService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TakeawayTypeExport implements FromCollection, WithHeadings
{
    public TakeawayTypeService $takeawayTypeService;
    public PaginateRequest $request;

    public function __construct(TakeawayTypeService $takeawayTypeService, $request)
    {
        $this->takeawayTypeService = $takeawayTypeService;
        $this->request             = $request;
    }

    public function collection()
    {
        $rows  = [];
        $types = $this->takeawayTypeService->list($this->request);

        foreach ($types as $type) {
            $rows[] = [
                $type->name,
                $type->sort_order,
                trans('statuse.' . $type->status),
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            trans('all.label.name'),
            'Sort Order',
            trans('all.label.status'),
        ];
    }
}




