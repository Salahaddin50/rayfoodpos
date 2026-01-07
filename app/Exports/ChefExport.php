<?php

namespace App\Exports;


use App\Services\ChefService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ChefExport implements FromCollection, WithHeadings
{

    public ChefService $chefService;
    public PaginateRequest $request;

    public function __construct(ChefService $chefService, $request)
    {
        $this->chefService = $chefService;
        $this->request         = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $waiterArray = [];
        $chefs     = $this->chefService->list($this->request);

        foreach ($chefs as $chef) {
            $waiterArray[] = [
                $chef->name,
                $chef->email,
                $chef->country_code . '' . $chef->phone,
                trans('statuse.' . $chef->status),
            ];
        }
        return collect($waiterArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.name'),
            trans('all.label.email'),
            trans('all.label.phone'),
            trans('all.label.status')
        ];
    }
}
