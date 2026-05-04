<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;

class ProfileController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = $user->employee;
        $balances = $employee
            ? LeaveBalance::with('leaveType')->where('employee_id', $employee->employee_id)->get()
            : collect();

        return view('employee.profile', compact('user', 'employee', 'balances'));
    }
}
