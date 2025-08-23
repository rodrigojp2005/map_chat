<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Limpar usuários offline a cada 5 minutos
        $schedule->command('users:clean-offline --minutes=10')->everyFiveMinutes();
        
        // Limpar usuários anônimos inativos a cada 30 minutos
        $schedule->command('cleanup:anonymous-users --force')->everyThirtyMinutes();
        
        // Limpar e reorganizar salas de chat a cada 15 minutos
        $schedule->command('chat:cleanup')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
