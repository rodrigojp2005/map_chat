<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GincanaCommentNotification extends Model
{
    use HasFactory;

    // Explicit table to match the migration name
    protected $table = 'mapchat_comment_notifications';

    protected $fillable = [
        'user_id',
    'mapchat_id',
        'unread_count',
        'last_comentario_id',
        'last_preview',
        'last_author_name',
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
