<?php

namespace App\Services;

use App\Models\TokenCounter;
use Dipokhalder\Settings\Facades\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TokenService
{
    /**
     * Generate next token for a branch
     * 
     * @param int $branchId
     * @param string|null $prefix (optional custom prefix, defaults to setting)
     * @return string
     */
    public function generateToken(int $branchId, ?string $prefix = null): string
    {
        try {
            // Get prefix from settings if not provided
            if (!$prefix) {
                $prefix = Settings::group('site')->get('site_token_prefix', 'T');
            }

            $today = now()->toDateString();

            // Use database transaction with lock to prevent duplicate tokens
            return DB::transaction(function () use ($branchId, $today, $prefix) {
                // Get or create counter for today's shift
                $counter = TokenCounter::lockForUpdate()
                    ->where('branch_id', $branchId)
                    ->where('shift_date', $today)
                    ->first();

                if (!$counter) {
                    // Create new counter for today
                    $counter = TokenCounter::create([
                        'branch_id'  => $branchId,
                        'shift_date' => $today,
                        'counter'    => 0,
                        'prefix'     => $prefix
                    ]);
                }

                // Increment counter
                $counter->counter += 1;
                $counter->save();

                // Return simple number: 1, 2, 3, etc.
                return (string) $counter->counter;
            });
        } catch (Exception $exception) {
            Log::error('Token generation error: ' . $exception->getMessage());
            // Fallback to timestamp-based token
            return (string) substr(time(), -6);
        }
    }

    /**
     * Reset token counters for a specific date (or all old dates)
     * 
     * @param string|null $date (null = reset all old dates, or specific date)
     * @return int Number of counters reset
     */
    public function resetShiftTokens(?string $date = null): int
    {
        try {
            $query = TokenCounter::query();

            if ($date) {
                // Reset specific date
                $query->where('shift_date', $date);
            } else {
                // Reset all dates before today
                $query->where('shift_date', '<', now()->toDateString());
            }

            $count = $query->count();
            $query->delete();

            Log::info("Token counters reset: {$count} entries deleted");
            return $count;
        } catch (Exception $exception) {
            Log::error('Token reset error: ' . $exception->getMessage());
            return 0;
        }
    }

    /**
     * Get current token counter for a branch
     * 
     * @param int $branchId
     * @return int
     */
    public function getCurrentCounter(int $branchId): int
    {
        $today = now()->toDateString();
        $counter = TokenCounter::where('branch_id', $branchId)
            ->where('shift_date', $today)
            ->first();

        return $counter ? $counter->counter : 0;
    }

    /**
     * Reset token counter for a specific branch (today's counter)
     * 
     * @param int $branchId
     * @return bool
     */
    public function resetBranchToken(int $branchId): bool
    {
        try {
            $today = now()->toDateString();
            
            $counter = TokenCounter::where('branch_id', $branchId)
                ->where('shift_date', $today)
                ->first();
            
            if ($counter) {
                $counter->counter = 0;
                $counter->save();
                Log::info("Token counter reset for branch {$branchId}");
            }
            
            return true;
        } catch (Exception $exception) {
            Log::error('Token reset error: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Check if auto token generation is enabled
     * 
     * @return bool
     */
    public function isAutoTokenEnabled(): bool
    {
        return (bool) Settings::group('site')->get('site_auto_token_enabled', true);
    }
}

