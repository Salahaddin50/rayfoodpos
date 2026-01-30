<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Campaign;
use App\Models\CampaignRegistration;
use App\Services\CampaignRegistrationService;
use App\Http\Requests\CampaignRegistrationRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\CampaignRegistrationResource;
use Illuminate\Routing\Controllers\Middleware;

class CampaignRegistrationController extends AdminController
{
    private CampaignRegistrationService $registrationService;

    public function __construct(CampaignRegistrationService $registrationService)
    {
        parent::__construct();
        $this->registrationService = $registrationService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:campaigns', only: ['index']),
            new Middleware('permission:campaigns_create', only: ['store']),
            new Middleware('permission:campaigns_edit', only: ['update']),
            new Middleware('permission:campaigns_delete', only: ['destroy']),
            new Middleware('permission:campaigns_show', only: ['show']),
        ];
    }

    public function index(
        PaginateRequest $request,
        Campaign $campaign
    ): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return CampaignRegistrationResource::collection($this->registrationService->list($request, $campaign));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(CampaignRegistrationRequest $request, Campaign $campaign): \Illuminate\Http\Response | CampaignRegistrationResource
    {
        try {
            return new CampaignRegistrationResource($this->registrationService->store($request, $campaign));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Campaign $campaign, CampaignRegistration $registration): \Illuminate\Http\Response | CampaignRegistrationResource
    {
        try {
            return new CampaignRegistrationResource($this->registrationService->show($registration));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(CampaignRegistrationRequest $request, Campaign $campaign, CampaignRegistration $registration): \Illuminate\Http\Response | CampaignRegistrationResource
    {
        try {
            return new CampaignRegistrationResource($this->registrationService->update($request, $campaign, $registration));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Campaign $campaign, CampaignRegistration $registration): \Illuminate\Http\Response
    {
        try {
            $this->registrationService->destroy($registration);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
