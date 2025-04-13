<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license_number',
        'phone',
        'license_expiry',
        'is_active',
    ];

    protected $casts = [
        'license_expiry' => 'date',
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
