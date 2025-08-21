<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'latitude',
        'longitude',
        'real_latitude',
        'real_longitude',
        'privacy_radius',
        'avatar_type',
        'is_online',
        'last_seen',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_seen' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'real_latitude' => 'decimal:8',
        'real_longitude' => 'decimal:8',
        'is_online' => 'boolean',
    ];

    /**
     * Relacionamentos
     */
    public function participacoes()
    {
        return $this->hasMany(Participacao::class);
    }

    public function mapchatsParticipando()
    {
        return $this->belongsToMany(Mapchat::class, 'participacoes', 'user_id', 'mapchat_id')
                    ->withPivot('pontuacao', 'status', 'tempo_total_segundos', 'locais_visitados')
                    ->withTimestamps();
    }

    public function mapchatsCriados()
    {
        return $this->hasMany(Mapchat::class, 'user_id');
    }
}
