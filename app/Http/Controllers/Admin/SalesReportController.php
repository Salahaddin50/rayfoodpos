<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\ThemeSetting;
use App\Services\OrderService;
use App\Services\ThemeService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CompanyService;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use Dipokhalder\Settings\Facades\Settings;
use App\Http\Resources\SimpleOrderResource;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\SalesReportOverviewResource;

class SalesReportController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private OrderService $orderService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(OrderService $order, CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->orderService = $order;
        $this->companyService = $companyService;
        $this->themeService  = $themeService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales-report', only: ['index', 'export', 'pdf']),
        ];
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return SimpleOrderResource::collection($this->orderService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new SalesReportExport($this->orderService, $request), 'Sales-Report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function pdf(PaginateRequest $request): mixed
    {
        try {
            $company = $this->companyService->list();
            $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright   = Settings::group('site')->get('site_copyright');
            $orders = $this->orderService->list($request);


            $pdf = Pdf::loadView('pdf.sales_report', compact('company', 'theme_logo', 'orders', 'copyright'))
                ->setPaper('a4');
            return response()->stream(
                fn() => print($pdf->output()),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="sales_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function salesReportOverview(PaginateRequest $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|SalesReportOverviewResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return new SalesReportOverviewResource($this->orderService->salesReportOverview($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
