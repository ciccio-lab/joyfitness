<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachUnavailability extends Model
{
    protected $fillable = ['coach_id', 'specific_date', 'day_of_week', 'start_time'];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
}