<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\WorkingDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $query = Attendance::where('employee_id', $employee->employee_id)
            ->orderBy('work_date', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('work_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('work_date', '<=', $request->to);
        }
        if ($request->filled('flag')) {
            match ($request->flag) {
                'late'      => $query->where('is_late', true),
                'undertime' => $query->where('is_undertime', true),
                'overtime'  => $query->where('overtime_hours', '>', 0),
                'off_shift' => $query->where('is_off_shift', true),
                default     => null,
            };
        }

        $records         = $query->paginate(10)->withQueryString();
        $workingDay      = WorkingDay::today();
        $todayAttendance = Attendance::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', today())
            ->first();

        return view('employee.attendance', compact('records', 'workingDay', 'todayAttendance', 'employee'));
    }

    public function timeIn(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->withErrors(['error' => 'No employee profile found.']);
        }

        // BR-03 / BR-17
        if (!WorkingDay::todayIsOpen()) {
            return back()->withErrors(['error' => 'Today is not an open working day.']);
        }

        // BR-02
        $existing = Attendance::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', today())->first();

        if ($existing) {
            return back()->withErrors(['error' => 'You have already timed in today.']);
        }

        $shift      = $employee->shift;
        $now        = Carbon::now();
        $isLate     = false;
        $isOffShift = false;

        if ($shift) {
            $shiftStart    = Carbon::today()->setTimeFromTimeString($shift->start_time);
            $shiftEnd      = Carbon::today()->setTimeFromTimeString($shift->end_time);
            // Overnight shift: end is next day
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
            $windowStart = $shiftStart->copy()->subHour();
            $windowEnd   = $shiftEnd->copy()->addHour();
            $isOffShift  = $now->lessThan($windowStart) || $now->greaterThan($windowEnd);
            if (!$isOffShift) {
                $isLate = $now->greaterThan($shiftStart->copy()->addMinutes($shift->grace_minutes));
            }
        }

        Attendance::create([
            'employee_id' => $employee->employee_id,
            'work_date'   => today(),
            'time_in'     => $now,
            'is_late'     => $isLate,
            'is_off_shift'=> $isOffShift,
        ]);

        return back()->with('success', 'Time-in recorded at ' . $now->format('h:i A'));
    }

    public function timeOut(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->withErrors(['error' => 'No employee profile found.']);
        }

        // BR-01
        $attendance = Attendance::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', today())->first();

        if (!$attendance || !$attendance->time_in) {
            return back()->withErrors(['error' => 'You have not timed in today.']);
        }

        if ($attendance->time_out) {
            return back()->withErrors(['error' => 'You have already timed out today.']);
        }

        $shift = $employee->shift;
        $now   = Carbon::now();

        $isUndertime   = false;
        $overtimeHours = 0;

        if ($shift) {
            $shiftEnd = Carbon::today()->setTimeFromTimeString($shift->end_time);

            // Check for approved OT
            $approvedOT = OvertimeRequest::where('employee_id', $employee->employee_id)
                ->whereDate('work_date', today())
                ->where('status', 'approved')
                ->first();

            if ($approvedOT) {
                $cutoff      = Carbon::parse($approvedOT->requested_end_time)->addMinutes(15);
                $isUndertime = $now->lessThan($shiftEnd);
                if ($now->greaterThan(Carbon::parse($approvedOT->requested_end_time))) {
                    $overtimeHours = round($shiftEnd->diffInMinutes($now) / 60, 2);
                }
            } else {
                $isUndertime = $now->lessThan($shiftEnd);
                if ($now->greaterThan($shiftEnd)) {
                    $overtimeHours = round($shiftEnd->diffInMinutes($now) / 60, 2);
                }
            }
        }

        $attendance->update([
            'time_out'       => $now,
            'is_undertime'   => $isUndertime,
            'overtime_hours' => $overtimeHours,
        ]);

        return back()->with('success', 'Time-out recorded at ' . $now->format('h:i A'));
    }
}
