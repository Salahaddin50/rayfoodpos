<?php

namespace App\Services;

use Exception;
use App\Enums\Status;
use App\Models\Campaign;
use Illuminate\Support\Str;
use App\Libraries\AppLibrary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\CampaignRequest;

class CampaignService
{
    public $campaign;
    
    protected $campaignFilter = [
        'name',
        'type',
        'status',
        'start_date',
        'end_date',
    ];

    protected $exceptFilter = [
        'excepts'
    ];

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
            $limit       = $request->get('limit') ? $request->get('limit') : '';

            return Campaign::with('registrations')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->campaignFilter)) {
                        if ($key == "start_date") {
                            $start_date = Date('Y-m-d', strtotime($request));
                            $query->whereDate($key, '>=', $start_date);
                        } else if ($key == "end_date") {
                            $end_date = Date('Y-m-d', strtotime($request));
                            $query->whereDate($key, '<=', $end_date);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }

                    if (in_array($key, $this->exceptFilter)) {
                        $explodes = explode('|', $request);
                        if (is_array($explodes)) {
                            foreach ($explodes as $explode) {
                                $query->where('id', '!=', $explode);
                            }
                        }
                    }
                }
            })->limit($limit)->orderBy($orderColumn, $orderType)->$method(
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
    public function store(CampaignRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->campaign = Campaign::create([
                    'name'               => $request->name,
                    'slug'               => Str::slug($request->name),
                    'description'        => $request->description,
                    'type'               => $request->type,
                    'discount_value'     => $request->discount_value,
                    'free_item_id'       => $request->free_item_id,
                    'required_purchases' => $request->required_purchases,
                    'start_date'         => $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                    'end_date'           => $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null,
                    'status'             => $request->status,
                ]);
            });
            return $this->campaign;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(CampaignRequest $request, Campaign $campaign)
    {
        try {
            DB::transaction(function () use ($request, $campaign) {
                $this->campaign              = $campaign;
                $campaign->name              = $request->name;
                $campaign->slug              = Str::slug($request->name);
                $campaign->description       = $request->description;
                $campaign->type              = $request->type;
                $campaign->discount_value    = $request->discount_value;
                $campaign->free_item_id      = $request->free_item_id;
                $campaign->required_purchases = $request->required_purchases;
                $campaign->start_date        = $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null;
                $campaign->end_date          = $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null;
                $campaign->status            = $request->status;
                $campaign->save();
            });
            return $this->campaign;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Campaign $campaign)
    {
        try {
            $campaign->registrations()->delete();
            $campaign->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Campaign $campaign): Campaign
    {
        try {
            return $campaign->load('registrations', 'freeItem');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}
