<?php

namespace App\Exports;

use App\Enums\OrderType;
use App\Libraries\AppLibrary;
use App\Services\OrderService;
use App\Http\Requests\PaginateRequest;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalesReportExport implements FromCollection, WithHeadings
{

    public OrderService $orderService;
    public PaginateRequest $request;

    public function __construct(OrderService $orderService, $request)
    {
        $this->orderService = $orderService;
        $this->request      = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $salesReportArray  = [];
        $salesReportsArray = $this->orderService->list($this->request);

        foreach ($salesReportsArray as $order) {
            $salesReportArray[] = [
                $order->order_serial_no,
                AppLibrary::datetime($order->order_datetime),
                AppLibrary::flatAmountFormat($order->subtotal),
                AppLibrary::flatAmountFormat($order->delivery_charge),
                AppLibrary::flatAmountFormat($order->discount),
                AppLibrary::flatAmountFormat($order->total),
                $order->transaction ? strtoupper($order->transaction->payment_method)
                    : $this->getPaymentMethod($order),
                trans('payment_status.' . $order->payment_status)
            ];
        }
        return collect($salesReportArray);
    }

    public function headings(): array
    {
        return [
            trans('all.label.order_serial_no'),
            trans('all.label.date'),
            trans('all.label.subtotal'),
            trans('all.label.pickup_cost'),
            trans('all.label.discount'),
            trans('all.label.total'),
            trans('all.label.payment_type'),
            trans('all.label.payment_status')
        ];
    }

    public function getPaymentMethod($order)
    {
        return trans('pos_payment_method.' . $order->pos_payment_method) != "pos_payment_method." ? trans('pos_payment_method.' . $order->pos_payment_method) : trans(
            'payment_gateway.' . $order->payment_method
        );
    }
}
