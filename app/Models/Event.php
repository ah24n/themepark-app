<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'schedule', 'location', 'capacity',
    ];

    public function tickets()
    {
        return $this->hasMany(ThemeParkTicket::class);
    }
}
