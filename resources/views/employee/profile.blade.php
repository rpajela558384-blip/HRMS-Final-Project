<x-app-layout>
<x-slot name="title">My Profile</x-slot>

<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">My Profile</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold text-2xl">
                {{ strtoupper(substr($user->username, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-800">
                    {{ $employee ? $employee->full_name : $user->username }}
                </h2>
                <div class="flex gap-2 mt-1">
                    <x-status-badge :status="$user->role" />
                    <x-status-badge :status="$user->status" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 pt-4 border-t border-slate-100">
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Username</p>
                <p class="font-medium text-slate-800 mt-0.5">{{ $user->username }}</p>
            </div>
            @if($employee)
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Full Name</p>
                <p class="font-medium text-slate-800 mt-0.5">{{ $employee->full_name ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Hire Date</p>
                <p class="font-medium text-slate-800 mt-0.5">{{ $employee->hire_date?->format('M d, Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Years of Service</p>
                <p class="font-medium text-slate-800 mt-0.5">{{ $employee->years_of_service }} years</p>
            </div>
            @if($employee->shift)
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Shift</p>
                <p class="font-medium text-slate-800 mt-0.5">{{ $employee->shift->shift_name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wide">Shift Hours</p>
                <p class="font-medium text-slate-800 mt-0.5">
                    {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} –
                    {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}
                    <span class="text-xs text-slate-400">({{ $employee->shift->grace_minutes }} min grace)</span>
                </p>
            </div>
            @endif
            @endif
        </div>
    </div>

    {{-- Leave Balances --}}
    @if($balances->count())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Leave Balances</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($balances as $bal)
                <div class="bg-slate-50 rounded-xl p-4 text-center">
                    <p class="text-xs text-slate-500">{{ $bal->leaveType->name }}</p>
                    <p class="text-2xl font-bold text-teal-600 mt-1">{{ $bal->remaining_days }}</p>
                    <p class="text-xs text-slate-400">days remaining</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</x-app-layout>
