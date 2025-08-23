<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'center_latitude',
        'center_longitude',
        'radius_km',
        'max_users',
        'current_users',
        'is_active',
        'last_activity'
    ];

    protected $casts = [
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'last_activity' => 'datetime'
    ];

    /**
     * Relacionamentos
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function users()
    {
        return $this->hasMany(ChatRoomUser::class);
    }

    public function activeUsers()
    {
        return $this->hasMany(ChatRoomUser::class)->where('is_active', true)
                    ->where('last_seen', '>', Carbon::now()->subMinutes(5));
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithinRadius($query, $latitude, $longitude, $maxDistanceKm = 1000)
    {
        // Usar fórmula de Haversine para calcular distância
        return $query->selectRaw("
            *, 
            (6371 * acos(cos(radians(?)) * cos(radians(center_latitude)) * 
            cos(radians(center_longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(center_latitude)))) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<=', $maxDistanceKm)
        ->orderBy('distance');
    }

    /**
     * Gerar ID único para a sala baseado em coordenadas
     */
    public static function generateRoomId($latitude, $longitude, $radiusKm = 100)
    {
        // Criar um grid baseado no raio para agrupar usuários próximos
        $gridSize = $radiusKm / 111; // aproximadamente em graus
        $gridLat = floor($latitude / $gridSize) * $gridSize;
        $gridLng = floor($longitude / $gridSize) * $gridSize;
        
        return 'room_' . md5($gridLat . '_' . $gridLng . '_' . $radiusKm);
    }

    /**
     * Gerar nome automático para a sala
     */
    public static function generateRoomName($latitude, $longitude)
    {
        // Aqui poderíamos usar geocoding reverso para obter o nome da cidade/região
        // Por simplicidade, vamos usar coordenadas aproximadas
        $latStr = $latitude >= 0 ? 'N' : 'S';
        $lngStr = $longitude >= 0 ? 'L' : 'O';
        
        return sprintf('Sala %s%d° %s%d°', 
            $latStr, abs(round($latitude)), 
            $lngStr, abs(round($longitude))
        );
    }

    /**
     * Limpar salas inativas
     */
    public static function cleanupInactiveRooms()
    {
        // Marcar salas como inativas se não há mensagens há mais de 1 hora
        $threshold = Carbon::now()->subHour();
        
        static::where('last_activity', '<', $threshold)
              ->orWhereNull('last_activity')
              ->update(['is_active' => false]);

        // Excluir salas muito antigas (mais de 24 horas inativas)
        $oldThreshold = Carbon::now()->subDay();
        static::where('is_active', false)
              ->where('updated_at', '<', $oldThreshold)
              ->delete();
    }

    /**
     * Atualizar contagem de usuários
     */
    public function updateUserCount()
    {
        $count = $this->activeUsers()->count();
        $this->update(['current_users' => $count]);
        return $count;
    }

    /**
     * Verificar se a sala pode aceitar mais usuários
     */
    public function canAcceptUser()
    {
        return $this->current_users < $this->max_users;
    }

    /**
     * Atualizar última atividade
     */
    public function touchActivity()
    {
        $this->update(['last_activity' => now()]);
    }
}
