<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        LeaveType::insert([
            ['name' => 'Vacation Leave',  'default_balance' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sick Leave',      'default_balance' => 15, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Emergency Leave', 'default_balance' => 5,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maternity Leave', 'default_balance' => 105,'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paternity Leave', 'default_balance' => 7,  'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
