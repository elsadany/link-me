<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class convertToOfflineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
      
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
        $last_online=Carbon::now()->subMinutes(5);
        Log::info('Converting users to offline');
        \App\Models\User::where('last_availablity','<',$last_online)->orWhereNull('last_availablity')->update(['is_online'=>0]);
        Log::info('Users converted to offline');
    }
}
