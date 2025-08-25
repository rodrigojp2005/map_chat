


@extends('layouts.app')

@section('title', 'MapChat - Conecte-se no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 80px);">
    <!-- Mapa principal -->
    <div id="map" class="absolute left-0 top-0 w-full h-full z-10" style="background: #e5e7eb; min-height: 400px;"></div>

    <!-- Painel de configuração (esquerda) -->
    <div id="config-panel" class="absolute left-4 top-4 z-20 bg-white rounded-lg shadow-lg max-w-80 sidebar-panel">
        <div class="p-4 border-b bg-green-50">
            <h3 class="font-bold text-green-600 mb-1">🌍 Configure seu Perfil</h3>
        </div>
        <!-- Seção de Configuração -->
        <div id="config-section" class="config-section">
            <!-- Avatar Selection -->
            <div class="p-4 border-b">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">1. Escolha seu avatar:</h4>
                    <div id="avatar-status" class="w-2 h-2 bg-gray-300 rounded-full"></div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="default" title="Padrão">
                        <img src="{{ asset('images/default.gif') }}" alt="Padrão" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="man" title="Homem">
                        <img src="{{ asset('images/mario.gif') }}" alt="Homem" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="woman" title="Mulher">
                        <img src="{{ asset('images/girl.gif') }}" alt="Mulher" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="pet" title="Pet">
                        <img src="{{ asset('images/pets.gif') }}" alt="Pet" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="geek" title="Geek">
                        <img src="{{ asset('images/geek.gif') }}" alt="Geek" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                    <button class="avatar-btn p-2 border-2 border-gray-200 rounded-lg hover:border-green-400 transition-colors" data-avatar="sport" title="Esporte">
                        <img src="{{ asset('images/sport.gif') }}" alt="Esporte" class="w-8 h-8 rounded-full mx-auto">
                    </button>
                </div>
            </div>

            <!-- Location Input (Desktop) / Auto-detection (Mobile) -->
            <div class="p-4 border-b" id="location-section">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">2. Sua localização:</h4>
                    <div id="location-status-dot" class="w-2 h-2 bg-gray-300 rounded-full"></div>
                </div>
                
                <!-- Desktop: Address Input -->
                <div id="desktop-location" class="hidden">
                    <div class="relative">
                        <input type="text" id="address-input" placeholder="Ex: São Paulo, SP ou CEP 01234-567" 
                               class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <svg class="absolute left-2.5 top-2.5 w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <button id="search-address" class="absolute right-2 top-2 text-gray-400 hover:text-green-500 transition-colors" aria-label="Buscar endereço">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2" id="address-feedback">Digite uma cidade, bairro ou CEP</p>
                </div>

                <!-- Mobile: GPS Detection -->
                <div id="mobile-location" class="hidden">
                    <div class="flex items-center space-x-3">
                        <div id="gps-spinner" class="w-5 h-5 border-2 border-green-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-sm text-gray-600" id="gps-status">Obtendo sua localização via GPS...</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Sua posição real será mantida privada</p>
                </div>
            </div>

            <!-- Privacy Radius -->
            <div class="p-4 border-b">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">3. Raio de Privacidade: <span id="radius-value">50 km</span></h4>
                <input type="range" id="privacy-radius" min="500" max="500000" step="500" value="50000" 
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>500m</span>
                    <span>500km</span>
                </div>
                <div class="mt-2 p-2 bg-yellow-50 rounded text-xs text-yellow-700">
                    🔒 <strong>Como funciona:</strong> Sua posição real será aleatoriamente deslocada dentro deste raio. Quanto maior o raio, maior a privacidade, mas menor a precisão para outros usuários.
                </div>
                <div class="mt-1 text-xs text-gray-500">
                    💡 <strong>Dica:</strong> Use raios menores (1-5km) para encontros locais ou maiores (50km+) para maior anonimato
                </div>
            </div>

            <!-- Apply Button -->
            <div class="p-4">
                <button id="apply-config" class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium" disabled>
                    Aplicar e Entrar no Mapa
                </button>
                <div class="mt-2 flex justify-center space-x-4 text-xs">
                    <span id="avatar-check" class="flex items-center text-gray-400">
                        <span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>
                        Avatar
                    </span>
                    <span id="location-check" class="flex items-center text-gray-400">
                        <span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>
                        Localização
                    </span>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Painel de usuários online (direita) -->
    <div id="online-panel" class="absolute right-4 top-4 z-20 bg-white rounded-lg shadow-lg p-4 w-64 max-h-96 overflow-y-auto sidebar-panel">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-green-600">👥 Online Agora (<span id="users-count">0</span>)</h3>
            <div class="text-xs text-gray-500" title="Chat MapChat disponível">💬</div>
        </div>
        <div id="users-list" class="space-y-2">
            <div class="text-gray-500 text-sm text-center py-4">
                Carregando usuários...
            </div>
        </div>
        
        <!-- Informação sobre o chat -->
        <div class="mt-3 pt-3 border-t">
            <div class="text-xs text-gray-500 mb-2">
                💡 <strong>Chat MapChat:</strong> Configure sua localização e avatar para participar de salas de chat baseadas em proximidade geográfica!
            </div>
            <div class="text-xs text-gray-400">
                🔄 Atualiza a cada 30s
            </div>
        </div>
    </div>
    
    <!-- Botões de toggle para painéis mobile -->
    <div class="md:hidden">
        <!-- Botão para painel de configuração (esquerda) -->
        <button id="toggle-config" class="absolute top-4 left-4 z-30 bg-green-600 text-white p-2 rounded-full shadow-lg" aria-label="Abrir configurações">
            ⚙️
        </button>
        
        <!-- Botão para painel online (direita) -->
        <button id="toggle-online" class="absolute top-4 right-4 z-30 bg-blue-600 text-white p-2 rounded-full shadow-lg" aria-label="Ver usuários online">
            👥
        </button>
    </div>

    <!-- CHAT MAPCHAT - Sempre visível -->
    <div id="chat-widget" class="fixed bottom-4 right-4 z-50">
        <!-- Botão principal do chat -->
        <button id="chat-toggle" class="w-16 h-16 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-2xl cursor-pointer flex items-center justify-center transition-all duration-200 transform hover:scale-110 animate-pulse" title="Chat MapChat">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.96 8.96 0 01-4.887-1.441c-.203-.108-.417-.11-.621-.04l-3.301 1.155a1 1 0 01-1.266-1.265l1.155-3.302c.07-.204.068-.418-.04-.621A8.96 8.96 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
            </svg>
            <!-- Contador de notificações -->
            <div id="chat-notification-badge" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden font-bold">0</div>
            <!-- Indicador de status -->
            <div id="chat-status-indicator" class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white" title="Configurando..."></div>
        </button>

        <!-- Painel do chat -->
        <div id="chat-panel" class="absolute bottom-20 right-0 w-80 h-96 bg-white rounded-lg shadow-2xl border hidden transform transition-all duration-200">
            <!-- Header -->
            <div class="flex items-center justify-between p-3 bg-green-600 text-white rounded-t-lg">
                <div class="flex-1">
                    <h3 class="font-bold text-sm" id="chat-room-title">Chat MapChat</h3>
                    <p class="text-xs opacity-90" id="chat-room-subtitle">👆 Clique no balão verde do mapa ou configure sua localização</p>
                </div>
                <button id="chat-close" class="w-6 h-6 hover:bg-green-700 rounded flex items-center justify-center" title="Fechar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Conteúdo do chat -->
            <div id="chat-content" class="h-64 flex flex-col">
                <!-- Estado inicial -->
                <div id="chat-initial-state" class="flex-1 flex items-center justify-center p-4 text-center">
                    <div>
                        <div class="text-4xl mb-3">🗺️💬</div>
                        <h4 class="font-bold text-gray-800 mb-2">Chat por Proximidade</h4>
                        <p class="text-sm text-gray-600 mb-4">Configure seu avatar e localização para conversar com pessoas próximas!</p>
                        
                        <!-- Botão de teste para ativar chat -->
                        <button onclick="testarChatDiretamente()" class="bg-green-500 text-white px-4 py-2 rounded mb-3 text-sm">
                            🧪 TESTAR CHAT AGORA
                        </button>
                        
                        <div class="text-xs text-gray-500">
                            <div class="flex items-center justify-center space-x-2 mb-1">
                                <span class="w-2 h-2 bg-red-500 rounded-full" id="avatar-indicator"></span>
                                <span>Avatar</span>
                            </div>
                            <div class="flex items-center justify-center space-x-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full" id="location-indicator"></span>
                                <span>Localização</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado de conectando -->
                <div id="chat-connecting-state" class="flex-1 flex items-center justify-center p-4 text-center hidden">
                    <div>
                        <div class="animate-spin w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full mx-auto mb-3"></div>
                        <h4 class="font-bold text-gray-800 mb-2">Conectando...</h4>
                        <p class="text-sm text-gray-600">Encontrando pessoas próximas a você</p>
                    </div>
                </div>

                <!-- Estado conectado - lista de mensagens -->
                <div id="chat-messages-container" class="flex-1 overflow-y-auto p-3 hidden">
                    <div id="chat-messages" class="space-y-2">
                        <!-- Mensagens aparecerão aqui -->
                    </div>
                </div>
            </div>

            <!-- Input de mensagem -->
            <div id="chat-input-section" class="border-t p-3 hidden">
                <div class="flex space-x-2">
                    <input type="text" id="chat-message-input" placeholder="Digite sua mensagem..." 
                           class="flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500" 
                           maxlength="500">
                    <button id="chat-send-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                        Enviar
                    </button>
                </div>
                <div class="text-xs text-gray-500 mt-1 text-right">
                    <span id="char-count">0</span>/500
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ====== Aparência dos botões de avatar ====== */
.avatar-btn { transition: all 0.2s ease; }
.avatar-btn:hover { transform: scale(1.05); }
.avatar-btn.border-green-500 { border-color: #10B981 !important; background-color: #F0FDF4 !important; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2); }

/* ====== Painéis laterais e controles ====== */
.sidebar-panel { 
    top: 16px !important; 
    max-height: calc(100vh - 200px) !important;
    overflow-y: auto !important;
}
#apply-config { position: relative !important; z-index: 999 !important; cursor: pointer !important; }
#apply-config:disabled { cursor: not-allowed !important; }

/* ====== Slider de raio de privacidade ====== */
#privacy-radius { -webkit-appearance: none; appearance: none; height: 8px; background: #e5e5e5; outline: none; border-radius: 5px; cursor: pointer; }
#privacy-radius::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 20px; height: 20px; background: #10B981; cursor: pointer; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
#privacy-radius::-moz-range-thumb { width: 20px; height: 20px; background: #10B981; cursor: pointer; border-radius: 50%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
#privacy-radius::-webkit-slider-track { height: 8px; background: #e5e5e5; border-radius: 5px; }
#privacy-radius::-moz-range-track { height: 8px; background: #e5e5e5; border-radius: 5px; border: none; }

/* ====== Visibilidade e loading ====== */
.config-section { position: relative; z-index: 10; }
.loading { opacity: 0.7; pointer-events: none; }
.loading::after { content: ''; position: absolute; top: 50%; left: 50%; width: 20px; height: 20px; margin: -10px 0 0 -10px; border: 2px solid #ccc; border-top-color: #10B981; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* ====== Inputs ====== */
#address-input:focus { border-color: #10B981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }

/* ====== Hovers utilitários ====== */
.hover\\:bg-green-600:hover { background-color: #059669 !important; }
.hover\\:bg-blue-600:hover { background-color: #2563EB !important; }
.hover\\:bg-orange-600:hover { background-color: #EA580C !important; }

/* ====== Indicadores de status ====== */
.status-complete .w-2.h-2 { background-color: #10B981 !important; }

/* ====== Modal ====== */
.modal-overlay { backdrop-filter: blur(4px); animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* ====== Contagem regressiva ====== */
.text-red-600 { animation: pulse 1s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

/* ====== Scrollbar discreta ====== */
.overflow-y-auto::-webkit-scrollbar { width: 4px; }
.overflow-y-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 2px; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 2px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

/* ====== Mobile ====== */
@media (max-width: 768px) {
    /* Garantir que mapa seja visível em mobile */
    #map {
        height: calc(100vh - 60px) !important;
    }
    
    /* Container principal em mobile */
    .relative.w-full {
        height: calc(100vh - 60px) !important;
    }
    
    /* Esconder painéis por padrão no mobile */
    .sidebar-panel { 
        display: none !important;
    }
    
    /* Painel de configuração quando visível */
    .sidebar-panel.mobile-config-visible { 
        display: block !important;
        position: fixed !important;
        top: 140px !important; 
        left: 8px !important; 
        right: 8px !important; 
        bottom: 20px !important;
        max-width: calc(100vw - 16px) !important;
        max-height: calc(100vh - 160px) !important;
        z-index: 45 !important;
        overflow-y: auto !important;
    }
    
    /* Painel online quando visível */
    .sidebar-panel.mobile-online-visible {
        display: block !important;
        position: fixed !important;
        top: 140px !important;
        right: 8px !important;
        left: auto !important;
        bottom: 20px !important;
        max-width: calc(100vw - 16px) !important;
        max-height: calc(100vh - 160px) !important;
        z-index: 45 !important;
        overflow-y: auto !important;
    }
    
    /* Melhorar botões mobile */
    .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; }
    .avatar-btn img { width: 2rem; height: 2rem; }
    
    /* Botões toggle sempre visíveis */
    #toggle-config,
    #toggle-online {
        position: fixed !important;
        top: 80px !important;
        z-index: 50 !important;
        border-radius: 50% !important;
        padding: 12px !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
        border: none !important;
        width: 48px !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
        color: white !important;
    }
    
    #toggle-config {
        left: 16px !important;
        background-color: #059669 !important;
    }
    
    #toggle-online {
        right: 16px !important;
        background-color: #2563eb !important;
    }
    
    /* Overlay escuro quando painéis abertos */
    .mobile-panels-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(0,0,0,0.5) !important;
        z-index: 35 !important;
        backdrop-filter: blur(2px) !important;
    }
}
</style>

<!-- Scripts -->
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script>
    // Debug: verificar se o script do chat está carregando
    console.log('🔍 Carregando scripts do MapChat...');
    
    // ChatManager completo
    class ChatManager {
        constructor(locationManager) {
            this.locationManager = locationManager;
            this.currentRoom = null;
            this.nickname = null;
            this.messages = [];
            this.heartbeatInterval = null;
            this.messagesInterval = null;
            this.onRoomJoined = null; // Callback para widget

            this.init();
        }

        async init() {
            console.log('🚀 ChatManager inicializado');
        }

        async findOrCreateRoom() {
            if (!this.locationManager?.isConfigured || !this.locationManager?.anonymousSessionId) {
                console.warn('❌ LocationManager não configurado para chat');
                console.log('Debug LocationManager:', {
                    isConfigured: this.locationManager?.isConfigured,
                    anonymousSessionId: this.locationManager?.anonymousSessionId
                });
                return null;
            }

            console.log('🚀 Iniciando findOrCreateRoom com sessionId:', this.locationManager.anonymousSessionId);

            try {
                const response = await fetch('/chat/find-room', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Anonymous-Session-ID': this.locationManager.anonymousSessionId
                    },
                    body: JSON.stringify({
                        anonymous_session_id: this.locationManager.anonymousSessionId
                    })
                });

                console.log('📡 Resposta do servidor:', response.status, response.statusText);
                const data = await response.json();
                console.log('📦 Dados recebidos:', data);
                
                if (data.success && data.room) {
                    this.currentRoom = data.room;
                    console.log('✅ Sala encontrada/criada:', data.room);
                    
                    // Notificar widget
                    if (this.onRoomJoined) {
                        this.onRoomJoined(data.room);
                    }

                    // Iniciar polling de mensagens
                    this.startPolling();
                    
                    return data.room;
                } else {
                    console.error('❌ Erro ao encontrar sala:', data.message);
                    return null;
                }
            } catch (error) {
                console.error('❌ Erro na requisição de sala:', error);
                return null;
            }
        }

        async sendMessage(content) {
            if (!this.currentRoom || !content.trim()) {
                console.warn('❌ Não é possível enviar mensagem');
                return false;
            }

            // Solicitar nickname se necessário
            if (!this.nickname) {
                await this.requestNickname();
            }

            try {
                const response = await fetch(`/chat/${this.currentRoom.room_code}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Anonymous-Session-ID': this.locationManager.anonymousSessionId
                    },
                    body: JSON.stringify({
                        room_code: this.currentRoom.room_code,
                        message: content.trim()
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Mensagem enviada:', data.message);
                    // A mensagem aparecerá no próximo polling
                    return true;
                } else {
                    console.error('❌ Erro ao enviar mensagem:', data.message);
                    return false;
                }
            } catch (error) {
                console.error('❌ Erro na requisição de envio:', error);
                return false;
            }
        }

        async loadMessages() {
            if (!this.currentRoom) return;

            try {
                const response = await fetch(`/chat/${this.currentRoom.room_code}/messages?room_code=${this.currentRoom.room_code}`, {
                    headers: {
                        'X-Anonymous-Session-ID': this.locationManager.anonymousSessionId
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    const newMessages = data.messages || [];
                    
                    // Verificar se há mensagens novas
                    if (newMessages.length !== this.messages.length) {
                        this.messages = newMessages;
                        this.renderMessages();
                    }
                }
            } catch (error) {
                console.error('❌ Erro ao carregar mensagens:', error);
            }
        }

        renderMessages() {
            const container = document.getElementById('chat-messages');
            if (!container) return;

            const mySessionId = this.locationManager?.anonymousSessionId;
            
            container.innerHTML = this.messages.map(msg => {
                const isOwn = msg.user_id === mySessionId;
                const timeStr = this.formatTime(msg.created_at);
                
                return `
                    <div class="flex ${isOwn ? 'justify-end' : 'justify-start'} mb-2">
                        <div class="${isOwn ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'} px-3 py-2 rounded-lg max-w-xs">
                            ${!isOwn ? `<div class="text-xs font-bold mb-1 opacity-75">${this.escapeHtml(msg.user_name || 'Anônimo')}</div>` : ''}
                            <div class="text-sm">${this.escapeHtml(msg.message)}</div>
                            <div class="text-xs opacity-75 mt-1">${timeStr}</div>
                        </div>
                    </div>
                `;
            }).join('');

            // Scroll para baixo
            container.scrollTop = container.scrollHeight;
        }

        formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('pt-BR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async requestNickname() {
            return new Promise((resolve) => {
                const nickname = prompt('Digite seu apelido para o chat (máx. 20 caracteres):');
                if (nickname && nickname.trim()) {
                    this.nickname = nickname.trim().substring(0, 20);
                } else {
                    this.nickname = 'Anônimo';
                }
                resolve(this.nickname);
            });
        }

        startPolling() {
            if (this.messagesInterval) {
                clearInterval(this.messagesInterval);
            }

            // Carregar mensagens imediatamente
            this.loadMessages();
            
            // Polling de mensagens a cada 3 segundos
            this.messagesInterval = setInterval(() => {
                this.loadMessages();
            }, 3000);
        }

        stopPolling() {
            if (this.messagesInterval) {
                clearInterval(this.messagesInterval);
                this.messagesInterval = null;
            }
        }

        destroy() {
            this.stopPolling();
            this.currentRoom = null;
            this.messages = [];
        }
    }
    
    // Exportar para uso global
    window.ChatManager = ChatManager;
</script>
<script>
class LocationManager {
    constructor(isAuthenticated) {
        this.isAuthenticated = !!isAuthenticated;
        this.selectedAvatar = null;
        this.userPosition = null; // Posição com privacidade aplicada (enviada ao servidor)
        this.realUserPosition = null; // Posição real do usuário (nunca enviada)
        this.privacyRadius = 50000; // 50 km
        this.geocoder = null;
        this.globalCountdown = null;
        this.globalTimeRemaining = 600; // 10 min
        this.updateInterval = null;
        this.searchTimeout = null;
        this.isMobile = false;
        this.userMarker = null;
        this.mapCenter = { lat: -14.2350, lng: -51.9253 };

        // Inicialização
        this.detectMobile();
        this.initGeocoder();
        this.setupEventListeners();
        this.initializeCountdown();
        this.initializeUIByPlatform();
        this.updateRadiusDisplay(this.privacyRadius);

        // Expor autenticado global para templates de infoWindow
        window.isAuthenticated = this.isAuthenticated;
        window.currentUserId = document.querySelector('meta[name="user-id"]')?.content || null;
    }

    initGeocoder() {
        if (window.google && google.maps) {
            this.geocoder = new google.maps.Geocoder();
        }
    }

    detectMobile() {
        const mq = window.matchMedia('(pointer: coarse)');
        this.isMobile = mq && mq.matches || /Mobi|Android/i.test(navigator.userAgent);
        return this.isMobile;
    }

    initializeUIByPlatform() {
        const desktopLocation = document.getElementById('desktop-location');
        const mobileLocation = document.getElementById('mobile-location');
        
        if (this.isMobile) {
            // Mobile: Mostrar UI de GPS e esconder painéis por padrão
            mobileLocation?.classList.remove('hidden');
            desktopLocation?.classList.add('hidden');
            
            // Garantir que painéis estão escondidos inicialmente
            document.querySelectorAll('.sidebar-panel').forEach(panel => {
                panel.classList.remove('mobile-config-visible', 'mobile-online-visible');
            });
            
            this.requestGPSLocation();
        } else {
            // Desktop: mostrar campo de endereço
            mobileLocation?.classList.add('hidden');
            desktopLocation?.classList.remove('hidden');
            this.initializeBrazilCenter();
        }
    }

    initializeCountdown() {
        // Iniciar cronômetro sincronizado com horário de Brasília
        this.syncCountdownWithBrasilia();
    }

    syncCountdownWithBrasilia() {
        try {
            // Obter horário atual de Brasília
            const now = new Date();
            const brasiliaTime = new Date(now.toLocaleString("en-US", {timeZone: "America/Sao_Paulo"}));
            
            // Calcular quantos segundos se passaram no minuto atual
            const currentSeconds = brasiliaTime.getSeconds();
            const currentMinutes = brasiliaTime.getMinutes();
            
            // Cronômetro de 10 minutos que reinicia a cada 10 minutos
            const minutesCycle = currentMinutes % 10; // 0-9
            const totalSecondsInCycle = (minutesCycle * 60) + currentSeconds;
            
            // Segundos restantes até completar os 10 minutos
            this.globalTimeRemaining = 600 - totalSecondsInCycle; // 10min = 600s
            
            console.log('Cronômetro sincronizado com Brasília:', {
                horarioBrasilia: brasiliaTime.toLocaleTimeString(),
                minutosNoCiclo: minutesCycle,
                segundosRestantes: this.globalTimeRemaining
            });
            
            this.startGlobalCountdown();
        } catch (error) {
            console.error('Erro ao sincronizar cronômetro:', error);
            // Fallback para cronômetro normal
            this.globalTimeRemaining = 600;
            this.startGlobalCountdown();
        }
    }

    requestGPSLocation() {
        const gpsSpinner = document.getElementById('gps-spinner');
        const gpsStatus = document.getElementById('gps-status');
        
        if (!navigator.geolocation) {
            gpsSpinner?.classList.add('hidden');
            if (gpsStatus) {
                gpsStatus.textContent = 'GPS não suportado - usando campo de endereço';
                gpsStatus.classList.add('text-orange-600');
            }
            this.switchToDesktopMode();
            return;
        }

        const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 };
        navigator.geolocation.getCurrentPosition(
            (position) => {
                // Obter posição real do GPS
                const realPosition = { 
                    lat: position.coords.latitude, 
                    lng: position.coords.longitude 
                };
                
                // Aplicar raio de privacidade
                this.realUserPosition = realPosition; // Guardar posição real (nunca enviada)
                this.userPosition = this.getPrivatePosition(realPosition); // Posição com privacidade
                
                gpsSpinner?.classList.add('hidden');
                if (gpsStatus) {
                    const privacyText = this.userPosition.isPrivacyApplied 
                        ? ` (±${this.privacyRadius < 1000 ? this.privacyRadius + 'm' : Math.round(this.privacyRadius/1000) + 'km'})` 
                        : ' (posição exata)';
                    gpsStatus.textContent = `✅ Localização obtida via GPS${privacyText}`;
                    gpsStatus.classList.add('text-green-600');
                }
                
                this.updateLocationStatus(true);
                this.checkConfigComplete();
                
                console.log('GPS obtido:', {
                    real: realPosition,
                    private: this.userPosition,
                    privacyApplied: this.userPosition.isPrivacyApplied,
                    radiusMeters: this.privacyRadius
                });
            },
            (error) => {
                console.warn('Erro GPS:', error.message);
                gpsSpinner?.classList.add('hidden');
                if (gpsStatus) {
                    gpsStatus.textContent = '❌ GPS não disponível - usando campo de endereço';
                    gpsStatus.classList.add('text-orange-600');
                }
                this.switchToDesktopMode();
            },
            options
        );
    }

    switchToDesktopMode() {
        const desktopLocation = document.getElementById('desktop-location');
        const mobileLocation = document.getElementById('mobile-location');
        mobileLocation?.classList.add('hidden');
        desktopLocation?.classList.remove('hidden');
        this.isMobile = false;
    }

    initializeBrazilCenter() {
        this.mapCenter = { lat: -14.2350, lng: -51.9253 };
    }

    startGlobalCountdown() {
        // Busca o cronômetro na barra de navegação (para todos os usuários)
        const countdownEl = document.getElementById('global-countdown-timer');
        if (!countdownEl) return;

        this.globalCountdown = setInterval(() => {
            this.globalTimeRemaining--;
            const minutes = Math.floor(this.globalTimeRemaining / 60);
            const seconds = this.globalTimeRemaining % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            countdownEl.textContent = display;

            // Quando restam 2 minutos, muda para vermelho mais intenso
            if (this.globalTimeRemaining <= 120) {
                const timerContainer = countdownEl.closest('.bg-red-100');
                if (timerContainer) {
                    timerContainer.classList.remove('bg-red-100', 'border-red-200');
                    timerContainer.classList.add('bg-red-200', 'border-red-300', 'animate-pulse');
                }
            }

            // Quando o tempo acaba, reiniciar o cronômetro
            if (this.globalTimeRemaining <= 0) {
                console.log('Cronômetro finalizado, reiniciando...');
                clearInterval(this.globalCountdown);
                
                // Resetar para 10 minutos e recomeçar
                this.globalTimeRemaining = 600;
                
                // Resetar visual do timer
                const timerContainer = countdownEl.closest('.bg-red-200, .bg-red-100');
                if (timerContainer) {
                    timerContainer.classList.remove('bg-red-200', 'border-red-300', 'animate-pulse');
                    timerContainer.classList.add('bg-red-100', 'border-red-200');
                }
                
                // Reiniciar cronômetro
                setTimeout(() => this.startGlobalCountdown(), 100);
                
                // Para usuários não autenticados, mostrar modal de login após reset
                if (!this.isAuthenticated) {
                    setTimeout(() => this.handleGlobalTimeExpired(), 1000);
                }
            }
        }, 1000);
    }

    handleGlobalTimeExpired() {
        if (this.globalCountdown) clearInterval(this.globalCountdown);
        this.clearAllUserPositions();
        const modal = `
            <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 modal-overlay" id="time-expired-modal" onclick="if(event.target === this) this.remove()">
                <div class="bg-white rounded-xl p-8 max-w-md mx-4 text-center shadow-2xl relative" onclick="event.stopPropagation()">
                    <button onclick="document.getElementById('time-expired-modal').remove()" class="absolute top-3 right-3 w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors" title="Fechar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="text-6xl mb-4">⏰</div>
                    <h3 class="text-2xl font-bold text-red-600 mb-4">Tempo Esgotado!</h3>
                    <p class="text-gray-700 mb-6">O cronômetro global zerou e todas as posições foram resetadas.</p>
                    <p class="text-sm text-gray-600 mb-6">Faça login para continuar usando o mapa sem limitações de tempo!</p>
                    <div class="flex space-x-3">
                        <a href="/login" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg text-center hover:bg-green-700 transition-colors font-semibold">Fazer Login</a>
                        <a href="/register" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg text-center hover:bg-blue-700 transition-colors font-semibold">Criar Conta</a>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modal);
        
        // Adicionar suporte para fechar com ESC
        const handleEscape = (event) => {
            if (event.key === 'Escape') {
                const modal = document.getElementById('time-expired-modal');
                if (modal) {
                    modal.remove();
                    document.removeEventListener('keydown', handleEscape);
                }
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    clearAllUserPositions() {
        // Limpar marcadores de outros usuários do mapa
        if (window.markers) {
            window.markers.forEach(m => m.setMap(null));
            window.markers = [];
        }
        if (window.markerCluster) {
            try { window.markerCluster.clearMarkers(); } catch (e) {}
            window.markerCluster = null;
        }
        
        // Resetar configuração do usuário atual
        this.resetConfiguration();
        
        // Limpar lista de usuários na UI
        const usersList = document.getElementById('users-list');
        const usersCount = document.getElementById('users-count');
        if (usersList) {
            usersList.innerHTML = '<div class="text-gray-500 text-sm text-center py-4">Tempo esgotado - Faça login para continuar</div>';
        }
        if (usersCount) {
            usersCount.textContent = '0';
        }
        
        // Parar atualizações periódicas
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
        
        console.log('Todas as posições de usuários foram limpas');
    }

    setupEventListeners() {
        // Avatar selection
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.addEventListener('click', () => this.selectAvatar(btn.dataset.avatar));
        });
        
        // Address input
        const addressInput = document.getElementById('address-input');
        const searchBtn = document.getElementById('search-address');
        addressInput?.addEventListener('input', (e) => {
            this.addressInput = e.target.value;
            this.debouncedAddressSearch();
        });
        addressInput?.addEventListener('keypress', (e) => { if (e.key === 'Enter') this.searchAddressImmediate(); });
        searchBtn?.addEventListener('click', () => this.searchAddressImmediate());
        
        // Radius
        const radiusSlider = document.getElementById('privacy-radius');
        radiusSlider?.addEventListener('input', (e) => {
            const newRadius = parseInt(e.target.value);
            this.updateRadiusDisplay(newRadius);
            
            // Se já temos uma posição real, reaplicar a privacidade com o novo raio
            if (this.realUserPosition) {
                this.userPosition = this.getPrivatePosition(this.realUserPosition);
                
                // Atualizar feedback visual se disponível
                const gpsStatus = document.getElementById('gps-status');
                const feedback = document.getElementById('address-feedback');
                
                const privacyText = this.userPosition.isPrivacyApplied 
                    ? ` (±${newRadius < 1000 ? newRadius + 'm' : Math.round(newRadius/1000) + 'km'})` 
                    : ' (posição exata)';
                
                if (gpsStatus && gpsStatus.textContent.includes('✅')) {
                    gpsStatus.textContent = `✅ Localização obtida via GPS${privacyText}`;
                }
                
                if (feedback && feedback.textContent.includes('✅')) {
                    const baseText = feedback.textContent.split('(±')[0].split('(posição exata)')[0];
                    feedback.textContent = `${baseText}${privacyText}`;
                }
                
                console.log('Raio de privacidade atualizado:', {
                    newRadius,
                    real: this.realUserPosition,
                    private: this.userPosition,
                    privacyApplied: this.userPosition.isPrivacyApplied
                });
            }
        });
        
        // Apply
        const applyBtn = document.getElementById('apply-config');
        applyBtn?.addEventListener('click', () => this.applyConfiguration());
        
        // Mobile toggle panels (separados)
        const toggleConfigBtn = document.getElementById('toggle-config');
        const toggleOnlineBtn = document.getElementById('toggle-online');
        
        // Debug: verificar se elementos existem
        console.log('Botões encontrados:', {
            config: !!toggleConfigBtn,
            online: !!toggleOnlineBtn,
            paineis: document.querySelectorAll('.sidebar-panel').length
        });
        
        toggleConfigBtn?.addEventListener('click', () => {
            // Se já está configurado, mostrar mensagem de bloqueio
            if (this.isConfigured) {
                this.showSessionActiveMessage();
                return;
            }
            this.toggleConfigPanel();
        });
        toggleOnlineBtn?.addEventListener('click', () => this.toggleOnlinePanel());
        
        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (this.globalCountdown) clearInterval(this.globalCountdown);
            if (this.updateInterval) clearInterval(this.updateInterval);
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
        });
    }

    debouncedAddressSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => this.searchAddressByGeocoder(), 500);
    }

    searchAddressImmediate() {
        clearTimeout(this.searchTimeout);
        this.searchAddressByGeocoder();
    }

    searchAddressByGeocoder() {
        const endereco = (document.getElementById('address-input')?.value || '').trim();
        const feedback = document.getElementById('address-feedback');
        const addressInput = document.getElementById('address-input');
        
        if (!endereco) {
            if (feedback) { 
                feedback.textContent = 'Digite uma cidade, bairro ou CEP'; 
                feedback.className = 'text-xs text-gray-500 mt-2'; 
            }
            this.updateLocationStatus(false);
            return;
        }
        
        if (!window.google || !this.geocoder) {
            if (window.google && !this.geocoder) {
                this.geocoder = new google.maps.Geocoder();
            }
            if (!this.geocoder) {
                if (feedback) { 
                    feedback.textContent = 'Aguarde o carregamento do Google Maps...'; 
                    feedback.className = 'text-xs text-orange-600 mt-2'; 
                }
                return;
            }
        }
        
        if (feedback) { 
            feedback.textContent = 'Buscando endereço...'; 
            feedback.className = 'text-xs text-blue-600 mt-2'; 
        }
        
        // Adicionar loading visual no input
        if (addressInput) {
            addressInput.classList.add('loading');
        }
        
        this.geocoder.geocode({ 
            address: `${endereco}, Brasil`,
            region: 'BR' // Forçar resultados do Brasil
        }, (results, status) => {
            // Remover loading visual
            if (addressInput) {
                addressInput.classList.remove('loading');
            }
            
            if (status === 'OK' && results && results[0]) {
                const location = results[0].geometry.location;
                const formattedAddress = results[0].formatted_address;
                
                // Obter posição real do geocoding
                const realPosition = { lat: location.lat(), lng: location.lng() };
                
                // Aplicar raio de privacidade
                this.realUserPosition = realPosition; // Posição real (nunca enviada)
                this.userPosition = this.getPrivatePosition(realPosition); // Posição com privacidade
                
                const privacyText = this.userPosition.isPrivacyApplied 
                    ? ` (±${this.privacyRadius < 1000 ? this.privacyRadius + 'm' : Math.round(this.privacyRadius/1000) + 'km'})` 
                    : '';
                
                if (feedback) { 
                    feedback.textContent = `✅ ${formattedAddress}${privacyText}`; 
                    feedback.className = 'text-xs text-green-600 mt-2'; 
                }
                
                if (addressInput) {
                    addressInput.classList.remove('border-red-500');
                    addressInput.classList.add('border-green-500', 'text-green-700');
                }
                
                this.updateLocationStatus(true);
                this.checkConfigComplete();
                
                console.log('Endereço geocodificado:', {
                    address: formattedAddress,
                    real: realPosition,
                    private: this.userPosition,
                    privacyApplied: this.userPosition.isPrivacyApplied,
                    radiusMeters: this.privacyRadius
                });
                
            } else {
                // Mapear erros específicos do Google Maps
                let errorMessage = 'Local não encontrado.';
                switch (status) {
                    case 'ZERO_RESULTS':
                        errorMessage = 'Endereço não encontrado. Tente: "São Paulo, SP" ou "01234-567"';
                        break;
                    case 'OVER_QUERY_LIMIT':
                        errorMessage = 'Muitas buscas. Aguarde um momento e tente novamente.';
                        break;
                    case 'REQUEST_DENIED':
                        errorMessage = 'Serviço de geolocalização indisponível.';
                        break;
                    case 'INVALID_REQUEST':
                        errorMessage = 'Formato de endereço inválido.';
                        break;
                    default:
                        errorMessage = 'Erro na busca. Verifique sua conexão.';
                }
                
                if (feedback) { 
                    feedback.textContent = `❌ ${errorMessage}`; 
                    feedback.className = 'text-xs text-red-600 mt-2'; 
                }
                
                if (addressInput) {
                    addressInput.classList.remove('border-green-500', 'text-green-700');
                    addressInput.classList.add('border-red-500');
                }
                
                this.updateLocationStatus(false);
            }
        });
    }

    selectAvatar(avatarType) {
        this.selectedAvatar = avatarType;
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.classList.remove('border-green-500', 'bg-green-50');
            btn.classList.add('border-gray-200');
        });
        const selectedBtn = document.querySelector(`[data-avatar="${avatarType}"]`);
        selectedBtn?.classList.remove('border-gray-200');
        selectedBtn?.classList.add('border-green-500', 'bg-green-50');
        const avatarStatus = document.getElementById('avatar-status');
        const avatarCheck = document.getElementById('avatar-check');
        if (avatarStatus) avatarStatus.className = 'w-2 h-2 bg-green-500 rounded-full';
        if (avatarCheck) { avatarCheck.className = 'flex items-center text-green-600'; avatarCheck.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>Avatar'; }
        this.checkConfigComplete();
    }

    updateLocationStatus(isComplete) {
        const locationDot = document.getElementById('location-status-dot');
        const locationCheck = document.getElementById('location-check');
        if (isComplete) {
            if (locationDot) locationDot.className = 'w-2 h-2 bg-green-500 rounded-full';
            if (locationCheck) { locationCheck.className = 'flex items-center text-green-600'; locationCheck.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>Localização'; }
        } else {
            if (locationDot) locationDot.className = 'w-2 h-2 bg-gray-300 rounded-full';
            if (locationCheck) { locationCheck.className = 'flex items-center text-gray-400'; locationCheck.innerHTML = '<span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>Localização'; }
        }
        this.checkConfigComplete();
    }

    updateRadiusDisplay(radiusMeters) {
        this.privacyRadius = radiusMeters;
        const display = document.getElementById('radius-value');
        if (display) {
            display.textContent = radiusMeters < 1000 ? `${radiusMeters} m` : `${Math.round(radiusMeters / 1000)} km`;
        }
    }

    // Aplicar raio de privacidade à posição real
    applyPrivacyRadius(realPosition, radiusMeters) {
        if (!realPosition || !radiusMeters) return realPosition;
        
        // Converter raio de metros para graus (aproximadamente)
        const radiusInDegrees = radiusMeters / 111000; // 1 grau ≈ 111km
        
        // Gerar um ângulo aleatório (0 a 360 graus)
        const randomAngle = Math.random() * 2 * Math.PI;
        
        // Gerar uma distância aleatória dentro do raio (distribuição uniforme em área)
        const randomDistance = Math.sqrt(Math.random()) * radiusInDegrees;
        
        // Calcular nova posição
        const newLat = realPosition.lat + (randomDistance * Math.cos(randomAngle));
        const newLng = realPosition.lng + (randomDistance * Math.sin(randomAngle));
        
        return {
            lat: newLat,
            lng: newLng,
            original: realPosition, // Manter referência à posição original
            isPrivacyApplied: true
        };
    }

    // Aplicar privacidade com base no tipo de localização
    getPrivatePosition(realPosition) {
        if (!realPosition) return null;
        
        // Se o raio é muito pequeno (menos de 100m), não aplicar privacidade
        if (this.privacyRadius < 100) {
            return {
                ...realPosition,
                original: realPosition,
                isPrivacyApplied: false
            };
        }
        
        return this.applyPrivacyRadius(realPosition, this.privacyRadius);
    }

    checkConfigComplete() {
        const applyBtn = document.getElementById('apply-config');
        const hasAvatar = this.selectedAvatar !== null;
        const hasLocation = this.userPosition !== null;
        if (applyBtn) {
            if (hasAvatar && hasLocation) {
                applyBtn.disabled = false;
                applyBtn.classList.remove('bg-gray-300');
                applyBtn.classList.add('bg-green-500', 'hover:bg-green-600');
            } else {
                applyBtn.disabled = true;
                applyBtn.classList.add('bg-gray-300');
                applyBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
            }
        }
    }

    async applyConfiguration() {
        if (!this.selectedAvatar || !this.userPosition) {
            alert('Selecione um avatar e defina sua localização primeiro.');
            return;
        }
        try {
            this.isConfigured = true;
            
            // Sempre tentar enviar localização para o servidor (mesmo para usuários não autenticados)
            try {
                await this.sendLocationToServer();
            } catch (error) {
                console.log('Usuário não autenticado - localização não salva no servidor');
            }
            
            // Atualizar dados se autenticado
            if (this.isAuthenticated) {
                await this.updateAvatar(this.selectedAvatar);
                await this.updatePrivacyRadius(this.privacyRadius);
            }
            
            // UI - Esconder painel completo de configuração (desktop e mobile)
            const configPanel = document.getElementById('config-panel');
            if (configPanel) {
                configPanel.classList.add('hidden'); // Desktop
                configPanel.classList.remove('mobile-config-visible'); // Mobile
            }
            
            // Remover overlay mobile se existir
            const overlay = document.querySelector('.mobile-panels-overlay');
            if (overlay) {
                overlay.remove();
            }
            
            this.addUserMarkerToMap();
            
            // Carregar usuários e iniciar atualização periódica
            await this.loadOnlineUsers();
            this.startPeriodicUpdates();
            
            // Callbacks
            this.onConfigurationApplied();
        } catch (error) {
            console.error('Erro ao aplicar configuração:', error);
            alert('Erro ao aplicar configuração. Tente novamente.');
        }
    }

    async sendLocationToServer() {
        if (!this.userPosition) {
            console.warn('⚠️ Sem posição do usuário para enviar');
            return;
        }
        
        const endpoint = this.isAuthenticated ? '/location/update' : '/location/anonymous';
        const sessionId = this.isAuthenticated ? null : this.generateSessionId();
        
        console.log('📍 Enviando localização para:', endpoint);
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ 
                    latitude: this.userPosition.lat, 
                    longitude: this.userPosition.lng,
                    avatar_type: this.selectedAvatar,
                    privacy_radius: this.privacyRadius,
                    session_id: sessionId
                })
            });
            
            const result = await response.json();
            
            if (!result.success) {
                console.error('❌ Erro na resposta do servidor:', result.message);
            }
            
        } catch (error) {
            console.error('❌ Erro ao enviar localização:', error);
        }
    }

    generateSessionId() {
        // Criar ID único para sessão anônima
        if (!this.anonymousSessionId) {
            this.anonymousSessionId = 'anon_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        return this.anonymousSessionId;
    }

    addUserMarkerToMap() {
        if (!window.map || !this.userPosition || !this.selectedAvatar) return;
        if (this.userMarker) this.userMarker.setMap(null);
        
        // Configurar detecção de interação do usuário (uma vez só)
        if (!window.userInteractionListenersAdded) {
            window.userHasInteracted = false;
            window.map.addListener('drag', () => {
                window.userHasInteracted = true;
                console.log('👋 Usuário moveu o mapa - não interferir no zoom');
            });
            window.map.addListener('zoom_changed', () => {
                window.userHasInteracted = true;
                console.log('🔍 Usuário mudou zoom - não interferir no zoom');
            });
            window.userInteractionListenersAdded = true;
        }
        
        const avatarFile = this.getAvatarFilename(this.selectedAvatar);
        this.userMarker = new google.maps.Marker({
            position: this.userPosition,
            map: window.map,
            title: 'Você está aqui',
            icon: { url: `/images/${avatarFile}`, scaledSize: new google.maps.Size(45, 45), anchor: new google.maps.Point(22.5, 22.5) },
            zIndex: 1000
        });
        const userInfoWindow = new google.maps.InfoWindow({
            content: `
                <div class="p-2 text-center">
                    <div class="flex items-center justify-center space-x-2 mb-2">
                        <img src="/images/${avatarFile}" alt="Você" class="w-6 h-6 rounded-full">
                        <strong>Você está aqui</strong>
                        ${this.userPosition.isPrivacyApplied ? '<span class="text-xs bg-yellow-100 text-yellow-800 px-1 rounded">🔒</span>' : ''}
                    </div>
                    <div class="text-xs text-gray-600">
                        Avatar: ${this.selectedAvatar} • Raio: ${this.privacyRadius < 1000 ? this.privacyRadius + 'm' : Math.round(this.privacyRadius/1000) + 'km'}
                    </div>
                    ${this.userPosition.isPrivacyApplied ? 
                        '<div class="text-xs text-yellow-700 mt-1">🔒 Posição randomizada para privacidade</div>' : 
                        '<div class="text-xs text-green-700 mt-1">📍 Posição exata (raio muito pequeno)</div>'
                    }
                </div>`
        });
        this.userMarker.addListener('click', () => userInfoWindow.open(window.map, this.userMarker));
        window.map.setCenter(this.userPosition);
        window.map.setZoom(12);
        const currentLoc = document.getElementById('current-location');
        if (currentLoc) currentLoc.textContent = `${this.userPosition.lat.toFixed(4)}, ${this.userPosition.lng.toFixed(4)}`;
        const debugLocation = document.getElementById('debug-location');
        if (debugLocation) debugLocation.textContent = `Localização: ${this.userPosition.lat.toFixed(4)}, ${this.userPosition.lng.toFixed(4)} (${this.isMobile ? 'GPS' : 'Endereço'})`;
    }

    toggleConfigPanel() {
        const configPanel = document.getElementById('config-panel');
        const toggleBtn = document.getElementById('toggle-config');
        
        console.log('Toggle config - painel encontrado:', !!configPanel);
        
        if (!configPanel || !toggleBtn) {
            console.error('Painel de config ou botão não encontrado');
            return;
        }
        
        const isVisible = configPanel.classList.contains('mobile-config-visible');
        console.log('Config panel visível:', isVisible);
        
        if (isVisible) {
            // Fechar painel
            configPanel.classList.remove('mobile-config-visible');
            toggleBtn.textContent = '⚙️';
            toggleBtn.setAttribute('aria-label', 'Abrir configurações');
            
            // Remover overlay se não há outros painéis abertos
            const onlineVisible = document.getElementById('online-panel')?.classList.contains('mobile-online-visible');
            if (!onlineVisible) {
                const overlay = document.querySelector('.mobile-panels-overlay');
                if (overlay) overlay.remove();
            }
        } else {
            // Abrir painel
            configPanel.classList.add('mobile-config-visible');
            toggleBtn.textContent = '✕';
            toggleBtn.setAttribute('aria-label', 'Fechar configurações');
            
            // Criar overlay se não existe
            if (!document.querySelector('.mobile-panels-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'mobile-panels-overlay';
                overlay.addEventListener('click', () => this.closeAllMobilePanels());
                document.body.appendChild(overlay);
            }
        }
    }

    toggleOnlinePanel() {
        const onlinePanel = document.getElementById('online-panel');
        const toggleBtn = document.getElementById('toggle-online');
        
        console.log('Toggle online - painel encontrado:', !!onlinePanel);
        
        if (!onlinePanel || !toggleBtn) {
            console.error('Painel online ou botão não encontrado');
            return;
        }
        
        const isVisible = onlinePanel.classList.contains('mobile-online-visible');
        console.log('Online panel visível:', isVisible);
        
        if (isVisible) {
            // Fechar painel
            onlinePanel.classList.remove('mobile-online-visible');
            toggleBtn.textContent = '👥';
            toggleBtn.setAttribute('aria-label', 'Ver usuários online');
            
            // Remover overlay se não há outros painéis abertos
            const configVisible = document.getElementById('config-panel')?.classList.contains('mobile-config-visible');
            if (!configVisible) {
                const overlay = document.querySelector('.mobile-panels-overlay');
                if (overlay) overlay.remove();
            }
        } else {
            // Abrir painel
            onlinePanel.classList.add('mobile-online-visible');
            toggleBtn.textContent = '✕';
            toggleBtn.setAttribute('aria-label', 'Fechar usuários online');
            
            // Criar overlay se não existe
            if (!document.querySelector('.mobile-panels-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'mobile-panels-overlay';
                overlay.addEventListener('click', () => this.closeAllMobilePanels());
                document.body.appendChild(overlay);
            }
        }
    }

    closeAllMobilePanels() {
        console.log('Fechando todos os painéis mobile');
        
        // Fechar ambos os painéis usando IDs
        const configPanel = document.getElementById('config-panel');
        const onlinePanel = document.getElementById('online-panel');
        
        if (configPanel) configPanel.classList.remove('mobile-config-visible');
        if (onlinePanel) onlinePanel.classList.remove('mobile-online-visible');
        
        // Remover overlay
        const overlay = document.querySelector('.mobile-panels-overlay');
        if (overlay) {
            overlay.remove();
            console.log('Overlay removido');
        }
        
        // Restaurar botões
        const toggleConfigBtn = document.getElementById('toggle-config');
        const toggleOnlineBtn = document.getElementById('toggle-online');
        
        if (toggleConfigBtn) {
            toggleConfigBtn.textContent = '⚙️';
            toggleConfigBtn.setAttribute('aria-label', 'Abrir configurações');
        }
        
        if (toggleOnlineBtn) {
            toggleOnlineBtn.textContent = '👥';
            toggleOnlineBtn.setAttribute('aria-label', 'Ver usuários online');
        }
    }

    showSessionActiveMessage() {
        const modal = `
            <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 modal-overlay" id="session-active-modal" onclick="if(event.target === this) this.remove()">
                <div class="bg-white rounded-xl p-8 max-w-md mx-4 text-center shadow-2xl relative" onclick="event.stopPropagation()">
                    <button onclick="document.getElementById('session-active-modal').remove()" class="absolute top-3 right-3 w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors" title="Fechar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="text-6xl mb-4">🔒</div>
                    <h3 class="text-2xl font-bold text-blue-600 mb-4">Sessão Ativa</h3>
                    <p class="text-gray-700 mb-4">Você só poderá modificar sua localização e avatar quando o cronômetro global zerar.</p>
                    <p class="text-sm text-gray-600 mb-6">Isso garante que todos tenham uma experiência justa no mapa.</p>
                    <div class="text-xs text-gray-500 mb-4">
                        💡 <strong>Dica:</strong> Você pode atualizar a página para reconfigurar, mas perderá sua posição atual.
                    </div>
                    <button onclick="document.getElementById('session-active-modal').remove()" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        Entendi
                    </button>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modal);
        
        // Adicionar suporte para fechar com ESC
        const handleEscape = (event) => {
            if (event.key === 'Escape') {
                const modal = document.getElementById('session-active-modal');
                if (modal) {
                    modal.remove();
                    document.removeEventListener('keydown', handleEscape);
                }
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    resetConfiguration() {
        // Limpar dados do usuário
        this.userPosition = null;
        this.realUserPosition = null; // Limpar também a posição real
        this.selectedAvatar = null;
        this.isConfigured = false;
        
        // Remover marcador do usuário do mapa
        if (this.userMarker) {
            this.userMarker.setMap(null);
            this.userMarker = null;
        }
        
        // Resetar UI para estado inicial (desktop e mobile)
        const configPanel = document.getElementById('config-panel');
        if (configPanel) {
            configPanel.classList.remove('hidden'); // Desktop
            // No mobile, mostrar o painel quando resetar
            if (this.isMobile) {
                configPanel.classList.add('mobile-config-visible');
            }
        }
        document.getElementById('config-section')?.classList.remove('hidden');
        // Resetar flag de interação do usuário para permitir auto-zoom na próxima sessão
        window.userHasInteracted = false;
        console.log('🔄 Reset: Auto-zoom reabilitado para nova sessão');
        
        // Limpar seleções de avatar
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.classList.remove('border-green-500', 'bg-green-50');
            btn.classList.add('border-gray-200');
        });
        
        // Resetar status indicators
        const avatarStatus = document.getElementById('avatar-status');
        const locationDot = document.getElementById('location-status-dot');
        const avatarCheck = document.getElementById('avatar-check');
        const locationCheck = document.getElementById('location-check');
        
        if (avatarStatus) avatarStatus.className = 'w-2 h-2 bg-gray-300 rounded-full';
        if (locationDot) locationDot.className = 'w-2 h-2 bg-gray-300 rounded-full';
        if (avatarCheck) {
            avatarCheck.className = 'flex items-center text-gray-400';
            avatarCheck.innerHTML = '<span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>Avatar';
        }
        if (locationCheck) {
            locationCheck.className = 'flex items-center text-gray-400';
            locationCheck.innerHTML = '<span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>Localização';
        }
        
        // Limpar input de endereço
        const addressInput = document.getElementById('address-input');
        const addressFeedback = document.getElementById('address-feedback');
        if (addressInput) {
            addressInput.value = '';
            addressInput.classList.remove('border-green-500', 'text-green-700', 'border-red-500');
        }
        if (addressFeedback) {
            addressFeedback.textContent = 'Digite uma cidade, bairro ou CEP';
            addressFeedback.className = 'text-xs text-gray-500 mt-2';
        }
        
        // Resetar slider para valor padrão
        const radiusSlider = document.getElementById('privacy-radius');
        if (radiusSlider) {
            radiusSlider.value = '50000';
            this.updateRadiusDisplay(50000);
        }
        
        // Reinicializar detecção de plataforma
        this.initializeUIByPlatform();
        
        // Verificar se configuração está completa
        this.checkConfigComplete();
        
        console.log('Configuração resetada completamente');
    }

    async updateAvatar(avatarType) {
        if (!this.isAuthenticated) return;
        try {
            await fetch('/location/avatar', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ avatar_type: avatarType })
            });
        } catch (error) {
            console.error('Erro ao atualizar avatar no servidor:', error);
        }
    }

    async updatePrivacyRadius(radiusMeters) {
        if (!this.isAuthenticated) return;
        try {
            await fetch('/location/privacy-radius', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ radius: radiusMeters })
            });
        } catch (error) {
            console.error('Erro ao atualizar raio de privacidade no servidor:', error);
        }
    }

    async loadOnlineUsers() {
        if (!this.isConfigured) {
            console.warn('⚠️ LocationManager não configurado, pulando loadOnlineUsers');
            return;
        }
        
        console.log('🔄 Carregando usuários online...');
        
        try {
            // Tentar primeiro a nova rota, depois fallback para a antiga
            let response;
            try {
                response = await fetch('/usuarios-online.json', { 
                    headers: { 'Accept': 'application/json' } 
                });
                console.log('📡 Resposta recebida:', response.status, response.statusText);
            } catch (error) {
                // Fallback para rota alternativa
                response = await fetch('/users/online', { 
                    headers: { 'Accept': 'application/json' } 
                });
                console.log('📡 Fallback - Resposta recebida:', response.status, response.statusText);
            }
            
            if (!response.ok) throw new Error(`HTTP ${response.status}: Falha ao carregar usuários online`);
            
            const data = await response.json();
            console.log('📊 Dados JSON recebidos:', data);
            
            // Suportar múltiplos formatos de resposta
            let users = [];
            if (Array.isArray(data)) {
                users = data;
            } else if (data.users && Array.isArray(data.users)) {
                users = data.users;
            } else if (data.success && Array.isArray(data.users)) {
                users = data.users;
            }
            
            console.log('👥 Usuários processados:', users.length, users);
            console.log('🔍 LocationManager state:', {
                isConfigured: this.isConfigured,
                isAuthenticated: this.isAuthenticated,
                anonymousSessionId: this.anonymousSessionId,
                currentUserId: window.currentUserId
            });
            
            this.onUsersUpdate(users);
            
            // Atualizar debug info
            const debugUsers = document.getElementById('debug-users');
            if (debugUsers) {
                debugUsers.textContent = `Usuários: ${users.length} online`;
            }
            
        } catch (error) {
            console.error('❌ Erro ao carregar usuários online:', error);
            
            // Mostrar erro no debug se disponível
            const debugUsers = document.getElementById('debug-users');
            if (debugUsers) {
                debugUsers.textContent = `Erro: ${error.message}`;
            }
        }
    }

    startPeriodicUpdates() {
        if (this.updateInterval) return;
        this.updateInterval = setInterval(() => this.loadOnlineUsers(), 15000); // Reduzir para 15s
        
        // Forçar atualização quando a página voltar a ter foco
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.loadOnlineUsers();
            }
        });
    }

    getAvatarFilename(avatarType) {
        const avatarMap = { 
            'default': 'default.gif', 
            'man': 'mario.gif', 
            'woman': 'girl.gif', 
            'pet': 'pets.gif', 
            'geek': 'geek.gif', 
            'sport': 'sport.gif' 
        };
        return avatarMap[avatarType] || 'default.gif';
    }

    // Utilitário para mostrar/ocultar loading em elementos
    setElementLoading(elementId, isLoading = true, loadingText = 'Carregando...') {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        if (isLoading) {
            element.classList.add('loading');
            if (element.textContent && !element.dataset.originalText) {
                element.dataset.originalText = element.textContent;
            }
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.disabled = true;
            } else {
                element.textContent = loadingText;
            }
        } else {
            element.classList.remove('loading');
            if (element.dataset.originalText) {
                element.textContent = element.dataset.originalText;
                delete element.dataset.originalText;
            }
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.disabled = false;
            }
        }
    }

    // Utilitário para mostrar feedback temporário
    showTemporaryFeedback(elementId, message, type = 'info', duration = 3000) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const typeClasses = {
            'success': 'text-green-600',
            'error': 'text-red-600',
            'warning': 'text-orange-600',
            'info': 'text-blue-600'
        };
        
        element.textContent = message;
        element.className = `text-xs ${typeClasses[type] || typeClasses.info} mt-2`;
        
        if (duration > 0) {
            setTimeout(() => {
                element.textContent = '';
                element.className = 'text-xs text-gray-500 mt-2';
            }, duration);
        }
    }

    onConfigurationApplied() { /* hook externo */ }
    onUsersUpdate(/* users */) { /* hook externo */ }

    destroy() {
        if (this.globalCountdown) clearInterval(this.globalCountdown);
        if (this.updateInterval) clearInterval(this.updateInterval);
        if (this.searchTimeout) clearTimeout(this.searchTimeout);
        if (this.userMarker) { this.userMarker.setMap(null); this.userMarker = null; }
    }
}

// Inicialização do mapa
window.initMapChatHome = function() {
    console.log('🗺️ Inicializando mapa...');
    console.log('🗺️ Google Maps disponível:', !!window.google?.maps);
    
    if (!window.google || !window.google.maps) {
        console.error('❌ Google Maps não carregou!');
        return;
    }
    
    const mapElement = document.getElementById('map');
    console.log('🗺️ Elemento do mapa encontrado:', !!mapElement);
    
    if (!mapElement) {
        console.error('❌ Elemento #map não encontrado!');
        return;
    }
    
    try {
        const map = new google.maps.Map(mapElement, {
            center: { lat: -14.2350, lng: -51.9253 },
            zoom: 4,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: false,
            zoomControl: true,
            gestureHandling: 'greedy',
            styles: [{ featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }]
        });
        
        window.map = map;
        console.log('✅ Mapa criado com sucesso!');
        
        // Verificar se o mapa foi realmente criado após um breve delay
        setTimeout(() => {
            console.log('🗺️ Status do mapa após criação:', {
                hasMap: !!window.map,
                mapCenter: window.map?.getCenter()?.toString()
            });
        }, 1000);
        
    } catch (error) {
        console.error('❌ Erro ao criar mapa:', error);
    }
};

// Variáveis globais do mapa
window.markers = [];
window.markerCluster = null;

function updateUsersDisplay(users) {
    const usersList = document.getElementById('users-list');
    const usersCount = document.getElementById('users-count');
    if (!usersList || !usersCount) return;
    usersCount.textContent = users.length;
    if (!users.length) {
        usersList.innerHTML = '<div class="text-gray-500 text-sm text-center py-4">Nenhum usuário online</div>';
        return;
    }
    const html = users.map(user => {
        const avatar = window.locationManager.getAvatarFilename(user.avatar_type);
        const lastSeen = new Date(user.last_seen || Date.now());
        const timeAgo = getTimeAgo(lastSeen);
        const lat = Number(user.latitude ?? user.lat ?? 0);
        const lng = Number(user.longitude ?? user.lng ?? 0);
        return `
            <div class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer" data-user-id="${user.id}" data-lat="${lat}" data-lng="${lng}">
                <img src="/images/${avatar}" alt="${user.name}" class="w-8 h-8 rounded-full border border-gray-300">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">${user.name}</div>
                    <div class="text-xs text-gray-500">${timeAgo}</div>
                </div>
                <div class="w-2 h-2 bg-green-500 rounded-full" aria-hidden="true"></div>
            </div>`;
    }).join('');
    usersList.innerHTML = html;
    usersList.querySelectorAll('[data-user-id]').forEach(el => {
        el.addEventListener('click', function() {
            const lat = parseFloat(this.getAttribute('data-lat'));
            const lng = parseFloat(this.getAttribute('data-lng'));
            if (window.map && !isNaN(lat) && !isNaN(lng)) {
                window.map.setCenter({ lat, lng });
                window.map.setZoom(15);
            }
        });
    });
}

function updateMapMarkers(users) {
    if (window.markerCluster) {
        try { window.markerCluster.clearMarkers(); } catch (e) {}
        window.markerCluster = null;
    }
    window.markers.forEach(m => m.setMap(null));
    window.markers = [];
    if (!users || !users.length || !window.map) {
        console.log('🚫 updateMapMarkers: Sem usuários ou mapa não carregado');
        return;
    }
    
    console.log('👥 updateMapMarkers: Recebidos', users.length, 'usuários');
    console.log('🔍 Dados dos usuários:', users);
    console.log('🔍 LocationManager state:', {
        isAuthenticated: window.locationManager?.isAuthenticated,
        anonymousSessionId: window.locationManager?.anonymousSessionId,
        currentUserId: window.currentUserId,
        isConfigured: window.locationManager?.isConfigured
    });
    
    // Filtrar o próprio usuário de forma mais robusta
    let otherUsers = users;
    
    if (window.locationManager?.isConfigured && window.locationManager?.anonymousSessionId) {
        // Se usuário está configurado e tem session_id, filtrar apenas o próprio
        const mySessionId = window.locationManager.anonymousSessionId;
        otherUsers = users.filter(user => {
            const isMe = user.id === mySessionId;
            console.log(`👤 Verificando ${user.id} vs ${mySessionId}: é meu = ${isMe}`);
            return !isMe;
        });
        console.log(`🎯 Filtrando meu próprio usuário: ${users.length} -> ${otherUsers.length}`);
    } else {
        console.log('🎯 Usuário não configurado ainda, mostrando todos os usuários');
    }
    
    console.log(`🎯 Exibindo ${otherUsers.length} outros usuários (filtrado de ${users.length} total)`);
    
    otherUsers.forEach(user => {
        const avatar = window.locationManager.getAvatarFilename(user.avatar_type);
        const lat = Number(user.latitude ?? user.lat);
        const lng = Number(user.longitude ?? user.lng);
        if (isNaN(lat) || isNaN(lng)) return;
        const marker = new google.maps.Marker({
            position: { lat, lng },
            title: user.name,
            icon: { url: `/images/${avatar}`, scaledSize: new google.maps.Size(40, 40), anchor: new google.maps.Point(20, 20) }
        });
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <img src="/images/${avatar}" alt="${user.name}" class="w-6 h-6 rounded-full">
                        <strong>${user.name}</strong>
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    </div>
                    <div class="text-xs text-gray-600">Online agora • Avatar: ${user.avatar_type}</div>
                    ${window.isAuthenticated ? '<button class="mt-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Conversar</button>' : '<div class="mt-2 text-xs text-gray-500">Configure seu perfil primeiro</div>'}
                </div>`
        });
        marker.addListener('click', () => infoWindow.open(window.map, marker));
        window.markers.push(marker);
    });
    if (window.markers.length) {
        try {
            window.markerCluster = new markerClusterer.MarkerClusterer({ map: window.map, markers: window.markers, gridSize: 60, maxZoom: 15 });
        } catch (e) {
            window.markers.forEach(m => m.setMap(window.map));
        }
        
        // Só ajustar zoom/centro se o usuário ainda não interagiu com o mapa
        if (window.markers.length && !window.userHasInteracted) {
            const bounds = new google.maps.LatLngBounds();
            window.markers.forEach(m => bounds.extend(m.getPosition()));
            
            if (window.markers.length === 1) {
                window.map.setCenter(window.markers[0].getPosition());
                window.map.setZoom(10);
                console.log('🎯 Auto-zoom: 1 usuário encontrado');
            } else {
                window.map.fitBounds(bounds);
                const listener = google.maps.event.addListener(window.map, 'idle', function() {
                    if (window.map.getZoom() > 15) window.map.setZoom(15);
                    google.maps.event.removeListener(listener);
                });
                console.log(`🎯 Auto-zoom: ${window.markers.length} usuários encontrados`);
            }
        } else if (window.userHasInteracted) {
            console.log('🚫 Zoom automático desabilitado - usuário está explorando');
        }
    }

    // Calcular e mostrar centro do chat (incluindo todos os usuários, não apenas "outros")
    console.log('🔍 Chamando calculateChatCenter com:', users.length, 'usuários (TODOS)');
    const chatCenter = calculateChatCenter(users); // Usar todos os usuários para o chat
    console.log('💬 Resultado do calculateChatCenter:', chatCenter);
    
    // TESTE: Adicionar marcador fixo de chat para debug
    if (users.length > 0 && window.map && !window.testChatMarker) {
        console.log('🧪 Criando marcador de teste...');
        window.testChatMarker = new google.maps.Marker({
            position: { lat: -15.8, lng: -47.9 }, // Posição fixa para teste
            map: window.map,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="100" height="40" xmlns="http://www.w3.org/2000/svg">
                        <rect x="5" y="5" width="90" height="25" rx="12" fill="#059669" stroke="#fff" stroke-width="2"/>
                        <text x="50" y="20" font-family="Arial, sans-serif" font-size="12" fill="white" text-anchor="middle" font-weight="bold">
                            💬 CHAT (${users.length})
                        </text>
                        <polygon points="50,30 45,35 55,35" fill="#059669"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(100, 40),
                anchor: new google.maps.Point(50, 40)
            },
            title: `TESTE: Chat com ${users.length} usuários online`,
            zIndex: 2000
        });

        window.testChatMarker.addListener('click', () => {
            console.log('🧪 TESTE: Marcador clicado!');
            alert(`Chat clicado! ${users.length} usuários online.`);
            document.getElementById('chat-panel').classList.remove('hidden');
        });
        
        console.log('🧪 Marcador de teste criado!');
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'agora';
    if (diff < 3600) return Math.floor(diff / 60) + ' min';
    if (diff < 86400) return Math.floor(diff / 3600) + ' h';
    return Math.floor(diff / 86400) + ' d';
}

