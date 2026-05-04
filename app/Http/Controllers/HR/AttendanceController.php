<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
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

        // Own attendance
        $myQuery = Attendance::where('employee_id', $employee->employee_id)
            ->orderBy('work_date', 'desc');

        if ($request->filled('my_from')) {
            $myQuery->whereDate('work_date', '>=', $request->my_from);
        }
        if ($request->filled('my_to')) {
            $myQuery->whereDate('work_date', '<=', $request->my_to);
        }
        if ($request->filled('my_flag')) {
            match ($request->my_flag) {
                'late'      => $myQuery->where('is_late', true),
                'undertime' => $myQuery->where('is_undertime', true),
                'overtime'  => $myQuery->where('overtime_hours', '>', 0),
                'off_shift' => $myQuery->where('is_off_shift', true),
                default     => null,
            };
        }

        $myRecords       = $myQuery->paginate(10, ['*'], 'my_page')->withQueryString();
        $workingDay      = WorkingDay::today();
        $todayAttendance = Attendance::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', today())->first();

        // All employees attendance (exclude admin)
        $allQuery = Attendance::with(['employee.user'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('work_date', 'desc');

        if ($request->filled('all_from')) {
            $allQuery->whereDate('work_date', '>=', $request->all_from);
        }
        if ($request->filled('all_to')) {
            $allQuery->whereDate('work_date', '<=', $request->all_to);
        }
        if ($request->filled('search_emp')) {
            $allQuery->whereHas('employee.user', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search_emp . '%');
            })->orWhereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search_emp . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search_emp . '%');
            });
        }
        if ($request->filled('flag')) {
            match ($request->flag) {
                'late'      => $allQuery->where('is_late', true),
                'undertime' => $allQuery->where('is_undertime', true),
                'overtime'  => $allQuery->where('overtime_hours', '>', 0),
                default     => null,
            };
        }

        $allRecords  = $allQuery->paginate(15, ['*'], 'all_page')->withQueryString();
        $activeTab   = $request->get('tab', 'mine');

        return view('hr.attendance', compact(
            'employee', 'myRecords', 'allRecords', 'workingDay',
            'todayAttendance', 'activeTab'
        ));
    }

    public function timeIn(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!WorkingDay::todayIsOpen()) {
            return back()->withErrors(['error' => 'Today is not an open working day.']);
        }

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
            $shiftStart  = Carbon::today()->setTimeFromTimeString($shift->start_time);
            $shiftEnd    = Carbon::today()->setTimeFromTimeString($shift->end_time);
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
            'employee_id'  => $employee->employee_id,
            'work_date'    => today(),
            'time_in'      => $now,
            'is_late'      => $isLate,
            'is_off_shift' => $isOffShift,
        ]);

        return back()->with('success', 'Time-in recorded at ' . $now->format('h:i A'));
    }

    public function timeOut(Request $request)
    {
        $employee   = auth()->user()->employee;
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
            $shiftEnd   = Carbon::today()->setTimeFromTimeString($shift->end_time);
            $approvedOT = OvertimeRequest::where('employee_id', $employee->employee_id)
                ->whereDate('work_date', today())->where('status', 'approved')->first();

            if ($approvedOT) {
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

    public function undoTimeout(Request $request, Attendance $attendance)
    {
        if (!$attendance->time_out) {
            return back()->with('error', 'This record has no time-out to undo.');
        }

        if ($attendance->work_date->toDateString() !== today()->toDateString()) {
            return back()->with('error', 'Undo time-out is only allowed for today\'s records.');
        }

        $attendance->update([
            'time_out'        => null,
            'is_undertime'    => false,
            'overtime_hours'  => 0,
            'is_auto_timeout' => false,
        ]);

        return redirect()->route('hr.attendance.index', ['tab' => 'all'])
            ->with('success', 'Time-out has been undone for ' . ($attendance->employee?->full_name ?: 'employee') . '.');
    }
}
