<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        User::where('last_availablity','<',$last_online)->update(['is_online'=>0]);
    }
}
