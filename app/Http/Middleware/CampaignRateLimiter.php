<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CampaignRateLimiter
{
    /**
     * Prevent campaign manipulation via rate limiting per phone number
     * 
     * Limits:
     * - 3 join attempts per phone number per hour
     * - 20 progress checks per phone number per hour
     */
    public function handle(Request $request, Closure $next, string $action = 'join')
    {
        $phone = $request->input('phone');
        
        if (!$phone) {
            return response()->json([
                'status' => false,
                'message' => 'Phone number is required',
            ], 422);
        }

        // Normalize phone for rate limiting
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $phone);
        $key = "campaign_{$action}:" . $normalizedPhone;
        
        // Different limits for different actions
        $limits = [
            'join' => [
                'max' => 3,      // 3 attempts
                'decay' => 3600, // per hour
                'message' => 'Too many campaign join attempts. Please try again in 1 hour.',
            ],
            'progress' => [
                'max' => 20,     // 20 checks
                'decay' => 3600, // per hour
                'message' => 'Too many requests. Please try again later.',
            ],
        ];
        
        $limit = $limits[$action] ?? $limits['join'];
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limit['max']) {
            \Log::warning('Campaign rate limit exceeded', [
                'action' => $action,
                'phone' => $normalizedPhone,
                'ip' => $request->ip(),
                'attempts' => $attempts,
            ]);
            
            return response()->json([
                'status' => false,
                'message' => $limit['message'],
            ], 429);
        }
        
        // Increment attempts
        Cache::put($key, $attempts + 1, $limit['decay']);
        
        return $next($request);
    }
}
