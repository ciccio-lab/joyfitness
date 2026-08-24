<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    protected $fillable = ['name', 'slug'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function unavailabilities()
    {
        return $this->hasMany(CoachUnavailability::class);
    }
}