<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnonymousUser;
use Carbon\Carbon;

class CleanupAnonymousUsers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cleanup:anonymous-users {--force : Force cleanup even if there are recent users}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old anonymous users who have been inactive for more than 1 hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpeza de usuários anônimos...');

        $cutoffTime = Carbon::now()->subHour();
        
        $oldUsersCount = AnonymousUser::where('last_seen', '<', $cutoffTime)->count();
        
        if ($oldUsersCount === 0) {
            $this->info('✅ Nenhum usuário anônimo antigo encontrado.');
            return 0;
        }

        $this->info("🗑️ Encontrados {$oldUsersCount} usuários anônimos inativos por mais de 1 hora.");

        if (!$this->option('force') && !$this->confirm('Deseja continuar com a limpeza?')) {
            $this->info('❌ Limpeza cancelada.');
            return 1;
        }

        $deleted = AnonymousUser::where('last_seen', '<', $cutoffTime)->delete();
        
        $this->info("✅ {$deleted} usuários anônimos removidos com sucesso!");

        // Também limpar usuários logados offline há mais de 24 horas
        $offlineUsersUpdated = \App\Models\User::where('last_seen', '<', Carbon::now()->subDay())
            ->where('is_online', true)
            ->update(['is_online' => false]);

        if ($offlineUsersUpdated > 0) {
            $this->info("🔄 {$offlineUsersUpdated} usuários logados marcados como offline.");
        }

        return 0;
    }
}
