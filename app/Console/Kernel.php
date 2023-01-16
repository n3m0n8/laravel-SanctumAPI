<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //here we implement a scheduler rule to force the calling of the session token expiry after a certain period - in this case every day (24hours)
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
        //note that the command() is essentially a termainal command make some directives to the laravel engine.
        //the chained on daily() method says, do this command every day
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
