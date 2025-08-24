<?php
 // Testar conexão com banco
use Illuminate\Support\Facades\DB;
use App\Models\AnonymousUser;
// Script de debug sem shell_exec para servidores com restrições
echo "=== DEBUG DO SERVIDOR (SEM SHELL_EXEC) ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Informações do ambiente
echo "1. AMBIENTE\n";
echo "   PHP: " . PHP_VERSION . "\n";
echo "   OS: " . php_uname() . "\n";
echo "   Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "   HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'ON' : 'OFF') . "\n\n";

// 2. Verificar funções desabilitadas
echo "2. FUNÇÕES PHP\n";
$disabled = ini_get('disable_functions');
if ($disabled) {
    echo "   Funções desabilitadas: " . $disabled . "\n";
} else {
    echo "   Funções desabilitadas: Nenhuma\n";
}
echo "   shell_exec: " . (function_exists('shell_exec') ? "✓" : "✗") . "\n";
echo "   exec: " . (function_exists('exec') ? "✓" : "✗") . "\n\n";

// 3. Verificar arquivos críticos
echo "3. ARQUIVOS CRÍTICOS\n";
$files = [
    'app/Http/Controllers/ChatController.php',
    'app/Services/LocationService.php', 
    'app/Models/AnonymousUser.php',
    'resources/views/welcome.blade.php',
    '.env',
    'artisan'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $mtime = filemtime($file);
        $size = filesize($file);
        echo "   {$file}: ✓ ({$size} bytes, " . date('Y-m-d H:i:s', $mtime) . ")\n";
    } else {
        echo "   {$file}: ✗ ARQUIVO NÃO ENCONTRADO\n";
    }
}

// 4. Verificar permissões
echo "\n4. PERMISSÕES\n";
$dirs = ['storage', 'storage/logs', 'bootstrap/cache', 'app'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? "✓" : "✗";
        echo "   {$dir}: {$perms} writable:{$writable}\n";
    } else {
        echo "   {$dir}: ✗ NÃO EXISTE\n";
    }
}

// 5. Testar Laravel e banco de dados
echo "\n5. LARAVEL E BANCO\n";
try {
    if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        echo "   Laravel bootstrap: ✓\n";
        
        // Framework version
        echo "   Laravel version: " . app()->version() . "\n";
        
       
        
        try {
            $pdo = DB::connection()->getPdo();
            echo "   Conexão DB: ✓\n";
            echo "   Driver DB: " . DB::connection()->getDriverName() . "\n";
            
            // Verificar tabela
            $tables = DB::select("SHOW TABLES LIKE 'anonymous_users'");
            if (!empty($tables)) {
                echo "   Tabela anonymous_users: ✓\n";
                
                $count = AnonymousUser::count();
                echo "   Total usuários anônimos: {$count}\n";
                
                // Últimos usuários
                $recent = AnonymousUser::orderBy('created_at', 'desc')->take(3)->get();
                foreach ($recent as $user) {
                    $created = $user->created_at->format('Y-m-d H:i:s');
                    echo "      ID: {$user->id}, Session: '{$user->session_id}', Nome: '{$user->name}', Criado: {$created}\n";
                }
            } else {
                echo "   Tabela anonymous_users: ✗ NÃO EXISTE\n";
            }
            
        } catch (Exception $dbError) {
            echo "   Erro DB: " . $dbError->getMessage() . "\n";
        }
        
    } else {
        echo "   Laravel: ✗ Arquivos básicos não encontrados\n";
    }
    
} catch (Exception $e) {
    echo "   Erro Laravel: " . $e->getMessage() . "\n";
}

