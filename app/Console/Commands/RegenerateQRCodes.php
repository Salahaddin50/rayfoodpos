<?php

namespace App\Console\Commands;

use App\Models\DiningTable;
use App\Models\Branch;
use App\Services\DiningTableService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:regenerate {--all : Regenerate all QR codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for dining tables with current APP_URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting QR code regeneration...');
        $this->info('Current APP_URL: ' . config('app.url'));
        $this->newLine();

        // Get all dining tables
        $tables = DiningTable::all();
        
        if ($tables->isEmpty()) {
            $this->warn('No dining tables found.');
            return 0;
        }

        $this->info("Found {$tables->count()} dining table(s) to process.");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        // Ensure QR codes directory exists
        if (!File::exists(storage_path('app/public/qr_codes/'))) {
            File::makeDirectory(storage_path('app/public/qr_codes/'), 0755, true);
        }

        foreach ($tables as $table) {
            try {
                $branch = Branch::find($table->branch_id);
                $branch_name = $branch ? $branch->name : "";

                // Generate new filename
                $filename = Str::random(10) . '.svg';
                
                // Generate URL with current APP_URL
                $slug = $table->slug;
                $url = URL::to('/') . "/menu/" . $slug;

                $this->info("Processing: {$table->name} (ID: {$table->id})");
                $this->line("  → Old QR: {$table->qr_code}");
                $this->line("  → New URL: {$url}");

                // Delete old QR code file if exists
                if ($table->qr_code && File::exists(storage_path('app/public/' . $table->qr_code))) {
                    File::delete(storage_path('app/public/' . $table->qr_code));
                    $this->line("  → Deleted old QR code file");
                }

                // Generate new QR code
                $qrCode = QrCode::format('svg')
                    ->size(200)
                    ->errorCorrection('H')
                    ->generate($url);

                // Save new QR code
                $qrCodePath = 'storage/qr_codes/' . $filename;
                File::put(storage_path('app/public/qr_codes/' . $filename), $qrCode);

                // Update database record
                $table->update(['qr_code' => $qrCodePath]);

                $this->info("  ✅ Successfully regenerated QR code");
                $successCount++;
                $this->newLine();

            } catch (\Exception $e) {
                $this->error("  ❌ Error processing table ID {$table->id}: {$e->getMessage()}");
                $errorCount++;
                $this->newLine();
            }
        }

        $this->newLine();
        $this->info("=========================================");
        $this->info("✅ Regeneration complete!");
        $this->info("   Success: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("   Errors: {$errorCount}");
        }
        $this->info("=========================================");

        return 0;
    }
}

