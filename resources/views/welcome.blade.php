@extends('layouts.app')

@section('title', 'MapChat - Conecte-se no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
    <!-- Mapa principal -->
    <div id="map" class="absolute left-0 top-0 w-full h-full z-1"></div>
    
    <!-- Cronômetro Global (para todos) -->
    @guest
    <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-30 bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-lg shadow-lg border border-red-400">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-center">
                <div class="text-lg font-bold font-mono tracking-wider" id="global-countdown">10:00</div>
                <div class="text-xs opacity-90">Tempo para todos no mapa</div>
            </div>
            <div class="flex flex-col text-xs space-y-1">
                <a href="{{ route('login') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-2 py-1 rounded text-center transition-colors">Login</a>
                <a href="{{ route('register') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-2 py-1 rounded text-center transition-colors">Criar conta</a>
            </div>
        </div>
    </div>
    @endguest

    <!-- Painel de configuração (esquerda) -->
    <div class="absolute top-4 left-4 z-20 bg-white rounded-lg shadow-lg max-w-80">
        <div class="p-4 border-b bg-green-50">
            <h3 class="font-bold text-green-600 mb-1">🌍 Configure seu Perfil</h3>
        </div>
        <!-- Seção de Configuração -->
        <div id="config-section">
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
                               class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <button id="search-address" class="absolute right-2 top-2 text-gray-400 hover:text-green-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <input type="range" id="privacy-radius" min="500" max="5000000" step="500" value="50000" 
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider">
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>500m</span>
                    <span>5000km</span>
                </div>
                <div class="mt-2 p-2 bg-yellow-50 rounded text-xs text-yellow-700">
                    🔒 Sua posição será randomizada dentro deste raio
                </div>
            </div>

            <!-- Apply Button -->
            <div class="p-4">
                <button id="apply-config" class="w-full bg-green-500 text-white py-2.5 px-4 rounded-lg hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-semibold" disabled>
                    <span class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Aplicar e Entrar no Mapa</span>
                    </span>
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
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
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
    <div class="absolute top-4 right-4 z-20 bg-white rounded-lg shadow-lg p-4 w-64 max-h-96 overflow-y-auto">
        <h3 class="font-bold text-green-600 mb-3">👥 Online Agora (<span id="users-count">0</span>)</h3>
        <div id="users-list" class="space-y-2">
            <div class="text-gray-500 text-sm text-center py-4">
                Carregando usuários...
            </div>
        </div>
        
        <div class="mt-3 pt-3 border-t text-xs text-gray-500">
            🔄 Atualiza automaticamente a cada 30s
        </div>
    </div>
    
    <!-- Botão de toggle para painéis mobile -->
    <div class="md:hidden">
        <button id="toggle-panels" class="absolute top-4 left-4 z-30 bg-green-600 text-white p-2 rounded-full shadow-lg">
            ⚙️
        </button>
    </div>
</div>

<style>
/* Avatar button styles */
.avatar-btn {
    transition: all 0.2s ease;
}

.avatar-btn:hover {
    transform: scale(1.05);
}

.avatar-btn.border-green-500 {
    border-color: #10B981 !important;
    background-color: #F0FDF4 !important;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

/* Slider styles */
#privacy-radius::-webkit-slider-thumb {
    appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #10B981;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

#privacy-radius::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #10B981;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

#privacy-radius::-webkit-slider-track {
    background: #E5E7EB;
    height: 6px;
    border-radius: 3px;
}

#privacy-radius::-moz-range-track {
    background: #E5E7EB;
    height: 6px;
    border-radius: 3px;
    border: none;
}

/* Address input styles */
#address-input:focus {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Button hover effects */
.hover\\:bg-green-600:hover {
    background-color: #059669 !important;
}

.hover\\:bg-blue-600:hover {
    background-color: #2563EB !important;
}

.hover\\:bg-orange-600:hover {
    background-color: #EA580C !important;
}

/* Status indicators */
.status-complete .w-2.h-2 {
    background-color: #10B981 !important;
}

