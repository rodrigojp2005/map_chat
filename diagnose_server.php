<?php
// Script de diagnóstico para comparar ambiente local vs servidor

echo "=== DIAGNÓSTICO DO SISTEMA DE CHAT ===\n\n";

// 1. Versão do PHP
echo "1. PHP Version: " . PHP_VERSION . "\n";

// 2. Extensões necessárias
$extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    echo "   Extension {$ext}: " . (extension_loaded($ext) ? "✓" : "✗") . "\n";
}

// 3. Verificar se os arquivos existem
$files = [
    'app/Http/Controllers/ChatController.php',
    'app/Services/LocationService.php',
    'app/Models/AnonymousUser.php',
    'resources/views/welcome.blade.php'
];

echo "\n2. Arquivos críticos:\n";
foreach ($files as $file) {
    echo "   {$file}: " . (file_exists($file) ? "✓" : "✗") . "\n";
}

// 4. Verificar sintaxe dos arquivos PHP principais
echo "\n3. Sintaxe dos arquivos:\n";
$phpFiles = [
    'app/Http/Controllers/ChatController.php',
    'app/Services/LocationService.php',
    'app/Models/AnonymousUser.php'
];

foreach ($phpFiles as $file) {
    $output = shell_exec("php -l {$file} 2>&1");
    $status = strpos($output, 'No syntax errors') !== false ? "✓" : "✗ ERROR";
    echo "   {$file}: {$status}\n";
    if ($status === "✗ ERROR") {
        echo "      Error: {$output}\n";
    }
}

// 5. Verificar configuração do Laravel
echo "\n4. Laravel Configuration:\n";
if (file_exists('artisan')) {
    echo "   Artisan exists: ✓\n";
    
    // Verificar se pode executar comandos artisan
    $output = shell_exec("php artisan --version 2>&1");
    echo "   Laravel Version: " . trim($output) . "\n";
    
    // Verificar cache
    echo "   Clearing caches...\n";
    shell_exec("php artisan config:clear 2>&1");
    shell_exec("php artisan cache:clear 2>&1");
    shell_exec("php artisan route:clear 2>&1");
    echo "   Caches cleared: ✓\n";
    
    // Verificar rotas
    $routes = shell_exec("php artisan route:list --name=chat 2>&1");
    $routeCount = substr_count($routes, 'chat.');
    echo "   Chat routes found: {$routeCount}\n";
} else {
    echo "   Artisan: ✗ (arquivo não encontrado)\n";
}

// 6. Verificar permissões de arquivos
echo "\n5. Permissões:\n";
$dirs = ['storage', 'storage/logs', 'bootstrap/cache'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? "✓" : "✗";
        echo "   {$dir}: {$perms} {$writable}\n";
    } else {
        echo "   {$dir}: ✗ (não existe)\n";
    }
}

// 7. Verificar variáveis de ambiente
echo "\n6. Environment:\n";
if (file_exists('.env')) {
    echo "   .env file: ✓\n";
    $envContent = file_get_contents('.env');
    $appDebug = strpos($envContent, 'APP_DEBUG=true') !== false ? "true" : "false";
    echo "   APP_DEBUG: {$appDebug}\n";
    
    $dbConnection = preg_match('/DB_CONNECTION=(.+)/', $envContent, $matches) ? $matches[1] : "not set";
    echo "   DB_CONNECTION: {$dbConnection}\n";
} else {
    echo "   .env file: ✗\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
