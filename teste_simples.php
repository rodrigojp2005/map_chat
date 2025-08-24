<?php
// Teste direto do endpoint de chat
echo "=== TESTE DO ENDPOINT DE CHAT ===\n";

// Teste 1: Verificar se a página principal carrega
$url = 'http://localhost';
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $url = $protocol . '://' . $_SERVER['HTTP_HOST'];
}

echo "URL base: $url\n\n";

// Teste 2: Simular requisição HTTP para chat/find-room
echo "Testando endpoint /chat/find-room...\n";

// Criar contexto para requisição POST
$postData = json_encode([]);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'X-Anonymous-Session-ID: test_' . time()
        ],
        'content' => $postData,
        'timeout' => 10
    ]
]);

// Tentar fazer requisição
$response = @file_get_contents($url . '/chat/find-room', false, $context);

if ($response === false) {
    $error = error_get_last();
    echo "❌ ERRO: " . ($error['message'] ?? 'Falha na requisição') . "\n";
    echo "Isso confirma que o erro do Monolog está travando a aplicação.\n\n";
} else {
    echo "✅ Endpoint respondeu: " . substr($response, 0, 100) . "...\n\n";
}

// Teste 3: Verificar arquivos críticos
echo "Verificando arquivos críticos:\n";
$files = [
    '.env' => 'Configuração',
    'app/Http/Controllers/ChatController.php' => 'Controller do Chat',
    'config/logging.php' => 'Config de Log'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        echo "✅ $desc: OK\n";
    } else {
        echo "❌ $desc: FALTANDO\n";
    }
}

echo "\n=== SOLUÇÕES ===\n";
echo "1. Execute: php artisan config:clear\n";
echo "2. Execute: php artisan cache:clear\n"; 
echo "3. Edite .env e adicione: LOG_CHANNEL=errorlog\n";
echo "4. Se não resolver: composer require \"monolog/monolog:^2.9\"\n";
echo "5. Teste novamente o chat no navegador\n";
?>
