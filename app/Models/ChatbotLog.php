<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_query',
        'bot_response',
        'session_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
