<?php

namespace App\Services;

use Exception;
use App\Enums\Status;
use App\Models\Campaign;
use App\Models\CampaignRegistration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Libraries\QueryExceptionLibrary;
use App\Http\Requests\CampaignRegistrationRequest;

class CampaignRegistrationService
{
    public $registration;
    
    protected $registrationFilter = [
        'name',
        'email',
        'phone',
        'status',
    ];

    protected $exceptFilter = [
        'excepts'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request, Campaign $campaign)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            $limit       = $request->get('limit') ? $request->get('limit') : '';

            return CampaignRegistration::where('campaign_id', $campaign->id)
                ->where(function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->registrationFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
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
    public function store(CampaignRegistrationRequest $request, Campaign $campaign)
    {
        try {
            DB::transaction(function () use ($request, $campaign) {
                $this->registration = CampaignRegistration::create([
                    'campaign_id'       => $campaign->id,
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'phone'             => $request->phone,
                    'verification_code' => $request->verification_code ?? strtoupper(Str::random(8)),
                    'status'            => $request->status ?? Status::INACTIVE,
                    'purchase_count'    => $request->purchase_count ?? 0,
                    'rewards_claimed'   => $request->rewards_claimed ?? 0,
                    'notes'             => $request->notes,
                ]);
            });
            return $this->registration;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(CampaignRegistrationRequest $request, Campaign $campaign, CampaignRegistration $registration)
    {
        try {
            DB::transaction(function () use ($request, $registration) {
                $this->registration = $registration;
                $registration->name = $request->name;
                $registration->email = $request->email;
                $registration->phone = $request->phone;
                $registration->status = $request->status;
                $registration->purchase_count = $request->purchase_count ?? $registration->purchase_count;
                $registration->rewards_claimed = $request->rewards_claimed ?? $registration->rewards_claimed;
                $registration->notes = $request->notes;
                $registration->save();
            });
            return $this->registration;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(CampaignRegistration $registration)
    {
        try {
            $registration->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(CampaignRegistration $registration): CampaignRegistration
    {
        try {
            return $registration->load('campaign');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}
