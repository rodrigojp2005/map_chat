/**
 * Sistema de localização e usuários online para MapChat
 */
class LocationManager {
    constructor() {
        this.userPosition = null;
        this.privacyRadius = 50000; // 50km em metros por padrão
        this.selectedAvatar = 'default';
        this.isAuthenticated = window.isAuthenticated || false;
        this.updateInterval = null;
        this.onlineUsers = [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.requestLocation();
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
     * Solicitar localização do usuário
     */
    requestLocation() {
        if (!navigator.geolocation) {
            console.warn('Geolocalização não suportada neste navegador');
            return;
        }
        
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutos
        };
        
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                console.log('Localização obtida:', this.userPosition);
                
                if (this.isAuthenticated) {
                    this.updateServerLocation();
                }
                
                this.onLocationUpdate(this.userPosition);
            },
            (error) => {
                console.error('Erro ao obter localização:', error);
                this.handleLocationError(error);
            },
            options
        );
        
        // Monitorar mudanças de posição
        navigator.geolocation.watchPosition(
            (position) => {
                const newPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Só atualizar se a posição mudou significativamente (>100m)
                if (this.userPosition && this.calculateDistance(
                    this.userPosition.lat, this.userPosition.lng,
                    newPosition.lat, newPosition.lng
                ) > 0.1) {
                    this.userPosition = newPosition;
                    if (this.isAuthenticated) {
                        this.updateServerLocation();
                    }
                }
            },
            (error) => console.warn('Erro no monitoramento de localização:', error),
            options
        );
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
     * Lidar com erro de localização
     */
    handleLocationError(error) {
        let message = '';
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = 'Permissão de localização negada. Você pode ajustar manualmente sua região.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Informação de localização indisponível.';
                break;
            case error.TIMEOUT:
                message = 'Tempo limite para obter localização excedido.';
                break;
            default:
                message = 'Erro desconhecido ao obter localização.';
                break;
        }
        
        this.onLocationError(error, message);
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
    onLocationUpdate(position) {
        console.log('Localização atualizada:', position);
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
