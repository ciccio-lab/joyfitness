<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Coach extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Utilizza la colonna 'slug' per il Route Model Binding (es. /coach/vito-piperis)
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function blockedSlots()
    {
        return $this->hasMany(BlockedSlot::class);
    }
}