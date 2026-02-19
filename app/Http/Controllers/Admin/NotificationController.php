<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Services\FirebaseService;
use Illuminate\Routing\Controllers\Middleware;

class NotificationController extends AdminController
{
    private NotificationService $notificationService;
    private FirebaseService $firebaseService;

    public function __construct(NotificationService $notificationService, FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->firebaseService = $firebaseService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings', only: ['update']),
            new Middleware('permission:settings', only: ['testPush']),
        ];
    }

    public function index(): \Illuminate\Http\JsonResponse | NotificationResource
    {
        try {
            $data = $this->notificationService->list();
            return new NotificationResource($data);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('NotificationController::index failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => true,
                'data' => [
                    'notification_fcm_api_key' => '',
                    'notification_fcm_public_vapid_key' => '',
                    'notification_fcm_auth_domain' => '',
                    'notification_fcm_project_id' => '',
                    'notification_fcm_storage_bucket' => '',
                    'notification_fcm_messaging_sender_id' => '',
                    'notification_fcm_app_id' => '',
                    'notification_fcm_measurement_id' => '',
                    'notification_fcm_json_file' => '',
                ],
            ], 200);
        }
    }

    public function update(
        NotificationRequest $request
    ): \Illuminate\Http\Response | NotificationResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory {
        try {
            return new NotificationResource($this->notificationService->update($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * Send a test push notification to the currently logged-in user (web/mobile token).
     */
    public function testPush(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = array_values(array_filter([
            $user->web_token ?? null,
            $user->device_token ?? null,
        ]));

        if (empty($tokens)) {
            return new JsonResponse([
                'status' => false,
                'message' => 'No device token found for this user. Please enable notifications in the browser first.',
            ], 422);
        }

        $data = (object) [
            'title' => 'Test Notification',
            'description' => 'Your push notification setup is working.',
            'image' => null,
        ];

        try {
            $this->firebaseService->sendNotification($data, $tokens, 'test-notification');
        } catch (\Throwable $e) {
            return new JsonResponse([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return new JsonResponse([
            'status' => true,
            'message' => 'Test notification sent.',
        ]);
    }
}
