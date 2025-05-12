<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'departure',
        'destination',
        'distance',
        'duration',
        'base_price',
        'description',
        'status',
    ];

    /**
     * Get the trips for the route.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
