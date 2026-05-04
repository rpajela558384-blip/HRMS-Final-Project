<x-app-layout>
<x-slot name="title">Dashboard</x-slot>

<div class="space-y-6">

    {{-- User Info + Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- User Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($user->username, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-800 text-lg">
                    {{ $employee ? trim($employee->first_name . ' ' . $employee->last_name) : $user->username }}
                </p>
                <x-status-badge :status="$user->role" />
                @if($employee?->shift)
                    <p class="text-xs text-slate-500 mt-1">{{ $employee->shift->shift_name }} &bull; {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}</p>
                @endif
                @if($employee?->hire_date)
                    <p class="text-xs text-slate-400 mt-0.5">Hired {{ $employee->hire_date->format('M d, Y') }}</p>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Quick Actions</h3>
            <div class="space-y-3">
                @if($workingDay && $workingDay->isOpen())
                    @if(!$todayAttendance)
                        <x-confirm-modal id="time-in" title="Time In" message="Record your time-in for today?">
                            <x-slot name="trigger">
                                <button type="button"
                                        @click="$dispatch('open-modal-time-in')"
                                        class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
                                    ⏱ Time In
                                </button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('employee.attendance.time-in') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold transition">Confirm Time In</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @elseif($todayAttendance->time_in && !$todayAttendance->time_out)
                        <x-confirm-modal id="time-out" title="Time Out" message="Record your time-out for today?">
                            <x-slot name="trigger">
                                <button type="button"
                                        @click="$dispatch('open-modal-time-out')"
                                        class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm transition">
                                    ⏹ Time Out
                                </button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('employee.attendance.time-out') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition">Confirm Time Out</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @else
                        <div class="w-full py-2.5 bg-slate-100 text-slate-400 font-semibold rounded-xl text-sm text-center">Attendance Complete</div>
                    @endif
                @else
                    <div class="w-full py-2.5 bg-slate-100 text-slate-400 font-semibold rounded-xl text-sm text-center">Day is Closed</div>
                @endif

                <a href="{{ route('employee.requests.index', ['tab' => 'overtime']) }}"
                   class="block w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm text-center transition">
                    📋 Request Overtime
                </a>
            </div>
        </div>

        {{-- Work Hours Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-2">Hours This Cutoff</h3>
            <div class="text-4xl font-bold text-teal-600">{{ number_format($cutoffHours, 1) }}<span class="text-lg text-slate-400 font-normal"> hrs</span></div>
            <p class="text-xs text-slate-400 mt-1">15-day cutoff period</p>
        </div>
    </div>

    {{-- Leave + Notifications --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Leave Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Leave Request</h3>
                <a href="{{ route('employee.requests.index', ['tab' => 'leave']) }}"
                   class="text-xs text-teal-600 hover:underline">View all</a>
            </div>
            <p class="text-sm text-slate-500 mb-4">Submit a leave request for HR review.</p>
            <a href="{{ route('employee.requests.index', ['tab' => 'leave']) }}"
               class="inline-block px-4 py-2 bg-teal-50 text-teal-700 hover:bg-teal-100 font-semibold rounded-xl text-sm transition">
                + Request a Leave
            </a>
        </div>

        {{-- Recent Notifications --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Recent Notifications</h3>
            @forelse($notifications as $notif)
                @php $data = $notif->data; @endphp
                <div class="flex gap-3 py-2 border-b border-slate-50 last:border-0">
                    @if(($data['status'] ?? '') === 'approved')
                        <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs flex-shrink-0">✓</span>
                    @elseif(($data['status'] ?? '') === 'rejected')
                        <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs flex-shrink-0">✗</span>
                    @else
                        <span class="w-6 h-6 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xs flex-shrink-0">⏳</span>
                    @endif
                    <div>
                        <p class="text-sm text-slate-700">{{ $data['message'] ?? '' }}</p>
                        <p class="text-xs text-slate-400">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">No new notifications.</p>
            @endforelse
        </div>
    </div>

    {{-- Today's Attendance Summary --}}
    @if($todayAttendance)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Today's Attendance</h3>
        <div class="flex flex-wrap gap-6">
            <div><p class="text-xs text-slate-400">Time In</p><p class="font-semibold text-slate-800">{{ $todayAttendance->time_in?->format('h:i A') ?? '—' }}</p></div>
            <div><p class="text-xs text-slate-400">Time Out</p><p class="font-semibold text-slate-800">{{ $todayAttendance->time_out?->format('h:i A') ?? '—' }}</p></div>
            <div><p class="text-xs text-slate-400">Status</p>
                <div class="flex gap-1 flex-wrap mt-0.5">
                    @if($todayAttendance->is_late) <x-status-badge status="late" /> @endif
                    @if($todayAttendance->is_undertime) <x-status-badge status="undertime" /> @endif
                    @if($todayAttendance->overtime_hours > 0) <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">OT {{ $todayAttendance->overtime_hours }}h</span> @endif
                    @if(!$todayAttendance->is_late && !$todayAttendance->is_undertime) <x-status-badge status="active" /> @endif
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
</x-app-layout>
