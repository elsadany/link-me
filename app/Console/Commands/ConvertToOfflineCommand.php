<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
class ConvertToOfflineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-to-offline-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Converting users to offline');
        
        $last_online=Carbon::now()->subMinutes(5);
        User::where('last_availablity','<',$last_online)->orWhereNull('last_availablity')->update(['is_online'=>0]);
        $this->info('Users converted to offline');
    }
}
