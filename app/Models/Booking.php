<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'coach_id', 
        'client_name', 
        'client_phone', 
        'client_email', 
        'booking_date', 
        'start_time', 
        'end_time'
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
}