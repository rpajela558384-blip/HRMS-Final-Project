<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function employeeSearch($query, string $search, string $relation = 'employee'): void
    {
        $query->whereHas($relation, function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$search}%"));
        });
    }

    public function index(Request $request)
    {
        // Attendance report
        $attendanceQuery = Attendance::with(['employee.user'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('work_date', 'desc');
        if ($request->filled('att_employee')) {
            $this->employeeSearch($attendanceQuery, $request->att_employee);
        }
        if ($request->filled('att_from')) {
            $attendanceQuery->whereDate('work_date', '>=', $request->att_from);
        }
        if ($request->filled('att_to')) {
            $attendanceQuery->whereDate('work_date', '<=', $request->att_to);
        }
        $attendanceRecords = $attendanceQuery->paginate(15, ['*'], 'att_page')->withQueryString();

        // Leave report
        $leaveQuery = LeaveRequest::with(['employee.user', 'leaveType'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('created_at', 'desc');
        if ($request->filled('leave_employee')) {
            $this->employeeSearch($leaveQuery, $request->leave_employee);
        }
        if ($request->filled('leave_status')) {
            $leaveQuery->where('status', $request->leave_status);
        }
        $leaveRecords = $leaveQuery->paginate(15, ['*'], 'leave_page')->withQueryString();

        // OT report
        $otQuery = OvertimeRequest::with(['employee.user'])
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->orderBy('created_at', 'desc');
        if ($request->filled('ot_employee')) {
            $this->employeeSearch($otQuery, $request->ot_employee);
        }
        if ($request->filled('ot_status')) {
            $otQuery->where('status', $request->ot_status);
        }
        $otRecords = $otQuery->paginate(15, ['*'], 'ot_page')->withQueryString();

        $activeTab = $request->get('tab', 'attendance');

        // ── Chart data ──────────────────────────────────────────────
        // Attendance: late vs on-time vs undertime counts per day (last 14 days)
        $attChartBase = Attendance::selectRaw('work_date, SUM(is_late) as late_count, SUM(is_undertime) as undertime_count, COUNT(*) as total')
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->whereDate('work_date', '>=', now()->subDays(13))
            ->groupBy('work_date')
            ->orderBy('work_date')
            ->get();
        $attChartLabels  = $attChartBase->pluck('work_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->values();
        $attChartLate    = $attChartBase->pluck('late_count')->values();
        $attChartUnder   = $attChartBase->pluck('undertime_count')->values();
        $attChartTotal   = $attChartBase->pluck('total')->values();

        // Leave: approved days by leave type
        $leaveByType = LeaveRequest::where('status', 'approved')
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->with('leaveType')
            ->get()
            ->groupBy('leave_type_id')
            ->map(fn($g) => ['label' => $g->first()->leaveType->name, 'total' => $g->sum('days_requested')]);
        $leaveTypeLabels = $leaveByType->pluck('label')->values();
        $leaveTypeTotals = $leaveByType->pluck('total')->values();

        // Leave: status breakdown
        $leaveStatusCounts = LeaveRequest::selectRaw('status, COUNT(*) as cnt')
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->groupBy('status')->pluck('cnt', 'status');

        // OT: approved vs pending vs rejected
        $otStatusCounts = OvertimeRequest::selectRaw('status, COUNT(*) as cnt')
            ->whereHas('employee.user', fn($u) => $u->where('role', '!=', 'admin'))
            ->groupBy('status')->pluck('cnt', 'status');

        return view('hr.reports', compact(
            'attendanceRecords', 'leaveRecords', 'otRecords', 'activeTab',
            'attChartLabels', 'attChartLate', 'attChartUnder', 'attChartTotal',
            'leaveTypeLabels', 'leaveTypeTotals', 'leaveStatusCounts',
            'otStatusCounts'
        ));
    }

    public function attendancePdf(Request $request)
    {
        $query = Attendance::with(['employee.user'])->orderBy('work_date', 'desc');
        if ($request->filled('att_employee')) {
            $this->employeeSearch($query, $request->att_employee);
        }
        if ($request->filled('att_from')) {
            $query->whereDate('work_date', '>=', $request->att_from);
        }
        if ($request->filled('att_to')) {
            $query->whereDate('work_date', '<=', $request->att_to);
        }
        $records = $query->get();
        $pdf = Pdf::loadView('hr.reports.attendance-pdf', compact('records'))->setPaper('a4', 'landscape');
        return $pdf->download('attendance-report.pdf');
    }

    public function leavePdf(Request $request)
    {
        $query = LeaveRequest::with(['employee.user', 'leaveType'])->orderBy('created_at', 'desc');
        if ($request->filled('leave_employee')) {
            $this->employeeSearch($query, $request->leave_employee);
        }
        if ($request->filled('leave_status')) {
            $query->where('status', $request->leave_status);
        }
        $records = $query->get();
        $pdf = Pdf::loadView('hr.reports.leave-pdf', compact('records'))->setPaper('a4', 'landscape');
        return $pdf->download('leave-report.pdf');
    }

    public function overtimePdf(Request $request)
    {
        $query = OvertimeRequest::with(['employee.user'])->orderBy('created_at', 'desc');
        if ($request->filled('ot_employee')) {
            $this->employeeSearch($query, $request->ot_employee);
        }
        if ($request->filled('ot_status')) {
            $query->where('status', $request->ot_status);
        }
        $records = $query->get();
        $pdf = Pdf::loadView('hr.reports.overtime-pdf', compact('records'))->setPaper('a4', 'landscape');
        return $pdf->download('overtime-report.pdf');
    }
}
