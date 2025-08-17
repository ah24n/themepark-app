<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FerryPass extends Model
{
    public function ticket() {
        return $this->belongsTo(FerryTicket::class, 'ferry_ticket_id');
    }
}
