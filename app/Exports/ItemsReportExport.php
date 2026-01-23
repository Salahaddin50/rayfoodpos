<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use App\Services\ItemService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ItemsReportExport implements FromCollection, WithHeadings
{

    public ItemService $itemService;
    public PaginateRequest $request;

    public function __construct(ItemService $itemService, $request)
    {
        $this->itemService = $itemService;
        $this->request     = $request;
    }

    public function collection() : \Illuminate\Support\Collection
    {
        $itemsReportArray  = [];
        $itemsReportsArray = $this->itemService->itemReport($this->request);

        $totalQuantity = 0;
        $totalIncome = 0;
        
        foreach ($itemsReportsArray as $item) {
            $quantity = (float)($item->total_quantity ?? 0);
            $income   = (float)($item->total_income ?? 0);
            $unit     = (float)($item->unit_price ?? 0);
            
            $totalQuantity += $quantity;
            $totalIncome += $income;
            
            $itemsReportArray[] = [
                $item->order_numbers ?? '',
                $item->item_name,
                $item->category_name ?? '',
                trans('itemType.' . $item->item_type),
                $item->first_order_date ? date('Y-m-d', strtotime($item->first_order_date)) : '',
                AppLibrary::flatAmountFormat($unit),
                $this->formatOptions($item->item_variations ?? null, $item->item_extras ?? null),
                $quantity,
                AppLibrary::flatAmountFormat($income)
            ];
        }
        
        $itemsReportArray[] = [
            trans('all.label.total'),
            '',
            '',
            '',
            '',
            '',
            '',
            $totalQuantity,
            AppLibrary::flatAmountFormat($totalIncome)
        ];
        
        return collect($itemsReportArray);
    }

    public function headings() : array
    {
        return [
            trans('all.label.order_serial_no'),
            trans('all.label.name'),
            trans('all.label.item_category_id'),
            trans('all.label.item_type'),
            trans('all.label.date'),
            trans('all.label.unit_price'),
            trans('all.label.options'),
            trans('all.label.quantity'),
            trans('all.label.total_income'),
        ];
    }

    private function formatOptions($itemVariations, $itemExtras): string
    {
        $parts = [];

        $variations = [];
        if (!empty($itemVariations)) {
            $decoded = is_string($itemVariations) ? json_decode($itemVariations, true) : $itemVariations;
            if (is_array($decoded)) {
                $variations = array_values($decoded);
            }
        }

        if (!empty($variations)) {
            $variationParts = [];
            foreach ($variations as $v) {
                if (!is_array($v)) {
                    continue;
                }
                if (!empty($v['variation_name']) && !empty($v['name'])) {
                    $variationParts[] = $v['variation_name'] . ': ' . $v['name'];
                } else if (!empty($v['name'])) {
                    $variationParts[] = $v['name'];
                }
            }
            if (!empty($variationParts)) {
                $parts[] = implode(', ', $variationParts);
            }
        }

        $extras = [];
        if (!empty($itemExtras)) {
            $decoded = is_string($itemExtras) ? json_decode($itemExtras, true) : $itemExtras;
            if (is_array($decoded)) {
                $extras = $decoded;
            }
        }

        if (!empty($extras)) {
            $extraNames = [];
            foreach ($extras as $e) {
                if (is_array($e) && !empty($e['name'])) {
                    $extraNames[] = $e['name'];
                }
            }
            if (!empty($extraNames)) {
                $parts[] = 'Extras: ' . implode(', ', $extraNames);
            }
        }

        return implode(' | ', $parts);
    }
}
