<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure',
        'destination',
        'distance',
        'duration',
        'base_price',
        'description',
        'status',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
