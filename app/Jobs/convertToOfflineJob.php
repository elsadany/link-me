<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
class convertToOfflineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $last_online=Carbon::now()->subMinutes(5);
        $users=User::where('last_availablity','<',$last_online)->update(['is_online'=>0]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
