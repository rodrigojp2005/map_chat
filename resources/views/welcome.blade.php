


@extends('layouts.app')

@section('title', 'MapChat - Conecte-se no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 80px);">
    <!-- Mapa principal -->
    <div id="map" class="absolute left-0 top-0 w-full h-full z-10"></div>

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

        <!-- Status Section (after apply) -->
        <div id="status-section" class="hidden">
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-700">Status:</h4>
                    <div class="w-2 h-2 bg-green-500 rounded-full" aria-hidden="true"></div>
                </div>
                <div id="location-status" class="text-sm text-gray-600">
                    <div class="flex items-center mb-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span>Você está no mapa!</span>
                    </div>
                    <div class="text-xs text-gray-500" id="current-location">
                        Localização: Calculando...
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-3 flex space-x-2">
                    <button id="change-avatar" class="flex-1 bg-blue-500 text-white py-1.5 px-3 rounded text-xs hover:bg-blue-600 transition-colors">
                        Trocar Avatar
                    </button>
                    <button id="change-location" class="flex-1 bg-orange-500 text-white py-1.5 px-3 rounded text-xs hover:bg-orange-600 transition-colors">
                        Mudar Local
                    </button>
                </div>
            </div>
        </div>

        <!-- Debug info (development only) -->
        <div class="p-4 border-t">
            <button id="toggle-debug" class="text-xs text-gray-500 hover:text-gray-700">Debug Info</button>
            <div id="debug-info" class="mt-2 text-xs text-gray-500 p-2 bg-gray-50 rounded hidden">
                <div id="debug-location">Localização: Aguardando...</div>
                <div id="debug-users">Usuários: Carregando...</div>
                <div id="debug-auth">Auth: {{ auth()->check() ? 'Logado' : 'Visitante' }}</div>
            </div>
        </div>
    </div>
    
    <!-- Painel de usuários online (direita) -->
    <div id="online-panel" class="absolute right-4 top-4 z-20 bg-white rounded-lg shadow-lg p-4 w-64 max-h-96 overflow-y-auto sidebar-panel">
        <h3 class="font-bold text-green-600 mb-3">👥 Online Agora (<span id="users-count">0</span>)</h3>
        <div id="users-list" class="space-y-2">
            <div class="text-gray-500 text-sm text-center py-4">
                Carregando usuários...
            </div>
        </div>
        
        <div class="mt-3 pt-3 border-t text-xs text-gray-500">
            🔄 Atualiza a cada 30s
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
        left: 8px !important;
        right: 8px !important;
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
        // Inicia cronômetro para todos (logados e não logados)
        this.startGlobalCountdown();
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

            if (this.globalTimeRemaining <= 0) {
                this.handleGlobalTimeExpired();
            }
        }, 1000);
    }

    handleGlobalTimeExpired() {
        if (this.globalCountdown) clearInterval(this.globalCountdown);
        this.clearAllUserPositions();
        const modal = `
            <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 modal-overlay">
                <div class="bg-white rounded-xl p-8 max-w-md mx-4 text-center shadow-2xl">
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
        
        // Quick action buttons (new)
        const changeAvatarBtn = document.getElementById('change-avatar');
        const changeLocationBtn = document.getElementById('change-location');
        
        changeAvatarBtn?.addEventListener('click', () => {
            document.getElementById('status-section')?.classList.add('hidden');
            document.getElementById('config-section')?.classList.remove('hidden');
            // Focus no primeiro avatar para melhor UX
            document.querySelector('.avatar-btn')?.focus();
        });
        
        changeLocationBtn?.addEventListener('click', () => {
            this.userPosition = null;
            this.updateLocationStatus(false);
            document.getElementById('status-section')?.classList.add('hidden');
            document.getElementById('config-section')?.classList.remove('hidden');
            // Reinicializar detecção de plataforma
            this.initializeUIByPlatform();
            // Focus no input de endereço se desktop
            if (!this.isMobile) {
                document.getElementById('address-input')?.focus();
            }
        });
        
        // Mobile toggle panels (separados)
        const toggleConfigBtn = document.getElementById('toggle-config');
        const toggleOnlineBtn = document.getElementById('toggle-online');
        
        // Debug: verificar se elementos existem
        console.log('Botões encontrados:', {
            config: !!toggleConfigBtn,
            online: !!toggleOnlineBtn,
            paineis: document.querySelectorAll('.sidebar-panel').length
        });
        
        toggleConfigBtn?.addEventListener('click', () => this.toggleConfigPanel());
        toggleOnlineBtn?.addEventListener('click', () => this.toggleOnlinePanel());
        
        // Debug
        document.getElementById('toggle-debug')?.addEventListener('click', () => this.toggleDebug());
        
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
            // Atualiza servidor se autenticado
            if (this.isAuthenticated) {
                await this.updateServerLocation();
                await this.updateAvatar(this.selectedAvatar);
                await this.updatePrivacyRadius(this.privacyRadius);
            }
            // UI
            document.getElementById('config-section')?.classList.add('hidden');
            document.getElementById('status-section')?.classList.remove('hidden');
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

    addUserMarkerToMap() {
        if (!window.map || !this.userPosition || !this.selectedAvatar) return;
        if (this.userMarker) this.userMarker.setMap(null);
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

    toggleDebug() {
        const debugInfo = document.getElementById('debug-info');
        const debugBtn = document.getElementById('toggle-debug');
        if (!debugInfo || !debugBtn) return;
        if (debugInfo.classList.contains('hidden')) {
            debugInfo.classList.remove('hidden');
            debugBtn.textContent = 'Ocultar Debug';
        } else {
            debugInfo.classList.add('hidden');
            debugBtn.textContent = 'Debug Info';
        }
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
        
        // Resetar UI para estado inicial
        document.getElementById('config-section')?.classList.remove('hidden');
        document.getElementById('status-section')?.classList.add('hidden');
        
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

    async updateServerLocation() {
        if (!this.userPosition || !this.isAuthenticated) return;
        try {
            await fetch('/location/update', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ 
                    latitude: this.userPosition.lat, 
                    longitude: this.userPosition.lng,
                    privacy_radius: this.privacyRadius 
                })
            });
        } catch (error) {
            console.error('Erro ao atualizar localização no servidor:', error);
        }
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
        if (!this.isConfigured) return;
        
        try {
            // Tentar primeiro a nova rota, depois fallback para a antiga
            let response;
            try {
                response = await fetch('/usuarios-online.json', { 
                    headers: { 'Accept': 'application/json' } 
                });
            } catch (error) {
                // Fallback para rota alternativa
                response = await fetch('/users/online', { 
                    headers: { 'Accept': 'application/json' } 
                });
            }
            
            if (!response.ok) throw new Error(`HTTP ${response.status}: Falha ao carregar usuários online`);
            
            const data = await response.json();
            
            // Suportar múltiplos formatos de resposta
            let users = [];
            if (Array.isArray(data)) {
                users = data;
            } else if (data.users && Array.isArray(data.users)) {
                users = data.users;
            } else if (data.success && Array.isArray(data.users)) {
                users = data.users;
            }
            
            this.onUsersUpdate(users);
            
            // Atualizar debug info
            const debugUsers = document.getElementById('debug-users');
            if (debugUsers) {
                debugUsers.textContent = `Usuários: ${users.length} online`;
            }
            
        } catch (error) {
            console.error('Erro ao carregar usuários online:', error);
            
            // Mostrar erro no debug se disponível
            const debugUsers = document.getElementById('debug-users');
            if (debugUsers) {
                debugUsers.textContent = `Erro: ${error.message}`;
            }
        }
    }

    startPeriodicUpdates() {
        if (this.updateInterval) return;
        this.updateInterval = setInterval(() => this.loadOnlineUsers(), 30000);
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
    if (!window.google || !window.google.maps) return;
    const map = new google.maps.Map(document.getElementById('map'), {
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
    if (!users || !users.length || !window.map) return;
    users.forEach(user => {
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
        const bounds = new google.maps.LatLngBounds();
        window.markers.forEach(m => bounds.extend(m.getPosition()));
        if (window.markers.length === 1) {
            window.map.setCenter(window.markers[0].getPosition());
            window.map.setZoom(10);
        } else {
            window.map.fitBounds(bounds);
            const listener = google.maps.event.addListener(window.map, 'idle', function() {
                if (window.map.getZoom() > 15) window.map.setZoom(15);
                google.maps.event.removeListener(listener);
            });
        }
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

// Inicialização principal
document.addEventListener('DOMContentLoaded', () => {
    const isAuthenticated = @json(auth()->check());
    window.locationManager = new LocationManager(isAuthenticated);

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
    };
});
</script>
@endsection

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'SUA_CHAVE_API_AQUI' ) }}&libraries=geometry&callback=initMapChatHome"></script>
@endsection

