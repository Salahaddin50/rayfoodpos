<?php

namespace App\Services;


use Exception;
use GuzzleHttp\Client;
use App\Models\User;
use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Dipokhalder\Settings\Facades\Settings;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseService
{
    public $filePath;
    private $invalidTokens = [];

    public function sendNotification($data, $fcmTokens, $topicName): void
    {

        try {
            $notification = Settings::group('notification')->all();

            $url = 'https://fcm.googleapis.com/v1/projects/' . $notification['notification_fcm_project_id'] . '/messages:send';
            $accessToken = $this->getAccessToken();

            $client  = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ];
            
            $successCount = 0;
            $failureCount = 0;

            foreach ($fcmTokens as $fcmToken) {

                $payload = [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => [
                            'title' => $data->title,
                            'body' => $data->description,
                            'image' => $data->image ?? null,
                        ],
                        'data' => [
                            'title' => $data->title,
                            'body' => $data->description,
                            'sound' => 'default',
                            'image' => $data->image ?? null,
                            'topicName' => $topicName,
                        ],
                        'webpush' => [
                            "headers" => [
                                "Urgency" => "high"
                            ]
                        ],
                    ],
                ];

                try {
                    $response = $client->post($url, [
                        'headers' => $headers,
                        "body"    => json_encode($payload)
                    ]);
                    $successCount++;
                } catch (\GuzzleHttp\Exception\ClientException $e) {
                    $failureCount++;
                    $responseBody = $e->getResponse()->getBody()->getContents();
                    $responseData = json_decode($responseBody, true);
                    
                    // Check if token is invalid/unregistered
                    $errorCode = $responseData['error']['status'] ?? '';
                    $errorMessage = $responseData['error']['message'] ?? '';
                    
                    if (in_array($errorCode, ['NOT_FOUND', 'INVALID_ARGUMENT', 'UNREGISTERED']) || 
                        str_contains($errorMessage, 'Requested entity was not found') ||
                        str_contains($errorMessage, 'not a valid FCM registration token')) {
                        
                        // Mark token as invalid for cleanup
                        $this->invalidTokens[] = $fcmToken;
                        
                        Log::warning('FCM: Invalid/expired token detected', [
                            'token' => substr($fcmToken, 0, 20) . '...',
                            'error' => $errorCode,
                            'message' => $errorMessage,
                            'topic' => $topicName
                        ]);
                    } else {
                        // Other errors (rate limit, server error, etc.)
                        Log::error('FCM: Notification send failed', [
                            'token' => substr($fcmToken, 0, 20) . '...',
                            'error' => $errorCode,
                            'message' => $errorMessage,
                            'topic' => $topicName,
                            'status_code' => $e->getResponse()->getStatusCode()
                        ]);
                    }
                    
                    // Log to failed_notifications table if exists
                    $this->logFailedNotification($fcmToken, $data, $topicName, $errorMessage);
                } catch (\Throwable $th) {
                    $failureCount++;
                    Log::error('FCM: Unexpected error sending notification', [
                        'token' => substr($fcmToken, 0, 20) . '...',
                        'error' => $th->getMessage(),
                        'topic' => $topicName
                    ]);
                }
            }
            
            // Clean up invalid tokens from database
            if (!empty($this->invalidTokens)) {
                $this->cleanInvalidTokens();
            }
            
            // Log summary
            Log::info('FCM: Notification batch complete', [
                'topic' => $topicName,
                'total' => count($fcmTokens),
                'success' => $successCount,
                'failed' => $failureCount,
                'invalid_tokens_removed' => count($this->invalidTokens)
            ]);
            
        } catch (Exception $e) {
            Log::error('FCM: Critical error in sendNotification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Remove invalid tokens from users table
     */
    private function cleanInvalidTokens(): void
    {
        try {
            $removedCount = User::where(function($query) {
                foreach ($this->invalidTokens as $token) {
                    $query->orWhere('web_token', $token)
                          ->orWhere('device_token', $token);
                }
            })->update([
                'web_token' => DB::raw("CASE 
                    WHEN web_token IN ('" . implode("','", array_map('addslashes', $this->invalidTokens)) . "') THEN NULL 
                    ELSE web_token 
                END"),
                'device_token' => DB::raw("CASE 
                    WHEN device_token IN ('" . implode("','", array_map('addslashes', $this->invalidTokens)) . "') THEN NULL 
                    ELSE device_token 
                END")
            ]);
            
            Log::info('FCM: Invalid tokens cleaned from database', [
                'count' => $removedCount,
                'tokens' => count($this->invalidTokens)
            ]);
        } catch (Exception $e) {
            Log::error('FCM: Failed to clean invalid tokens', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Log failed notification to database
     */
    private function logFailedNotification($token, $data, $topicName, $errorMessage): void
    {
        try {
            // Only log if table exists
            if (DB::getSchemaBuilder()->hasTable('failed_notifications')) {
                DB::table('failed_notifications')->insert([
                    'token' => $token,
                    'title' => $data->title ?? null,
                    'body' => $data->description ?? null,
                    'topic' => $topicName,
                    'error_message' => $errorMessage,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } catch (Exception $e) {
            // Silent fail - don't break notification flow
        }
    }

    function getAccessToken()
    {

        $keyFilePath = NotificationSetting::where(['key' => 'notification_fcm_json_file'])->first()->file;
        $parsed_url = parse_url($keyFilePath);

        if (isset($parsed_url['path'])) {
            $relative_path = ltrim($parsed_url['path'], '/storage');
            $this->filePath = storage_path('app/public/' . $relative_path);
        } else {
            throw new Exception('No file found in the URL');
        }

        $SCOPES = ['https://www.googleapis.com/auth/cloud-platform'];

        if (!file_exists($this->filePath)) {
            throw new Exception('Service account key file not found');
        }

        $credentials = new ServiceAccountCredentials($SCOPES, $this->filePath);
        $token = $credentials->fetchAuthToken();

        if (isset($token['access_token'])) {
            return $token['access_token'];
        } else {
            throw new Exception('Failed to fetch access token');
        }
    }
}
