<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'trip_id',
        'seat_id',
        'status',
        'ticket_code',
    ];

    /**
     * Get the booking that owns the ticket.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the trip that owns the ticket.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the seat that owns the ticket.
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
}
