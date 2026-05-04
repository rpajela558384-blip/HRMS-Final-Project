<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use App\Notifications\LeaveStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        // Own leave
        $myLeaveQuery = LeaveRequest::where('employee_id', $employee->employee_id)
            ->with('leaveType')->orderBy('created_at', 'desc');

        if ($request->filled('my_leave_status')) {
            $myLeaveQuery->where('status', $request->my_leave_status);
        }
        if ($request->filled('my_leave_type')) {
            $myLeaveQuery->where('leave_type_id', $request->my_leave_type);
        }

        // Own OT
        $myOtQuery = OvertimeRequest::where('employee_id', $employee->employee_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('my_ot_status')) {
            $myOtQuery->where('status', $request->my_ot_status);
        }

        // All leave requests (for validation)
        $allLeaveQuery = LeaveRequest::with(['employee.user', 'leaveType'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('created_at', 'desc');

        if ($request->filled('leave_status')) {
            $allLeaveQuery->where('status', $request->leave_status);
        }
        if ($request->filled('leave_type')) {
            $allLeaveQuery->where('leave_type_id', $request->leave_type);
        }
        if ($request->filled('leave_employee')) {
            $allLeaveQuery->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->leave_employee . '%')
                  ->orWhere('last_name', 'like', '%' . $request->leave_employee . '%');
            });
        }

        // All OT requests (for validation)
        $allOtQuery = OvertimeRequest::with(['employee.user'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('created_at', 'desc');

        if ($request->filled('ot_status')) {
            $allOtQuery->where('status', $request->ot_status);
        }
        if ($request->filled('ot_employee')) {
            $allOtQuery->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->ot_employee . '%')
                  ->orWhere('last_name', 'like', '%' . $request->ot_employee . '%');
            });
        }

        $myLeave   = $myLeaveQuery->paginate(10, ['*'], 'my_leave')->withQueryString();
        $myOt      = $myOtQuery->paginate(10, ['*'], 'my_ot')->withQueryString();
        $allLeave  = $allLeaveQuery->paginate(10, ['*'], 'all_leave')->withQueryString();
        $allOt     = $allOtQuery->paginate(10, ['*'], 'all_ot')->withQueryString();
        $leaveTypes = LeaveType::all();
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->employee_id)->get();
        $activeTab = $request->get('tab', 'my_leave');

        return view('hr.requests', compact(
            'myLeave', 'myOt', 'allLeave', 'allOt',
            'leaveTypes', 'leaveBalances', 'activeTab', 'employee'
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

        $daysRequested = Carbon::parse($validated['start_date'])
            ->diffInDays(Carbon::parse($validated['end_date'])) + 1;

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

    public function approve(Request $request, LeaveRequest $leave)
    {
        if (!$leave->isPending()) {
            return back()->withErrors(['error' => 'Request already processed.']);
        }

        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'remarks'     => $request->remarks,
        ]);

        // BR-16: Deduct balance
        $daysRequested = $leave->start_date->diffInDays($leave->end_date) + 1;
        $balance = LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)->first();
        if ($balance) {
            $balance->decrement('remaining_days', $daysRequested);
        }

        // Create attendance placeholders for leave days
        $current = $leave->start_date->copy();
        while ($current->lte($leave->end_date)) {
            Attendance::firstOrCreate(
                ['employee_id' => $leave->employee_id, 'work_date' => $current->toDateString()],
                ['time_in' => null, 'time_out' => null]
            );
            $current->addDay();
        }

        // Notify employee
        $leave->employee->user->notify(new LeaveStatusUpdated($leave));

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        if (!$leave->isPending()) {
            return back()->withErrors(['error' => 'Request already processed.']);
        }

        $request->validate(['remarks' => 'nullable|string|max:500']);

        $leave->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'remarks'     => $request->remarks,
        ]);

        $leave->employee->user->notify(new LeaveStatusUpdated($leave));

        return back()->with('success', 'Leave request rejected.');
    }
}
