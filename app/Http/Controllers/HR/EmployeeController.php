<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\Shift;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'shift'])->whereHas('user', fn($u) => $u->where('role', '!=', 'admin'));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        if ($request->filled('status')) {
            $query->whereHas('user', fn($u) => $u->where('status', $request->status));
        }

        $employees = $query->paginate(10)->withQueryString();
        $shifts    = Shift::all();

        return view('hr.employees.index', compact('employees', 'shifts'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'shift', 'leaveBalances.leaveType']);

        $recentAttendance = Attendance::where('employee_id', $employee->employee_id)
            ->orderBy('work_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('hr.employees.show', compact('employee', 'recentAttendance'));
    }
}
