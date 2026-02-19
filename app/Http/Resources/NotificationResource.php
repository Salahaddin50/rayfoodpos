<?php

namespace App\Http\Resources;


use App\Models\NotificationSetting;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{

    public $info;

    public function __construct($info)
    {
        parent::__construct($info);
        $this->info = $info;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $info = $this->info ?? [];
        $setting = $this->notificationFile('notification_fcm_json_file');

        return [
            "notification_fcm_api_key"             => $info['notification_fcm_api_key'] ?? '',
            "notification_fcm_public_vapid_key"    => $info['notification_fcm_public_vapid_key'] ?? '',
            "notification_fcm_auth_domain"         => $info['notification_fcm_auth_domain'] ?? '',
            "notification_fcm_project_id"          => $info['notification_fcm_project_id'] ?? '',
            "notification_fcm_storage_bucket"      => $info['notification_fcm_storage_bucket'] ?? '',
            "notification_fcm_messaging_sender_id" => $info['notification_fcm_messaging_sender_id'] ?? '',
            "notification_fcm_app_id"              => $info['notification_fcm_app_id'] ?? '',
            "notification_fcm_measurement_id"      => $info['notification_fcm_measurement_id'] ?? '',
            "notification_fcm_json_file"           => $setting?->file ?? '',
        ];
    }

    public function notificationFile($key)
    {
        try {
            return NotificationSetting::where(['key' => $key])->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
