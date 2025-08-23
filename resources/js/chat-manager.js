/**
 * ChatManager - Sistema de Chat MapChat
 * Gerencia salas de chat baseadas em proximidade geográfica
 */
class ChatManager {
    constructor(locationManager) {
        this.locationManager = locationManager;
        this.currentRoom = null;
        this.currentUserId = null;
        this.userType = 'anonymous';
        this.sessionId = null;
        this.nickname = null;
        this.isConnected = false;
        this.isConnecting = false;
        this.messageHistory = [];
        this.heartbeatInterval = null;
        this.pollInterval = null;
        this.lastMessageId = 0;

        // UI Elements
        this.chatContainer = null;
        this.chatPanel = null;
        this.messagesContainer = null;
        this.messageInput = null;
        this.sendButton = null;
        this.usersList = null;
        this.roomInfo = null;
        this.nicknameModal = null;

        this.initializeUI();
        this.setupEventListeners();
    }

    /**
     * Inicializar UI do chat
     */
    initializeUI() {
        this.createChatUI();
        this.createNicknameModal();
    }

    /**
     * Criar interface do chat
     */
    createChatUI() {
        // Container principal do chat (inicialmente oculto)
        this.chatContainer = document.createElement('div');
        this.chatContainer.id = 'chat-container';
        this.chatContainer.className = 'fixed bottom-4 right-4 z-40 hidden';
        
        this.chatContainer.innerHTML = `
            <!-- Botão toggle do chat -->
            <div id="chat-toggle" class="absolute bottom-0 right-0 w-14 h-14 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-lg cursor-pointer flex items-center justify-center transition-all duration-200 transform hover:scale-110" title="Abrir Chat">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.96 8.96 0 01-4.887-1.441c-.203-.108-.417-.11-.621-.04l-3.301 1.155a1 1 0 01-1.266-1.265l1.155-3.302c.07-.204.068-.418-.04-.621A8.96 8.96 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                </svg>
                <div id="chat-notification" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden">0</div>
            </div>

            <!-- Painel do chat -->
            <div id="chat-panel" class="absolute bottom-16 right-0 w-80 h-96 bg-white rounded-lg shadow-2xl border hidden">
                <!-- Header -->
                <div class="flex items-center justify-between p-3 bg-green-600 text-white rounded-t-lg">
                    <div>
                        <h3 class="font-bold text-sm" id="chat-room-name">Chat MapChat</h3>
                        <p class="text-xs opacity-90" id="chat-room-users">0 usuários online</p>
                    </div>
                    <button id="chat-close" class="w-6 h-6 hover:bg-green-700 rounded flex items-center justify-center" title="Fechar chat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Status do chat -->
                <div id="chat-status" class="p-2 bg-yellow-50 border-b text-sm text-yellow-700 hidden">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                        <span>Conectando ao chat...</span>
                    </div>
                </div>

                <!-- Lista de mensagens -->
                <div id="chat-messages" class="flex-1 overflow-y-auto p-3 h-64 space-y-2 bg-gray-50">
                    <div class="text-center text-gray-500 text-sm py-4">
                        Configure seu avatar e localização para participar do chat
                    </div>
                </div>

                <!-- Input de mensagem -->
                <div class="border-t p-3">
                    <div id="chat-nickname-prompt" class="mb-2 text-sm text-gray-600 hidden">
                        <button id="set-nickname-btn" class="text-blue-600 hover:underline">Definir nickname</button> para começar a conversar
                    </div>
                    <div class="flex space-x-2">
                        <input type="text" id="chat-message-input" placeholder="Digite sua mensagem..." 
                               class="flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent disabled:bg-gray-100" 
                               disabled maxlength="500">
                        <button id="chat-send-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                            Enviar
                        </button>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 text-right">
                        <span id="char-count">0</span>/500 caracteres
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(this.chatContainer);

        // Referenciar elementos
        this.chatPanel = document.getElementById('chat-panel');
        this.messagesContainer = document.getElementById('chat-messages');
        this.messageInput = document.getElementById('chat-message-input');
        this.sendButton = document.getElementById('chat-send-btn');
        this.roomInfo = {
            name: document.getElementById('chat-room-name'),
            users: document.getElementById('chat-room-users')
        };
    }

    /**
     * Criar modal de nickname
     */
    createNicknameModal() {
        const modalHtml = `
            <div id="nickname-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-2xl">
                    <div class="text-center mb-4">
                        <div class="text-4xl mb-2">💭</div>
                        <h3 class="text-xl font-bold text-gray-800">Escolha seu Nickname</h3>
                        <p class="text-sm text-gray-600">Como você gostaria de aparecer no chat?</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <input type="text" id="nickname-input" placeholder="Ex: João123, Maria_SP, Gamer..." 
                                   class="w-full px-4 py-3 border rounded-lg text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                   maxlength="20">
                            <p class="text-xs text-gray-500 mt-1">Máximo 20 caracteres (letras, números e _ apenas)</p>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button id="nickname-random" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm transition-colors">
                                🎲 Aleatório
                            </button>
                            <button id="nickname-confirm" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors disabled:bg-gray-300" disabled>
                                ✓ Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.nicknameModal = document.getElementById('nickname-modal');
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Toggle do chat
        document.getElementById('chat-toggle').addEventListener('click', () => {
            this.toggleChat();
        });

        // Fechar chat
        document.getElementById('chat-close').addEventListener('click', () => {
            this.toggleChat();
        });

        // Input de mensagem
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Contador de caracteres
        this.messageInput.addEventListener('input', (e) => {
            const count = e.target.value.length;
            document.getElementById('char-count').textContent = count;
            
            if (count > 450) {
                document.getElementById('char-count').classList.add('text-red-500');
            } else {
                document.getElementById('char-count').classList.remove('text-red-500');
            }
        });

        // Enviar mensagem
        this.sendButton.addEventListener('click', () => {
            this.sendMessage();
        });

        // Nickname modal
        this.setupNicknameModalListeners();

        // Botão definir nickname
        document.getElementById('set-nickname-btn').addEventListener('click', () => {
            this.showNicknameModal();
        });

        // Hook para quando a configuração do LocationManager for aplicada
        if (this.locationManager) {
            const originalCallback = this.locationManager.onConfigurationApplied;
            this.locationManager.onConfigurationApplied = () => {
                if (originalCallback) originalCallback.call(this.locationManager);
                this.onLocationConfigured();
            };
        }
    }

