<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatRoomService;

class ChatCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'chat:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Limpar dados antigos das salas de chat e reorganizar salas baseado na atividade';

    /**
     * Chat room service
     */
    protected $chatRoomService;

    /**
     * Create a new command instance.
     */
    public function __construct(ChatRoomService $chatRoomService)
    {
        parent::__construct();
        $this->chatRoomService = $chatRoomService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpeza e manutenção das salas de chat...');
        
        try {
            // Executar limpeza completa
            $this->chatRoomService->cleanup();
            
            $this->info('✅ Limpeza concluída com sucesso!');
            
            // Estatísticas após a limpeza
            $this->showStats();
            
        } catch (\Exception $e) {
            $this->error('❌ Erro durante a limpeza: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Mostrar estatísticas das salas de chat
     */
    private function showStats()
    {
        $activeRooms = \App\Models\ChatRoom::active()->count();
        $totalMessages = \App\Models\ChatMessage::where('created_at', '>', now()->subDay())->count();
        $activeUsers = \App\Models\ChatRoomUser::where('is_active', true)
                                               ->where('last_seen', '>', now()->subMinutes(5))
                                               ->count();

        $this->info("\n📊 Estatísticas atuais:");
        $this->line("   • Salas ativas: {$activeRooms}");
        $this->line("   • Mensagens (24h): {$totalMessages}");
        $this->line("   • Usuários ativos: {$activeUsers}");
    }
}
