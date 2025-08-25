<!DOCTYPE html>
<html>
<head>
    <title>Debug Chat MapChat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .step { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; margin: 5px; }
        #logs { background: #f8f9fa; padding: 10px; max-height: 400px; overflow-y: auto; font-family: monospace; white-space: pre-line; }
    </style>
</head>
<body>
    <h1>🔧 Debug Sistema de Chat MapChat</h1>
    
    <div class="step info">
        <h3>📋 Teste Completo</h3>
        <button onclick="testeCompleto()">▶️ Executar Teste Completo</button>
        <button onclick="limparLogs()">🗑️ Limpar Logs</button>
    </div>

    <div class="step">
        <h3>📊 Logs de Debug</h3>
        <div id="logs"></div>
    </div>

    <script>
    function log(message, type = 'info') {
        const logs = document.getElementById('logs');
        const timestamp = new Date().toLocaleTimeString();
        const colors = {
            info: '#333',
            success: '#28a745', 
            error: '#dc3545',
            warning: '#ffc107'
        };
        
        logs.innerHTML += `<span style="color: ${colors[type]}">[${timestamp}] ${message}</span>\n`;
        logs.scrollTop = logs.scrollHeight;
        console.log(`[${type.toUpperCase()}] ${message}`);
    }

    function limparLogs() {
        document.getElementById('logs').innerHTML = '';
    }

    async function testeCompleto() {
        log('🚀 INICIANDO TESTE COMPLETO DO CHAT', 'info');
        
        try {
            // 1. Gerar sessão única
            const sessionId = 'anon_debug_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            const nickname = 'DebugUser_' + Date.now().toString().slice(-4);
            
            log(`1️⃣ Session ID gerado: ${sessionId}`, 'info');
            log(`1️⃣ Nickname gerado: ${nickname}`, 'info');

            // 2. Primeiro salvar no banco via location/anonymous
            log('2️⃣ Salvando usuário no banco...', 'info');
            
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
                    name: nickname,
                    privacy_radius: 5000
                })
            });

            const locationData = await locationResponse.json();
            
            if (locationData.success) {
                log('✅ Usuário salvo no banco com sucesso!', 'success');
            } else {
                log(`❌ Erro ao salvar usuário: ${locationData.message}`, 'error');
                return;
            }

            // 3. Aguardar um pouco para garantir que foi salvo
            log('3️⃣ Aguardando salvamento...', 'info');
            await new Promise(resolve => setTimeout(resolve, 1000));

            // 4. Tentar conectar ao chat
            log('4️⃣ Conectando ao chat...', 'info');
            
            const chatResponse = await fetch('/chat/find-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Anonymous-Session-ID': sessionId
                },
                body: JSON.stringify({
                    anonymous_session_id: sessionId
                })
            });

            log(`📡 Status da resposta: ${chatResponse.status} ${chatResponse.statusText}`, 'info');

            const chatData = await chatResponse.json();
            log(`📦 Dados recebidos: ${JSON.stringify(chatData, null, 2)}`, 'info');

            if (chatData.success && chatData.room) {
                log('✅ CHAT CONECTADO COM SUCESSO!', 'success');
                log(`🏠 Sala: ${chatData.room.room_code} (${chatData.room.name})`, 'success');
                log(`👤 User ID: ${chatData.user_id}`, 'info');

                // 5. Buscar usuários da sala para verificar nickname
                log('5️⃣ Verificando usuários na sala...', 'info');
                
                const usersResponse = await fetch(`/chat/${chatData.room.room_code}/users`, {
                    method: 'GET',
                    headers: {
                        'X-Anonymous-Session-ID': sessionId
                    }
                });

                const usersData = await usersResponse.json();
                log(`👥 Dados dos usuários: ${JSON.stringify(usersData, null, 2)}`, 'info');

                if (usersData.success && usersData.users) {
                    const myUser = usersData.users.find(user => 
                        user.user_id === chatData.user_id || 
                        user.user_id.includes(sessionId.replace('anon_', ''))
                    );

                    if (myUser) {
                        log(`👤 MEU USUÁRIO ENCONTRADO!`, 'success');
                        log(`   Nome: "${myUser.user_name}"`, 'success');
                        log(`   ID: "${myUser.user_id}"`, 'info');
                        
                        if (myUser.user_name === nickname) {
                            log('🎉 NICKNAME ESTÁ CORRETO!', 'success');
                        } else {
                            log(`⚠️ NICKNAME INCORRETO! Esperado: "${nickname}", Recebido: "${myUser.user_name}"`, 'warning');
                        }
                    } else {
                        log('❌ Meu usuário não encontrado na lista', 'error');
                    }
                } else {
                    log(`❌ Erro ao buscar usuários: ${usersData.message}`, 'error');
                }

            } else {
                log(`❌ ERRO AO CONECTAR: ${chatData.message}`, 'error');
            }

        } catch (error) {
            log(`💥 ERRO CRÍTICO: ${error.message}`, 'error');
            log(`Stack trace: ${error.stack}`, 'error');
        }
    }
    </script>
</body>
</html>
