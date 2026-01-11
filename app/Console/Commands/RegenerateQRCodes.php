<?php

namespace App\Console\Commands;

use App\Models\DiningTable;
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
    protected $signature = 'regenerate:qr-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate all dining table QR codes with current APP_URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Regenerating QR codes for all dining tables...');
        
        $tables = DiningTable::all();
        $count = 0;
        
        foreach ($tables as $table) {
            try {
                $branch = $table->branch;
                $branch_name = $branch ? $branch->name : "";
                
                $filename = Str::random(10) . '.svg';
                $slug = $table->slug ?: Str::slug($branch_name . "-" . $table->name);
                $url = URL::to('/') . "/menu/" . $slug;
                
                if (!File::exists(storage_path('app/public/qr_codes/'))) {
                    File::makeDirectory(storage_path('app/public/qr_codes/'), 0755, true);
                }
                
                // Delete old QR code if exists
                if ($table->qr_code && File::exists(storage_path('app/public/' . $table->qr_code))) {
                    File::delete(storage_path('app/public/' . $table->qr_code));
                }
                
                // Generate new QR code
                $qrCode = QrCode::format('svg')
                    ->size(200)
                    ->errorCorrection('H')
                    ->generate($url);
                
                $filePath = 'storage/qr_codes/' . $filename;
                File::put(storage_path('app/public/qr_codes/' . $filename), $qrCode);
                
                // Update table with new QR code and slug
                $table->update([
                    'qr_code' => $filePath,
                    'slug' => $slug
                ]);
                
                $count++;
                $this->line("✅ Updated: {$table->name} -> {$url}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to update {$table->name}: " . $e->getMessage());
            }
        }
        
        $this->info("🎉 Successfully regenerated {$count} QR code(s)!");
        $this->info("📋 Current APP_URL: " . URL::to('/'));
        
        return Command::SUCCESS;
    }
}

