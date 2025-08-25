<?php
/**
 * Script de teste direto das rotas do chat
 */

echo "=== Teste das Rotas de Chat ===\n\n";

$base_url = 'http://localhost:8000';
$session_id = 'test_' . time();

// Headers comuns
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test', // Será ignorado por ser teste
    'User-Agent: ChatTestScript/1.0'
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
    echo "Response: $response\n\n";
    
    return json_decode($response, true);
}

// Teste 1: Definir nickname
echo "1. Testando set-nickname...\n";
$result = makeRequest(
    "$base_url/chat/set-nickname",
    'POST',
    ['nickname' => 'TestUser', 'session_id' => $session_id],
    $headers
);

if (!$result || !$result['success']) {
    echo "❌ Falha ao definir nickname\n\n";
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

if (!$roomResult || !$roomResult['success']) {
    echo "❌ Falha ao encontrar/criar sala\n\n";
    exit(1);
}

$roomId = $roomResult['room']['room_code'];
echo "✅ Sala encontrada: $roomId\n\n";

// Teste 3: Enviar mensagem
echo "3. Testando envio de mensagem...\n";
$messageResult = makeRequest(
    "$base_url/chat/$roomId/send",
    'POST',
    ['content' => 'Mensagem de teste', 'session_id' => $session_id],
    $headers
);

if (!$messageResult || !$messageResult['success']) {
    echo "❌ Falha ao enviar mensagem\n\n";
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

if (!$messagesResult || !$messagesResult['success']) {
    echo "❌ Falha ao obter mensagens\n\n";
} else {
    $messageCount = count($messagesResult['messages']);
    echo "✅ $messageCount mensagens obtidas\n";
    
    if ($messageCount > 0) {
        echo "Última mensagem: " . $messagesResult['messages'][0]['content'] . "\n";
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

if (!$usersResult || !$usersResult['success']) {
    echo "❌ Falha ao obter usuários\n\n";
} else {
    $userCount = count($usersResult['users']);
    echo "✅ $userCount usuários na sala\n\n";
}

echo "=== Fim dos Testes ===\n";
