<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'query',
        'response',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user that owns the chatbot log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
