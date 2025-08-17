<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public function ticket() {
        return $this->belongsTo(ThemeParkTicket::class, 'theme_park_ticket_id');
    }

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
