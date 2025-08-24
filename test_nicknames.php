<?php
// Script de teste para o sistema de nicknames

require_once 'vendor/autoload.php';

// Inicializar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AnonymousUser;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DO SISTEMA DE NICKNAMES ===\n\n";

try {
    // 1. Testar criação de usuário anônimo
    $sessionId = 'test_' . time();
    $nickname = 'TestUser_' . rand(100, 999);
    
    echo "1. Testando criação de usuário anônimo...\n";
    echo "   Session ID: {$sessionId}\n";
    echo "   Nickname: {$nickname}\n";
    
    // Criar usuário
    $user = AnonymousUser::create([
        'session_id' => $sessionId,
        'name' => $nickname,
        'avatar_type' => 'default',
        'real_latitude' => -23.5505,
        'real_longitude' => -46.6333,
        'latitude' => -23.5505,
        'longitude' => -46.6333,
        'privacy_radius' => 5000,
        'is_online' => true,
        'last_seen' => now()
    ]);
    
    echo "   Usuário criado: ✓ (ID: {$user->id})\n\n";
    
    // 2. Testar busca por session_id exato
    echo "2. Testando busca por session_id exato...\n";
    $found1 = AnonymousUser::where('session_id', $sessionId)->first();
    if ($found1) {
        echo "   Encontrado: ✓ (Nome: {$found1->name})\n";
    } else {
        echo "   Encontrado: ✗\n";
    }
    
    // 3. Testar busca com prefixo anon_
    echo "\n3. Testando busca com prefixo anon_...\n";
    $sessionIdWithPrefix = 'anon_' . $sessionId;
    echo "   Buscando por: {$sessionIdWithPrefix}\n";
    $found2 = AnonymousUser::where('session_id', $sessionIdWithPrefix)->first();
    if ($found2) {
        echo "   Encontrado: ✓ (Nome: {$found2->name})\n";
    } else {
        echo "   Encontrado: ✗\n";
    }
    
    // 4. Simular o comportamento do ChatController
    echo "\n4. Simulando ChatController::getSimpleUserInfo...\n";
    $userId = 'anon_' . $sessionId;
    echo "   User ID original: {$userId}\n";
    
    // Reproduzir lógica do método
    $cleanSessionId = str_replace('anon_', '', $userId);
    echo "   Session ID limpo: {$cleanSessionId}\n";
    
    $anonymousUser = AnonymousUser::where('session_id', $cleanSessionId)->first();
    if ($anonymousUser && $anonymousUser->name && $anonymousUser->name !== 'Usuário Anônimo') {
        echo "   Resultado: ✓ Nome encontrado: '{$anonymousUser->name}'\n";
    } else {
        echo "   Resultado: ✗ Nome não encontrado, usando padrão\n";
        echo "   Tentativa com prefixo...\n";
        
        $anonymousUserWithPrefix = AnonymousUser::where('session_id', $userId)->first();
        if ($anonymousUserWithPrefix && $anonymousUserWithPrefix->name && $anonymousUserWithPrefix->name !== 'Usuário Anônimo') {
            echo "   Com prefixo: ✓ Nome encontrado: '{$anonymousUserWithPrefix->name}'\n";
        } else {
            echo "   Com prefixo: ✗ Nome não encontrado\n";
        }
    }
    
    // 5. Listar todos os usuários para debug
    echo "\n5. Todos os usuários anônimos recentes:\n";
    $recentUsers = AnonymousUser::where('created_at', '>=', now()->subHours(1))
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
    
    foreach ($recentUsers as $u) {
        echo "   ID: {$u->id}, Session: '{$u->session_id}', Nome: '{$u->name}'\n";
    }
    
    // 6. Limpeza
    echo "\n6. Limpando dados de teste...\n";
    AnonymousUser::where('session_id', $sessionId)->delete();
    echo "   Dados de teste removidos: ✓\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
