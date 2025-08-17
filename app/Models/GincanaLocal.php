<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GincanaLocal extends Model
{
    use HasFactory;
    
    protected $table = 'mapchat_locais';
    
    protected $fillable = [
        'mapchat_id', 
        'latitude', 
        'longitude'
    ];

    public function mapchat()
    {
        return $this->belongsTo(Mapchat::class);
    }
}
