<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AnonymousUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'name',
        'real_latitude',
        'real_longitude', 
        'latitude',
        'longitude',
        'privacy_radius',
        'is_online',
        'last_seen',
        'avatar_type'
    ];

    protected $casts = [
        'real_latitude' => 'float',
        'real_longitude' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'privacy_radius' => 'integer',
        'is_online' => 'boolean',
        'last_seen' => 'datetime'
    ];

    /**
     * Scope para usuários online
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true)
                    ->where('last_seen', '>', Carbon::now()->subMinutes(5));
    }

    /**
     * Limpar usuários anônimos antigos (mais de 1 hora offline)
     */
    public static function cleanupOldUsers()
    {
        self::where('last_seen', '<', Carbon::now()->subHour())->delete();
    }
}
