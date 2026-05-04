<x-app-layout>
<x-slot name="title">Attendance</x-slot>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">My Attendance</h1>
        <div class="flex gap-3">
            @if($workingDay && $workingDay->isOpen())
                @if(!$todayAttendance)
                    <x-confirm-modal id="att-time-in" title="Time In" message="Record your time-in for today?">
                        <x-slot name="trigger">
                            <button @click="$dispatch('open-modal-att-time-in')"
                                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
                                ⏱ Time In
                            </button>
                        </x-slot>
                        <x-slot name="action">
                            <form method="POST" action="{{ route('employee.attendance.time-in') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold transition">Confirm</button>
                            </form>
                        </x-slot>
                    </x-confirm-modal>
                @elseif($todayAttendance->time_in && !$todayAttendance->time_out)
                    <x-confirm-modal id="att-time-out" title="Time Out" message="Record your time-out for today?">
                        <x-slot name="trigger">
                            <button @click="$dispatch('open-modal-att-time-out')"
                                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm transition">
                                ⏹ Time Out
                            </button>
                        </x-slot>
                        <x-slot name="action">
                            <form method="POST" action="{{ route('employee.attendance.time-out') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition">Confirm</button>
                            </form>
                        </x-slot>
                    </x-confirm-modal>
                @else
                    <span class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl text-sm">Done for today</span>
                @endif
            @else
                <span class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl text-sm">Day is Closed</span>
            @endif
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4" x-data>
        <form method="GET" action="{{ route('employee.attendance.index') }}"
              x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Flag</label>
                <select name="flag" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">All Flags</option>
                    <option value="late"      {{ request('flag') === 'late'      ? 'selected' : '' }}>Late</option>
                    <option value="undertime" {{ request('flag') === 'undertime' ? 'selected' : '' }}>Undertime</option>
                    <option value="overtime"  {{ request('flag') === 'overtime'  ? 'selected' : '' }}>Overtime</option>
                    <option value="off_shift" {{ request('flag') === 'off_shift' ? 'selected' : '' }}>Off-shift</option>
                </select>
            </div>
            @if(request('from') || request('to') || request('flag'))
                <a href="{{ route('employee.attendance.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-red-500 transition self-end">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-10">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Time In</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Time Out</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Hours</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($records as $rec)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $records->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $rec->work_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $rec->total_hours > 0 ? $rec->total_hours . 'h' : '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1 flex-wrap">
                                @if($rec->is_auto_timeout) <x-status-badge status="auto-out" /> @endif
                                @if($rec->is_late) <x-status-badge status="late" /> @endif
                                @if($rec->is_undertime) <x-status-badge status="undertime" /> @endif
                                @if($rec->overtime_hours > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">OT {{ $rec->overtime_hours }}h</span>
                                @endif
                                @if(!$rec->is_late && !$rec->is_undertime && !$rec->is_auto_timeout && $rec->time_out)
                                    <x-status-badge status="active" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($records->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $records->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
