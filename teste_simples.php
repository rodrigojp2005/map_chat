<?php
// Debug sem usar sistema de log do Laravel - SAFE VERSION
echo "=== TESTE SIMPLES SEM LOGS ===\n";

try {
    // Testar sintaxe dos arquivos principais primeiro
    $files = [
        'app/Http/Controllers/ChatController.php',
        'app/Services/LocationService.php'
    ];
    
    foreach ($files as $file) {
        $syntax = `php -l $file 2>&1`;
        if (strpos($syntax, 'No syntax errors') !== false) {
            echo "✓ $file - sintaxe OK\n";
        } else {
            echo "✗ $file - ERRO: $syntax\n";
        }
    }
    
    // Teste básico de banco sem Laravel
    $config = parse_ini_file('.env');
    
    if (isset($config['DB_HOST'])) {
        $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_DATABASE']}";
        $pdo = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD']);
        echo "✓ Conexão direta com banco OK\n";
        
        // Testar tabela
        $stmt = $pdo->query("SELECT COUNT(*) FROM anonymous_users");
        $count = $stmt->fetchColumn();
        echo "✓ Tabela anonymous_users: $count registros\n";
        
        // Buscar usuários recentes
        $stmt = $pdo->query("SELECT session_id, name FROM anonymous_users ORDER BY created_at DESC LIMIT 3");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['session_id']}: {$row['name']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== AGORA TESTE A PÁGINA ORIGINAL ===\n";
echo "Acesse: https://seudominio.com/ \n";
echo "1. Digite um nickname\n";
echo "2. Clique em 'Ativar Chat'\n";
echo "3. Veja se o nickname aparece\n";
?>
