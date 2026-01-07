<?php

namespace App\Console\Commands;

use App\Services\TokenService;
use Illuminate\Console\Command;

class ResetTokenCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:reset-shift 
                            {--date= : Specific date to reset (YYYY-MM-DD), leave empty to reset all old shifts}
                            {--keep-today : Keep today\'s counter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset token counters for shift changes (resets old dates by default)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tokenService = app(TokenService::class);
        
        $date = $this->option('date');
        $keepToday = $this->option('keep-today');

        if ($keepToday && !$date) {
            // Reset all except today
            $date = null; // Will delete all old dates in service
        }

        $this->info('Resetting token counters...');
        
        $count = $tokenService->resetShiftTokens($date);
        
        if ($count > 0) {
            $this->info("✅ Successfully reset {$count} token counter(s).");
        } else {
            $this->info('ℹ️  No token counters to reset.');
        }

        return Command::SUCCESS;
    }
}

