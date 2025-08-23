<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatRoomUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'user_name',
        'user_type',
        'avatar_type',
        'latitude',
        'longitude',
        'joined_at',
        'last_seen',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'joined_at' => 'datetime',
        'last_seen' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Relacionamentos
     */
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('last_seen', '>', Carbon::now()->subMinutes(5));
    }

    public function scopeInRoom($query, $roomId)
    {
        return $query->where('chat_room_id', $roomId);
    }

    /**
     * Marcar usuário como ativo
     */
    public function updateActivity()
    {
        $this->update([
            'last_seen' => now(),
            'is_active' => true
        ]);
    }

    /**
     * Marcar usuário como inativo
     */
    public function markInactive()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Verificar se o usuário está online
     */
    public function isOnline()
    {
        return $this->is_active && 
               $this->last_seen > Carbon::now()->subMinutes(5);
    }

    /**
     * Calcular tempo na sala
     */
    public function getTimeInRoomAttribute()
    {
        if (!$this->joined_at) return 0;
        
        $endTime = $this->is_active ? now() : $this->last_seen;
        return $this->joined_at->diffInMinutes($endTime);
    }

    /**
     * Obter avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        $avatarMap = [
            'default' => 'default.gif',
            'man' => 'mario.gif',
            'woman' => 'girl.gif',
            'pet' => 'pets.gif',
            'geek' => 'geek.gif',
            'sport' => 'sport.gif',
            'anonymous' => 'default.gif'
        ];
        
        $filename = $avatarMap[$this->avatar_type] ?? 'default.gif';
        return "/images/{$filename}";
    }

    /**
     * Limpar usuários inativos
     */
    public static function cleanupInactiveUsers()
    {
        $threshold = Carbon::now()->subMinutes(10);
        
        static::where('last_seen', '<', $threshold)
              ->update(['is_active' => false]);
              
        // Excluir registros muito antigos (mais de 24 horas)
        $oldThreshold = Carbon::now()->subDay();
        static::where('is_active', false)
              ->where('last_seen', '<', $oldThreshold)
              ->delete();
    }
}
