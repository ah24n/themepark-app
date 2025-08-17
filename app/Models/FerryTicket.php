<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FerryTicket extends Model
{
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function schedule() {
        return $this->belongsTo(FerrySchedule::class, 'ferry_schedule_id');
    }

    public function hotelBooking() {
        return $this->belongsTo(RoomBooking::class, 'hotel_booking_id');
    }

    public function pass() {
        return $this->hasOne(FerryPass::class);
    }
}
