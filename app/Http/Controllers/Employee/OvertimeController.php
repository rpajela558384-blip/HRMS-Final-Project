<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function store(Request $request)
    {
        $employee = auth()->user()->employee;

        $validated = $request->validate([
            'work_date'           => 'required|date',
            'requested_end_time'  => 'required|date|after:work_date',
            'reason'              => 'required|string|max:500',
        ]);

        // Validate OT end is after shift end
        if ($employee->shift) {
            $shiftEnd = \Carbon\Carbon::parse($validated['work_date'])
                ->setTimeFromTimeString($employee->shift->end_time);
            // Handle overnight shift: if shift end < shift start, shift ends next day
            $shiftStart = \Carbon\Carbon::parse($validated['work_date'])
                ->setTimeFromTimeString($employee->shift->start_time);
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
            $requestedEnd = \Carbon\Carbon::parse($validated['requested_end_time']);
            if ($requestedEnd->lessThanOrEqualTo($shiftEnd)) {
                return back()->withErrors(['error' => 'Overtime end time must be after your shift end (' . $shiftEnd->format('h:i A') . ').']);
            }
        }

        // BR-08: one OT request per day
        $existing = OvertimeRequest::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', $validated['work_date'])
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'You already have an overtime request for this date.']);
        }

        OvertimeRequest::create([
            'employee_id'        => $employee->employee_id,
            'work_date'          => $validated['work_date'],
            'requested_end_time' => $validated['requested_end_time'],
            'reason'             => $validated['reason'],
            'status'             => 'pending',
        ]);

        return back()->with('success', 'Overtime request submitted.');
    }
}
