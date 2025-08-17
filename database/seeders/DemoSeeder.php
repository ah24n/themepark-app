<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Room;
use App\Models\FerrySchedule;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::factory()->create([
            'name'=>'Evening Parade','type'=>'parade',
            'schedule'=>now()->addDays(1)->setTime(19,0),
            'location'=>'Main Street','capacity'=>300
        ]);

        Room::insert([
            ['type'=>'Family Suite','price'=>250,'availability'=>10],
            ['type'=>'Deluxe','price'=>180,'availability'=>15],
        ]);

        FerrySchedule::create([
            'departure_time'=>now()->addDays(1)->setTime(9,0),
            'arrival_time'=>now()->addDays(1)->setTime(9,45),
            'from_location'=>'Harbor A','to_location'=>'Park Pier','capacity'=>120
        ]);
    }
}
