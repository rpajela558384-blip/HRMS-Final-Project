<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkingDay;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $todayAttendance = null;
        $workingDay      = WorkingDay::today();
        $cutoffHours     = 0;

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('work_date', today())
                ->first();

            // 15-day cutoff hours
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

        $notifications = $user->unreadNotifications()->latest()->take(5)->get();

        return view('employee.dashboard', compact(
            'user', 'employee', 'todayAttendance', 'workingDay', 'cutoffHours', 'notifications'
        ));
    }
}
