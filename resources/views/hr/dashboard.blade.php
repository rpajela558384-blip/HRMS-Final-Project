<x-app-layout>
<x-slot name="title">HR Dashboard</x-slot>

<div class="space-y-6">

    {{-- Row 1: User Info + Quick Actions + Hours --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- User Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($user->username, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-800 text-lg">
                    {{ $employee ? trim($employee->first_name . ' ' . $employee->last_name) : $user->username }}
                </p>
                <x-status-badge status="hr" />
                @if($employee?->shift)
                    <p class="text-xs text-slate-500 mt-1">{{ $employee->shift->shift_name }}</p>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Quick Actions</h3>
            <div class="space-y-3">
                @if($workingDay && $workingDay->isOpen())
                    @if(!$todayAttendance)
                        <x-confirm-modal id="hr-time-in" title="Time In" message="Record your time-in for today?">
                            <x-slot name="trigger">
                                <button @click="$dispatch('open-modal-hr-time-in')"
                                        class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">⏱ Time In</button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('hr.attendance.time-in') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold">Confirm</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @elseif($todayAttendance->time_in && !$todayAttendance->time_out)
                        <x-confirm-modal id="hr-time-out" title="Time Out" message="Record your time-out for today?">
                            <x-slot name="trigger">
                                <button @click="$dispatch('open-modal-hr-time-out')"
                                        class="w-full py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm transition">⏹ Time Out</button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('hr.attendance.time-out') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-semibold">Confirm</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @else
                        <div class="w-full py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm text-center">Attendance Complete</div>
                    @endif
                @else
                    <div class="w-full py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm text-center">Day is Closed</div>
                @endif

                <button @click="$dispatch('open-modal-hr-ot-form')"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    📋 Request Overtime
                </button>
            </div>
        </div>

        {{-- Hours Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-2">Hours This Cutoff</h3>
            <div class="text-4xl font-bold text-indigo-600">{{ number_format($cutoffHours, 1) }}<span class="text-lg text-slate-400 font-normal"> hrs</span></div>
            <p class="text-xs text-slate-400 mt-1">15-day cutoff period</p>
        </div>
    </div>

    {{-- Row 2: Working Day + Pending Requests --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Working Day Control --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Working Day Control</h3>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Today — {{ today()->format('l, M d Y') }}</p>
                    @if($workingDay)
                        <x-status-badge :status="$workingDay->status" />
                    @else
                        <x-status-badge status="closed" />
                    @endif
                </div>
                <div>
                    @if(!$workingDay || !$workingDay->isOpen())
                        <x-confirm-modal id="open-day" title="Open Working Day" message="Allow employees to time in for today?">
                            <x-slot name="trigger">
                                <button @click="$dispatch('open-modal-open-day')"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl text-sm transition">
                                    Open Day
                                </button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('hr.working-day.open') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold">Confirm Open</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @else
                        <x-confirm-modal id="close-day" title="Close Working Day" message="Close today's working day? Employees will no longer be able to time in.">
                            <x-slot name="trigger">
                                <button @click="$dispatch('open-modal-close-day')"
                                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl text-sm transition">
                                    Close Day
                                </button>
                            </x-slot>
                            <x-slot name="action">
                                <form method="POST" action="{{ route('hr.working-day.close') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold">Confirm Close</button>
                                </form>
                            </x-slot>
                        </x-confirm-modal>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pending Requests --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Pending Approvals</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('hr.requests.index', ['tab' => 'validate_leave']) }}"
                   class="flex flex-col items-center justify-center p-4 bg-yellow-50 border border-yellow-200 rounded-xl hover:bg-yellow-100 transition group">
                    <span class="text-3xl font-bold text-yellow-600 group-hover:scale-110 transition">{{ $pendingLeave }}</span>
                    <span class="text-xs text-yellow-700 mt-1 font-medium">Leave Requests</span>
                </a>
                <a href="{{ route('hr.requests.index', ['tab' => 'validate_ot']) }}"
                   class="flex flex-col items-center justify-center p-4 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition group">
                    <span class="text-3xl font-bold text-indigo-600 group-hover:scale-110 transition">{{ $pendingOvertime }}</span>
                    <span class="text-xs text-indigo-700 mt-1 font-medium">OT Requests</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Leave Card + Notifications --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Leave Request</h3>
            <p class="text-sm text-slate-500 mb-4">Submit your own leave request for processing.</p>
            <button @click="$dispatch('open-modal-hr-leave-form')"
                    class="inline-block px-4 py-2 bg-teal-50 text-teal-700 hover:bg-teal-100 font-semibold rounded-xl text-sm transition">
                + Request a Leave
            </button>
        </div>

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
</div>

{{-- HR OT Request Modal --}}
<div x-data="{ open: false }" x-on:open-modal-hr-ot-form.window="open = true">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale @click.outside="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Request Overtime <span class="text-xs text-green-600 font-normal ml-1">(Auto-approved)</span></h3>
                <form method="POST" action="{{ route('hr.requests.overtime.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Work Date</label>
                        <input type="date" name="work_date" value="{{ today()->toDateString() }}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Requested End Time</label>
                        <input type="datetime-local" name="requested_end_time" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- HR Leave Request Modal --}}
<div x-data="{ open: false }" x-on:open-modal-hr-leave-form.window="open = true">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale @click.outside="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Request a Leave</h3>
                <form method="POST" action="{{ route('hr.requests.leave.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Leave Type</label>
                        <select name="leave_type_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">Select type</option>
                            @foreach(\App\Models\LeaveType::all() as $type)
                                <option value="{{ $type->leave_type_id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                            <input type="date" name="end_date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
</x-app-layout>
