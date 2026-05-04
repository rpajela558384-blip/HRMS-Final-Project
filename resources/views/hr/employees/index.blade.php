<x-app-layout>
<x-slot name="title">Employees</x-slot>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">Employees</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4" x-data>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 items-end">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or username..."
                   class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-56" />
            <select name="shift_id" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Shifts</option>
                @foreach($shifts as $shift)
                    <option value="{{ $shift->shift_id }}" {{ request('shift_id') == $shift->shift_id ? 'selected' : '' }}>{{ $shift->shift_name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>Active</option>
                <option value="resigned"   {{ request('status') === 'resigned'   ? 'selected' : '' }}>Resigned</option>
                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
            @if(request()->hasAny(['search','shift_id','status']))
                <a href="{{ route('hr.employees.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-red-500 transition">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Shift</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Hire Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($employees as $emp)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $emp->full_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $emp->user?->username }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $emp->shift?->shift_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $emp->hire_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $emp->years_of_service }} yrs</td>
                        <td class="px-4 py-3"><x-status-badge :status="$emp->user?->status ?? 'active'" /></td>
                        <td class="px-4 py-3">
                            <a href="{{ route('hr.employees.show', $emp->employee_id) }}"
                               class="text-teal-600 hover:underline text-xs font-medium">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
