<?php

namespace App\Console\Commands;

use App\Models\CampaignCompletion;
use App\Models\OnlineUser;
use Illuminate\Console\Command;

class CleanOnlineUsersCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'online-users:clean-campaigns 
                            {--branch= : Clean campaigns for specific branch ID}
                            {--whatsapp= : Clean campaigns for specific WhatsApp number}
                            {--completions-only : Only delete completion records, keep campaign assignments}
                            {--assignments-only : Only clear campaign assignments, keep completion records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean campaign data for online users (campaign assignments and completion records)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $branchId = $this->option('branch');
        $whatsapp = $this->option('whatsapp');
        $completionsOnly = $this->option('completions-only');
        $assignmentsOnly = $this->option('assignments-only');

        $this->info('Starting campaign data cleanup...');
        $this->newLine();

        // Clean completion records
        if (!$assignmentsOnly) {
            $this->info('Cleaning campaign completion records...');
            $completionQuery = CampaignCompletion::withoutGlobalScopes();
            
            if ($branchId) {
                $completionQuery->where('branch_id', $branchId);
                $this->info("  - Filtering by branch_id: {$branchId}");
            }
            
            if ($whatsapp) {
                $completionQuery->where('whatsapp', $whatsapp);
                $this->info("  - Filtering by whatsapp: {$whatsapp}");
            }
            
            $completionCount = $completionQuery->count();
            if ($completionCount > 0) {
                if ($this->confirm("Delete {$completionCount} completion record(s)?", true)) {
                    $deleted = $completionQuery->delete();
                    $this->info("  ✓ Deleted {$deleted} completion record(s)");
                } else {
                    $this->warn('  ✗ Skipped deleting completion records');
                }
            } else {
                $this->info('  ✓ No completion records found to delete');
            }
            $this->newLine();
        }

        // Clean campaign assignments
        if (!$completionsOnly) {
            $this->info('Cleaning campaign assignments from online_users...');
            $userQuery = OnlineUser::withoutGlobalScopes()
                ->whereNotNull('campaign_id');
            
            if ($branchId) {
                $userQuery->where('branch_id', $branchId);
                $this->info("  - Filtering by branch_id: {$branchId}");
            }
            
            if ($whatsapp) {
                $userQuery->where('whatsapp', $whatsapp);
                $this->info("  - Filtering by whatsapp: {$whatsapp}");
            }
            
            $userCount = $userQuery->count();
            if ($userCount > 0) {
                if ($this->confirm("Clear campaign assignments for {$userCount} user(s)?", true)) {
                    $updated = $userQuery->update([
                        'campaign_id' => null,
                        'campaign_joined_at' => null,
                        'campaign_manual_order_count' => null,
                    ]);
                    $this->info("  ✓ Cleared campaign assignments for {$updated} user(s)");
                } else {
                    $this->warn('  ✗ Skipped clearing campaign assignments');
                }
            } else {
                $this->info('  ✓ No users with campaign assignments found');
            }
            $this->newLine();
        }

        $this->info('Campaign data cleanup completed!');
        
        return Command::SUCCESS;
    }
}
