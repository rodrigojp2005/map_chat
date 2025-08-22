<?php

namespace App\Services;

use App\Models\User;
use App\Models\AnonymousUser;
use Illuminate\Support\Facades\Http;

class LocationService
{
    /**
     * Gera uma localização aleatória baseada na localização real do usuário
     * mantendo a privacidade dentro do raio especificado
     */
    public function generateRandomLocation(float $realLat, float $realLng, int $radiusKm = 50): array
    {
        // Limitar o raio máximo a 5000km
        $radiusKm = min($radiusKm, 5000);
        $radiusKm = max($radiusKm, 0.5); // mínimo 500 metros
        
        // Converter km para graus (aproximadamente)
        $radiusInDegrees = $radiusKm / 111.32; // 1 grau ≈ 111.32 km
        
        // Gerar ângulo aleatório
        $angle = rand(0, 360) * (M_PI / 180);
        
        // Gerar distância aleatória dentro do raio
        $distance = sqrt(rand(0, 10000) / 10000) * $radiusInDegrees;
        
        // Calcular nova posição
        $newLat = $realLat + ($distance * cos($angle));
        $newLng = $realLng + ($distance * sin($angle));
        
        // Verificar se a nova posição está dentro dos limites válidos
        $newLat = max(-90, min(90, $newLat));
        $newLng = max(-180, min(180, $newLng));
        
        // Se o raio for muito grande (>1000km), tentar manter no mesmo país
        if ($radiusKm > 1000) {
            $countryInfo = $this->getCountryInfo($realLat, $realLng);
            if ($countryInfo) {
                $newLocation = $this->adjustLocationToCountry($newLat, $newLng, $countryInfo, $radiusKm);
                if ($newLocation) {
                    return $newLocation;
                }
            }
        }
        
        return [
            'latitude' => round($newLat, 8),
            'longitude' => round($newLng, 8)
        ];
    }
    
    /**
     * Atualiza a localização do usuário
     */
    public function updateUserLocation(User $user, float $realLat, float $realLng, int $radiusKm = null): void
    {
        $radiusKm = $radiusKm ?? $user->privacy_radius / 1000; // converter metros para km
        
        $randomLocation = $this->generateRandomLocation($realLat, $realLng, $radiusKm);
        
        $user->update([
            'real_latitude' => $realLat,
            'real_longitude' => $realLng,
            'latitude' => $randomLocation['latitude'],
            'longitude' => $randomLocation['longitude'],
            'privacy_radius' => $radiusKm * 1000, // salvar em metros
            'is_online' => true,
            'last_seen' => now()
        ]);
    }
    
    /**
     * Marca usuário como offline
     */
    public function setUserOffline(User $user): void
    {
        $user->update([
            'is_online' => false,
            'last_seen' => now()
        ]);
    }
    
    /**
     * Obter todos os usuários online (incluindo anônimos)
     */
    public function getOnlineUsers(): array
    {
        // Limpar usuários anônimos antigos antes de buscar
        AnonymousUser::cleanupOldUsers();
        
        // Buscar usuários registrados online
        $registeredUsers = User::where('is_online', true)
            ->where('last_seen', '>', now()->subMinutes(5))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => 'user_' . $user->id,
                    'name' => $user->name,
                    'latitude' => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                    'avatar_type' => $user->avatar_type,
                    'last_seen' => $user->last_seen->toISOString(),
                    'type' => 'registered'
                ];
            });

        // Buscar usuários anônimos online
        $anonymousUsers = AnonymousUser::online()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => 'anon_' . $user->session_id,
                    'name' => $user->name,
                    'latitude' => (float) $user->latitude,
                    'longitude' => (float) $user->longitude,
                    'avatar_type' => $user->avatar_type,
                    'last_seen' => $user->last_seen->toISOString(),
                    'type' => 'anonymous'
                ];
            });

        // Combinar ambos os tipos de usuários
        return $registeredUsers->concat($anonymousUsers)->toArray();
    }
    
    /**
     * Obter informações do país baseado na localização
     */
    private function getCountryInfo(float $lat, float $lng): ?array
    {
        try {
            // Usar um serviço de geocoding reverso gratuito
            $response = Http::timeout(5)->get("https://api.bigdatacloud.net/data/reverse-geocode-client", [
                'latitude' => $lat,
                'longitude' => $lng,
                'localityLanguage' => 'pt'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'country_code' => $data['countryCode'] ?? null,
                    'country_name' => $data['countryName'] ?? null,
                    'locality' => $data['locality'] ?? null
                ];
            }
        } catch (\Exception $e) {
            // Em caso de erro na API externa, continuar sem restrição de país
        }
        
        return null;
    }
    
    /**
     * Ajustar localização para tentar manter no mesmo país
     */
    private function adjustLocationToCountry(float $lat, float $lng, array $countryInfo, int $radiusKm): ?array
    {
        // Para simplicidade, vamos apenas reduzir o raio se estivermos muito longe
        // Uma implementação completa incluiria verificação de fronteiras
        
        // Se o país for pequeno (como alguns países europeus), reduzir o raio
        $smallCountries = ['PT', 'BE', 'NL', 'CH', 'AT', 'DK', 'CR', 'SV', 'BE'];
        
        if (in_array($countryInfo['country_code'] ?? '', $smallCountries)) {
            // Para países pequenos, limitar a 100km
            $radiusKm = min($radiusKm, 100);
            return $this->generateRandomLocation($lat, $lng, $radiusKm);
        }
        
        return [
            'latitude' => round($lat, 8),
            'longitude' => round($lng, 8)
        ];
    }
    
    /**
     * Calcular distância entre duas coordenadas em km
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // raio da Terra em km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Atualizar localização de usuário anônimo
     */
    public function updateAnonymousUserLocation(string $sessionId, float $realLat, float $realLng, int $radiusKm = 5, string $avatarType = 'anonymous'): void
    {
        $randomLocation = $this->generateRandomLocation($realLat, $realLng, $radiusKm);
        
        // Buscar ou criar usuário anônimo
        $anonymousUser = AnonymousUser::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'name' => 'Usuário Anônimo',
                'avatar_type' => $avatarType
            ]
        );

        // Atualizar localização e avatar
        $anonymousUser->update([
            'real_latitude' => $realLat,
            'real_longitude' => $realLng,
            'latitude' => $randomLocation['latitude'],
            'longitude' => $randomLocation['longitude'],
            'privacy_radius' => $radiusKm * 1000, // salvar em metros
            'is_online' => true,
            'last_seen' => now(),
            'avatar_type' => $avatarType // Atualizar avatar também
        ]);
    }
}
