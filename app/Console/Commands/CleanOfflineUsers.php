<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CleanOfflineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean-offline {--minutes=10 : Minutes since last seen to consider offline}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as offline if they haven\'t been seen for specified minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        
        $cutoffTime = now()->subMinutes($minutes);
        
        $updated = User::where('is_online', true)
            ->where('last_seen', '<', $cutoffTime)
            ->update(['is_online' => false]);
            
        $this->info("Marked {$updated} users as offline (inactive for more than {$minutes} minutes)");
        
        return 0;
    }
}
