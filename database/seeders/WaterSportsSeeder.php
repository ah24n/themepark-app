<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Carbon;

class WaterSportsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Scuba Diving',
                'type' => 'water-sport',
                'schedule' => Carbon::now()->addDays(1)->setTime(10, 0),
                'location' => 'Lagoon Dock',
                'capacity' => 12,
            ],
            [
                'name' => 'Jet Ski',
                'type' => 'water-sport',
                'schedule' => Carbon::now()->addDays(1)->setTime(11, 30),
                'location' => 'Lagoon Dock',
                'capacity' => 20,
            ],
            [
                'name' => 'Banana Boat Ride',
                'type' => 'water-sport',
                'schedule' => Carbon::now()->addDays(1)->setTime(15, 0),
                'location' => 'Sunset Beach',
                'capacity' => 30,
            ],
        ];

        foreach ($rows as $row) {
            Event::updateOrCreate(
                ['name' => $row['name'], 'schedule' => $row['schedule']],
                $row
            );
        }
    }
}