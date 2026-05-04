<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\WorkingDay;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $workingDay      = WorkingDay::today();
        $todayAttendance = null;
        $cutoffHours     = 0;

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('work_date', today())->first();

            $cutoffStart = Carbon::now()->startOfMonth()->day <= 15
                ? Carbon::now()->startOfMonth()
                : Carbon::now()->startOfMonth()->addDays(15);
            $cutoffEnd = $cutoffStart->copy()->addDays(14);

            $cutoffHours = Attendance::where('employee_id', $employee->employee_id)
                ->whereBetween('work_date', [$cutoffStart, $cutoffEnd])
                ->whereNotNull('time_out')
                ->get()
                ->sum('total_hours');
        }

        $pendingLeave    = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertime = OvertimeRequest::where('status', 'pending')->count();
        $notifications   = $user->unreadNotifications()->latest()->take(5)->get();

        return view('hr.dashboard', compact(
            'user', 'employee', 'workingDay', 'todayAttendance',
            'cutoffHours', 'pendingLeave', 'pendingOvertime', 'notifications'
        ));
    }
}