/* Modal overlay */
.modal-overlay {
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Countdown animation */
.text-red-600 {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Responsividade */
@media (max-width: 768px) {
    .absolute.top-4.left-4 {
        width: calc(100% - 32px);
        max-width: none;
        left: 16px;
        right: 16px;
    }
    
    .absolute.top-4.right-4 {
        position: fixed;
        top: auto;
        bottom: 16px;
        right: 16px;
        left: 16px;
        width: auto;
        max-height: 200px;
    }
    
    .grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
    }
    
    .avatar-btn img {
        width: 2rem;
        height: 2rem;
    }
}

/* Loading states */
.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #ccc;
    border-top-color: #10B981;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Hide scrollbars but keep functionality */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<!-- Scripts -->
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script>
// Incluir LocationManager aprimorado inline para evitar problemas de carregamento
/**
 * Sistema de localização aprimorado para MapChat
 * Inclui simulação de localização para desktops sem GPS
 */
class LocationManager {
    constructor() {
        this.userPosition = null;
        this.privacyRadius = 50000;
        this.selectedAvatar = null;
        this.addressInput = '';
        this.isAuthenticated = window.isAuthenticated || false;
        this.updateInterval = null;
        this.onlineUsers = [];
        this.isConfigured = false;
        this.geocoder = null;
        this.globalCountdown = null;
        this.globalTimeRemaining = 600; // 10 minutos em segundos
        this.isMobile = this.detectMobile();
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeBrazilCenter();
        this.detectLocationMethod();
        this.startGlobalCountdown();
    }
    
    detectMobile() {
        const userAgent = navigator.userAgent.toLowerCase();
        const mobileKeywords = ['mobile', 'android', 'iphone', 'ipad', 'tablet', 'phone'];
        return mobileKeywords.some(keyword => userAgent.includes(keyword)) || 
               'ontouchstart' in window || 
               navigator.maxTouchPoints > 0;
    }
    
    detectLocationMethod() {
        const desktopLocation = document.getElementById('desktop-location');
        const mobileLocation = document.getElementById('mobile-location');
        
        if (this.isMobile && navigator.geolocation) {
            // Mobile: GPS automático
            console.log('Dispositivo móvel detectado - usando GPS');
            desktopLocation.classList.add('hidden');
            mobileLocation.classList.remove('hidden');
            this.requestGPSLocation();
        } else {
            // Desktop: Campo de endereço
            console.log('Desktop detectado - usando campo de endereço');
            mobileLocation.classList.add('hidden');
            desktopLocation.classList.remove('hidden');
        }
    }
    
    requestGPSLocation() {
        const gpsStatus = document.getElementById('gps-status');
        const gpsSpinner = document.getElementById('gps-spinner');
        
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Atualizar UI
                gpsSpinner.classList.add('hidden');
                gpsStatus.textContent = '✅ Localização obtida via GPS';
                gpsStatus.classList.add('text-green-600');
                
                // Marcar como completa
                this.updateLocationStatus(true);
                this.checkConfigComplete();
                
                console.log('GPS obtido:', this.userPosition);
            },
            (error) => {
                console.warn('Erro GPS:', error.message);
                gpsSpinner.classList.add('hidden');
                gpsStatus.textContent = '❌ GPS não disponível - usando campo de endereço';
                gpsStatus.classList.add('text-orange-600');
                
                // Fallback para desktop
                this.switchToDesktopMode();
            },
            options
        );
    }
    
    switchToDesktopMode() {
        const desktopLocation = document.getElementById('desktop-location');
        const mobileLocation = document.getElementById('mobile-location');
        
        mobileLocation.classList.add('hidden');
        desktopLocation.classList.remove('hidden');
        this.isMobile = false;
    }
    
    initializeBrazilCenter() {
        // Para desktop, inicializar em posição central do Brasil
        this.mapCenter = {
            lat: -14.2350, 
            lng: -51.9253
        };
        console.log('Centro do mapa no Brasil:', this.mapCenter);
    }
    
    startGlobalCountdown() {
        if (this.isAuthenticated) return; // Só para visitantes
        
        this.globalCountdown = setInterval(() => {
            this.globalTimeRemaining--;
            const minutes = Math.floor(this.globalTimeRemaining / 60);
            const seconds = this.globalTimeRemaining % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            const countdownEl = document.getElementById('global-countdown');
            if (countdownEl) {
                countdownEl.textContent = display;
                
                // Mudar cor quando restam 2 minutos
                const container = countdownEl.closest('.bg-gradient-to-r');
                if (this.globalTimeRemaining <= 120) {
                    container.classList.remove('from-red-500', 'to-red-600');
                    container.classList.add('from-red-700', 'to-red-800', 'animate-pulse');
                }
                
                if (this.globalTimeRemaining <= 0) {
                    this.handleGlobalTimeExpired();
                }
            }
        }, 1000);
    }
    
    handleGlobalTimeExpired() {
        clearInterval(this.globalCountdown);
        
        // Reset global de todos os usuários
        this.clearAllUserPositions();
        
        // Modal para visitantes
        const modal = `
            <div class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl p-8 max-w-md mx-4 text-center shadow-2xl">
                    <div class="text-6xl mb-4">⏰</div>
                    <h3 class="text-2xl font-bold text-red-600 mb-4">Tempo Esgotado!</h3>
                    <p class="text-gray-700 mb-6">O cronômetro global zerou e todas as posições foram resetadas.</p>
                    <p class="text-sm text-gray-600 mb-6">Faça login para continuar usando o mapa sem limitações de tempo!</p>
                    <div class="flex space-x-3">
                        <a href="/login" class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg text-center hover:bg-green-700 transition-colors font-semibold">
                            Fazer Login
                        </a>
                        <a href="/register" class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg text-center hover:bg-blue-700 transition-colors font-semibold">
                            Criar Conta
                        </a>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modal);
    }
    
    clearAllUserPositions() {
        // Limpar marcadores do mapa
        if (window.markers) {
            window.markers.forEach(marker => marker.setMap(null));
            window.markers = [];
        }
        
        // Reset configuração local
        this.isConfigured = false;
        this.userPosition = null;
        this.selectedAvatar = null;
        
        // Mostrar seção de configuração novamente
        document.getElementById('config-section').classList.remove('hidden');
        document.getElementById('status-section')?.classList.add('hidden');
        
        console.log('Posições globais resetadas');
    }
    
    setupEventListeners() {
        // Avatar selection
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectAvatar(btn.dataset.avatar);
            });
        });
        
        // Address input (desktop)
        const addressInput = document.getElementById('address-input');
        const searchBtn = document.getElementById('search-address');
        
        if (addressInput) {
            addressInput.addEventListener('input', (e) => {
                this.addressInput = e.target.value;
                this.debouncedAddressSearch();
            });
            
            addressInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.searchAddressImmediate();
                }
            });
        }
        
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.searchAddressImmediate();
            });
        }
        
        // Privacy radius
        const radiusSlider = document.getElementById('privacy-radius');
        radiusSlider.addEventListener('input', (e) => {
            this.updateRadiusDisplay(parseInt(e.target.value));
        });
        
        // Apply configuration
        const applyBtn = document.getElementById('apply-config');
        applyBtn.addEventListener('click', () => {
            this.applyConfiguration();
        });
        
        // Debug toggle
        document.getElementById('toggle-debug')?.addEventListener('click', () => {
            this.toggleDebug();
        });
        
        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (this.globalCountdown) clearInterval(this.globalCountdown);
            if (this.updateInterval) clearInterval(this.updateInterval);
        });
    }
    
    // Debounced address search (usar o código sugerido)
    debouncedAddressSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.searchAddressByGeocoder();
        }, 500);
    }
    
    searchAddressImmediate() {
        clearTimeout(this.searchTimeout);
        this.searchAddressByGeocoder();
    }
    
    searchAddressByGeocoder() {
        const endereco = document.getElementById('address-input').value;
        const feedback = document.getElementById('address-feedback');
        
        if (!endereco) {
            feedback.textContent = 'Digite uma cidade, bairro ou CEP';
            feedback.className = 'text-xs text-gray-500 mt-2';
            return;
        }
        
        if (!window.google || !this.geocoder) {
            if (window.google) {
                this.geocoder = new google.maps.Geocoder();
            } else {
                feedback.textContent = 'Aguarde o carregamento do Google Maps...';
                feedback.className = 'text-xs text-orange-600 mt-2';
                return;
            }
        }
        
        feedback.textContent = 'Buscando endereço...';
        feedback.className = 'text-xs text-blue-600 mt-2';
        
        this.geocoder.geocode({ address: endereco + ', Brasil' }, (results, status) => {
            if (status === 'OK') {
                const location = results[0].geometry.location;
                this.userPosition = {
                    lat: location.lat(),
                    lng: location.lng()
                };
                
                feedback.textContent = 'Endereço encontrado: ' + results[0].formatted_address;
                feedback.className = 'text-xs text-green-600 mt-2';
                
                // Atualizar status
                this.updateLocationStatus(true);
                this.checkConfigComplete();
                
                console.log('Endereço encontrado:', results[0].formatted_address, this.userPosition);
            } else {
                feedback.textContent = 'Local não encontrado. Tente: "São Paulo, SP" ou "01234-567"';
                feedback.className = 'text-xs text-red-600 mt-2';
            }
        });
    }
    
    
    selectAvatar(avatarType) {
        this.selectedAvatar = avatarType;
        
        // Update UI
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.classList.remove('border-green-500', 'bg-green-50');
            btn.classList.add('border-gray-200');
        });
        
        const selectedBtn = document.querySelector(`[data-avatar="${avatarType}"]`);
        selectedBtn.classList.remove('border-gray-200');
        selectedBtn.classList.add('border-green-500', 'bg-green-50');
        
        // Update status
        const avatarStatus = document.getElementById('avatar-status');
        const avatarCheck = document.getElementById('avatar-check');
        avatarStatus.className = 'w-2 h-2 bg-green-500 rounded-full';
        avatarCheck.className = 'flex items-center text-green-600';
        avatarCheck.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>Avatar';
        
        this.checkConfigComplete();
        console.log('Avatar selecionado:', avatarType);
    }
    
    updateLocationStatus(isComplete) {
        const locationDot = document.getElementById('location-status-dot');
        const locationCheck = document.getElementById('location-check');
        
        if (isComplete) {
            locationDot.className = 'w-2 h-2 bg-green-500 rounded-full';
            locationCheck.className = 'flex items-center text-green-600';
            locationCheck.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>Localização';
        } else {
            locationDot.className = 'w-2 h-2 bg-gray-300 rounded-full';
            locationCheck.className = 'flex items-center text-gray-400';
            locationCheck.innerHTML = '<span class="w-2 h-2 bg-gray-300 rounded-full mr-1"></span>Localização';
        }
        
        this.checkConfigComplete();
    }
    
    updateRadiusDisplay(radiusMeters) {
        this.privacyRadius = radiusMeters;
        const display = document.getElementById('radius-value');
        if (radiusMeters < 1000) {
            display.textContent = radiusMeters + ' m';
        } else {
            display.textContent = Math.round(radiusMeters / 1000) + ' km';
        }
    }
    
    checkConfigComplete() {
        const applyBtn = document.getElementById('apply-config');
        const hasAvatar = this.selectedAvatar !== null;
        const hasLocation = this.userPosition !== null;
        
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
    
    async applyConfiguration() {
        if (!this.selectedAvatar || !this.userPosition) {
            alert('Selecione um avatar e defina sua localização primeiro.');
            return;
        }
        
        try {
            this.isConfigured = true;
            
            // Update server if authenticated
            if (this.isAuthenticated) {
                await this.updateServerLocation();
                await this.updateAvatar(this.selectedAvatar);
                await this.updatePrivacyRadius(this.privacyRadius);
            }
            
            // Switch to status section
            document.getElementById('config-section').classList.add('hidden');
            document.getElementById('status-section')?.classList.remove('hidden');
            
            // Start loading users and updates
            this.loadOnlineUsers();
            this.startPeriodicUpdates();
            
            // Trigger callbacks
            this.onConfigurationApplied();
            
            console.log('Configuração aplicada:', {
                avatar: this.selectedAvatar,
                position: this.userPosition,
                radius: this.privacyRadius,
                method: this.isMobile ? 'GPS' : 'Address'
            });
            
        } catch (error) {
            console.error('Erro ao aplicar configuração:', error);
            alert('Erro ao aplicar configuração. Tente novamente.');
        }
    }
    
    toggleDebug() {
        const debugInfo = document.getElementById('debug-info');
        const debugBtn = document.getElementById('toggle-debug');
        
        if (debugInfo && debugInfo.classList.contains('hidden')) {
            debugInfo.classList.remove('hidden');
            debugBtn.textContent = 'Ocultar Debug';
        } else if (debugInfo) {
            debugInfo.classList.add('hidden');
            debugBtn.textContent = 'Debug Info';
        }
    }
    
    async updateServerLocation() {
        if (!this.userPosition || !this.isAuthenticated) return;
        try {
            const response = await fetch('/location/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: this.userPosition.lat,
                    longitude: this.userPosition.lng,
                    privacy_radius: this.privacyRadius
                })
            });
            const data = await response.json();
            if (data.success) {
                console.log('Localização atualizada no servidor');
            }
        } catch (error) {
            console.error('Erro ao atualizar localização:', error);
        }
    }
    
    async updateAvatar(avatarType) {
        if (!this.isAuthenticated) return;
        try {
            const response = await fetch('/location/avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ avatar_type: avatarType })
            });
            const data = await response.json();
            if (data.success) {
                console.log('Avatar atualizado no servidor');
            }
        } catch (error) {
            console.error('Erro ao atualizar avatar:', error);
        }
    }
    
    async updatePrivacyRadius(radiusMeters) {
        if (!this.isAuthenticated) return;
        try {
            const response = await fetch('/location/privacy-radius', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ radius: radiusMeters })
            });
            const data = await response.json();
            if (data.success && data.new_location) {
                this.userPosition = {
                    lat: data.new_location.latitude,
                    lng: data.new_location.longitude
                };
                console.log('Raio de privacidade atualizado');
            }
        } catch (error) {
            console.error('Erro ao atualizar raio:', error);
        }
    }
    
    async loadOnlineUsers() {
        if (!this.isConfigured) return;
        
        try {
            const response = await fetch('/usuarios-online.json');
            const data = await response.json();
            if (data.success) {
                this.onlineUsers = data.users;
                console.log(`Carregados ${data.users.length} usuários online`);
                this.onUsersUpdate(this.onlineUsers);
                
                // Update debug
                const debugUsers = document.getElementById('debug-users');
                if (debugUsers) {
                    debugUsers.textContent = `Usuários: ${data.users.length} online`;
                }
            }
        } catch (error) {
            console.error('Erro ao carregar usuários:', error);
        }
    }
    
    startPeriodicUpdates() {
        if (this.updateInterval) return;
        this.updateInterval = setInterval(() => {
            this.loadOnlineUsers();
        }, 30000);
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
    
    // Callbacks
    onConfigurationApplied() { console.log('Configuração aplicada'); }
    onUsersUpdate(users) { console.log('Usuários atualizados:', users.length); }
    
    destroy() {
        if (this.globalCountdown) clearInterval(this.globalCountdown);
        if (this.updateInterval) clearInterval(this.updateInterval);
        if (this.searchTimeout) clearTimeout(this.searchTimeout);
    }
}

// Initialize LocationManager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = @json(auth()->check());
    
    // Initialize LocationManager
    window.locationManager = new LocationManager(isAuthenticated);
    
    // Set up callbacks for integration with map
    locationManager.onConfigurationApplied = function() {
        console.log('LocationManager configuration applied');
        
        // Update location display in status section
        if (this.userPosition && document.getElementById('current-location')) {
            document.getElementById('current-location').textContent = 
                `${this.userPosition.lat.toFixed(4)}, ${this.userPosition.lng.toFixed(4)}`;
        }
        
        // Center map on user position if available
        if (window.map && this.userPosition) {
            map.setCenter(this.userPosition);
            map.setZoom(12);
        }
        
        // Update debug info
        const debugLocation = document.getElementById('debug-location');
        if (debugLocation && this.userPosition) {
            debugLocation.textContent = `Localização: ${this.userPosition.lat.toFixed(4)}, ${this.userPosition.lng.toFixed(4)} (${this.isMobile ? 'GPS' : 'Endereço'})`;
        }
    };
    
    locationManager.onUsersUpdate = function(users) {
        console.log('Users updated in LocationManager:', users.length);
        // This will be used by the map system
        if (window.updateUsersDisplay) {
            updateUsersDisplay(users);
        }
        if (window.updateMapMarkers) {
            updateMapMarkers(users);
        }
    };
    
    // Initialize countdown timer immediately
    locationManager.initializeCountdown();
    
    // Auto-detect mobile/desktop and set up UI
    const isMobile = locationManager.detectMobile();
    
    console.log('LocationManager initialized:', {
        authenticated: isAuthenticated,
        mobile: isMobile,
        countdown: '10 minutes global timer'
    });
    
    // Set up event listeners for UI elements
    
    // Address input for desktop
    const addressInput = document.getElementById('address-input');
    if (addressInput && !isMobile) {
        addressInput.addEventListener('input', function() {
            locationManager.addressInput = this.value;
            locationManager.searchAddressByGeocoder();
        });
        
        addressInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                locationManager.searchAddressByGeocoder();
            }
        });
    }
    
    // Mobile GPS button
    const gpsBtn = document.getElementById('request-gps');
    if (gpsBtn && isMobile) {
        gpsBtn.addEventListener('click', function() {
            locationManager.requestGPSLocation();
        });
    }
    
    // Avatar selection buttons
    document.querySelectorAll('.avatar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const avatarType = this.getAttribute('data-avatar');
            locationManager.selectAvatar(avatarType);
        });
    });
    
    // Privacy radius slider
    const radiusSlider = document.getElementById('privacy-slider');
    if (radiusSlider) {
        radiusSlider.addEventListener('input', function() {
            locationManager.updateRadiusDisplay(parseInt(this.value));
        });
    }
    
    // Apply configuration button
    const applyBtn = document.getElementById('apply-config');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            locationManager.applyConfiguration();
        });
    }
    
    // Debug toggle button
    const debugBtn = document.getElementById('toggle-debug');
    if (debugBtn) {
        debugBtn.addEventListener('click', function() {
            locationManager.toggleDebug();
        });
    }
    
    // Configuration reset button (if exists)
    const resetBtn = document.getElementById('reset-config');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Resetar configuração e voltar ao início?')) {
                location.reload();
            }
        });
    }
});
</script>

<script>
// Função para inicializar o mapa
window.initMapChatHome = function() {
    if (!window.google || !window.google.maps) return;
    
    const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -14.2350, lng: -51.9253 }, // Centro do Brasil
        zoom: 4,
        streetViewControl: false,
        mapTypeControl: true,
        fullscreenControl: true,
        zoomControl: true,
        gestureHandling: 'greedy',
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });
    
    // Store map globally for LocationManager access
    window.map = map;
    
    console.log('Google Maps initialized');
};

// Global variables for map functionality
let markers = [], markerCluster;

// Function to update users display in sidebar
function updateUsersDisplay(users) {
    const usersList = document.getElementById('users-list');
    const usersCount = document.getElementById('users-count');
    
    if (!usersList || !usersCount) return;
    
    usersCount.textContent = users.length;
    
    if (users.length === 0) {
        usersList.innerHTML = '<div class="text-gray-500 text-sm text-center py-4">Nenhum usuário online</div>';
        return;
    }
    
    const html = users.map(user => {
        const avatar = locationManager.getAvatarFilename(user.avatar_type);
        const lastSeen = new Date(user.last_seen);
        const timeAgo = getTimeAgo(lastSeen);
        
        return `
            <div class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer" data-user-id="${user.id}" data-lat="${user.latitude}" data-lng="${user.longitude}">
                <img src="/images/${avatar}" alt="${user.name}" class="w-8 h-8 rounded-full border border-gray-300">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">${user.name}</div>
                    <div class="text-xs text-gray-500">${timeAgo}</div>
                </div>
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            </div>
        `;
    }).join('');
    
    usersList.innerHTML = html;
    
    // Adicionar eventos de clique
    usersList.querySelectorAll('[data-user-id]').forEach(userEl => {
        userEl.addEventListener('click', function() {
            const lat = parseFloat(this.getAttribute('data-lat'));
            const lng = parseFloat(this.getAttribute('data-lng'));
            
            if (window.map) {
                window.map.setCenter({ lat, lng });
                window.map.setZoom(15);
            }
        });
    });
}

// Function to update map markers
function updateMapMarkers(users) {
    console.log('Updating map markers:', users.length, 'users');
    
    // Clear existing markers
    if (markerCluster) {
        markerCluster.clearMarkers();
    }
    markers.forEach(marker => marker.setMap(null));
    markers = [];
    
    if (!users || users.length === 0 || !window.map) {
        console.warn('No users to display on map');
        return;
    }
    
    // Add markers for online users
    users.forEach((user, index) => {
        console.log(`Creating marker ${index + 1}:`, user);
        
        const avatar = locationManager.getAvatarFilename(user.avatar_type);
        
        const marker = new google.maps.Marker({
            position: { lat: user.latitude, lng: user.longitude },
            title: user.name,
            icon: {
                url: `/images/${avatar}`,
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(20, 20)
            }
        });
        
        // Add info window
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <img src="/images/${avatar}" alt="${user.name}" class="w-6 h-6 rounded-full">
                        <strong>${user.name}</strong>
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        Online agora • Avatar: ${user.avatar_type}
                    </div>
                    ${window.isAuthenticated ? '<button class="mt-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Conversar</button>' : '<div class="mt-2 text-xs text-gray-500">Configure seu perfil primeiro</div>'}
                </div>
            `
        });
        
        marker.addListener('click', () => {
            console.log('Clicked marker for:', user.name);
            infoWindow.open(window.map, marker);
        });
        
        markers.push(marker);
    });
    
    console.log(`${markers.length} markers created`);
    
    // Apply clustering if available and we have markers
    if (window.markerClusterer && markers.length > 0) {
        try {
            markerCluster = new markerClusterer.MarkerClusterer({ 
                map: window.map, 
                markers,
                gridSize: 60,
                maxZoom: 15
            });
            console.log('Marker clustering applied');
        } catch (error) {
            console.error('Error creating cluster:', error);
            // Fallback: add markers directly to map
            markers.forEach(marker => marker.setMap(window.map));
        }
    } else if (markers.length > 0) {
        // If no clustering available, add markers directly
        markers.forEach(marker => marker.setMap(window.map));
        console.log('Markers added directly to map');
    }
    
    // Adjust map view to show all markers
    if (markers.length > 0 && window.map) {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.getPosition()));
        
        if (markers.length === 1) {
            // For single marker, center and set zoom
            window.map.setCenter(markers[0].getPosition());
            window.map.setZoom(10);
        } else {
            // For multiple markers, fit bounds
            window.map.fitBounds(bounds);
            // Limit max zoom to avoid too much zoom
            const listener = google.maps.event.addListener(window.map, "idle", function() {
                if (window.map.getZoom() > 15) window.map.setZoom(15);
                google.maps.event.removeListener(listener);
            });
        }
        
        console.log('Map view adjusted to show all markers');
    }
}

// Utility function for time ago display
function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'agora';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' h';
    return Math.floor(diffInSeconds / 86400) + ' d';
}

// Cleanup when page is closed
window.addEventListener('beforeunload', function() {
    if (window.locationManager) {
        locationManager.destroy();
    }
});
</script>
@endsection

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'SUA_CHAVE_API_AQUI' ) }}&libraries=geometry&callback=initMapChatHome"></script>
@endsection
