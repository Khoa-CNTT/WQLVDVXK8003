<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'departure_date',
        'departure_time',
        'arrival_date',
        'arrival_time',
        'price',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'arrival_date' => 'date',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function availableSeats()
    {
        $vehicleSeats = $this->vehicle->seats->where('is_active', true);
        $bookedSeatIds = $this->tickets->where('status', '!=', 'cancelled')->pluck('seat_id')->toArray();

        return $vehicleSeats->whereNotIn('id', $bookedSeatIds);
    }
}
