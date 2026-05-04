<x-app-layout>
<x-slot name="title">Reports</x-slot>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="space-y-6" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-slate-800">Reports</h1>

        {{-- PDF Export Buttons --}}
        <div x-show="tab === 'attendance'">
            <a href="{{ route('hr.reports.attendance.pdf', request()->query()) }}"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition">
                ↓ Export PDF
            </a>
        </div>
        <div x-show="tab === 'leave'" x-cloak>
            <a href="{{ route('hr.reports.leave.pdf', request()->query()) }}"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition">
                ↓ Export PDF
            </a>
        </div>
        <div x-show="tab === 'overtime'" x-cloak>
            <a href="{{ route('hr.reports.overtime.pdf', request()->query()) }}"
               class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm transition">
                ↓ Export PDF
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <button @click="tab = 'attendance'" :class="tab==='attendance' ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-5 py-2 rounded-lg text-sm transition">Attendance</button>
        <button @click="tab = 'leave'"      :class="tab==='leave'      ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-5 py-2 rounded-lg text-sm transition">Leave</button>
        <button @click="tab = 'overtime'"   :class="tab==='overtime'   ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-5 py-2 rounded-lg text-sm transition">Overtime</button>
    </div>

    {{-- Attendance Report --}}
    <div x-show="tab === 'attendance'">
        {{-- Attendance Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
            <h3 class="text-sm font-semibold text-slate-600 mb-3">Attendance Flags — Last 14 Days</h3>
            <div class="relative h-52">
                <canvas id="attChart"></canvas>
            </div>
        </div>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4 items-center" x-data>
            <input type="hidden" name="tab" value="attendance" />
            <input type="text" name="att_employee" value="{{ request('att_employee') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-44" />
            <input type="date" name="att_from" value="{{ request('att_from') }}" placeholder="From" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            <input type="date" name="att_to" value="{{ request('att_to') }}" placeholder="To" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time In</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time Out</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Hours</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">OT Hours</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Flags</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($attendanceRecords as $rec)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $rec->employee?->full_name ?: $rec->employee?->user?->username }}</td>
                            <td class="px-4 py-3">{{ $rec->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->total_hours > 0 ? $rec->total_hours.'h' : '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->overtime_hours > 0 ? $rec->overtime_hours.'h' : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @if($rec->is_late)<x-status-badge status="late" />@endif
                                    @if($rec->is_undertime)<x-status-badge status="undertime" />@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($attendanceRecords->hasPages())<div class="px-4 py-3 border-t">{{ $attendanceRecords->links() }}</div>@endif
        </div>
    </div>

    {{-- Leave Report --}}
    <div x-show="tab === 'leave'" x-cloak>
        {{-- Leave Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-sm font-semibold text-slate-600 mb-3">Approved Days by Leave Type</h3>
                <div class="relative h-52">
                    <canvas id="leaveTypeChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-sm font-semibold text-slate-600 mb-3">Leave Requests by Status</h3>
                <div class="relative h-52">
                    <canvas id="leaveStatusChart"></canvas>
                </div>
            </div>
        </div>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4 items-center" x-data>
            <input type="hidden" name="tab" value="leave" />
            <input type="text" name="leave_employee" value="{{ request('leave_employee') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-44" />
            <select name="leave_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('leave_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('leave_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('leave_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Dates</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Days</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Submitted</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leaveRecords as $rec)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $rec->employee?->full_name ?: $rec->employee?->user?->username }}</td>
                            <td class="px-4 py-3">{{ $rec->leaveType->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $rec->start_date->format('M d') }} – {{ $rec->end_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->days_requested }}</td>
                            <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $rec->reason }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$rec->status" /></td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $rec->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($leaveRecords->hasPages())<div class="px-4 py-3 border-t">{{ $leaveRecords->links() }}</div>@endif
        </div>
    </div>

    {{-- Overtime Report --}}
    <div x-show="tab === 'overtime'" x-cloak>
        {{-- OT Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
            <h3 class="text-sm font-semibold text-slate-600 mb-3">Overtime Requests by Status</h3>
            <div class="relative h-52">
                <canvas id="otStatusChart"></canvas>
            </div>
        </div>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4 items-center" x-data>
            <input type="hidden" name="tab" value="overtime" />
            <input type="text" name="ot_employee" value="{{ request('ot_employee') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-44" />
            <select name="ot_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('ot_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('ot_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('ot_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Requested End</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Submitted</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($otRecords as $rec)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $rec->employee?->full_name ?: $rec->employee?->user?->username }}</td>
                            <td class="px-4 py-3">{{ $rec->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($rec->requested_end_time)->format('h:i A') }}</td>
                            <td class="px-4 py-3 text-slate-500 max-w-xs truncate">{{ $rec->reason }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$rec->status" /></td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $rec->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($otRecords->hasPages())<div class="px-4 py-3 border-t">{{ $otRecords->links() }}</div>@endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const gridColor = 'rgba(148,163,184,0.15)';

    // ── Attendance Chart ─────────────────────────────────────────
    new Chart(document.getElementById('attChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($attChartLabels) !!},
            datasets: [
                {
                    label: 'Total',
                    data: {!! json_encode($attChartTotal) !!},
                    backgroundColor: 'rgba(20,184,166,0.25)',
                    borderColor: 'rgba(20,184,166,0.8)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                },
                {
                    label: 'Late',
                    data: {!! json_encode($attChartLate) !!},
                    backgroundColor: 'rgba(251,146,60,0.7)',
                    borderColor: 'rgba(234,88,12,0.8)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                },
                {
                    label: 'Undertime',
                    data: {!! json_encode($attChartUnder) !!},
                    backgroundColor: 'rgba(148,163,184,0.5)',
                    borderColor: 'rgba(100,116,139,0.7)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1, font: { size: 11 } } }
            }
        }
    });

    // ── Leave Type Chart ─────────────────────────────────────────
    new Chart(document.getElementById('leaveTypeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($leaveTypeLabels) !!},
            datasets: [{
                label: 'Approved Days',
                data: {!! json_encode($leaveTypeTotals) !!},
                backgroundColor: ['rgba(20,184,166,0.6)','rgba(99,102,241,0.6)','rgba(251,146,60,0.6)','rgba(244,63,94,0.6)'],
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { stepSize: 1, font: { size: 11 } } }
            }
        }
    });

    // ── Leave Status Doughnut ─────────────────────────────────────
    const lsc = {!! json_encode($leaveStatusCounts) !!};
    new Chart(document.getElementById('leaveStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending','Approved','Rejected'],
            datasets: [{
                data: [lsc.pending ?? 0, lsc.approved ?? 0, lsc.rejected ?? 0],
                backgroundColor: ['rgba(251,191,36,0.8)','rgba(20,184,166,0.8)','rgba(244,63,94,0.8)'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });

    // ── OT Status Doughnut ───────────────────────────────────────
    const osc = {!! json_encode($otStatusCounts) !!};
    new Chart(document.getElementById('otStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending','Approved','Rejected'],
            datasets: [{
                data: [osc.pending ?? 0, osc.approved ?? 0, osc.rejected ?? 0],
                backgroundColor: ['rgba(251,191,36,0.8)','rgba(99,102,241,0.8)','rgba(244,63,94,0.8)'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
});
</script>
</x-app-layout>
