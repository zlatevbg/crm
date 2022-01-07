<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Notification;
use App\Facades\Domain;
use App\Notifications\TaskExpired;
use App\Notifications\ViewingExpired;
use App\Notifications\ViewingApproaching;
use App\Models\Task;
use App\Models\Status;
use App\Models\Viewing;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('queue:work --daemon --tries=100')->everyMinute()->withoutOverlapping();

        $schedule->call(function () {
            $domain = Domain::domain();

            // TASKS
            $query = Task::with('users')->whereNotNull('end_at')->whereDate('end_at', '<=', Carbon::now());
            $tasks = $query->get();
            if ($tasks->count()) {
                foreach ($tasks as $task) {
                    Notification::send($task->users, new TaskExpired($domain, $task));
                }

                $query->update(['end_at' => null]);
            }

            // VIEWINGS
            $future = Status::where('parent', 4)->where('action', 'future-viewing')->value('id');

            $queryExpired = Viewing::with('user')->leftJoin('status_viewing', 'status_viewing.viewing_id', '=', 'viewings.id')->where('status_viewing.status_id', $future)->whereDate('viewings.viewed_at', '<', Carbon::now()->setTime(0, 0, 0));
            $viewings = $queryExpired->get();
            if ($viewings->count()) {
                $noShow = Status::where('parent', 4)->where('action', 'no-show')->value('id');
                foreach ($viewings as $viewing) {
                    Notification::send($viewing->user, new ViewingExpired($domain, $viewing));
                }

                $queryExpired->update(['status_viewing.status_id' => $noShow]);
            }

            $queryApproaching = Viewing::with('user')->leftJoin('status_viewing', 'status_viewing.viewing_id', '=', 'viewings.id')->where('status_viewing.status_id', $future)->whereDate('viewings.viewed_at', Carbon::now()->addDay()->setTime(0, 0, 0));
            $viewings = $queryApproaching->get();
            if ($viewings->count()) {
                foreach ($viewings as $viewing) {
                    Notification::send($viewing->user, new ViewingApproaching($domain, $viewing));
                }
            }
        })->daily();
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
