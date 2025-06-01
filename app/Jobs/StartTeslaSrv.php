<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class StartTeslaSrv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $script = '/var/www/vhosts/ilogistix.net/vehicle-command/cmd/tesla-http-proxy/start.sh';

        // Make sure the script is executable
        if (!is_executable($script)) {
            chmod($script, 0755);
        }

        // Start the script in the background
        exec("nohup {$script} > /dev/null 2>&1 &");
    }
}
