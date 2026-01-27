<?php

namespace App\Http\Controllers\Auth;


use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Libraries\AppLibrary;
use App\Models\User;
use App\Services\DefaultAccessService;
use App\Services\MenuService;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Dipokhalder\Settings\Facades\Settings;

class LoginController extends Controller
{
    public string $token;
    public DefaultAccessService $defaultAccessService;
    public PermissionService $permissionService;
    public MenuService $menuService;

    public function __construct(
        MenuService $menuService,
        PermissionService $permissionService,
        DefaultAccessService $defaultAccessService
    ) {
        $this->menuService          = $menuService;
        $this->permissionService    = $permissionService;
        $this->defaultAccessService = $defaultAccessService;
    }

    /**
     * @throws \Exception
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'errors' => $validator->errors()
            ], 422);
        }

        if (config('services.turnstile.enabled')) {
            $turnstileToken = $request->input('cf_turnstile_response')
                ?? $request->input('cf-turnstile-response')
                ?? $request->input('turnstile_token');

            if (!$turnstileToken) {
                return new JsonResponse([
                    'errors' => ['validation' => 'Captcha is required.']
                ], 422);
            }

            if (!$this->verifyTurnstile($turnstileToken, $request->ip())) {
                return new JsonResponse([
                    'errors' => ['validation' => 'Captcha verification failed. Please try again.']
                ], 422);
            }
        }

        $request->merge(['status' => Status::ACTIVE]);

        if (!Auth::guard('web')->attempt($request->only('email', 'password', 'status'))) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.credentials_invalid')]
            ], 400);
        }

        $branchId = Auth::user()->branch_id;
        if (Auth::user()->branch_id == 0) {
            $branchId = Settings::group('site')->get('site_default_branch');
        }
        $this->defaultAccessService->storeOrUpdate(['branch_id' => $branchId]);
        $user        = User::where('email', $request['email'])->first();
        $this->token = $user->createToken('auth_token')->plainTextToken;

        if (!isset($user->roles[0])) {
            return new JsonResponse([
                'errors' => ['validation' => trans('all.message.role_exist')]
            ], 400);
        }

        $permission        = PermissionResource::collection($this->permissionService->permission($user->roles[0]));
        $defaultPermission = AppLibrary::defaultPermission($permission);
        $defaultMenu = (object)AppLibrary::defaultMenu($this->menuService->menu($user->roles[0]), $defaultPermission);

        return new JsonResponse([
            'message'           => trans('all.message.login_success'),
            'token'             => $this->token,
            'branch_id'         => (int)$user->branch_id,
            'user'              => new UserResource($user),
            'menu'              => MenuResource::collection(collect($this->menuService->menu($user->roles[0]))),
            'permission'        => $permission,
            'defaultPermission' => $defaultPermission,
            'defaultMenu' => $defaultMenu
        ], 201);
    }

    private function verifyTurnstile(string $token, ?string $ip = null): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (!$secret) {
            return false;
        }

        $payload = [
            'secret'   => $secret,
            'response' => $token,
        ];

        if ($ip) {
            $payload['remoteip'] = $ip;
        }

        try {
            $res = Http::asForm()
                ->timeout(8)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', $payload);

            if (!$res->ok()) {
                return false;
            }

            $data = $res->json();
            return (bool)($data['success'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return new JsonResponse([
            'message' => trans('all.message.logout_success')
        ], 200);
    }
}
