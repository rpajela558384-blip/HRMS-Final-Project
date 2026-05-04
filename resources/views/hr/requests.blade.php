<x-app-layout>
<x-slot name="title">Requests</x-slot>

<div class="space-y-6" x-data="{
    tab: '{{ $activeTab }}',
    viewReason: false,
    reasonTitle: '',
    reasonText: '',
    showReason(title, text) { this.reasonTitle = title; this.reasonText = text; this.viewReason = true; }
}">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-2xl font-bold text-slate-800">Requests</h1>
        <div class="flex gap-2">
            <div x-show="tab === 'my_leave'">
                <button @click="$dispatch('open-modal-hr-leave-form2')" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">+ Request a Leave</button>
            </div>
            <div x-show="tab === 'my_ot'" x-cloak>
                <button @click="$dispatch('open-modal-hr-ot-form2')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">+ Request Overtime</button>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <button @click="tab = 'my_leave'"        :class="tab==='my_leave'       ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm transition">My Leave</button>
        <button @click="tab = 'my_ot'"           :class="tab==='my_ot'          ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm transition">My OT</button>
        <button @click="tab = 'validate_leave'"  :class="tab==='validate_leave'  ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm transition">Validate Leave</button>
        <button @click="tab = 'validate_ot'"     :class="tab==='validate_ot'     ? 'bg-white shadow text-slate-800 font-semibold':'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg text-sm transition">Validate OT</button>
    </div>

    {{-- My Leave --}}
    <div x-show="tab === 'my_leave'">
        <div class="flex flex-wrap gap-3 mb-4">
            @foreach($leaveBalances as $bal)
                <div class="bg-white border border-slate-100 rounded-xl px-4 py-2 text-sm shadow-sm">
                    <span class="text-slate-500">{{ $bal->leaveType->name }}</span>
                    <span class="font-bold text-teal-600 ml-2">{{ $bal->remaining_days }} days</span>
                </div>
            @endforeach
        </div>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="my_leave" />
            <select name="my_leave_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('my_leave_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('my_leave_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('my_leave_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
            <select name="my_leave_type" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->leave_type_id }}" {{ request('my_leave_type') == $type->leave_type_id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Dates</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Days</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($myLeave as $req)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $req->leaveType->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $req->start_date->format('M d') }} – {{ $req->end_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $req->days_requested }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[140px] truncate">{{ $req->reason }}</span>
                                    <button @click="showReason('Leave — {{ addslashes($req->leaveType->name) }}', {{ json_encode($req->reason) }})" class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$req->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No leave requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($myLeave->hasPages())<div class="px-4 py-3 border-t">{{ $myLeave->links() }}</div>@endif
        </div>
    </div>

    {{-- My OT --}}
    <div x-show="tab === 'my_ot'" x-cloak>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="my_ot" />
            <select name="my_ot_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('my_ot_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('my_ot_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('my_ot_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Requested End</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($myOt as $ot)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $ot->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($ot->requested_end_time)->format('h:i A') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[140px] truncate">{{ $ot->reason }}</span>
                                    <button @click="showReason('OT — {{ $ot->work_date->format('M d, Y') }}', {{ json_encode($ot->reason) }})" class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$ot->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No OT requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($myOt->hasPages())<div class="px-4 py-3 border-t">{{ $myOt->links() }}</div>@endif
        </div>
    </div>

    {{-- Validate Leave --}}
    <div x-show="tab === 'validate_leave'" x-cloak>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="validate_leave" />
            <input type="text" name="leave_employee" value="{{ request('leave_employee') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-48" />
            <select name="leave_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('leave_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('leave_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('leave_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
            <select name="leave_type" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->leave_type_id }}" {{ request('leave_type') == $type->leave_type_id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Dates</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allLeave as $req)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $req->employee?->full_name ?: $req->employee?->user?->username }}</td>
                            <td class="px-4 py-3">{{ $req->leaveType->name }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $req->start_date->format('M d') }} – {{ $req->end_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[160px] truncate">{{ $req->reason }}</span>
                                    <button @click="showReason('Leave — {{ addslashes($req->employee?->full_name ?: $req->employee?->user?->username) }}', {{ json_encode($req->reason) }})"
                                            class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$req->status" /></td>
                            <td class="px-4 py-3">
                                @if($req->isPending())
                                    <div class="flex gap-2">
                                        <x-confirm-modal id="leave-approve-{{ $req->leave_id }}" title="Approve Leave" message="Approve this leave request for {{ $req->employee?->full_name }}?">
                                            <x-slot name="trigger">
                                                <button @click="$dispatch('open-modal-leave-approve-{{ $req->leave_id }}')" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold transition">Approve</button>
                                            </x-slot>
                                            <x-slot name="action">
                                                <form method="POST" action="{{ route('hr.requests.leave.approve', $req->leave_id) }}">@csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-sm font-semibold">Confirm Approve</button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-modal>
                                        <x-confirm-modal id="leave-reject-{{ $req->leave_id }}" title="Reject Leave" message="Reject this leave request?">
                                            <x-slot name="trigger">
                                                <button @click="$dispatch('open-modal-leave-reject-{{ $req->leave_id }}')" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-semibold transition">Reject</button>
                                            </x-slot>
                                            <x-slot name="action">
                                                <form method="POST" action="{{ route('hr.requests.leave.reject', $req->leave_id) }}">@csrf
                                                    <input type="text" name="remarks" placeholder="Remarks (optional)" class="w-full mb-2 px-3 py-1.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 focus:outline-none" />
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-sm font-semibold">Confirm Reject</button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-modal>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No leave requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($allLeave->hasPages())<div class="px-4 py-3 border-t">{{ $allLeave->links() }}</div>@endif
        </div>
    </div>

    {{-- Validate OT --}}
    <div x-show="tab === 'validate_ot'" x-cloak>
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 mb-4" x-data>
            <input type="hidden" name="tab" value="validate_ot" />
            <input type="text" name="ot_employee" value="{{ request('ot_employee') }}" placeholder="Search employee..." class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-48" />
            <select name="ot_status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('ot_status')==='pending'  ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('ot_status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('ot_status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
        </form>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Requested End</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allOt as $ot)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $ot->employee?->full_name ?: $ot->employee?->user?->username }}</td>
                            <td class="px-4 py-3">{{ $ot->work_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Carbon\Carbon::parse($ot->requested_end_time)->format('h:i A') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 text-sm max-w-[160px] truncate">{{ $ot->reason }}</span>
                                    <button @click="showReason('OT — {{ addslashes($ot->employee?->full_name ?: $ot->employee?->user?->username) }}', {{ json_encode($ot->reason) }})"
                                            class="text-xs text-teal-600 hover:underline whitespace-nowrap">View</button>
                                </div>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$ot->status" /></td>
                            <td class="px-4 py-3">
                                @if($ot->isPending())
                                    <div class="flex gap-2">
                                        <x-confirm-modal id="ot-approve-{{ $ot->ot_id }}" title="Approve OT" message="Approve overtime for {{ $ot->employee?->full_name }}?">
                                            <x-slot name="trigger">
                                                <button @click="$dispatch('open-modal-ot-approve-{{ $ot->ot_id }}')" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold transition">Approve</button>
                                            </x-slot>
                                            <x-slot name="action">
                                                <form method="POST" action="{{ route('hr.requests.overtime.approve', $ot->ot_id) }}">@csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-sm font-semibold">Confirm</button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-modal>
                                        <x-confirm-modal id="ot-reject-{{ $ot->ot_id }}" title="Reject OT" message="Reject this overtime request?">
                                            <x-slot name="trigger">
                                                <button @click="$dispatch('open-modal-ot-reject-{{ $ot->ot_id }}')" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-semibold transition">Reject</button>
                                            </x-slot>
                                            <x-slot name="action">
                                                <form method="POST" action="{{ route('hr.requests.overtime.reject', $ot->ot_id) }}">@csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-sm font-semibold">Confirm</button>
                                                </form>
                                            </x-slot>
                                        </x-confirm-modal>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No OT requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($allOt->hasPages())<div class="px-4 py-3 border-t">{{ $allOt->links() }}</div>@endif
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
                <button @click="viewReason = false"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">Close</button>
            </div>
        </div>
    </div>

</div>

{{-- HR Leave Form Modal --}}
<div x-data="{ open: false }" x-on:open-modal-hr-leave-form2.window="open = true">
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
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->leave_type_id }}">{{ $type->name }} ({{ $leaveBalances->firstWhere('leave_type_id', $type->leave_type_id)?->remaining_days ?? 0 }} days left)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" /></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                            <input type="date" name="end_date" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" /></div>
                    </div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none"></textarea></div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

{{-- HR OT Form Modal --}}
<div x-data="{ open: false }" x-on:open-modal-hr-ot-form2.window="open = true">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" @keydown.escape.window="open = false">
            <div x-show="open" x-transition.scale @click.outside="open = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Request Overtime <span class="text-xs text-green-600 font-normal">(Auto-approved)</span></h3>
                <form method="POST" action="{{ route('hr.requests.overtime.store') }}" class="space-y-4">
                    @csrf
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Work Date</label>
                        <input type="date" name="work_date" value="{{ today()->toDateString() }}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" /></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Requested End Time</label>
                        <input type="datetime-local" name="requested_end_time" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" /></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none resize-none"></textarea></div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
</x-app-layout>
