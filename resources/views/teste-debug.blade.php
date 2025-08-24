<!DOCTYPE html>
<html>
<head>
    <title>Teste de Chat - Debug</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Teste de Debug do Chat</h1>
    
    <div>
        <label>Nickname:</label>
        <input type="text" id="nickname" placeholder="Digite um nickname" value="TesteDebug">
        <button onclick="testarChat()">Testar Chat</button>
    </div>
    
    <div>
        <h3>Resultados:</h3>
        <div id="resultado"></div>
    </div>
    
    <script>
    async function testarChat() {
        const resultado = document.getElementById('resultado');
        const nickname = document.getElementById('nickname').value;
        
        resultado.innerHTML = '<p>Iniciando teste...</p>';
        
        try {
            // 1. Gerar session ID
            const sessionId = 'anon_debug_' + Date.now();
            resultado.innerHTML += `<p>1. Session ID gerado: ${sessionId}</p>`;
            
            // 2. Primeiro salvar localização com nickname
            const locationResponse = await fetch('/location/anonymous', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: -23.5505,
                    longitude: -46.6333,
                    session_id: sessionId,
                    avatar_type: 'default',
                    name: nickname
                })
            });
            
            const locationData = await locationResponse.json();
            resultado.innerHTML += `<p>2. Localização salva: ${locationData.success ? '✓' : '✗'}</p>`;
            if (!locationData.success) {
                resultado.innerHTML += `<p>   Erro: ${locationData.message}</p>`;
            }
            
            // 3. Aguardar um pouco e então conectar ao chat
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // 4. Conectar ao chat
            const chatResponse = await fetch('/chat/find-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Anonymous-Session-ID': sessionId
                },
                body: JSON.stringify({})
            });
            
            const chatData = await chatResponse.json();
            resultado.innerHTML += `<p>3. Chat conectado: ${chatData.success ? '✓' : '✗'}</p>`;
            
            if (chatData.success && chatData.room) {
                const roomCode = chatData.room.room_code;
                resultado.innerHTML += `<p>   Sala: ${roomCode}</p>`;
                
                // 5. Buscar usuários da sala
                const usersResponse = await fetch(`/chat/${roomCode}/users`, {
                    headers: {
                        'X-Anonymous-Session-ID': sessionId
                    }
                });
                
                const usersData = await usersResponse.json();
                resultado.innerHTML += `<p>4. Usuários na sala: ${usersData.success ? '✓' : '✗'}</p>`;
                
                if (usersData.success && usersData.users) {
                    usersData.users.forEach(user => {
                        const isMe = user.user_id.includes('debug_');
                        const style = isMe ? 'color: green; font-weight: bold;' : '';
                        resultado.innerHTML += `<p style="${style}">   ${user.user_id}: ${user.user_name}</p>`;
                    });
                }
            } else {
                resultado.innerHTML += `<p>   Erro: ${chatData.message}</p>`;
            }
            
        } catch (error) {
            resultado.innerHTML += `<p style="color: red;">ERRO: ${error.message}</p>`;
            console.error('Erro no teste:', error);
        }
    }
    </script>
</body>
</html>