// Função para calcular centro geográfico dos usuários e mostrar ícone de chat
function calculateChatCenter(users) {
    console.log('💬 calculateChatCenter chamada com:', users?.length, 'usuários');
    
    if (!users || users.length < 1 || !window.map) { // Mudei de 2 para 1 para teste
        console.log('💬 Removendo marcador de chat - poucos usuários ou mapa não carregado');
        // Remover ícone de chat se houver
        if (window.chatCenterMarker) {
            window.chatCenterMarker.setMap(null);
            window.chatCenterMarker = null;
        }
        return null;
    }

    // Filtrar usuários válidos com coordenadas
    const validUsers = users.filter(user => 
        user.latitude && user.longitude && 
        !isNaN(parseFloat(user.latitude)) && !isNaN(parseFloat(user.longitude))
    );

    console.log('💬 Usuários válidos filtrados:', validUsers.length, 'de', users.length);

    if (validUsers.length < 1) { // Mudei de 2 para 1 para teste
        console.log('💬 Removendo marcador - menos de 1 usuário válido');
        if (window.chatCenterMarker) {
            window.chatCenterMarker.setMap(null);
            window.chatCenterMarker = null;
        }
        return null;
    }

    // Calcular centro geográfico
    let totalLat = 0;
    let totalLng = 0;
    
    validUsers.forEach(user => {
        totalLat += parseFloat(user.latitude);
        totalLng += parseFloat(user.longitude);
    });

    const centerLat = totalLat / validUsers.length;
    const centerLng = totalLng / validUsers.length;

    // Calcular raio (distância do usuário mais distante)
    let maxDistance = 0;
    const centerPos = { lat: centerLat, lng: centerLng };
    
    validUsers.forEach(user => {
        const userPos = { lat: parseFloat(user.latitude), lng: parseFloat(user.longitude) };
        const distance = google.maps.geometry.spherical.computeDistanceBetween(
            new google.maps.LatLng(centerPos.lat, centerPos.lng),
            new google.maps.LatLng(userPos.lat, userPos.lng)
        );
        maxDistance = Math.max(maxDistance, distance);
    });

    const radiusKm = Math.round(maxDistance / 1000);

    // Criar ou atualizar marker de chat
    if (window.chatCenterMarker) {
        window.chatCenterMarker.setPosition(centerPos);
        // Atualizar tooltip
        window.chatCenterMarker.setTitle(`Chat MapChat - ${validUsers.length} pessoa(s) em raio de ${radiusKm}km`);
    } else {
        window.chatCenterMarker = new google.maps.Marker({
            position: centerPos,
            map: window.map,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="80" height="40" xmlns="http://www.w3.org/2000/svg">
                        <rect x="5" y="5" width="70" height="25" rx="12" fill="#059669" stroke="#fff" stroke-width="2"/>
                        <text x="40" y="20" font-family="Arial, sans-serif" font-size="12" fill="white" text-anchor="middle" font-weight="bold">
                            👥 ${validUsers.length} online
                        </text>
                        <polygon points="40,30 35,35 45,35" fill="#059669"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(80, 40),
                anchor: new google.maps.Point(40, 40)
            },
            title: `Clique para abrir chat com ${validUsers.length} pessoa(s) em raio de ${radiusKm}km`,
            zIndex: 1000
        });

        // Adicionar click listener
        window.chatCenterMarker.addListener('click', async () => {
            console.log('🎯 Balão de chat clicado!');
            
            // Verificar se usuário está configurado
            if (!window.locationManager?.isConfigured || !window.locationManager?.anonymousSessionId) {
                console.log('❌ Usuário não configurado, abrindo painel de configuração');
                // Se não configurado, abrir o painel de configuração
                document.getElementById('chat-panel').classList.remove('hidden');
                return;
            }
            
            console.log('✅ Usuário configurado, buscando sala de chat...');
            
            // Usuário configurado - abrir diretamente a sala de chat
            if (window.chatManager) {
                // Abrir painel primeiro
                document.getElementById('chat-panel').classList.remove('hidden');
                
                // Mostrar estado de conectando
                showChatConnecting();
                
                // Encontrar ou criar sala
                const room = await window.chatManager.findOrCreateRoom();
                
                if (room) {
                    console.log('✅ Sala encontrada:', room);
                    // Pular direto para o estado conectado
                    showChatConnected(room);
                } else {
                    console.log('❌ Não foi possível criar sala');
                    // Se não conseguiu criar sala, voltar ao estado inicial
                    document.getElementById('chat-connecting-state').classList.add('hidden');
                    document.getElementById('chat-initial-state').classList.remove('hidden');
                }
            } else {
                console.log('❌ ChatManager não disponível');
                // Fallback para configuração
                document.getElementById('chat-panel').classList.remove('hidden');
            }
        });
    }

    console.log(`💬 Centro do chat: ${centerLat.toFixed(4)}, ${centerLng.toFixed(4)} | ${validUsers.length} usuários | Raio: ${radiusKm}km`);
    
    return {
        center: centerPos,
        radius: maxDistance,
        userCount: validUsers.length
    };
}
function initializeChatWidget() {
    const toggleBtn = document.getElementById('chat-toggle');
    const chatPanel = document.getElementById('chat-panel');
    const closeBtn = document.getElementById('chat-close');
    const statusIndicator = document.getElementById('chat-status-indicator');
    const avatarIndicator = document.getElementById('avatar-indicator');
    const locationIndicator = document.getElementById('location-indicator');

    // Toggle do painel
    toggleBtn.addEventListener('click', () => {
        chatPanel.classList.toggle('hidden');
    });

    // Fechar painel
    closeBtn.addEventListener('click', () => {
        chatPanel.classList.add('hidden');
    });

    // Atualizar indicadores baseado no estado
    function updateChatIndicators() {
        const hasAvatar = window.locationManager?.avatarType;
        const hasLocation = window.locationManager?.isConfigured;

        // Avatar indicator
        if (hasAvatar) {
            avatarIndicator.className = 'w-2 h-2 bg-green-500 rounded-full';
        } else {
            avatarIndicator.className = 'w-2 h-2 bg-red-500 rounded-full';
        }

        // Location indicator
        if (hasLocation) {
            locationIndicator.className = 'w-2 h-2 bg-green-500 rounded-full';
        } else {
            locationIndicator.className = 'w-2 h-2 bg-red-500 rounded-full';
        }

        // Status indicator
        if (hasAvatar && hasLocation) {
            statusIndicator.className = 'absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white';
            statusIndicator.title = 'Pronto para chat';
            showChatConnecting();
        } else if (hasAvatar || hasLocation) {
            statusIndicator.className = 'absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white';
            statusIndicator.title = 'Configuração incompleta';
        } else {
            statusIndicator.className = 'absolute -bottom-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white';
            statusIndicator.title = 'Não configurado';
        }
    }

    // Mostrar estado de conectando
    function showChatConnecting() {
        document.getElementById('chat-initial-state').classList.add('hidden');
        document.getElementById('chat-connecting-state').classList.remove('hidden');
        document.getElementById('chat-messages-container').classList.add('hidden');
        document.getElementById('chat-input-section').classList.add('hidden');

        // Simular busca por sala
        setTimeout(() => {
            if (window.chatManager && window.locationManager?.isConfigured) {
                window.chatManager.findOrCreateRoom();
            }
        }, 2000);
    }

    // Mostrar estado conectado
    function showChatConnected(roomData) {
        document.getElementById('chat-initial-state').classList.add('hidden');
        document.getElementById('chat-connecting-state').classList.add('hidden');
        document.getElementById('chat-messages-container').classList.remove('hidden');
        document.getElementById('chat-input-section').classList.remove('hidden');

        // Atualizar título da sala
        document.getElementById('chat-room-title').textContent = roomData.name || 'Chat MapChat';
        document.getElementById('chat-room-subtitle').textContent = `${roomData.users_count || 0} pessoa(s) próximas`;
    }

    // Callbacks para o LocationManager
    function setupLocationCallbacks() {
        if (window.locationManager) {
            const originalOnConfigApplied = window.locationManager.onConfigurationApplied;
            window.locationManager.onConfigurationApplied = function() {
                if (originalOnConfigApplied) originalOnConfigApplied.call(this);
                updateChatIndicators();
            };

            const originalOnUsersUpdate = window.locationManager.onUsersUpdate;
            window.locationManager.onUsersUpdate = function(users) {
                if (originalOnUsersUpdate) originalOnUsersUpdate.call(this, users);
                updateChatIndicators();
            };
        }
    }

    // Integração com ChatManager
    if (window.chatManager) {
        window.chatManager.onRoomJoined = showChatConnected;
    }

    // Configurar callbacks quando LocationManager estiver pronto
    setTimeout(setupLocationCallbacks, 1000);

    // Input de mensagem
    const messageInput = document.getElementById('chat-message-input');
    const sendBtn = document.getElementById('chat-send-btn');
    const charCount = document.getElementById('char-count');

    if (messageInput && sendBtn) {
        // Contador de caracteres
        messageInput.addEventListener('input', () => {
            charCount.textContent = messageInput.value.length;
        });

        // Enviar com Enter
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Enviar com botão
        sendBtn.addEventListener('click', sendMessage);

        function sendMessage() {
            const message = messageInput.value.trim();
            if (message && window.chatManager) {
                window.chatManager.sendMessage(message);
                messageInput.value = '';
                charCount.textContent = '0';
            }
        }
    }

    // Inicializar indicadores
    updateChatIndicators();
}

