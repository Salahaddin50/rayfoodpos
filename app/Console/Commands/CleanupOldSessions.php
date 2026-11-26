<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupOldSessions extends Command
{
    protected $signature = 'sessions:cleanup {--hours=24 : Delete sessions older than this many hours}';
    protected $description = 'Clean up old session files to improve performance';

    public function handle()
    {
        $hours = $this->option('hours');
        $sessionPath = storage_path('framework/sessions');
        
        if (!File::exists($sessionPath)) {
            $this->error('Session directory does not exist.');
            return 1;
        }

        $cutoffTime = Carbon::now()->subHours($hours)->timestamp;
        $deleted = 0;
        $files = File::files($sessionPath);
        $fileCount = count($files);

        $this->info("Checking {$fileCount} session files...");
        
        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->info("✅ Deleted {$deleted} old session files (older than {$hours} hours).");
        
        return 0;
    }
}
