<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Online Orders</title>
    <style>
        body {
            font-family: "Urbanist", sans-serif;
            color: #1F1F39;
        }

        .container {
            width: 100%;
            height: 100vh;
            margin: auto;
            position: relative;

        }

        .report {
            width: 100%;
            text-align: center;
        }
        img {
            margin: 0px 0px 8px 0px;
            font-size: 16px;
            font-weight: 600;
        }

        p {
            margin: 0px 0px 16px 0px;
        }
        th,
        td {
            border-collapse: collapse;
            border: 1px solid #EFF0F6;
            padding: 12px 11px;
            text-align: left;
            font-size: 12px;
            font-weight: 400;
        }

        table {
            border-radius: 8px;
            outline: 1px solid #EFF0F6;
            outline-offset: -1px;
            overflow: hidden;
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #F8FBFB;
        }

        th:first-child {
            text-wrap: nowrap;
        }

        tbody {
            color: #6E7191;
        }

        .date,
        .time {
            text-wrap: nowrap;
        }

        .total {
            color: #1F1F39;
        }

        .footer {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-weight: 400;
            bottom: 20px;
        }
    </style>
</head>

<body>
    @php 
         $total_quantity = 0;
         $total_income = 0;
    @endphp 
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'],60) }}</p>
            <p  style="color: #ff006b;margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold;">{{ trans('all.label.items_report', [], 'en') }}</p>
            <table>
                <thead>
                    <tr>
                        <th>{{ trans('all.label.name', [], 'en') }}</th>
                        <th>{{ trans('all.label.item_category_id', [], 'en') }}</th>
                        <th>{{ trans('all.label.item_type', [], 'en') }}</th>
                        <th>{{ trans('all.label.date', [], 'en') }}</th>
                        <th>{{ trans('all.label.unit_price', [], 'en') }}</th>
                        <th>{{ trans('all.label.options', [], 'en') }}</th>
                        <th>{{ trans('all.label.quantity', [], 'en') }}</th>
                        <th>{{ trans('all.label.total_income', [], 'en') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $total_quantity += $item->total_quantity;
                            $total_income += $item->total_income;
                         @endphp
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->category_name ?? '' }}</td>
                            <td>{{ trans('itemType.' . $item->item_type, [], 'en') }}</td>
                            <td>{{ $item->first_order_date ? date('Y-m-d', strtotime($item->first_order_date)) : '' }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($item->unit_price) }}</td>
                            <td>
                                @php
                                    $optionsParts = [];
                                    $variations = !empty($item->item_variations) ? json_decode($item->item_variations, true) : [];
                                    if (is_array($variations) && count($variations) > 0) {
                                        $variationParts = [];
                                        foreach ($variations as $v) {
                                            if (is_array($v) && !empty($v['variation_name']) && !empty($v['name'])) {
                                                $variationParts[] = $v['variation_name'] . ': ' . $v['name'];
                                            } elseif (is_array($v) && !empty($v['name'])) {
                                                $variationParts[] = $v['name'];
                                            }
                                        }
                                        if (count($variationParts) > 0) {
                                            $optionsParts[] = implode(', ', $variationParts);
                                        }
                                    }

                                    $extras = !empty($item->item_extras) ? json_decode($item->item_extras, true) : [];
                                    if (is_array($extras) && count($extras) > 0) {
                                        $extraNames = [];
                                        foreach ($extras as $e) {
                                            if (is_array($e) && !empty($e['name'])) {
                                                $extraNames[] = $e['name'];
                                            }
                                        }
                                        if (count($extraNames) > 0) {
                                            $optionsParts[] = 'Extras: ' . implode(', ', $extraNames);
                                        }
                                    }
                                @endphp
                                {{ implode(' | ', $optionsParts) }}
                            </td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($item->total_income) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td colspan="6">{{ trans('all.label.total', [], 'en') }}</td>
                        <td>{{ $total_quantity }}</td>
                        <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_income) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="footer">
            {{ $copyright }}
        </div>
    </div>
</body>

</html>