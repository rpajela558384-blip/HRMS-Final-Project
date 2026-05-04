<x-app-layout>
<x-slot name="title">Requests</x-slot>

<div class="space-y-6" x-data="{
    tab: '{{ $activeTab }}',
    viewReason: false,
    reasonTitle: '',
    reasonText: '',
    showReason(title, text) { this.reasonTitle = title; this.reasonText = text; this.viewReason = true; }
}">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">My Requests</h1>
        <div x-show="tab === 'leave'">
            <button @click="$dispatch('open-modal-leave-form')"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
                + Request a Leave
            </button>
        </div>
        <div x-show="tab === 'overtime'" x-cloak>
            <button @click="$dispatch('open-modal-ot-form')"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                + Request Overtime
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <button @click="tab = 'leave'" :class="tab === 'leave' ? 'bg-white shadow text-slate-800 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2 rounded-lg text-sm transition">Leave</button>
        <button @click="tab = 'overtime'" :class="tab === 'overtime' ? 'bg-white shadow text-slate-800 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2 rounded-lg text-sm transition">Overtime</button>
    </div>

    {{-- Leave Tab --}}
    <div x-show="tab === 'leave'">
        {{-- Balances --}}
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach($leaveBalances as $bal)
                <div class="bg-white border border-slate-100 rounded-xl px-4 py-2 text-sm shadow-sm">
                    <span class="text-slate-500">{{ $bal->leaveType->name }}</span>
                    <span class="font-bold text-teal-600 ml-2">{{ $bal->remaining_days }} days</span>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('employee.requests.index') }}"
              x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="leave" />
            <select name="leave_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('leave_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('leave_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('leave_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <select name="leave_type_id" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->leave_type_id }}" {{ request('leave_type_id') == $type->leave_type_id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Days</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leaveRequests as $req)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium">{{ $req->leaveType->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $req->start_date->format('M d') }} – {{ $req->end_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $req->days_requested }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[160px] truncate">{{ $req->reason }}</span>
                                    <button @click="showReason('Leave — {{ addslashes($req->leaveType->name) }}', {{ json_encode($req->reason) }})" class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$req->status" /></td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $req->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($leaveRequests->hasPages())
                <div class="px-4 py-3 border-t">{{ $leaveRequests->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Overtime Tab --}}
    <div x-show="tab === 'overtime'" x-cloak>
        <form method="GET" action="{{ route('employee.requests.index') }}"
              x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="overtime" />
            <select name="ot_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('ot_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('ot_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('ot_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </form>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Requested End</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($otRequests as $ot)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-medium">{{ $ot->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($ot->requested_end_time)->format('h:i A') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[160px] truncate">{{ $ot->reason }}</span>
                                    <button @click="showReason('OT — {{ $ot->work_date->format('M d, Y') }}', {{ json_encode($ot->reason) }})" class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$ot->status" /></td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $ot->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No overtime requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($otRequests->hasPages())
                <div class="px-4 py-3 border-t">{{ $otRequests->links() }}</div>
            @endif
        </div>
    </div>

    {{-- View Reason Modal --}}
    <div x-show="viewReason" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
         @keydown.escape.window="viewReason = false"
         style="display:none">
        <div x-show="viewReason" x-transition.scale
             @click.outside="viewReason = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-slate-800" x-text="reasonTitle"></h3>
                <button @click="viewReason = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap" x-text="reasonText"></p>
            <div class="mt-5 flex justify-end">
                <button @click="viewReason = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Leave Request Modal --}}
<div x-data="{ open: false }"
     x-on:open-modal-leave-form.window="open = true">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity
             class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
             @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale @click.outside="open = false"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Request a Leave</h3>
                <form method="POST" action="{{ route('employee.requests.leave.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Leave Type</label>
                        <select name="leave_type_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">Select type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->leave_type_id }}">{{ $type->name }} ({{ $leaveBalances->firstWhere('leave_type_id', $type->leave_type_id)?->remaining_days ?? 0 }} days left)</option>
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
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none" placeholder="Provide a reason for your leave..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- OT Request Modal --}}
<div x-data="{ open: false }"
     x-on:open-modal-ot-form.window="open = true">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity
             class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
             @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale @click.outside="open = false"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Request Overtime</h3>
                <form method="POST" action="{{ route('employee.requests.overtime.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Work Date</label>
                        <input type="date" name="work_date" value="{{ today()->toDateString() }}" required
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Requested End Time</label>
                        <input type="datetime-local" name="requested_end_time" required
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none" placeholder="Reason for overtime..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
</x-app-layout>
