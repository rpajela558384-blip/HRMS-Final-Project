<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Models\WorkingDay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $workDate     = Carbon::parse('2026-04-28');
        $tomorrow     = $workDate->copy()->addDay();   // 2026-04-29
        $hrEmployee   = Employee::whereHas('user', fn($u) => $u->where('username', 'bart'))->first();
        $empEmployee  = Employee::whereHas('user', fn($u) => $u->where('username', 'red'))->first();
        $approver     = User::where('username', 'bart')->first();
        $vacationLeave = LeaveType::where('name', 'Vacation Leave')->first();
        $sickLeave     = LeaveType::where('name', 'Sick Leave')->first();
        $emergencyLeave= LeaveType::where('name', 'Emergency Leave')->first();

        // ── Working Day ──────────────────────────────────────────────
        WorkingDay::firstOrCreate(
            ['work_date' => $workDate->toDateString()],
            ['status' => 'closed', 'opened_by' => $approver->user_id]
        );

        // ────────────────────────────────────────────────────────────
        // BART (HR) — Day Shift 08:00–17:00
        // Attendance: late time-in (08:45), undertime time-out (16:30)
        // ────────────────────────────────────────────────────────────
        Attendance::create([
            'employee_id'     => $hrEmployee->employee_id,
            'work_date'       => $workDate->toDateString(),
            'time_in'         => $workDate->copy()->setTime(8, 45),
            'time_out'        => $workDate->copy()->setTime(16, 30),
            'is_late'         => true,
            'is_undertime'    => true,
            'is_auto_timeout' => false,
            'is_off_shift'    => false,
            'overtime_hours'  => 0,
        ]);

        // OT Requests (Bart) — one per status
        OvertimeRequest::create([
            'employee_id'         => $hrEmployee->employee_id,
            'work_date'           => $workDate->toDateString(),
            'requested_end_time'  => $workDate->copy()->setTime(19, 0),
            'reason'              => 'Month-end report finalization.',
            'status'              => 'approved',
            'approved_by'         => $approver->user_id,
            'approved_at'         => now(),
        ]);
        OvertimeRequest::create([
            'employee_id'         => $hrEmployee->employee_id,
            'work_date'           => $tomorrow->toDateString(),
            'requested_end_time'  => $tomorrow->copy()->setTime(19, 0),
            'reason'              => 'Pending payroll adjustments.',
            'status'              => 'pending',
        ]);
        OvertimeRequest::create([
            'employee_id'         => $hrEmployee->employee_id,
            'work_date'           => $workDate->copy()->subDay()->toDateString(),
            'requested_end_time'  => $workDate->copy()->subDay()->setTime(20, 0),
            'reason'              => 'System migration support — eventually not needed.',
            'status'              => 'rejected',
            'approved_by'         => $approver->user_id,
            'approved_at'         => now(),
        ]);

        // Leave Requests (Bart) — one per status
        LeaveRequest::create([
            'employee_id'    => $hrEmployee->employee_id,
            'leave_type_id'  => $vacationLeave->leave_type_id,
            'start_date'     => $tomorrow->toDateString(),
            'end_date'       => $tomorrow->copy()->addDays(2)->toDateString(),
            'reason'         => 'Family vacation trip',
            'status'         => 'approved',
            'approved_by'    => $approver->user_id,
            'approved_at'    => now(),
            'remarks'        => 'Approved. Enjoy your leave.',
        ]);
        LeaveRequest::create([
            'employee_id'    => $hrEmployee->employee_id,
            'leave_type_id'  => $sickLeave->leave_type_id,
            'start_date'     => $workDate->copy()->addDays(5)->toDateString(),
            'end_date'       => $workDate->copy()->addDays(5)->toDateString(),
            'reason'         => 'Feeling unwell, need rest.',
            'status'         => 'pending',
        ]);
        LeaveRequest::create([
            'employee_id'    => $hrEmployee->employee_id,
            'leave_type_id'  => $emergencyLeave->leave_type_id,
            'start_date'     => $workDate->copy()->subDays(3)->toDateString(),
            'end_date'       => $workDate->copy()->subDays(3)->toDateString(),
            'reason'         => 'Personal emergency — later resolved without absence.',
            'status'         => 'rejected',
            'approved_by'    => $approver->user_id,
            'approved_at'    => now(),
            'remarks'        => 'Not qualified for emergency classification.',
        ]);

        // ────────────────────────────────────────────────────────────
        // RED (Employee) — Night Shift 22:00–06:00
        // Attendance: on-time, with overtime (out at 08:00 = 2h OT)
        // ────────────────────────────────────────────────────────────
        Attendance::create([
            'employee_id'     => $empEmployee->employee_id,
            'work_date'       => $workDate->toDateString(),
            'time_in'         => $workDate->copy()->setTime(22, 0),
            'time_out'        => $workDate->copy()->addDay()->setTime(8, 0),
            'is_late'         => false,
            'is_undertime'    => false,
            'is_auto_timeout' => false,
            'is_off_shift'    => false,
            'overtime_hours'  => 2.00,
        ]);

        // OT Requests (Red) — one per status
        OvertimeRequest::create([
            'employee_id'         => $empEmployee->employee_id,
            'work_date'           => $workDate->toDateString(),
            'requested_end_time'  => $workDate->copy()->addDay()->setTime(8, 0),
            'reason'              => 'Production server maintenance window.',
            'status'              => 'approved',
            'approved_by'         => $approver->user_id,
            'approved_at'         => now(),
        ]);
        OvertimeRequest::create([
            'employee_id'         => $empEmployee->employee_id,
            'work_date'           => $tomorrow->toDateString(),
            'requested_end_time'  => $tomorrow->copy()->addDay()->setTime(8, 30),
            'reason'              => 'Additional deployment tasks needed.',
            'status'              => 'pending',
        ]);
        OvertimeRequest::create([
            'employee_id'         => $empEmployee->employee_id,
            'work_date'           => $workDate->copy()->subDay()->toDateString(),
            'requested_end_time'  => $workDate->copy()->setTime(9, 0),
            'reason'              => 'Requested extra coverage — cancelled by team.',
            'status'              => 'rejected',
            'approved_by'         => $approver->user_id,
            'approved_at'         => now(),
        ]);

        // Leave Requests (Red) — one per status
        LeaveRequest::create([
            'employee_id'    => $empEmployee->employee_id,
            'leave_type_id'  => $sickLeave->leave_type_id,
            'start_date'     => $tomorrow->toDateString(),
            'end_date'       => $tomorrow->toDateString(),
            'reason'         => 'High fever, doctor advised rest.',
            'status'         => 'approved',
            'approved_by'    => $approver->user_id,
            'approved_at'    => now(),
            'remarks'        => 'Approved. Get well soon.',
        ]);
        LeaveRequest::create([
            'employee_id'    => $empEmployee->employee_id,
            'leave_type_id'  => $vacationLeave->leave_type_id,
            'start_date'     => $workDate->copy()->addDays(7)->toDateString(),
            'end_date'       => $workDate->copy()->addDays(9)->toDateString(),
            'reason'         => 'Rest and recuperation leave.',
            'status'         => 'pending',
        ]);
        LeaveRequest::create([
            'employee_id'    => $empEmployee->employee_id,
            'leave_type_id'  => $emergencyLeave->leave_type_id,
            'start_date'     => $workDate->copy()->subDays(5)->toDateString(),
            'end_date'       => $workDate->copy()->subDays(5)->toDateString(),
            'reason'         => 'Claimed emergency leave without prior notice.',
            'status'         => 'rejected',
            'approved_by'    => $approver->user_id,
            'approved_at'    => now(),
            'remarks'        => 'No supporting documentation provided.',
        ]);
    }
}
