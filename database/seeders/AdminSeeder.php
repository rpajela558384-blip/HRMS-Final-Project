<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $dayShift   = Shift::where('shift_name', 'like', '%Day%')->first();
        $nightShift = Shift::where('shift_name', 'like', '%Night%')->first();
        $leaveTypes = LeaveType::all();

        // ── Admin ─────────────────────────────────────────────────────
        $admin = User::create([
            'username'      => 'admin',
            'password_hash' => Hash::make('123'),
            'role'          => 'admin',
            'status'        => 'active',
        ]);
        Employee::create([
            'user_id'    => $admin->user_id,
            'first_name' => 'System',
            'last_name'  => 'Admin',
        ]);

        // ── HR (bart) ─────────────────────────────────────────────────
        $hr = User::create([
            'username'      => 'bart',
            'password_hash' => Hash::make('123'),
            'role'          => 'hr',
            'status'        => 'active',
        ]);
        $hrEmployee = Employee::create([
            'user_id'    => $hr->user_id,
            'first_name' => 'Bart',
            'last_name'  => 'Macirin',
            'hire_date'  => '2019-04-28',
            'shift_id'   => $dayShift?->shift_id,
        ]);
        $leaveTypes->each(fn($type) => LeaveBalance::create([
            'employee_id'    => $hrEmployee->employee_id,
            'leave_type_id'  => $type->leave_type_id,
            'remaining_days' => $type->default_balance,
        ]));

        // ── Employee (red) ────────────────────────────────────────────
        $emp = User::create([
            'username'      => 'red',
            'password_hash' => Hash::make('123'),
            'role'          => 'employee',
            'status'        => 'active',
        ]);
        $empEmployee = Employee::create([
            'user_id'    => $emp->user_id,
            'first_name' => 'Red',
            'last_name'  => 'Pajela',
            'hire_date'  => '2020-04-28',
            'shift_id'   => $nightShift?->shift_id,
        ]);
        $leaveTypes->each(fn($type) => LeaveBalance::create([
            'employee_id'    => $empEmployee->employee_id,
            'leave_type_id'  => $type->leave_type_id,
            'remaining_days' => $type->default_balance,
        ]));
    }
}
