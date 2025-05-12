<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seat_number',
        'seat_type',
        'position',
        'status',
    ];

    /**
     * Get the tickets for the seat.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
