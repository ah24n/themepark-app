<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FerryScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'departure_time' => Carbon::tomorrow()->setTime(9, 0),
                'arrival_time'   => Carbon::tomorrow()->setTime(9, 30),
                'from_location'  => 'Malé Jetty',
                'to_location'    => 'Faru Picnic Isle',
                'capacity'       => 40,
            ],
            [
                'departure_time' => Carbon::tomorrow()->setTime(11, 0),
                'arrival_time'   => Carbon::tomorrow()->setTime(11, 30),
                'from_location'  => 'Hulhumalé Jetty',
                'to_location'    => 'Faru Picnic Isle',
                'capacity'       => 35,
            ],
            [
                'departure_time' => Carbon::tomorrow()->setTime(17, 0),
                'arrival_time'   => Carbon::tomorrow()->setTime(17, 30),
                'from_location'  => 'Faru Picnic Isle',
                'to_location'    => 'Malé Jetty',
                'capacity'       => 40,
            ],
        ];

        foreach ($rows as $r) {
            DB::table('ferry_schedules')->updateOrInsert(
                [
                    'departure_time' => $r['departure_time'],
                    'from_location'  => $r['from_location'],
                    'to_location'    => $r['to_location'],
                ],
                $r
            );
        }
    }
}