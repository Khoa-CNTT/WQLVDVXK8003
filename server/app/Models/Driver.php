<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'license_number',
        'license_expiry',
        'address',
        'birth_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'license_expiry' => 'date',
        'birth_date' => 'date',
    ];

    /**
     * Get the trips for the driver.
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
