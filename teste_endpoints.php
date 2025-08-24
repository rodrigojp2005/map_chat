<?php
// Criar usuário de teste primeiro
    use App\Models\AnonymousUser;
    use App\Http\Controllers\ChatController;
    use Illuminate\Http\Request;

// Teste específico do endpoint de chat via HTTP
echo "=== TESTE DOS ENDPOINTS DE CHAT ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Simular uma requisição real para o endpoint de chat
echo "1. TESTE DO ENDPOINT /chat/find-room\n";

try {
    // Bootstrap Laravel
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    
    
    $testSessionId = 'anon_test_endpoint_' . time();
    $testNickname = 'EndpointTest';
    
    echo "   Criando usuário para teste: {$testSessionId}\n";
    
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
    
    echo "   Usuário criado: ✓ (ID: {$testUser->id})\n\n";
    
    // 2. Simular requisição para ChatController
    echo "2. SIMULAÇÃO DE REQUISIÇÃO HTTP\n";
    
    // Criar request mock
    $request = new Request();
    $request->headers->set('X-Anonymous-Session-ID', $testSessionId);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    // Instanciar controller
    $controller = new ChatController(app()->make(\App\Services\ChatRoomService::class));
    
    echo "   Chamando findOrCreateRoom...\n";
    $response = $controller->findOrCreateRoom($request);
    
    if ($response->getStatusCode() === 200) {
        $data = json_decode($response->getContent(), true);
        echo "   Status: ✓ 200 OK\n";
        echo "   Success: " . ($data['success'] ? "✓" : "✗") . "\n";
        echo "   Room ID: " . ($data['room']['room_code'] ?? 'N/A') . "\n";
        echo "   User ID: " . ($data['user_id'] ?? 'N/A') . "\n";
    } else {
        echo "   Status: ✗ " . $response->getStatusCode() . "\n";
        echo "   Content: " . $response->getContent() . "\n";
    }
    
    // 3. Testar getRoomUsers para ver como os nomes aparecem
    echo "\n3. TESTE DO ENDPOINT getRoomUsers\n";
    
    if (isset($data['room']['room_code'])) {
        $roomCode = $data['room']['room_code'];
        echo "   Testando sala: {$roomCode}\n";
        
        $usersResponse = $controller->getRoomUsers($request, $roomCode);
        
        if ($usersResponse->getStatusCode() === 200) {
            $usersData = json_decode($usersResponse->getContent(), true);
            echo "   Status: ✓ 200 OK\n";
            echo "   Usuários encontrados: " . count($usersData['users'] ?? []) . "\n";
            
            foreach ($usersData['users'] ?? [] as $user) {
                $userId = $user['user_id'] ?? 'N/A';
                $userName = $user['user_name'] ?? 'N/A';
                echo "      User ID: {$userId}, Nome: '{$userName}'\n";
                
                // Se é nosso usuário de teste, verificar se o nome está correto
                if (strpos($userId, 'test_endpoint_') !== false) {
                    if ($userName === $testNickname) {
                        echo "      ✓ NICKNAME CORRETO!\n";
                    } else {
                        echo "      ✗ NICKNAME INCORRETO! Esperado: '{$testNickname}', Recebido: '{$userName}'\n";
                    }
                }
            }
        } else {
            echo "   Status: ✗ " . $usersResponse->getStatusCode() . "\n";
            echo "   Content: " . $usersResponse->getContent() . "\n";
        }
    }
    
    // 4. Verificar como está salvo no banco
    echo "\n4. VERIFICAÇÃO NO BANCO DE DADOS\n";
    $dbUser = AnonymousUser::where('session_id', $testSessionId)->first();
    if ($dbUser) {
        echo "   Usuário no banco: ✓\n";
        echo "   Session ID: '{$dbUser->session_id}'\n";
        echo "   Nome: '{$dbUser->name}'\n";
        echo "   Avatar: '{$dbUser->avatar_type}'\n";
    } else {
        echo "   Usuário no banco: ✗ NÃO ENCONTRADO\n";
    }
    
    // 5. Limpeza
    echo "\n5. LIMPEZA\n";
    AnonymousUser::where('session_id', $testSessionId)->delete();
    echo "   Usuário de teste removido: ✓\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// 6. Verificar rotas registradas
echo "\n6. ROTAS REGISTRADAS\n";
try {
    $routes = app('router')->getRoutes();
    $chatRoutes = 0;
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'chat') !== false) {
            $chatRoutes++;
            echo "   {$route->methods()[0]} /{$uri}\n";
        }
    }
    
    echo "   Total rotas de chat: {$chatRoutes}\n";
    
} catch (Exception $e) {
    echo "   Erro ao listar rotas: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";

// Salvar resultado
$timestamp = date('Y-m-d_H-i-s');
$filename = "teste_endpoints_{$timestamp}.txt";
file_put_contents($filename, ob_get_contents());
echo "\nResultado salvo em: {$filename}\n";
?>
