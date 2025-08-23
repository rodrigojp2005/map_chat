<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'user_name',
        'user_type',
        'avatar_type',
        'message',
        'message_type',
        'sent_at',
        'is_visible'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_visible' => 'boolean'
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
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('sent_at', '>', now()->subMinutes($minutes));
    }

    public function scopeByRoom($query, $roomId)
    {
        return $query->where('chat_room_id', $roomId);
    }

    /**
     * Verificar se é uma mensagem do sistema
     */
    public function isSystemMessage()
    {
        return $this->message_type === 'system';
    }

    /**
     * Verificar se é do usuário atual
     */
    public function isFromCurrentUser($currentUserId)
    {
        return $this->user_id === $currentUserId;
    }

    /**
     * Formatar a mensagem para exibição
     */
    public function getFormattedMessageAttribute()
    {
        if ($this->message_type === 'emoji') {
            return $this->message; // Emojis são exibidos como estão
        }
        
        if ($this->message_type === 'system') {
            return $this->message; // Mensagens do sistema
        }
        
        // Para mensagens de texto, aplicar escape para segurança
        return htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obter avatar do usuário
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
}
