<?php
/**
 * Script de teste direto das rotas do chat no servidor online
 */

echo "=== Teste das Rotas de Chat (Servidor Online) ===\n\n";

$base_url = 'https://mapchat.com.br'; // Servidor online
$session_id = 'test_' . time();

// Headers comuns
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: ChatTestScript/1.0',
    'Referer: https://mapchat.com.br/'
];

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIEJAR => '/tmp/chat_cookies.txt',
        CURLOPT_COOKIEFILE => '/tmp/chat_cookies.txt'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "ERRO CURL: $error\n";
        return null;
    }
    
    echo "HTTP $httpCode\n";
    
    // Verificar se é JSON válido
    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Response (não-JSON): " . substr($response, 0, 500) . "...\n\n";
        return null;
    }
    
    echo "Response: " . json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";
    return $decoded;
}

// Primeiro, obter a página principal para pegar o CSRF token
echo "0. Obtendo CSRF token...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $base_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_COOKIEJAR => '/tmp/chat_cookies.txt',
    CURLOPT_COOKIEFILE => '/tmp/chat_cookies.txt'
]);
$homepage = curl_exec($ch);
curl_close($ch);

// Extrair CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $homepage, $matches);
$csrfToken = $matches[1] ?? 'not-found';
echo "CSRF Token: $csrfToken\n\n";

// Adicionar CSRF token aos headers
$headers[] = "X-CSRF-TOKEN: $csrfToken";

// Teste 1: Definir nickname
echo "1. Testando set-nickname...\n";
$result = makeRequest(
    "$base_url/chat/set-nickname",
    'POST',
    ['nickname' => 'TestUser', 'session_id' => $session_id],
    $headers
);

if (!$result || !isset($result['success']) || !$result['success']) {
    echo "❌ Falha ao definir nickname\n\n";
    if ($result && isset($result['message'])) {
        echo "Erro: " . $result['message'] . "\n\n";
    }
} else {
    echo "✅ Nickname definido com sucesso\n\n";
}

// Teste 2: Encontrar/criar sala
echo "2. Testando find-room...\n";
$roomResult = makeRequest(
    "$base_url/chat/find-room",
    'POST',
    ['session_id' => $session_id],
    $headers
);

if (!$roomResult || !isset($roomResult['success']) || !$roomResult['success']) {
    echo "❌ Falha ao encontrar/criar sala\n\n";
    if ($roomResult && isset($roomResult['message'])) {
        echo "Erro: " . $roomResult['message'] . "\n\n";
    }
    exit(1);
}

$roomId = $roomResult['room']['room_id'] ?? $roomResult['room']['room_code'] ?? null;
if (!$roomId) {
    echo "❌ Room ID não encontrado na resposta\n\n";
    exit(1);
}

echo "✅ Sala encontrada: $roomId\n\n";

// Teste 3: Enviar mensagem
echo "3. Testando envio de mensagem...\n";
$messageResult = makeRequest(
    "$base_url/chat/$roomId/send",
    'POST',
    ['content' => 'Mensagem de teste from script', 'session_id' => $session_id, 'message_type' => 'text'],
    $headers
);

if (!$messageResult || !isset($messageResult['success']) || !$messageResult['success']) {
    echo "❌ Falha ao enviar mensagem\n\n";
    if ($messageResult && isset($messageResult['message'])) {
        echo "Erro: " . $messageResult['message'] . "\n\n";
    }
} else {
    echo "✅ Mensagem enviada com sucesso\n\n";
}

// Teste 4: Obter mensagens
echo "4. Testando obtenção de mensagens...\n";
$messagesResult = makeRequest(
    "$base_url/chat/$roomId/messages?session_id=$session_id",
    'GET',
    null,
    $headers
);

if (!$messagesResult || !isset($messagesResult['success']) || !$messagesResult['success']) {
    echo "❌ Falha ao obter mensagens\n\n";
    if ($messagesResult && isset($messagesResult['message'])) {
        echo "Erro: " . $messagesResult['message'] . "\n\n";
    }
} else {
    $messageCount = count($messagesResult['messages'] ?? []);
    echo "✅ $messageCount mensagens obtidas\n";
    
    if ($messageCount > 0) {
        $lastMessage = end($messagesResult['messages']);
        echo "Última mensagem: " . ($lastMessage['message'] ?? $lastMessage['content'] ?? 'N/A') . "\n";
        echo "Por: " . ($lastMessage['user_name'] ?? 'N/A') . "\n";
    }
    echo "\n";
}

// Teste 5: Obter usuários da sala
echo "5. Testando obtenção de usuários...\n";
$usersResult = makeRequest(
    "$base_url/chat/$roomId/users?session_id=$session_id",
    'GET',
    null,
    $headers
);

if (!$usersResult || !isset($usersResult['success']) || !$usersResult['success']) {
    echo "❌ Falha ao obter usuários\n\n";
    if ($usersResult && isset($usersResult['message'])) {
        echo "Erro: " . $usersResult['message'] . "\n\n";
    }
} else {
    $userCount = count($usersResult['users'] ?? []);
    echo "✅ $userCount usuários na sala\n\n";
}

echo "=== Fim dos Testes ===\n";
