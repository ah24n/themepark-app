<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['type' => 'Family Suite', 'price' => 250, 'availability' => 5],
            ['type' => 'Deluxe',       'price' => 180, 'availability' => 8],
            ['type' => 'Standard',     'price' => 120, 'availability' => 12],
        ];

        foreach ($rows as $row) {
            Room::updateOrCreate(['type' => $row['type']], $row);
        }
    }
}