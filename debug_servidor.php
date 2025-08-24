<?php
// Script de debug completo para servidor
echo "=== DEBUG COMPLETO DO SERVIDOR ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Informações do ambiente
echo "1. AMBIENTE\n";
echo "   PHP: " . PHP_VERSION . "\n";
echo "   OS: " . php_uname() . "\n";
echo "   Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n\n";

// 2. Verificar arquivos críticos e suas datas
echo "2. ARQUIVOS CRÍTICOS\n";
$files = [
    'app/Http/Controllers/ChatController.php',
    'app/Services/LocationService.php', 
    'app/Models/AnonymousUser.php',
    'resources/views/welcome.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $mtime = filemtime($file);
        $size = filesize($file);
        echo "   {$file}: ✓ ({$size} bytes, " . date('Y-m-d H:i:s', $mtime) . ")\n";
        
        // Verificar sintaxe se for PHP
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = shell_exec("php -l {$file} 2>&1");
            if (strpos($output, 'No syntax errors') === false) {
                echo "      ERRO DE SINTAXE: {$output}\n";
            }
        }
    } else {
        echo "   {$file}: ✗ ARQUIVO NÃO ENCONTRADO\n";
    }
}

// 3. Verificar se Laravel funciona
echo "\n3. LARAVEL\n";
try {
    if (file_exists('artisan')) {
        $version = trim(shell_exec("php artisan --version 2>&1"));
        echo "   Version: {$version}\n";
        
        // Tentar limpar caches
        shell_exec("php artisan config:clear 2>&1");
        shell_exec("php artisan cache:clear 2>&1");
        shell_exec("php artisan route:clear 2>&1");
        echo "   Caches limpos: ✓\n";
        
        // Verificar rotas do chat
        $routes = shell_exec("php artisan route:list --name=chat 2>&1");
        $chatRoutes = substr_count($routes, 'chat.');
        echo "   Chat routes: {$chatRoutes}\n";
        
    } else {
        echo "   Artisan: ✗ NÃO ENCONTRADO\n";
    }
} catch (Exception $e) {
    echo "   Erro Laravel: " . $e->getMessage() . "\n";
}

// 4. Testar conexão com banco de dados
echo "\n4. BANCO DE DADOS\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    use Illuminate\Support\Facades\DB;
    use App\Models\AnonymousUser;
    
    // Testar conexão
    $pdo = DB::connection()->getPdo();
    echo "   Conexão: ✓\n";
    
    // Verificar tabela anonymous_users
    $exists = DB::select("SHOW TABLES LIKE 'anonymous_users'");
    if ($exists) {
        echo "   Tabela anonymous_users: ✓\n";
        
        $count = AnonymousUser::count();
        echo "   Total de usuários anônimos: {$count}\n";
        
        // Mostrar últimos 3 usuários
        $recent = AnonymousUser::orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recent as $user) {
            echo "      ID: {$user->id}, Session: '{$user->session_id}', Nome: '{$user->name}'\n";
        }
    } else {
        echo "   Tabela anonymous_users: ✗ NÃO EXISTE\n";
    }
    
} catch (Exception $e) {
    echo "   Erro DB: " . $e->getMessage() . "\n";
}

// 5. Testar funcionalidade específica do ChatController
echo "\n5. TESTE DO CHATCONTROLLER\n";
try {
    // Simular requisição
    $testUserId = 'anon_debug_' . time();
    
    // Incluir o ChatController manualmente para testar
    require_once 'app/Http/Controllers/ChatController.php';
    
    echo "   Simulando getSimpleUserInfo para: {$testUserId}\n";
    
    // Como não podemos instanciar direto, vamos reproduzir a lógica
    $sessionId = str_replace('anon_', '', $testUserId);
    echo "   Session ID extraído: {$sessionId}\n";
    
    $anonymousUser = AnonymousUser::where('session_id', $sessionId)->first();
    if ($anonymousUser && $anonymousUser->name && $anonymousUser->name !== 'Usuário Anônimo') {
        echo "   Resultado: ✓ Nome: '{$anonymousUser->name}'\n";
    } else {
        echo "   Resultado: ✗ Usando nome padrão\n";
        
        // Tentar com prefixo
        $anonymousUserWithPrefix = AnonymousUser::where('session_id', $testUserId)->first();
        if ($anonymousUserWithPrefix) {
            echo "   Com prefixo: ✓ Nome: '{$anonymousUserWithPrefix->name}'\n";
        } else {
            echo "   Com prefixo: ✗ Não encontrado\n";
        }
    }
    
} catch (Exception $e) {
    echo "   Erro ChatController: " . $e->getMessage() . "\n";
}

// 6. Verificar logs recentes
echo "\n6. LOGS RECENTES\n";
if (file_exists('storage/logs/laravel.log')) {
    $logs = shell_exec('tail -n 10 storage/logs/laravel.log');
    echo "   Últimas 10 linhas do log:\n";
    echo "   " . str_replace("\n", "\n   ", $logs) . "\n";
} else {
    echo "   Log do Laravel: ✗ NÃO ENCONTRADO\n";
}

// 7. Informações do servidor web
echo "\n7. SERVIDOR WEB\n";
echo "   HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "   HTTPS: " . (isset($_SERVER['HTTPS']) ? 'ON' : 'OFF') . "\n";

echo "\n=== FIM DO DEBUG ===\n";

// Salvar resultado em arquivo para análise
$result = ob_get_contents();
file_put_contents('debug_resultado_' . date('Y-m-d_H-i-s') . '.txt', $result);
echo "\nResultado salvo em: debug_resultado_" . date('Y-m-d_H-i-s') . ".txt\n";
?>
