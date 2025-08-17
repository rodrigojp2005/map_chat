<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapchat extends Model
{
    use HasFactory;

    protected $table = 'mapchats';

    protected $fillable = [
        'user_id',
        'nome',
        'duracao',
        'latitude',
        'longitude',
        'contexto',
        'privacidade',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'mapchat_id')->orderBy('created_at', 'desc');
    }

    public function participacoes()
    {
        return $this->hasMany(Participacao::class, 'mapchat_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(User::class, 'participacoes', 'mapchat_id', 'user_id')
                    ->withPivot('pontuacao', 'status', 'tempo_total_segundos', 'locais_visitados')
                    ->withTimestamps();
    }
}
