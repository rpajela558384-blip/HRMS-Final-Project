<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Shift::insert([
            [
                'shift_name'    => 'Day Shift',
                'start_time'    => '08:00:00',
                'end_time'      => '17:00:00',
                'grace_minutes' => 30,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'shift_name'    => 'Morning Shift',
                'start_time'    => '06:00:00',
                'end_time'      => '14:00:00',
                'grace_minutes' => 15,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'shift_name'    => 'Night Shift',
                'start_time'    => '22:00:00',
                'end_time'      => '06:00:00',
                'grace_minutes' => 30,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