// 6. Testar funcionalidade do ChatController
echo "\n6. TESTE CHATCONTROLLER\n";
try {
    // Criar usuário de teste
    $testSessionId = 'test_debug_' . time();
    $testNickname = 'DebugUser';
    
    echo "   Criando usuário teste...\n";
    $testUser = AnonymousUser::create([
        'session_id' => $testSessionId,
        'name' => $testNickname,
        'avatar_type' => 'default',
        'real_latitude' => -23.5505,
        'real_longitude' => -46.6333,
        'latitude' => -23.5505,
        'longitude' => -46.6333,
        'privacy_radius' => 5000,
        'is_online' => true,
        'last_seen' => now()
    ]);
    echo "   Usuário teste criado: ✓ (ID: {$testUser->id})\n";
    
    // Simular lógica do ChatController::getSimpleUserInfo
    $userId = 'anon_' . $testSessionId;
    echo "   Testando com userId: {$userId}\n";
    
    $sessionId = str_replace('anon_', '', $userId);
    echo "   SessionId extraído: {$sessionId}\n";
    
    $anonymousUser = AnonymousUser::where('session_id', $sessionId)->first();
    if ($anonymousUser && $anonymousUser->name && $anonymousUser->name !== 'Usuário Anônimo') {
        echo "   Busca direta: ✓ Nome encontrado: '{$anonymousUser->name}'\n";
    } else {
        echo "   Busca direta: ✗ Não encontrado\n";
        
        // Tentar com prefixo
        $anonymousUserWithPrefix = AnonymousUser::where('session_id', $userId)->first();
        if ($anonymousUserWithPrefix && $anonymousUserWithPrefix->name) {
            echo "   Busca com prefixo: ✓ Nome: '{$anonymousUserWithPrefix->name}'\n";
        } else {
            echo "   Busca com prefixo: ✗ Não encontrado\n";
        }
    }
    
    // Limpeza
    AnonymousUser::where('session_id', $testSessionId)->delete();
    echo "   Usuário teste removido: ✓\n";
    
} catch (Exception $e) {
    echo "   Erro teste ChatController: " . $e->getMessage() . "\n";
}

// 7. Informações de configuração
echo "\n7. CONFIGURAÇÃO\n";
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    echo "   .env existe: ✓\n";
    echo "   APP_DEBUG: " . (strpos($env, 'APP_DEBUG=true') !== false ? "true" : "false") . "\n";
    echo "   APP_ENV: " . (preg_match('/APP_ENV=(.+)/', $env, $matches) ? trim($matches[1]) : "não definido") . "\n";
    
    // Não mostrar dados sensíveis, apenas confirmar existência
    echo "   DB_CONNECTION: " . (strpos($env, 'DB_CONNECTION=') !== false ? "definido" : "não definido") . "\n";
} else {
    echo "   .env: ✗ NÃO ENCONTRADO\n";
}

// 8. Verificar logs se existirem
echo "\n8. LOGS\n";
if (file_exists('storage/logs/laravel.log')) {
    $logSize = filesize('storage/logs/laravel.log');
    echo "   Laravel log: ✓ ({$logSize} bytes)\n";
    
    // Ler últimas linhas manualmente
    if ($logSize > 0) {
        $handle = fopen('storage/logs/laravel.log', 'r');
        if ($handle) {
            fseek($handle, max(0, $logSize - 2000)); // Últimos ~2KB
            $tail = fread($handle, 2000);
            fclose($handle);
            
            $lines = explode("\n", $tail);
            $lastLines = array_slice($lines, -5); // Últimas 5 linhas
            
            echo "   Últimas linhas:\n";
            foreach ($lastLines as $line) {
                if (!empty(trim($line))) {
                    echo "      " . substr($line, 0, 100) . (strlen($line) > 100 ? "..." : "") . "\n";
                }
            }
        }
    }
} else {
    echo "   Laravel log: ✗ NÃO ENCONTRADO\n";
}

echo "\n=== FIM DO DEBUG ===\n";

// Salvar resultado
$timestamp = date('Y-m-d_H-i-s');
$filename = "debug_resultado_{$timestamp}.txt";
file_put_contents($filename, ob_get_contents());
echo "\nResultado salvo em: {$filename}\n";
?>
