<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_plate',
        'type',
        'capacity',
        'last_maintenance',
        'is_active',
        'amenities',
    ];

    protected $casts = [
        'last_maintenance' => 'date',
        'amenities' => 'array',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }
}
