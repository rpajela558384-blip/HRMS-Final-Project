<x-app-layout>
<x-slot name="title">Employee Detail</x-slot>

<div class="max-w-4xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('hr.employees.index') }}" class="text-slate-400 hover:text-teal-600 transition text-sm">← Back to Employees</a>
    </div>

    {{-- Profile Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold text-2xl">
                {{ strtoupper(substr($employee->user?->username ?? 'E', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-800">{{ $employee->full_name ?: ($employee->user?->username ?? '—') }}</h2>
                <div class="flex gap-2 mt-1">
                    <x-status-badge :status="$employee->user?->role ?? 'employee'" />
                    <x-status-badge :status="$employee->user?->status ?? 'active'" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 border-t border-slate-100 pt-4">
            <div><p class="text-xs text-slate-400 uppercase tracking-wide">Username</p><p class="font-medium text-slate-800 mt-0.5">{{ $employee->user?->username ?? '—' }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase tracking-wide">Full Name</p><p class="font-medium text-slate-800 mt-0.5">{{ $employee->full_name ?: '—' }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase tracking-wide">Hire Date</p><p class="font-medium text-slate-800 mt-0.5">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase tracking-wide">Years of Service</p><p class="font-medium text-slate-800 mt-0.5">{{ $employee->years_of_service }} years</p></div>
            @if($employee->shift)
                <div><p class="text-xs text-slate-400 uppercase tracking-wide">Shift</p><p class="font-medium text-slate-800 mt-0.5">{{ $employee->shift->shift_name }}</p></div>
                <div><p class="text-xs text-slate-400 uppercase tracking-wide">Shift Hours</p>
                    <p class="font-medium text-slate-800 mt-0.5">
                        {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} –
                        {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}
                        <span class="text-xs text-slate-400">({{ $employee->shift->grace_minutes }}min grace)</span>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Leave Balances --}}
    @if($employee->leaveBalances->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Leave Balances</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($employee->leaveBalances as $bal)
                <div class="bg-slate-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">{{ $bal->leaveType->name }}</p>
                    <p class="text-2xl font-bold text-teal-600 mt-1">{{ $bal->remaining_days }}</p>
                    <p class="text-xs text-slate-400">days left</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Attendance --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Recent Attendance (last 10)</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b"><tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time In</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time Out</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Hours</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Flags</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($recentAttendance as $rec)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $rec->work_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->total_hours > 0 ? $rec->total_hours.'h' : '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1 flex-wrap">
                                @if($rec->is_late)<x-status-badge status="late" />@endif
                                @if($rec->is_undertime)<x-status-badge status="undertime" />@endif
                                @if($rec->overtime_hours > 0)<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">OT</span>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No attendance records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
