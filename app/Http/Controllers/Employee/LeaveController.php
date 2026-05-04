<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $leaveQuery = LeaveRequest::where('employee_id', $employee->employee_id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc');

        if ($request->filled('leave_status')) {
            $leaveQuery->where('status', $request->leave_status);
        }
        if ($request->filled('leave_type_id')) {
            $leaveQuery->where('leave_type_id', $request->leave_type_id);
        }

        $otQuery = OvertimeRequest::where('employee_id', $employee->employee_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('ot_status')) {
            $otQuery->where('status', $request->ot_status);
        }

        $leaveRequests  = $leaveQuery->paginate(10, ['*'], 'leave_page')->withQueryString();
        $otRequests     = $otQuery->paginate(10, ['*'], 'ot_page')->withQueryString();
        $leaveTypes     = LeaveType::all();
        $leaveBalances  = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->employee_id)->get();

        $activeTab = $request->get('tab', 'leave');

        return view('employee.requests', compact(
            'leaveRequests', 'otRequests', 'leaveTypes', 'leaveBalances', 'activeTab'
        ));
    }

    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:500',
        ]);

        // BR-14: check balance
        $daysRequested = \Carbon\Carbon::parse($validated['start_date'])
            ->diffInDays(\Carbon\Carbon::parse($validated['end_date'])) + 1;

        $balance = LeaveBalance::where('employee_id', $employee->employee_id)
            ->where('leave_type_id', $validated['leave_type_id'])->first();

        if (!$balance || $balance->remaining_days < $daysRequested) {
            return back()->withErrors(['error' => 'Insufficient leave balance.'])->withInput();
        }

        LeaveRequest::create([
            'employee_id'   => $employee->employee_id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'],
            'reason'        => $validated['reason'],
            'status'        => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }
}
