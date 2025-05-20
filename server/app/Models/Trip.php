<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'line_id',
        'vehicle_id',
        'driver_id',
        'departure_time',
        'arrival_time',
        'price',
        'status',
        'trip_code',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the line that owns the trip.
     */
    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * Get the vehicle that owns the trip.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver that owns the trip.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the bookings for the trip.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the tickets for the trip.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
