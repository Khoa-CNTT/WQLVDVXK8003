<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_location',
        'arrival_location',
        'distance',
        'base_price',
        'estimated_time',
        'is_active',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function getFormattedEstimatedTimeAttribute()
    {
        $hours = floor($this->estimated_time / 60);
        $minutes = $this->estimated_time % 60;

        return $hours . ' giờ ' . $minutes . ' phút';
    }
}