    /**
     * Configurar listeners do modal de nickname
     */
    setupNicknameModalListeners() {
        const nicknameInput = document.getElementById('nickname-input');
        const confirmBtn = document.getElementById('nickname-confirm');
        const randomBtn = document.getElementById('nickname-random');

        // Validação em tempo real
        nicknameInput.addEventListener('input', (e) => {
            const value = e.target.value.trim();
            const isValid = value.length >= 2 && /^[a-zA-Z0-9_\s]+$/.test(value);
            confirmBtn.disabled = !isValid;
            
            if (value && !isValid) {
                nicknameInput.classList.add('border-red-500');
            } else {
                nicknameInput.classList.remove('border-red-500');
            }
        });

        // Enter para confirmar
        nicknameInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !confirmBtn.disabled) {
                this.confirmNickname();
            }
        });

        // Gerar nickname aleatório
        randomBtn.addEventListener('click', () => {
            const randomNicknames = [
                'Viajante', 'Explorador', 'Aventureiro', 'Curioso', 'Navegante',
                'Descobridor', 'Andarilho', 'Caminhante', 'Nômade', 'Peregrino'
            ];
            const randomNumbers = Math.floor(Math.random() * 999) + 1;
            const randomNickname = randomNicknames[Math.floor(Math.random() * randomNicknames.length)] + randomNumbers;
            
            nicknameInput.value = randomNickname;
            nicknameInput.dispatchEvent(new Event('input'));
        });

        // Confirmar nickname
        confirmBtn.addEventListener('click', () => {
            this.confirmNickname();
        });

        // Fechar modal clicando fora
        this.nicknameModal.addEventListener('click', (e) => {
            if (e.target === this.nicknameModal) {
                this.hideNicknameModal();
            }
        });
    }

    /**
     * Quando a localização é configurada, habilitar o chat
     */
    onLocationConfigured() {
        console.log('🎯 Localização configurada - habilitando chat');
        
        // Mostrar o container do chat
        this.chatContainer.classList.remove('hidden');
        
        // Usar o mesmo session ID do LocationManager
        if (this.locationManager.anonymousSessionId) {
            this.sessionId = this.locationManager.anonymousSessionId;
            this.currentUserId = this.sessionId; // Usar o formato completo
        } else {
            // Gerar session ID se não existe
            this.sessionId = this.locationManager.generateSessionId();
            this.currentUserId = this.sessionId;
        }

        console.log('📱 Chat SessionID:', this.sessionId);

        // Verificar se já tem nickname definido
        if (!this.nickname && this.locationManager.isAuthenticated) {
            // Usuário logado - usar nome do usuário
            this.nickname = 'Usuário'; // Seria obtido do backend
            this.userType = 'registered';
            this.connectToChat();
        } else if (!this.nickname) {
            // Usuário anônimo - solicitar nickname
            this.showNicknamePrompt();
        }
    }

    /**
     * Mostrar prompt para definir nickname
     */
    showNicknamePrompt() {
        document.getElementById('chat-nickname-prompt').classList.remove('hidden');
        this.updateChatStatus('Defina um nickname para começar a conversar', 'info');
    }

    /**
     * Mostrar modal de nickname
     */
    showNicknameModal() {
        this.nicknameModal.classList.remove('hidden');
        document.getElementById('nickname-input').focus();
    }

    /**
     * Esconder modal de nickname
     */
    hideNicknameModal() {
        this.nicknameModal.classList.add('hidden');
        document.getElementById('nickname-input').value = '';
        document.getElementById('nickname-confirm').disabled = true;
    }

    /**
     * Confirmar nickname
     */
    async confirmNickname() {
        const nicknameInput = document.getElementById('nickname-input');
        const nickname = nicknameInput.value.trim();

        if (nickname.length < 2) return;

        try {
            const response = await fetch('/chat/set-nickname', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    nickname: nickname,
                    session_id: this.sessionId.replace('anon_', '')
                })
            });

            const result = await response.json();

            if (result.success) {
                this.nickname = nickname;
                this.hideNicknameModal();
                document.getElementById('chat-nickname-prompt').classList.add('hidden');
                this.connectToChat();
            } else {
                alert('Erro ao definir nickname: ' + result.message);
            }

        } catch (error) {
            console.error('Erro ao definir nickname:', error);
            alert('Erro ao definir nickname. Tente novamente.');
        }
    }

    /**
     * Conectar ao sistema de chat
     */
    async connectToChat() {
        if (this.isConnecting || this.isConnected) return;

        this.isConnecting = true;
        this.updateChatStatus('Encontrando sala de chat baseada na sua localização...', 'connecting');

        try {
            const response = await fetch('/chat/find-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    session_id: this.sessionId.replace('anon_', '')
                })
            });

            const result = await response.json();

            if (result.success && result.room) {
                this.currentRoom = result.room;
                this.currentUserId = result.user_id;
                this.userType = result.user_type;
                
                console.log('💬 Conectado à sala de chat:', this.currentRoom);
                
                await this.loadChatData();
                this.enableChat();
                this.startPolling();
                this.startHeartbeat();
                
                this.isConnected = true;
                this.updateChatStatus('', 'connected');

            } else {
                throw new Error(result.message || 'Falha ao conectar ao chat');
            }

        } catch (error) {
            console.error('Erro ao conectar ao chat:', error);
            this.updateChatStatus('Erro ao conectar ao chat. Tente novamente.', 'error');
        } finally {
            this.isConnecting = false;
        }
    }

    /**
     * Carregar dados do chat (mensagens e usuários)
     */
    async loadChatData() {
        if (!this.currentRoom) return;

        try {
            // Carregar mensagens
            await this.loadMessages();
            
            // Carregar informações da sala
            await this.updateRoomInfo();

        } catch (error) {
            console.error('Erro ao carregar dados do chat:', error);
        }
    }

    /**
     * Carregar mensagens da sala
     */
    async loadMessages() {
        if (!this.currentRoom) return;

        try {
            const response = await fetch(`/chat/${this.currentRoom.room_id}/messages`);
            const result = await response.json();

            if (result.success && result.messages) {
                this.messageHistory = result.messages;
                this.renderMessages();
                
                // Atualizar último ID de mensagem
                if (result.messages.length > 0) {
                    this.lastMessageId = Math.max(...result.messages.map(m => m.id));
                }
            }

        } catch (error) {
            console.error('Erro ao carregar mensagens:', error);
        }
    }

    /**
     * Renderizar mensagens na UI
     */
    renderMessages() {
        this.messagesContainer.innerHTML = '';

        if (this.messageHistory.length === 0) {
            this.messagesContainer.innerHTML = `
                <div class="text-center text-gray-500 text-sm py-4">
                    <div class="mb-2">🎉</div>
                    <p>Seja o primeiro a iniciar a conversa!</p>
                    <p class="text-xs mt-1">Esta sala foi criada baseada na proximidade geográfica</p>
                </div>
            `;
            return;
        }

        this.messageHistory.forEach(message => {
            this.appendMessage(message);
        });

        this.scrollToBottom();
    }

    /**
     * Adicionar mensagem à UI
     */
    appendMessage(message, isNew = false) {
        const isCurrentUser = message.user_id === this.currentUserId;
        const isSystem = message.is_system || message.message_type === 'system';

        const messageElement = document.createElement('div');
        messageElement.className = `message ${isCurrentUser ? 'current-user' : ''} ${isSystem ? 'system-message' : ''}`;
        
        if (isSystem) {
            messageElement.innerHTML = `
                <div class="text-center text-gray-500 text-xs py-1">
                    <span class="bg-gray-100 px-2 py-1 rounded-full">
                        ${message.message}
                    </span>
                </div>
            `;
        } else {
            messageElement.innerHTML = `
                <div class="flex items-start space-x-2 ${isCurrentUser ? 'flex-row-reverse space-x-reverse' : ''}">
                    <img src="${message.avatar_url}" alt="${message.user_name}" class="w-6 h-6 rounded-full border border-gray-300">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 ${isCurrentUser ? 'flex-row-reverse space-x-reverse' : ''}">
                            <span class="text-xs font-medium text-gray-700">${message.user_name}</span>
                            <span class="text-xs text-gray-400" title="${message.sent_at}">${message.sent_at_human}</span>
                            ${message.user_type === 'registered' ? '<span class="w-1.5 h-1.5 bg-green-500 rounded-full" title="Usuário registrado"></span>' : ''}
                        </div>
                        <div class="mt-1">
                            <div class="inline-block px-3 py-2 rounded-lg text-sm ${
                                isCurrentUser 
                                    ? 'bg-green-600 text-white' 
                                    : 'bg-white border text-gray-800'
                            }">${message.message}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        if (isNew) {
            messageElement.classList.add('animate-fadeIn');
        }

        this.messagesContainer.appendChild(messageElement);
        
        if (isNew) {
            this.scrollToBottom();
        }
    }

    /**
     * Atualizar informações da sala
     */
    async updateRoomInfo() {
        if (!this.currentRoom) return;

        try {
            const response = await fetch(`/chat/${this.currentRoom.room_id}/users`);
            const result = await response.json();

            if (result.success && result.users) {
                const userCount = result.users.length;
                this.roomInfo.name.textContent = this.currentRoom.name || 'Chat MapChat';
                this.roomInfo.users.textContent = `${userCount} usuário${userCount !== 1 ? 's' : ''} online`;
            }

        } catch (error) {
            console.error('Erro ao atualizar informações da sala:', error);
        }
    }

    /**
     * Habilitar interface do chat
     */
    enableChat() {
        this.messageInput.disabled = false;
        this.sendButton.disabled = false;
        this.messageInput.placeholder = 'Digite sua mensagem...';
    }

    /**
     * Enviar mensagem
     */
    async sendMessage() {
        const message = this.messageInput.value.trim();
        if (!message || !this.currentRoom) return;

        const originalMessage = message;
        this.messageInput.value = '';
        this.messageInput.disabled = true;
        this.sendButton.disabled = true;
        document.getElementById('char-count').textContent = '0';

        try {
            const response = await fetch(`/chat/${this.currentRoom.room_id}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    message: originalMessage,
                    session_id: this.sessionId.replace('anon_', ''),
                    message_type: 'text'
                })
            });

            const result = await response.json();

            if (result.success && result.message) {
                // Mensagem enviada com sucesso - será carregada no próximo poll
                console.log('✅ Mensagem enviada');
            } else {
                throw new Error(result.message || 'Falha ao enviar mensagem');
            }

        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
            this.messageInput.value = originalMessage; // Restaurar mensagem
            alert('Erro ao enviar mensagem. Tente novamente.');
        } finally {
            this.messageInput.disabled = false;
            this.sendButton.disabled = false;
            this.messageInput.focus();
        }
    }

    /**
     * Iniciar polling para novas mensagens
     */
    startPolling() {
        if (this.pollInterval) return;

        this.pollInterval = setInterval(async () => {
            await this.checkForNewMessages();
            await this.updateRoomInfo();
        }, 3000); // Poll a cada 3 segundos
    }

    /**
     * Verificar por novas mensagens
     */
    async checkForNewMessages() {
        if (!this.currentRoom) return;

        try {
            const response = await fetch(`/chat/${this.currentRoom.room_id}/messages`);
            const result = await response.json();

            if (result.success && result.messages) {
                const newMessages = result.messages.filter(m => m.id > this.lastMessageId);
                
                if (newMessages.length > 0) {
                    newMessages.forEach(message => {
                        this.messageHistory.push(message);
                        this.appendMessage(message, true);
                    });

                    this.lastMessageId = Math.max(...result.messages.map(m => m.id));
                    
                    // Notificação visual se chat estiver fechado
                    if (this.chatPanel.classList.contains('hidden')) {
                        this.showNotification(newMessages.length);
                    }
                }
            }

        } catch (error) {
            console.error('Erro ao verificar novas mensagens:', error);
        }
    }

    /**
     * Iniciar heartbeat para manter conexão ativa
     */
    startHeartbeat() {
        if (this.heartbeatInterval) return;

        this.heartbeatInterval = setInterval(async () => {
            if (this.currentRoom) {
                try {
                    await fetch(`/chat/${this.currentRoom.room_id}/heartbeat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            session_id: this.sessionId.replace('anon_', '')
                        })
                    });
                } catch (error) {
                    console.error('Erro no heartbeat:', error);
                }
            }
        }, 30000); // Heartbeat a cada 30 segundos
    }

    /**
     * Mostrar notificação de novas mensagens
     */
    showNotification(count) {
        const notification = document.getElementById('chat-notification');
        const currentCount = parseInt(notification.textContent) || 0;
        const newCount = currentCount + count;
        
        notification.textContent = newCount > 99 ? '99+' : newCount;
        notification.classList.remove('hidden');
        
        // Animação de bounce
        notification.classList.add('animate-bounce');
        setTimeout(() => notification.classList.remove('animate-bounce'), 1000);
    }

    /**
     * Limpar notificações
     */
    clearNotifications() {
        const notification = document.getElementById('chat-notification');
        notification.classList.add('hidden');
        notification.textContent = '0';
    }

    /**
     * Toggle do chat
     */
    toggleChat() {
        const isVisible = !this.chatPanel.classList.contains('hidden');
        
        if (isVisible) {
            this.chatPanel.classList.add('hidden');
        } else {
            this.chatPanel.classList.remove('hidden');
            this.clearNotifications();
            this.scrollToBottom();
            
            // Focar no input se estiver habilitado
            if (!this.messageInput.disabled) {
                setTimeout(() => this.messageInput.focus(), 100);
            }
        }
    }

    /**
     * Atualizar status do chat
     */
    updateChatStatus(message, type = 'info') {
        const statusElement = document.getElementById('chat-status');
        
        if (!message) {
            statusElement.classList.add('hidden');
            return;
        }

        const colors = {
            connecting: 'bg-yellow-50 text-yellow-700',
            connected: 'bg-green-50 text-green-700',
            error: 'bg-red-50 text-red-700',
            info: 'bg-blue-50 text-blue-700'
        };

        statusElement.className = `p-2 border-b text-sm ${colors[type] || colors.info}`;
        statusElement.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full ${type === 'connecting' ? 'bg-yellow-500 animate-pulse' : type === 'connected' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'}"></div>
                <span>${message}</span>
            </div>
        `;
        statusElement.classList.remove('hidden');

        // Auto-hide para alguns tipos
        if (type === 'connected' || type === 'info') {
            setTimeout(() => statusElement.classList.add('hidden'), 3000);
        }
    }

    /**
     * Rolar para o final das mensagens
     */
    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 50);
    }

    /**
     * Desconectar do chat
     */
    disconnect() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }

        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }

        if (this.currentRoom && this.currentUserId) {
            // Tentar sair da sala
            fetch(`/chat/${this.currentRoom.room_id}/leave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    session_id: this.sessionId.replace('anon_', '')
                })
            }).catch(() => {}); // Ignorar erros na desconexão
        }

        this.isConnected = false;
        this.currentRoom = null;
        this.messageHistory = [];
        this.lastMessageId = 0;

        console.log('💬 Desconectado do chat');
    }

    /**
     * Destruir instância do ChatManager
     */
    destroy() {
        this.disconnect();
        
        if (this.chatContainer && this.chatContainer.parentNode) {
            this.chatContainer.parentNode.removeChild(this.chatContainer);
        }

        if (this.nicknameModal && this.nicknameModal.parentNode) {
            this.nicknameModal.parentNode.removeChild(this.nicknameModal);
        }
    }
}

// Adicionar estilos CSS customizados
const chatStyles = `
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    #chat-messages::-webkit-scrollbar {
        width: 4px;
    }
    
    #chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    #chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }
    
    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    
    .message {
        margin-bottom: 8px;
    }
    
    .system-message {
        margin: 4px 0;
    }
    
    #chat-container {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .animate-bounce {
        animation: bounce 1s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% {
            transform: translateY(0);
        }
        40%, 43% {
            transform: translateY(-8px);
        }
        70% {
            transform: translateY(-4px);
        }
        90% {
            transform: translateY(-2px);
        }
    }
</style>
`;

document.head.insertAdjacentHTML('beforeend', chatStyles);

// Exportar para uso global
window.ChatManager = ChatManager;
