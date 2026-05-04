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
        $hrEmployee    = Employee::whereHas('user', fn($u) => $u->where('username', 'bart'))->first();
        $empEmployee   = Employee::whereHas('user', fn($u) => $u->where('username', 'red'))->first();
        $approver      = User::where('username', 'bart')->first();
        $vacationLeave = LeaveType::where('name', 'Vacation Leave')->first();
        $sickLeave     = LeaveType::where('name', 'Sick Leave')->first();
        $emergencyLeave= LeaveType::where('name', 'Emergency Leave')->first();

        // Generate 30 days of attendance history (April 1-30, 2026)
        $startDate = Carbon::parse('2026-04-01');
        $endDate   = Carbon::parse('2026-04-30');

        // Create working days for the month
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip Sundays (day 0)
            if ($date->dayOfWeek === 0) continue;
            
            WorkingDay::firstOrCreate(
                ['work_date' => $date->toDateString()],
                ['status' => 'closed', 'opened_by' => $approver->user_id]
            );
        }

        // BART (HR) — Day Shift 08:00–17:00 scenarios
        $bartScenarios = [
            // [day, time_in, time_out, is_late, is_undertime, is_off_shift, overtime_hours, is_auto_timeout]
            [1,  '08:00', '17:00', false, false, false, 0, false],   // Perfect
            [2,  '08:05', '17:00', false, false, false, 0, false],   // Slightly early
            [3,  '08:45', '16:30', true,  true,  false, 0, false],   // Late + undertime
            [4,  '08:00', '17:30', false, false, false, 0.5, false], // OT 30 min
            [5,  '08:00', '18:00', false, false, false, 1, false],  // OT 1 hour
            [7,  '08:10', '17:00', false, false, false, 0, false],   // Small delay
            [8,  '08:00', '16:00', false, true,  false, 0, false],   // Undertime
            [9,  '07:30', '17:00', false, false, true,  0, false],   // Off-shift (early)
            [10, '09:15', '17:00', true,  false, false, 0, false],   // Very late
            [11, '08:00', '19:00', false, false, false, 2, false],   // OT 2 hours
            [12, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [14, '08:00', '15:30', false, true,  false, 0, false],   // Early out
            [15, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [16, '08:00', '17:45', false, false, false, 0.75, false],// OT 45 min
            [17, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [18, '08:30', '16:00', true,  true,  false, 0, false],   // Late + undertime
            [19, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [21, '08:00', '18:30', false, false, false, 1.5, false], // OT 1.5h
            [22, '07:45', '17:00', false, false, true,  0, false],   // Off-shift
            [23, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [24, '09:00', '17:00', true,  false, false, 0, false],   // Late
            [25, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [26, '08:00', '16:45', false, true,  false, 0, false],   // Slight undertime
            [28, '08:00', '17:00', false, false, false, 0, false],   // Normal
            [29, '08:00', '19:30', false, false, false, 2.5, false], // OT 2.5h
            [30, '08:00', '17:00', false, false, false, 0, false],   // Normal
        ];

        foreach ($bartScenarios as $scenario) {
            [$day, $in, $out, $late, $undertime, $offShift, $otHours, $auto] = $scenario;
            $date = Carbon::parse("2026-04-{$day}");
            
            Attendance::create([
                'employee_id'     => $hrEmployee->employee_id,
                'work_date'       => $date->toDateString(),
                'time_in'         => $date->copy()->setTimeFromTimeString($in),
                'time_out'        => $date->copy()->setTimeFromTimeString($out),
                'is_late'         => $late,
                'is_undertime'    => $undertime,
                'is_auto_timeout' => $auto,
                'is_off_shift'    => $offShift,
                'overtime_hours'  => $otHours,
            ]);
        }

        // RED (Employee) — Night Shift 22:00–06:00 scenarios
        $redScenarios = [
            // [day, time_in, time_out (next day), is_late, is_undertime, is_off_shift, overtime_hours]
            [1,  '22:00', '06:00', false, false, false, 0],    // Perfect
            [2,  '22:00', '08:00', false, false, false, 2],    // OT 2 hours
            [3,  '22:15', '06:00', true,  false, false, 0],    // Late in
            [4,  '22:00', '05:30', false, true,  false, 0],   // Undertime
            [5,  '22:00', '09:00', false, false, false, 3],   // OT 3 hours
            [7,  '21:30', '06:00', false, false, true,  0],   // Off-shift (early)
            [8,  '22:00', '06:00', false, false, false, 0],    // Normal
            [9,  '23:00', '06:00', true,  false, false, 0],   // Very late
            [10, '22:00', '07:00', false, false, false, 1],   // OT 1 hour
            [11, '22:00', '05:00', false, true,  false, 0],   // Early out
            [12, '22:00', '06:00', false, false, false, 0],   // Normal
            [14, '22:00', '08:30', false, false, false, 2.5], // OT 2.5h
            [15, '22:00', '06:00', false, false, false, 0],   // Normal
            [16, '22:05', '06:00', false, false, false, 0],   // Slightly late
            [17, '22:00', '04:00', false, true,  false, 0],   // Big undertime
            [18, '22:00', '06:00', false, false, false, 0],   // Normal
            [19, '22:00', '10:00', false, false, false, 4],   // OT 4 hours
            [21, '22:00', '06:00', false, false, false, 0],   // Normal
            [22, '21:00', '06:00', false, false, true,  0],   // Off-shift (1h early)
            [23, '22:00', '06:00', false, false, false, 0],   // Normal
            [24, '22:30', '06:00', true,  false, false, 0],   // Late
            [25, '22:00', '06:30', false, false, false, 0.5], // Small OT
            [26, '22:00', '05:45', false, true,  false, 0],   // Small undertime
            [28, '22:00', '06:00', false, false, false, 0],   // Normal
            [29, '22:00', '11:00', false, false, false, 5],   // Big OT
            [30, '22:00', '06:00', false, false, false, 0],   // Normal
        ];

        foreach ($redScenarios as $scenario) {
            [$day, $in, $out, $late, $undertime, $offShift, $otHours] = $scenario;
            $date = Carbon::parse("2026-04-{$day}");
            $outDate = $date->copy()->addDay();
            
            Attendance::create([
                'employee_id'     => $empEmployee->employee_id,
                'work_date'       => $date->toDateString(),
                'time_in'         => $date->copy()->setTimeFromTimeString($in),
                'time_out'        => $outDate->setTimeFromTimeString($out),
                'is_late'         => $late,
                'is_undertime'    => $undertime,
                'is_auto_timeout' => false,
                'is_off_shift'    => $offShift,
                'overtime_hours'  => $otHours,
            ]);
        }

        // OT Requests — 10 per employee (mix of statuses)
        $otReasons = [
            'Month-end report finalization',
            'Quarterly audit preparation',
            'System backup and maintenance',
            'Urgent client deliverable',
            'Inventory count extension',
            'Payroll processing delay',
            'Server migration support',
            'Year-end closing activities',
            'Emergency patch deployment',
            'Additional QA testing required',
        ];

        // Bart OT requests
        for ($i = 0; $i < 10; $i++) {
            $date = $startDate->copy()->addDays(rand(1, 25));
            $status = ['approved', 'approved', 'approved', 'pending', 'rejected'][rand(0, 4)];
            $data = [
                'employee_id'         => $hrEmployee->employee_id,
                'work_date'           => $date->toDateString(),
                'requested_end_time'  => $date->copy()->setTime(19 + rand(0, 3), rand(0, 59)),
                'reason'              => $otReasons[$i],
                'status'              => $status,
            ];
            if (in_array($status, ['approved', 'rejected'])) {
                $data['approved_by'] = $approver->user_id;
                $data['approved_at'] = now();
            }
            OvertimeRequest::create($data);
        }

        // Red OT requests
        for ($i = 0; $i < 10; $i++) {
            $date = $startDate->copy()->addDays(rand(1, 25));
            $status = ['approved', 'approved', 'pending', 'pending', 'rejected'][rand(0, 4)];
            $endHour = 8 + rand(0, 4);
            $data = [
                'employee_id'         => $empEmployee->employee_id,
                'work_date'           => $date->toDateString(),
                'requested_end_time'  => $date->copy()->addDay()->setTime($endHour, rand(0, 59)),
                'reason'              => $otReasons[($i + 3) % count($otReasons)],
                'status'              => $status,
            ];
            if (in_array($status, ['approved', 'rejected'])) {
                $data['approved_by'] = $approver->user_id;
                $data['approved_at'] = now();
            }
            OvertimeRequest::create($data);
        }

        // Leave Requests — 8 per employee
        $leaveReasons = [
            'Family vacation trip',
            'Medical appointment',
            'Home repairs needed',
            'Childcare emergency',
            'Personal wellness day',
            'Bereavement leave',
            'Moving to new residence',
            'Jury duty summons',
        ];

        // Bart leave requests
        for ($i = 0; $i < 8; $i++) {
            $start = $startDate->copy()->addDays(rand(5, 28));
            $days = rand(1, 3);
            $types = [$vacationLeave, $sickLeave, $emergencyLeave];
            $status = ['approved', 'approved', 'pending', 'rejected'][rand(0, 3)];
            
            $data = [
                'employee_id'    => $hrEmployee->employee_id,
                'leave_type_id'  => $types[rand(0, 2)]->leave_type_id,
                'start_date'     => $start->toDateString(),
                'end_date'       => $start->copy()->addDays($days - 1)->toDateString(),
                'reason'         => $leaveReasons[$i],
                'status'         => $status,
            ];
            if (in_array($status, ['approved', 'rejected'])) {
                $data['approved_by'] = $approver->user_id;
                $data['approved_at'] = now();
                $data['remarks'] = $status === 'approved' ? 'Approved per policy.' : 'Insufficient documentation.';
            }
            LeaveRequest::create($data);
        }

        // Red leave requests
        for ($i = 0; $i < 8; $i++) {
            $start = $startDate->copy()->addDays(rand(5, 28));
            $days = rand(1, 3);
            $types = [$vacationLeave, $sickLeave, $emergencyLeave];
            $status = ['approved', 'pending', 'pending', 'rejected'][rand(0, 3)];
            
            $data = [
                'employee_id'    => $empEmployee->employee_id,
                'leave_type_id'  => $types[rand(0, 2)]->leave_type_id,
                'start_date'     => $start->toDateString(),
                'end_date'       => $start->copy()->addDays($days - 1)->toDateString(),
                'reason'         => $leaveReasons[($i + 4) % count($leaveReasons)],
                'status'         => $status,
            ];
            if (in_array($status, ['approved', 'rejected'])) {
                $data['approved_by'] = $approver->user_id;
                $data['approved_at'] = now();
                $data['remarks'] = $status === 'approved' ? 'Approved. Get well soon.' : 'Request denied per guidelines.';
            }
            LeaveRequest::create($data);
        }
    }
}
