<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'client_name',
        'client_email',
        'client_phone',
        'booking_date',
        'start_time',
        'booking_time',
        'time',
        'end_time',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
}