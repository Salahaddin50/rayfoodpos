<?php

namespace App\Services;


use Exception;
use App\Models\Branch;
use App\Models\DiningTable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Support\Facades\File;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use Dipokhalder\Settings\Facades\Settings;
use App\Http\Requests\DiningTableRequest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DiningTableService
{
    public $envService;
    protected array $diningTableFilter = [
        'name',
        'size',
        'branch_id',
        'status'
    ];

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }
    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return DiningTable::with('branch')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->diningTableFilter)) {
                        if ($key == "except") {
                            $explodes = explode('|', $request);
                            if (count($explodes)) {
                                foreach ($explodes as $explode) {
                                    $query->where('id', '!=', $explode);
                                }
                            }
                        } else {
                            if ($key == "branch_id") {
                                $query->where($key, $request);
                            } else {
                                $query->where($key, 'like', '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(DiningTableRequest $request)
    {
        try {
            $branch      = Branch::find($request->branch_id);
            $branch_name = $branch ? $branch->name : "";

            $filename = Str::random(10) . '.svg';
            $slug     = Str::slug($branch_name . "-" . $request->name);
            $url      = config('app.url') . "/menu/" . $slug;

            if (!File::exists(storage_path('app/public/qr_codes/'))) {
                File::makeDirectory(storage_path('app/public/qr_codes/'), 0755, true);
            }
            
            // Generate QR code as SVG (no image extension required)
            $filename = str_replace('.png', '.svg', $filename);
            $qrCode = QrCode::format('svg')
                ->size(200)
                ->errorCorrection('H')
                ->generate($url);
            
            File::put(storage_path('app/public/qr_codes/' . $filename), $qrCode);
            return DiningTable::create($request->validated() + ['qr_code' => 'storage/qr_codes/' . $filename, 'slug' => $slug]);
        } catch (Exception $exception) {
            Log::error('DiningTable store error: ' . $exception->getMessage());
            Log::error('Stack trace: ' . $exception->getTraceAsString());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(DiningTableRequest $request, DiningTable $diningTable)
    {
        try {
            $branch      = Branch::find($request->branch_id);
            $branch_name = $branch ? $branch->name : "";

            $filename = Str::random(10) . '.svg';
            $slug     = Str::slug($branch_name . "-" . $request->name);
            $url      = config('app.url') . "/menu/" . $slug;

            if (!File::exists(storage_path('app/public/qr_codes/'))) {
                File::makeDirectory(storage_path('app/public/qr_codes/'), 0755, true);
            }

            if (File::exists($diningTable->qr_code) && !$this->envService->getValue('DEMO')) {
                File::delete($diningTable->qr_code);
            }

            // Generate QR code as SVG (no image extension required)
            $filename = str_replace('.png', '.svg', $filename);
            $qrCode = QrCode::format('svg')
                ->size(200)
                ->errorCorrection('H')
                ->generate($url);
            
            File::put(storage_path('app/public/qr_codes/' . $filename), $qrCode);

            return tap($diningTable)->update($request->validated() + ['qr_code' => 'storage/qr_codes/' . $filename, 'slug' => $slug]);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(DiningTable $diningTable): void
    {
        try {

            if (File::exists($diningTable->qr_code) && !$this->envService->getValue('DEMO')) {
                File::delete($diningTable->qr_code);
            }
            $diningTable->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(DiningTable $diningTable): DiningTable
    {
        try {
            return $diningTable;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * Get all dining tables with their active (unpaid) orders
     * @throws Exception
     */
    public function overview()
    {
        try {
            $tables = DiningTable::with('branch')
                ->where('status', \App\Enums\Status::ACTIVE)
                ->orderBy('name')
                ->get();

            // Get active (unpaid) orders for each table
            $tableData = $tables->map(function ($table) {
                // Get orders linked to this table that are not paid
                $activeOrders = \App\Models\Order::where('dining_table_id', $table->id)
                    ->where('payment_status', '!=', \App\Enums\PaymentStatus::PAID)
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'order_serial_no', 'total', 'payment_status', 'status', 'source', 'created_at']);

                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'size' => $table->size,
                    'branch_id' => $table->branch_id,
                    'branch_name' => $table->branch->name ?? '',
                    'orders' => $activeOrders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_serial_no' => $order->order_serial_no,
                            'total' => $order->total,
                            'total_formatted' => \App\Libraries\AppLibrary::currencyAmountFormat($order->total),
                            'payment_status' => $order->payment_status,
                            'status' => $order->status,
                            'source' => $order->source,
                            'created_at' => $order->created_at->format('H:i'),
                        ];
                    }),
                    'has_orders' => $activeOrders->count() > 0,
                    'order_count' => $activeOrders->count(),
                ];
            });

            return $tableData;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}
