<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkingDay;
use Illuminate\Http\Request;

class WorkingDayController extends Controller
{
    public function open(Request $request)
    {
        $today = WorkingDay::today();

        if ($today && $today->isOpen()) {
            return back()->withErrors(['error' => 'Today is already open.']);
        }

        WorkingDay::updateOrCreate(
            ['work_date' => today()],
            ['status' => 'open', 'opened_by' => auth()->id()]
        );

        return back()->with('success', 'Working day opened. Employees can now time in.');
    }

    public function close(Request $request)
    {
        $today = WorkingDay::today();

        if (!$today || !$today->isOpen()) {
            return back()->withErrors(['error' => 'Today is not open.']);
        }

        $today->update(['status' => 'closed']);

        // Auto-timeout all employees still timed in today
        $timedOut = Attendance::whereDate('work_date', today())
            ->whereNotNull('time_in')
            ->whereNull('time_out')
            ->get();

        foreach ($timedOut as $record) {
            $record->update([
                'time_out'        => now(),
                'is_auto_timeout' => true,
                'is_undertime'    => true,
            ]);
        }

        $msg = 'Working day closed.';
        if ($timedOut->count() > 0) {
            $msg .= ' ' . $timedOut->count() . ' employee(s) were automatically timed out.';
        }

        return back()->with('success', $msg);
    }

    public function reopen(Request $request)
    {
        $today = WorkingDay::today();

        if (!$today || $today->isOpen()) {
            return back()->with('error', 'Today is not closed, nothing to reopen.');
        }

        $today->update(['status' => 'open']);

        return back()->with('success', 'Working day re-opened.');
    }
}
