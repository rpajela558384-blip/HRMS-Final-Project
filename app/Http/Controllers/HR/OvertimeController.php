<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Notifications\OvertimeStatusUpdated;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function storeSelf(Request $request)
    {
        $employee = auth()->user()->employee;

        $validated = $request->validate([
            'work_date'          => 'required|date',
            'requested_end_time' => 'required|date|after:work_date',
            'reason'             => 'required|string|max:500',
        ]);

        // Validate OT end is after shift end
        if ($employee->shift) {
            $shiftEnd = \Carbon\Carbon::parse($validated['work_date'])
                ->setTimeFromTimeString($employee->shift->end_time);
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

        $existing = OvertimeRequest::where('employee_id', $employee->employee_id)
            ->whereDate('work_date', $validated['work_date'])->first();

        if ($existing) {
            return back()->withErrors(['error' => 'You already have an overtime request for this date.']);
        }

        // HR OT is auto-approved
        OvertimeRequest::create([
            'employee_id'        => $employee->employee_id,
            'work_date'          => $validated['work_date'],
            'requested_end_time' => $validated['requested_end_time'],
            'reason'             => $validated['reason'],
            'status'             => 'approved',
            'approved_by'        => auth()->id(),
            'approved_at'        => now(),
        ]);

        return back()->with('success', 'Overtime request auto-approved.');
    }

    public function approve(Request $request, OvertimeRequest $ot)
    {
        if (!$ot->isPending()) {
            return back()->withErrors(['error' => 'Request already processed.']);
        }

        $ot->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $ot->employee->user->notify(new OvertimeStatusUpdated($ot));

        return back()->with('success', 'Overtime request approved.');
    }

    public function reject(Request $request, OvertimeRequest $ot)
    {
        if (!$ot->isPending()) {
            return back()->withErrors(['error' => 'Request already processed.']);
        }

        $ot->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $ot->employee->user->notify(new OvertimeStatusUpdated($ot));

        return back()->with('success', 'Overtime request rejected.');
    }
}
