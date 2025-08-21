/**
 * Sistema de localização aprimorado para MapChat
 * Inclui simulação de localização para desktops sem GPS
 */
class LocationManager {
    constructor() {
        this.userPosition = null;
        this.privacyRadius = 50000; // 50km em metros por padrão
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
        // Evento quando a página é fechada para marcar usuário como offline
        window.addEventListener('beforeunload', () => {
            if (this.isAuthenticated) {
                this.setOffline();
            }
        });
        
        // Detectar mudanças de visibilidade da página
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseUpdates();
            } else {
                this.resumeUpdates();
            }
        });
    }
    
    /**
     * Solicitar localização com fallback para simulação
     */
    requestLocationWithFallback() {
        if (!navigator.geolocation) {
            console.warn('Geolocalização não suportada neste navegador');
            this.simulateLocation();
            return;
        }
        
        const options = {
            enableHighAccuracy: false, // Menos preciso mas mais rápido
            timeout: 8000, // 8 segundos de timeout
            maximumAge: 600000 // 10 minutos de cache
        };
        
        // Primeiro, tentar obter localização
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                this.isSimulated = false;
                console.log('Localização real obtida:', this.userPosition);
                
                if (this.isAuthenticated) {
                    this.updateServerLocation();
                }
                
                this.onLocationUpdate(this.userPosition);
            },
            (error) => {
                console.warn('Erro ao obter localização real, usando simulação:', error.message);
                this.simulateLocation();
            },
            options
        );
        
        // Fallback automático após 5 segundos
        setTimeout(() => {
            if (!this.userPosition) {
                console.log('Timeout de geolocalização, usando simulação...');
                this.simulateLocation();
            }
        }, 5000);
    }
    
    /**
     * Simular localização para desktop
     */
    simulateLocation() {
        const brazilianCities = [
            { lat: -23.5505, lng: -46.6333, name: "São Paulo, SP" },
            { lat: -22.9068, lng: -43.1729, name: "Rio de Janeiro, RJ" },
            { lat: -15.7942, lng: -47.8822, name: "Brasília, DF" },
            { lat: -25.4244, lng: -49.2654, name: "Curitiba, PR" },
            { lat: -30.0346, lng: -51.2177, name: "Porto Alegre, RS" },
            { lat: -8.0476, lng: -34.8770, name: "Recife, PE" },
            { lat: -12.9714, lng: -38.5014, name: "Salvador, BA" },
            { lat: -19.9167, lng: -43.9345, name: "Belo Horizonte, MG" },
            { lat: -3.1190, lng: -60.0217, name: "Manaus, AM" },
            { lat: -1.4558, lng: -48.4902, name: "Belém, PA" }
        ];
        
        // Escolher cidade aleatória
        const randomCity = brazilianCities[Math.floor(Math.random() * brazilianCities.length)];
        
        // Adicionar variação aleatória pequena para simular diferentes usuários na mesma cidade
        const variation = 0.1; // ~11km de variação
        const lat = randomCity.lat + (Math.random() - 0.5) * variation;
        const lng = randomCity.lng + (Math.random() - 0.5) * variation;
        
        this.userPosition = { lat, lng };
        this.isSimulated = true;
        
        console.log(`Localização simulada em ${randomCity.name}:`, this.userPosition);
        
        this.onLocationUpdate(this.userPosition, randomCity.name);
        
        if (this.isAuthenticated) {
            this.updateServerLocation();
        }
    }
    
    /**
     * Atualizar localização no servidor
     */
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
                // Atualizar lista de usuários online
                this.loadOnlineUsers();
            }
        } catch (error) {
            console.error('Erro ao atualizar localização:', error);
        }
    }
    
    /**
     * Atualizar avatar do usuário
     */
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
                body: JSON.stringify({
                    avatar_type: avatarType
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.selectedAvatar = avatarType;
                this.onAvatarUpdate(avatarType);
                this.loadOnlineUsers(); // Recarregar usuários para ver mudança
            }
        } catch (error) {
            console.error('Erro ao atualizar avatar:', error);
        }
    }
    
    /**
     * Atualizar raio de privacidade
     */
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
                body: JSON.stringify({
                    radius: radiusMeters
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.onPrivacyRadiusUpdate(radiusMeters, data.new_location);
                this.loadOnlineUsers();
            }
        } catch (error) {
            console.error('Erro ao atualizar raio de privacidade:', error);
        }
    }
    
    /**
     * Marcar usuário como offline
     */
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
            console.error('Erro ao marcar como offline:', error);
        }
    }
    
    /**
     * Carregar usuários online
     */
    async loadOnlineUsers() {
        try {
            const response = await fetch('/usuarios-online.json');
            const data = await response.json();
            
            if (data.success) {
                this.onlineUsers = data.users;
                this.onUsersUpdate(this.onlineUsers);
            }
        } catch (error) {
            console.error('Erro ao carregar usuários online:', error);
        }
    }
    
    /**
     * Iniciar atualizações periódicas
     */
    startPeriodicUpdates() {
        // Atualizar usuários online a cada 30 segundos
        this.updateInterval = setInterval(() => {
            this.loadOnlineUsers();
        }, 30000);
    }
    
    /**
     * Pausar atualizações
     */
    pauseUpdates() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }
    
    /**
     * Retomar atualizações
     */
    resumeUpdates() {
        if (!this.updateInterval) {
            this.startPeriodicUpdates();
            this.loadOnlineUsers();
        }
    }
    
    /**
     * Calcular distância entre duas coordenadas
     */
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // raio da Terra em km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    /**
     * Obter avatar filename baseado no tipo
     */
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
    
    // Callbacks que podem ser sobrescritos
    onLocationUpdate(position, cityName = null) {
        console.log('Localização atualizada:', position);
        if (cityName) {
            console.log('Cidade simulada:', cityName);
        }
    }
    
    onAvatarUpdate(avatarType) {
        console.log('Avatar atualizado:', avatarType);
    }
    
    onPrivacyRadiusUpdate(radius, newLocation = null) {
        console.log('Raio de privacidade atualizado:', radius, newLocation);
    }
    
    onUsersUpdate(users) {
        console.log('Usuários online atualizados:', users);
    }
    
    onLocationError(error, message) {
        console.error('Erro de localização:', message, error);
    }
    
    /**
     * Destruir instância e limpar recursos
     */
    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        if (this.isAuthenticated) {
            this.setOffline();
        }
    }
}

// Tornar disponível globalmente
window.LocationManager = LocationManager;
