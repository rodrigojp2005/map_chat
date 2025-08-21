@extends('layouts.app')

@section('title', 'MapChat - Conecte-se no mapa!')

@section('content')
<div class="relative w-full" style="height: calc(100vh - 120px);">
    <!-- Mapa principal -->
    <div id="map" class="absolute left-0 top-0 w-full h-full z-1"></div>
    
    <!-- Painel de controle superior esquerdo -->
    <div class="absolute top-4 left-4 z-20 bg-white rounded-lg shadow-lg p-4 max-w-80">
        <h3 class="font-bold text-green-600 mb-3">🌍 Seu Perfil no Mapa</h3>
        
        <!-- Seleção de Avatar -->
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Escolha seu avatar:</label>
            <div class="grid grid-cols-3 gap-2">
                <button class="avatar-btn active" data-avatar="default" title="Padrão">
                    <img src="{{ asset('images/default.gif') }}" alt="Padrão" class="w-10 h-10 rounded-full border-2 border-green-500 hover:border-green-600">
                </button>
                <button class="avatar-btn" data-avatar="man" title="Homem">
                    <img src="{{ asset('images/mario.gif') }}" alt="Homem" class="w-10 h-10 rounded-full border-2 border-gray-300 hover:border-green-600">
                </button>
                <button class="avatar-btn" data-avatar="woman" title="Mulher">
                    <img src="{{ asset('images/girl.gif') }}" alt="Mulher" class="w-10 h-10 rounded-full border-2 border-gray-300 hover:border-green-600">
                </button>
                <button class="avatar-btn" data-avatar="pet" title="Pet">
                    <img src="{{ asset('images/pets.gif') }}" alt="Pet" class="w-10 h-10 rounded-full border-2 border-gray-300 hover:border-green-600">
                </button>
                <button class="avatar-btn" data-avatar="geek" title="Geek">
                    <img src="{{ asset('images/geek.gif') }}" alt="Geek" class="w-10 h-10 rounded-full border-2 border-gray-300 hover:border-green-600">
                </button>
                <button class="avatar-btn" data-avatar="sport" title="Esporte">
                    <img src="{{ asset('images/sport.gif') }}" alt="Esporte" class="w-10 h-10 rounded-full border-2 border-gray-300 hover:border-green-600">
                </button>
            </div>
        </div>
        
        <!-- Controle de raio de privacidade -->
        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Raio de Privacidade: <span id="radius-display">50 km</span>
            </label>
            <input type="range" id="privacy-radius" min="500" max="5000000" value="50000" step="500" 
                   class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>500m</span>
                <span>5000km</span>
            </div>
        </div>
        
        <!-- Status de localização -->
        <div class="text-sm text-gray-600">
            <div id="location-status" class="flex items-center">
                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                <span>Obtendo localização...</span>
            </div>
        </div>
        
        <!-- Debug info (development only) -->
        <div id="debug-info" class="mt-2 text-xs text-gray-500 p-2 bg-gray-50 rounded" style="display: none;">
            <div><strong>Debug Info:</strong></div>
            <div id="debug-location">Localização: Aguardando...</div>
            <div id="debug-users">Usuários: Carregando...</div>
            <div id="debug-auth">Auth: <span id="auth-status">{{ auth()->check() ? 'Logado' : 'Visitante' }}</span></div>
        </div>
        
        <button id="toggle-debug" class="mt-2 text-xs text-blue-600 underline">Mostrar Debug</button>
        
        @guest
        <div class="mt-3 p-2 bg-blue-50 rounded text-sm text-blue-700">
            💡 <a href="{{ route('login') }}" class="underline">Faça login</a> para aparecer no mapa e conversar com outros usuários!
        </div>
        @endguest
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
.avatar-btn.active img {
    border-color: #10B981 !important;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

/* Responsividade para mobile */
@media (max-width: 768px) {
    .absolute.top-4.left-4 {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .absolute.top-4.right-4 {
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .panels-visible .absolute.top-4.left-4,
    .panels-visible .absolute.top-4.right-4 {
        transform: translateX(0);
    }
}

/* Estilo do slider */
#privacy-radius::-webkit-slider-thumb {
    appearance: none;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #10B981;
    cursor: pointer;
}

#privacy-radius::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #10B981;
    cursor: pointer;
    border: none;
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
        this.selectedAvatar = 'default';
        this.isAuthenticated = window.isAuthenticated || false;
        this.updateInterval = null;
        this.onlineUsers = [];
        this.isSimulated = false;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.requestLocationWithFallback();
        this.loadOnlineUsers();
        this.startPeriodicUpdates();
    }
    
    setupEventListeners() {
        window.addEventListener('beforeunload', () => {
            if (this.isAuthenticated) {
                this.setOffline();
            }
        });
        
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseUpdates();
            } else {
                this.resumeUpdates();
            }
        });
    }
    
    requestLocationWithFallback() {
        if (!navigator.geolocation) {
            console.warn('Geolocalização não suportada');
            this.simulateLocation();
            return;
        }
        
        const options = {
            enableHighAccuracy: false,
            timeout: 5000,
            maximumAge: 300000
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                this.isSimulated = false;
                console.log('Localização real obtida:', this.userPosition);
                if (this.isAuthenticated) this.updateServerLocation();
                this.onLocationUpdate(this.userPosition);
            },
            (error) => {
                console.warn('Erro GPS, usando simulação:', error.message);
                this.simulateLocation();
            },
            options
        );
        
        setTimeout(() => {
            if (!this.userPosition) {
                console.log('Timeout GPS, usando simulação...');
                this.simulateLocation();
            }
        }, 3000);
    }
    
    simulateLocation() {
        const brazilianCities = [
            { lat: -23.5505, lng: -46.6333, name: "São Paulo, SP" },
            { lat: -22.9068, lng: -43.1729, name: "Rio de Janeiro, RJ" },
            { lat: -15.7942, lng: -47.8822, name: "Brasília, DF" },
            { lat: -25.4244, lng: -49.2654, name: "Curitiba, PR" },
            { lat: -30.0346, lng: -51.2177, name: "Porto Alegre, RS" },
            { lat: -8.0476, lng: -34.8770, name: "Recife, PE" },
            { lat: -12.9714, lng: -38.5014, name: "Salvador, BA" },
            { lat: -19.9167, lng: -43.9345, name: "Belo Horizonte, MG" }
        ];
        
        const randomCity = brazilianCities[Math.floor(Math.random() * brazilianCities.length)];
        const variation = 0.1;
        const lat = randomCity.lat + (Math.random() - 0.5) * variation;
        const lng = randomCity.lng + (Math.random() - 0.5) * variation;
        
        this.userPosition = { lat, lng };
        this.isSimulated = true;
        console.log(`Localização simulada em ${randomCity.name}:`, this.userPosition);
        this.onLocationUpdate(this.userPosition, randomCity.name);
        if (this.isAuthenticated) this.updateServerLocation();
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
                this.loadOnlineUsers();
            }
        } catch (error) {
            console.error('Erro ao atualizar localização:', error);
        }
    }
    
    async updateAvatar(avatarType) {
        if (!this.isAuthenticated) {
            this.selectedAvatar = avatarType;
            this.onAvatarUpdate(avatarType);
            return;
        }
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
                this.selectedAvatar = avatarType;
                this.onAvatarUpdate(avatarType);
                this.loadOnlineUsers();
            }
        } catch (error) {
            console.error('Erro ao atualizar avatar:', error);
        }
    }
    
    async updatePrivacyRadius(radiusMeters) {
        this.privacyRadius = radiusMeters;
        if (!this.isAuthenticated || !this.userPosition) {
            this.onPrivacyRadiusUpdate(radiusMeters);
            return;
        }
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
            if (data.success) {
                this.onPrivacyRadiusUpdate(radiusMeters, data.new_location);
                this.loadOnlineUsers();
            }
        } catch (error) {
            console.error('Erro ao atualizar raio:', error);
        }
    }
    
    async setOffline() {
        if (!this.isAuthenticated) return;
        try {
            await fetch('/location/offline', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
        } catch (error) {
            console.error('Erro offline:', error);
        }
    }
    
    async loadOnlineUsers() {
        try {
            const response = await fetch('/usuarios-online.json');
            const data = await response.json();
            if (data.success) {
                this.onlineUsers = data.users;
                console.log(`Carregados ${data.users.length} usuários online`);
                this.onUsersUpdate(this.onlineUsers);
            } else {
                console.warn('Falha ao carregar usuários:', data);
            }
        } catch (error) {
            console.error('Erro ao carregar usuários:', error);
        }
    }
    
    startPeriodicUpdates() {
        this.updateInterval = setInterval(() => {
            this.loadOnlineUsers();
        }, 30000);
    }
    
    pauseUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
    
    resumeUpdates() {
        if (!this.updateInterval) {
            this.startPeriodicUpdates();
            this.loadOnlineUsers();
        }
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
    onLocationUpdate(position, cityName = null) { console.log('Localização:', position); }
    onAvatarUpdate(avatarType) { console.log('Avatar:', avatarType); }
    onPrivacyRadiusUpdate(radius, newLocation = null) { console.log('Raio:', radius); }
    onUsersUpdate(users) { console.log('Usuários:', users); }
    onLocationError(error, message) { console.error('Erro:', message, error); }
    
    destroy() {
        if (this.updateInterval) clearInterval(this.updateInterval);
        if (this.isAuthenticated) this.setOffline();
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.isAuthenticated = @json(auth()->check());
    
    let map, markers = [], markerCluster;
    let locationManager;
    let userMarker = null;
    
    // Inicializar gerenciador de localização
    locationManager = new LocationManager();
    
    // Sobrescrever callbacks do LocationManager
    locationManager.onLocationUpdate = function(position, cityName) {
        const statusMsg = cityName ? `Localização simulada em ${cityName}` : 'Localização obtida';
        const statusType = cityName ? 'warning' : 'success';
        updateLocationStatus(statusType, statusMsg);
        
        // Debug info
        document.getElementById('debug-location').textContent = 
            `Localização: ${position.lat.toFixed(4)}, ${position.lng.toFixed(4)} ${cityName ? '(Simulada)' : '(Real)'}`;
        
        // Centralizar mapa na localização aproximada (não exata)
        if (map) {
            map.setCenter({ lat: position.lat, lng: position.lng });
            map.setZoom(cityName ? 8 : 10); // Zoom menor para cidades simuladas
        }
    };
    
    locationManager.onUsersUpdate = function(users) {
        updateUsersDisplay(users);
        updateMapMarkers(users);
        
        // Debug info
        document.getElementById('debug-users').textContent = `Usuários: ${users.length} online`;
    };
    
    locationManager.onAvatarUpdate = function(avatarType) {
        updateAvatarSelection(avatarType);
    };
    
    locationManager.onPrivacyRadiusUpdate = function(radius, newLocation) {
        updateRadiusDisplay(radius);
        if (newLocation && userMarker) {
            userMarker.setPosition({ lat: newLocation.latitude, lng: newLocation.longitude });
        }
    };
    
    // Configurar eventos dos controles
    setupControlEvents();
    
    // Função para inicializar o mapa
    window.initMapChatHome = function() {
        if (!window.google || !window.google.maps) return;
        
        map = new google.maps.Map(document.getElementById('map'), {
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
        
        // Carregar dados iniciais SEMPRE, independente da localização
        setTimeout(() => {
            console.log('Forçando carregamento de usuários online...');
            locationManager.loadOnlineUsers();
        }, 1000);
        
        // Carregar novamente após 3 segundos para garantir
        setTimeout(() => {
            locationManager.loadOnlineUsers();
        }, 3000);
    };
    
    function setupControlEvents() {
        // Seleção de avatar
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const avatarType = this.getAttribute('data-avatar');
                locationManager.updateAvatar(avatarType);
            });
        });
        
        // Controle de raio de privacidade
        const radiusSlider = document.getElementById('privacy-radius');
        const radiusDisplay = document.getElementById('radius-display');
        
        radiusSlider.addEventListener('input', function() {
            const radius = parseInt(this.value);
            updateRadiusDisplay(radius);
        });
        
        radiusSlider.addEventListener('change', function() {
            const radius = parseInt(this.value);
            locationManager.updatePrivacyRadius(radius);
        });
        
        // Toggle de painéis em mobile
        const toggleBtn = document.getElementById('toggle-panels');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                document.body.classList.toggle('panels-visible');
            });
        }
        
        // Debug toggle
        const debugBtn = document.getElementById('toggle-debug');
        const debugInfo = document.getElementById('debug-info');
        debugBtn.addEventListener('click', function() {
            if (debugInfo.style.display === 'none') {
                debugInfo.style.display = 'block';
                debugBtn.textContent = 'Ocultar Debug';
            } else {
                debugInfo.style.display = 'none';
                debugBtn.textContent = 'Mostrar Debug';
            }
        });
    }
    
    function updateLocationStatus(status, message) {
        const statusEl = document.getElementById('location-status');
        const dot = statusEl.querySelector('div');
        const text = statusEl.querySelector('span');
        
        if (status === 'success') {
            dot.className = 'w-2 h-2 bg-green-500 rounded-full mr-2';
            text.textContent = message;
        } else if (status === 'error') {
            dot.className = 'w-2 h-2 bg-red-500 rounded-full mr-2';
            text.textContent = message;
        } else if (status === 'warning') {
            dot.className = 'w-2 h-2 bg-yellow-500 rounded-full mr-2';
            text.textContent = message;
        } else {
            dot.className = 'w-2 h-2 bg-yellow-500 rounded-full mr-2';
            text.textContent = message;
        }
    }
    
    function updateRadiusDisplay(radiusMeters) {
        const display = document.getElementById('radius-display');
        if (radiusMeters < 1000) {
            display.textContent = radiusMeters + ' m';
        } else {
            display.textContent = Math.round(radiusMeters / 1000) + ' km';
        }
    }
    
    function updateAvatarSelection(avatarType) {
        document.querySelectorAll('.avatar-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        document.querySelector(`[data-avatar="${avatarType}"]`)?.classList.add('active');
    }
    
    function updateUsersDisplay(users) {
        const usersList = document.getElementById('users-list');
        const usersCount = document.getElementById('users-count');
        
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
                
                if (map) {
                    map.setCenter({ lat, lng });
                    map.setZoom(15);
                }
            });
        });
    }
    
    function updateMapMarkers(users) {
        console.log('Atualizando marcadores no mapa:', users.length, 'usuários');
        
        // Limpar marcadores existentes
        if (markerCluster) {
            markerCluster.clearMarkers();
        }
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        
        // Verificar se temos usuários
        if (!users || users.length === 0) {
            console.warn('Nenhum usuário online para exibir no mapa');
            return;
        }
        
        // Adicionar marcadores para usuários online
        users.forEach((user, index) => {
            console.log(`Criando marcador ${index + 1}:`, user);
            
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
            
            // Adicionar info window
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
                        ${window.isAuthenticated ? '<button class="mt-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Conversar</button>' : '<div class="mt-2 text-xs text-gray-500">Faça login para conversar</div>'}
                    </div>
                `
            });
            
            marker.addListener('click', () => {
                console.log('Clicou no marcador de:', user.name);
                infoWindow.open(map, marker);
            });
            
            markers.push(marker);
        });
        
        console.log(`${markers.length} marcadores criados`);
        
        // Aplicar cluster aos marcadores se houver muitos
        if (window.markerClusterer && markers.length > 0) {
            try {
                markerCluster = new markerClusterer.MarkerClusterer({ 
                    map, 
                    markers,
                    gridSize: 60,
                    maxZoom: 15
                });
                console.log('Cluster aplicado aos marcadores');
            } catch (error) {
                console.error('Erro ao criar cluster:', error);
                // Fallback: adicionar marcadores diretamente ao mapa
                markers.forEach(marker => marker.setMap(map));
            }
        } else if (markers.length > 0) {
            // Se não há cluster disponível, adicionar marcadores diretamente
            markers.forEach(marker => marker.setMap(map));
            console.log('Marcadores adicionados diretamente ao mapa');
        }
        
        // Ajustar visualização do mapa para mostrar todos os marcadores
        if (markers.length > 0 && map) {
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(marker => bounds.extend(marker.getPosition()));
            
            if (markers.length === 1) {
                // Para um único marcador, centralizar e definir zoom
                map.setCenter(markers[0].getPosition());
                map.setZoom(10);
            } else {
                // Para múltiplos marcadores, ajustar aos limites
                map.fitBounds(bounds);
            }
            
            console.log('Vista do mapa ajustada para mostrar todos os marcadores');
        }
    }
    
    function getTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'agora';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' min';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' h';
        return Math.floor(diffInSeconds / 86400) + ' d';
    }
    
    // Cleanup quando a página for fechada
    window.addEventListener('beforeunload', function() {
        if (locationManager) {
            locationManager.destroy();
        }
    });
});
</script>
@endsection

@section('scripts')
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'SUA_CHAVE_API_AQUI' ) }}&libraries=geometry&callback=initMapChatHome"></script>
@endsection
