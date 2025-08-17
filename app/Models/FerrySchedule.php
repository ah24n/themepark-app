<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FerrySchedule extends Model
{
    public function tickets() {
        return $this->hasMany(FerryTicket::class);
    }
}
