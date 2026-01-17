<?php

namespace App\Services;

use App\Enums\Source;
use App\Models\FrontendOrder;
use App\Models\OnlineUser;
use App\Support\WhatsAppNormalizer;
use App\Traits\DefaultAccessModelTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineUserService
{
    use DefaultAccessModelTrait;

    /**
     * Upsert a single online user record from an order that contains whatsapp_number.
     * Note: Dining table orders may also include whatsapp_number, so we include them too.
     */
    public function upsertFromOrder(FrontendOrder $order): void
    {
        try {
            $whatsapp = WhatsAppNormalizer::normalize($order->whatsapp_number ?? null);
            if ($whatsapp === '') {
                return;
            }

            OnlineUser::updateOrCreate(
                [
                    'branch_id' => $order->branch_id,
                    'whatsapp'  => $whatsapp,
                ],
                [
                    'location'      => $order->location_url ?? null,
                    'last_order_id'  => $order->id,
                    'last_order_at'  => $order->order_datetime,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('OnlineUser upsertFromOrder failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure online_users is populated from historical online orders for the current branch.
     * This keeps behavior "DB-backed" even if the table starts empty.
     *
     * @throws Exception
     */
    public function ensureSyncedForCurrentBranch(): void
    {
        try {
            $branchId = $this->branch();
            if (!$branchId) {
                return;
            }

            $alreadyHasData = OnlineUser::where('branch_id', $branchId)->exists();
            if ($alreadyHasData) {
                return;
            }

            // Latest order per whatsapp_number for the branch (WEB orders, including dining-table orders when they have whatsapp_number)
            $base = DB::table('orders')
                ->where('branch_id', $branchId)
                ->where('source', Source::WEB)
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', '');

            $latest = $base
                ->select('whatsapp_number', DB::raw('MAX(order_datetime) as last_order_at'))
                ->groupBy('whatsapp_number');

            $rows = DB::table('orders as o')
                ->joinSub($latest, 'x', function ($join) {
                    $join->on('x.whatsapp_number', '=', 'o.whatsapp_number')
                        ->on('x.last_order_at', '=', 'o.order_datetime');
                })
                ->where('o.branch_id', $branchId)
                ->where('o.source', Source::WEB)
                ->select([
                    DB::raw((int) $branchId . ' as branch_id'),
                    DB::raw('o.whatsapp_number as whatsapp'),
                    DB::raw('o.location_url as location'),
                    DB::raw('o.id as last_order_id'),
                    DB::raw('o.order_datetime as last_order_at'),
                ])
                ->get()
                ->map(function ($r) {
                    $whatsapp = WhatsAppNormalizer::normalize($r->whatsapp ?? null);
                    if ($whatsapp === '') {
                        return null;
                    }
                    return [
                        'branch_id'     => (int) $r->branch_id,
                        'whatsapp'      => $whatsapp,
                        'location'      => $r->location,
                        'last_order_id' => (int) $r->last_order_id,
                        'last_order_at' => $r->last_order_at,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                })
                ->filter()
                ->values()
                ->all();

            if (!empty($rows)) {
                OnlineUser::upsert(
                    $rows,
                    ['branch_id', 'whatsapp'],
                    ['location', 'last_order_id', 'last_order_at', 'updated_at']
                );
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw $exception;
        }
    }
}


