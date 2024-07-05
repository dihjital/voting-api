<?php

namespace App\Console;

use App\Console\Commands\CloseQuestions;
use App\Console\Commands\RegisterQuestionVoters;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CloseQuestions::class,
        RegisterQuestionVoters::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:close-questions')->dailyAt('01:00');
        $schedule->command('app:register-question-voters')->daily();
    }
}