// Inicialização principal
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM Carregado - Inicializando aplicação');
    
    const isAuthenticated = @json(auth()->check());
    
    try {
        window.locationManager = new LocationManager(isAuthenticated);
        console.log('✅ LocationManager inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar LocationManager:', error);
        // Continuar mesmo com erro
    }

    try {
        // Inicializar ChatManager
        window.chatManager = new ChatManager(window.locationManager);
        console.log('✅ ChatManager inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar ChatManager:', error);
        // Continuar mesmo com erro
    }

    try {
        // Inicializar Widget de Chat
        initializeChatWidget();
        console.log('✅ ChatWidget inicializado');
    } catch (error) {
        console.error('❌ Erro ao inicializar ChatWidget:', error);
        // Continuar mesmo com erro
    }

    // Callbacks para integrar com o mapa e UI
    window.locationManager.onConfigurationApplied = function() {
        if (this.userPosition && window.map) {
            window.map.setCenter(this.userPosition);
            window.map.setZoom(12);
        }
    };
    window.locationManager.onUsersUpdate = function(users) {
        updateUsersDisplay(users);
        updateMapMarkers(users);
        
        // NOVA LÓGICA: Criar/atualizar balão de chat inteligente
        if (users && users.length >= 2 && window.map) {
            // Calcular centro geográfico dos usuários
            let totalLat = 0, totalLng = 0, validUsers = 0;
            users.forEach(user => {
                if (user.latitude && user.longitude && 
                    !isNaN(parseFloat(user.latitude)) && !isNaN(parseFloat(user.longitude))) {
                    totalLat += parseFloat(user.latitude);
                    totalLng += parseFloat(user.longitude);
                    validUsers++;
                }
            });
            
            if (validUsers >= 2) {
                const centerLat = totalLat / validUsers;
                const centerLng = totalLng / validUsers;
                
                if (window.simpleChatBalloon) {
                    // Atualizar posição e texto do balão existente
                    window.simpleChatBalloon.setPosition({ lat: centerLat, lng: centerLng });
                    window.simpleChatBalloon.setTitle(`Chat com ${users.length} usuários online - Clique para conversar`);
                    
                    // Atualizar ícone com nova contagem
                    window.simpleChatBalloon.setIcon({
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 12,
                        fillColor: '#059669',
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    });
                } else {
                    console.log(`💬 Criando balão de chat para ${users.length} usuários no centro geográfico...`);
                    
                    window.simpleChatBalloon = new google.maps.Marker({
                        position: { lat: centerLat, lng: centerLng },
                        map: window.map,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 12,
                            fillColor: '#059669',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 2
                        },
                        title: `Chat com ${users.length} usuários online - Clique para conversar`,
                        zIndex: 2000
                    });
                    
                    // Adicionar evento de clique inteligente
                    window.simpleChatBalloon.addListener('click', async () => {
                        console.log('🟢 BALÃO VERDE CLICADO! DEBUG ATIVO');
                        alert('Balão clicado! Verificando configuração...');
                        
                        console.log('🔍 Estado do LocationManager:', {
                            exists: !!window.locationManager,
                            isConfigured: window.locationManager?.isConfigured,
                            hasSessionId: !!window.locationManager?.anonymousSessionId,
                            sessionId: window.locationManager?.anonymousSessionId
                        });
                        
                        // Abrir painel de chat SEMPRE
                        document.getElementById('chat-panel').classList.remove('hidden');
                        
                        // Se usuário não está configurado, mostrar tela de configuração
                        if (!window.locationManager?.isConfigured) {
                            console.log('⚠️ Usuário não configurado - mostrando tela inicial');
                            alert('Usuário não configurado - configure primeiro!');
                            return;
                        }
                        
                        console.log('✅ Usuário configurado - conectando ao chat...');
                        alert('Usuário configurado! Conectando...');
                        
                        // Usuário configurado - tentar conectar ao chat
                        if (window.chatManager) {
                            showChatConnecting();
                            
                            try {
                                const room = await window.chatManager.findOrCreateRoom();
                                console.log('🏠 Resultado da sala:', room);
                                
                                if (room) {
                                    showChatConnected(room);
                                } else {
                                    console.log('❌ Não foi possível criar sala - voltando ao estado inicial');
                                    // Voltar ao estado inicial
                                    document.getElementById('chat-connecting-state').classList.add('hidden');
                                    document.getElementById('chat-initial-state').classList.remove('hidden');
                                }
                            } catch (error) {
                                console.error('❌ Erro ao buscar sala:', error);
                                // Voltar ao estado inicial
                                document.getElementById('chat-connecting-state').classList.add('hidden');
                                document.getElementById('chat-initial-state').classList.remove('hidden');
                            }
                        } else {
                            console.log('❌ ChatManager não disponível');
                        }
                    });
                    
                    console.log('✅ Balão de chat criado no centro geográfico!');
                }
            }
        } else if (window.simpleChatBalloon && (!users || users.length < 2)) {
            // Remover balão se não há usuários suficientes
            console.log('❌ Removendo balão - poucos usuários online');
            window.simpleChatBalloon.setMap(null);
            window.simpleChatBalloon = null;
        }
        
        // Manter a lógica original do chat center (opcional)
        // calculateChatCenter(users);
    };
    
    // FALLBACK: Tentar inicializar o mapa se não foi inicializado após 3 segundos
    setTimeout(() => {
        console.log('🔄 Verificando se mapa foi inicializado...', { hasMap: !!window.map, hasGoogle: !!window.google });
        if (!window.map) {
            console.log('⚠️ Mapa não foi inicializado, tentando fallback...');
            if (window.initMapChatHome) {
                window.initMapChatHome();
            }
        }
    }, 3000);
    
    // FALLBACK 2: Forçar inicialização após 6 segundos
    setTimeout(() => {
        if (!window.map && window.google?.maps) {
            console.log('🔥 Forçando inicialização do mapa...');
            try {
                const mapElement = document.getElementById('map');
                if (mapElement) {
                    window.map = new google.maps.Map(mapElement, {
                        center: { lat: -14.2350, lng: -51.9253 },
                        zoom: 4,
                        streetViewControl: false,
                        mapTypeControl: false,
                        fullscreenControl: false,
                        zoomControl: true,
                        gestureHandling: 'greedy'
                    });
                    console.log('✅ Mapa forçado criado com sucesso!');
                    
                    // Recarregar usuários se locationManager existe
                    if (window.locationManager?.loadOnlineUsers) {
                        window.locationManager.loadOnlineUsers();
                    }
                }
            } catch (error) {
                console.error('❌ Erro ao forçar criação do mapa:', error);
            }
        }
    }, 6000);
    
    // Função de teste para ativar chat diretamente
    window.testarChatDiretamente = async function() {
        console.log('🧪 TESTE DIRETO DO CHAT INICIADO');
        alert('Iniciando teste do chat...');
        
        try {
            // Configurar usuário fake se não estiver configurado
            if (!window.locationManager?.isConfigured) {
                console.log('📍 Configurando usuário fake para teste...');
                
                // Simular configuração
                if (window.locationManager) {
                    window.locationManager.anonymousSessionId = 'anon_teste_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    window.locationManager.selectedAvatar = 'default';
                    window.locationManager.userPosition = { lat: -23.5505, lng: -46.6333 };
                    window.locationManager.isConfigured = true;
                    
                    console.log('✅ Usuário fake configurado:', {
                        sessionId: window.locationManager.anonymousSessionId,
                        configured: window.locationManager.isConfigured
                    });
                }
            }
            
            // Abrir painel
            document.getElementById('chat-panel').classList.remove('hidden');
            
            // Testar conexão com chat
            if (window.chatManager) {
                console.log('🚀 Testando conexão do ChatManager...');
                const room = await window.chatManager.findOrCreateRoom();
                
                if (room) {
                    alert('✅ Chat conectado com sucesso! Sala: ' + room.name);
                    console.log('✅ Sala conectada:', room);
                } else {
                    alert('❌ Erro ao conectar na sala de chat');
                }
            } else {
                alert('❌ ChatManager não disponível');
                console.error('❌ window.chatManager não existe');
            }
            
        } catch (error) {
            console.error('❌ Erro no teste:', error);
            alert('❌ Erro: ' + error.message);
        }
    };
});
</script>
@endsection

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'SUA_CHAVE_API_AQUI' ) }}&libraries=geometry&callback=initMapChatHome"></script>
@endsection

