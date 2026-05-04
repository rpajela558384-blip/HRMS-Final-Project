<x-app-layout>
<x-slot name="title">Attendance</x-slot>

<div class="space-y-6" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Attendance</h1>
        <div class="flex gap-3" x-show="tab === 'mine'">
            @if($workingDay && $workingDay->isOpen())
                @if(!$todayAttendance)
                    <x-confirm-modal id="hr-att-in" title="Time In" message="Record your time-in for today?">
                        <x-slot name="trigger">
                            <button @click="$dispatch('open-modal-hr-att-in')" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">⏱ Time In</button>
                        </x-slot>
                        <x-slot name="action">
                            <form method="POST" action="{{ route('hr.attendance.time-in') }}">@csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold">Confirm</button>
                            </form>
                        </x-slot>
                    </x-confirm-modal>
                @elseif($todayAttendance->time_in && !$todayAttendance->time_out)
                    <x-confirm-modal id="hr-att-out" title="Time Out" message="Record your time-out for today?">
                        <x-slot name="trigger">
                            <button @click="$dispatch('open-modal-hr-att-out')" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm transition">⏹ Time Out</button>
                        </x-slot>
                        <x-slot name="action">
                            <form method="POST" action="{{ route('hr.attendance.time-out') }}">@csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold">Confirm</button>
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
        {{-- Day management buttons (always visible for HR) --}}
        <div class="flex gap-2" x-show="tab === 'all'">
            @if($workingDay && $workingDay->isOpen())
                {{-- Day is open: only Close is available --}}
                <x-confirm-modal id="hr-day-close" title="Close Working Day" message="Close today? Timed-in employees will be auto timed-out.">
                    <x-slot name="trigger">
                        <button @click="$dispatch('open-modal-hr-day-close')" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white font-semibold rounded-xl text-sm transition">Close Day</button>
                    </x-slot>
                    <x-slot name="action">
                        <form method="POST" action="{{ route('hr.working-day.close') }}">@csrf
                            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-semibold">Confirm Close</button>
                        </form>
                    </x-slot>
                </x-confirm-modal>
            @else
                {{-- Day is closed or not yet created: Open and Re-open both available --}}
                <x-confirm-modal id="hr-day-open" title="Open Working Day" message="Open today as a working day?">
                    <x-slot name="trigger">
                        <button @click="$dispatch('open-modal-hr-day-open')" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">Open Day</button>
                    </x-slot>
                    <x-slot name="action">
                        <form method="POST" action="{{ route('hr.working-day.open') }}">@csrf
                            <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold">Confirm Open</button>
                        </form>
                    </x-slot>
                </x-confirm-modal>
                @if($workingDay)
                    {{-- Day exists but is closed: Re-open is available --}}
                    <x-confirm-modal id="hr-day-reopen" title="Re-open Working Day" message="Re-open today? This allows new time-ins again.">
                        <x-slot name="trigger">
                            <button @click="$dispatch('open-modal-hr-day-reopen')" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition">Re-open Day</button>
                        </x-slot>
                        <x-slot name="action">
                            <form method="POST" action="{{ route('hr.working-day.reopen') }}">@csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold">Confirm</button>
                            </form>
                        </x-slot>
                    </x-confirm-modal>
                @endif
            @endif
        </div>
    </div>

    {{-- Sub-tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <button @click="tab = 'mine'" :class="tab === 'mine' ? 'bg-white shadow text-slate-800 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-2 rounded-lg text-sm transition">My Attendance</button>
        <button @click="tab = 'all'" :class="tab === 'all' ? 'bg-white shadow text-slate-800 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-5 py-2 rounded-lg text-sm transition">All Employees</button>
    </div>

    {{-- My Attendance --}}
    <div x-show="tab === 'mine'">
        <form method="GET" action="{{ route('hr.attendance.index') }}" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4 items-center" x-data>
            <input type="hidden" name="tab" value="mine" />
            <input type="date" name="my_from" value="{{ request('my_from') }}" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            <input type="date" name="my_to" value="{{ request('my_to') }}" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            <select name="my_flag" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Flags</option>
                <option value="late"      {{ request('my_flag') === 'late'      ? 'selected' : '' }}>Late</option>
                <option value="undertime" {{ request('my_flag') === 'undertime' ? 'selected' : '' }}>Undertime</option>
                <option value="overtime"  {{ request('my_flag') === 'overtime'  ? 'selected' : '' }}>Overtime</option>
                <option value="off_shift" {{ request('my_flag') === 'off_shift' ? 'selected' : '' }}>Off-shift</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time In</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time Out</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Hours</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($myRecords as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium">{{ $rec->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->total_hours > 0 ? $rec->total_hours.'h' : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @if($rec->is_late)<x-status-badge status="late" />@endif
                                    @if($rec->is_undertime)<x-status-badge status="undertime" />@endif
                                    @if($rec->overtime_hours > 0)<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">OT {{ $rec->overtime_hours }}h</span>@endif
                                    @if(!$rec->is_late && !$rec->is_undertime && $rec->time_out)<x-status-badge status="active" />@endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($myRecords->hasPages())<div class="px-4 py-3 border-t">{{ $myRecords->links() }}</div>@endif
        </div>
    </div>

    {{-- All Attendance --}}
    <div x-show="tab === 'all'" x-cloak>
        <form method="GET" action="{{ route('hr.attendance.index') }}" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4 items-center" x-data>
            <input type="hidden" name="tab" value="all" />
            <input type="text" name="search_emp" value="{{ request('search_emp') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-44" />
            <input type="date" name="all_from" value="{{ request('all_from') }}" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            <input type="date" name="all_to" value="{{ request('all_to') }}" class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
            <select name="flag" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Flags</option>
                <option value="late" {{ request('flag') === 'late' ? 'selected' : '' }}>Late</option>
                <option value="undertime" {{ request('flag') === 'undertime' ? 'selected' : '' }}>Undertime</option>
                <option value="overtime" {{ request('flag') === 'overtime' ? 'selected' : '' }}>Overtime</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time In</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time Out</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Hours</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allRecords as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium">{{ $rec->employee?->full_name ?: ($rec->employee?->user?->username ?? '—') }}</td>
                            <td class="px-4 py-3">{{ $rec->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_in?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->time_out?->format('h:i A') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $rec->total_hours > 0 ? $rec->total_hours.'h' : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @if($rec->is_late)<x-status-badge status="late" />@endif
                                    @if($rec->is_undertime)<x-status-badge status="undertime" />@endif
                                    @if($rec->is_auto_timeout)<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">Auto-out</span>@endif
                                    @if($rec->is_off_shift)<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">Off-shift</span>@endif
                                    @if($rec->overtime_hours > 0)<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">OT {{ $rec->overtime_hours }}h</span>@endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($rec->time_out && $rec->work_date->isToday())
                                    <x-confirm-modal id="undo-{{ $rec->attendance_id }}" title="Undo Time-Out" message="Clear time-out for {{ $rec->employee?->full_name ?? 'this employee' }} on {{ $rec->work_date->format('M d') }}?">
                                        <x-slot name="trigger">
                                            <button @click="$dispatch('open-modal-undo-{{ $rec->attendance_id }}')" class="px-2 py-1 text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg font-medium transition">Undo Timeout</button>
                                        </x-slot>
                                        <x-slot name="action">
                                            <form method="POST" action="{{ route('hr.attendance.undo-timeout', $rec->attendance_id) }}">@csrf @method('PATCH')
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold">Confirm</button>
                                            </form>
                                        </x-slot>
                                    </x-confirm-modal>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($allRecords->hasPages())<div class="px-4 py-3 border-t">{{ $allRecords->links() }}</div>@endif
        </div>
    </div>
</div>
</x-app-layout>
