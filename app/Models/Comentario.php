<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
    'mapchat_id',
        'user_id',
        'conteudo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mapchat()
    {
        return $this->belongsTo(Mapchat::class);
    }
}
