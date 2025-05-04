<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TempUser;
use Carbon\Carbon;

class CleanUpExpiredTempUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tempusers:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired temp_users records from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = TempUser::where('expires_at', '<', Carbon::now())->delete();
        $this->info("Deleted {$count} expired temp_users records.");
    }
} 