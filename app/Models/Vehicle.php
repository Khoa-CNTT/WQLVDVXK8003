<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'license_plate',
        'type',
        'capacity',
        'manufacture_year',
        'last_maintenance',
        'status',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_maintenance' => 'date',
    ];

    /**
     * Get the trips for the vehicle.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
