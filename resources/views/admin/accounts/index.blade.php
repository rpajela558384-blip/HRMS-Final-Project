<x-app-layout>
<x-slot name="title">Account Management</x-slot>

{{-- Single x-data scope for the whole page --}}
<div class="space-y-6"
     x-data="{
         showCreate: false,
         showEdit: false,
         editUser: {},
         openEdit(user) {
             this.editUser = user;
             this.showEdit = true;
         }
     }">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Account Management</h1>
        <button @click="showCreate = true"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
            + New Account
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
        <form method="GET" x-on:change="$el.requestSubmit()" class="flex flex-wrap gap-3 items-end">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search username..."
                   class="h-10 px-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none w-48" />
            <select name="role" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Roles</option>
                <option value="employee" {{ request('role')==='employee' ? 'selected':'' }}>Employee</option>
                <option value="hr"       {{ request('role')==='hr'       ? 'selected':'' }}>HR</option>
                <option value="admin"    {{ request('role')==='admin'    ? 'selected':'' }}>Admin</option>
            </select>
            <select name="status" class="h-10 px-3 pr-8 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="active"     {{ request('status')==='active'     ? 'selected':'' }}>Active</option>
                <option value="resigned"   {{ request('status')==='resigned'   ? 'selected':'' }}>Resigned</option>
                <option value="terminated" {{ request('status')==='terminated' ? 'selected':'' }}>Terminated</option>
            </select>
            @if(request()->hasAny(['search','role','status']))
                <a href="{{ route('admin.accounts.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-red-500 transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Accounts Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Full Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $u->username }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->employee?->full_name ?: '—' }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$u->role" /></td>
                        <td class="px-4 py-3"><x-status-badge :status="$u->status" /></td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $u->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                {{-- Edit button passes the full user+employee data --}}
                                <button @click="openEdit({{ json_encode([
                                    'user_id'     => $u->user_id,
                                    'username'    => $u->username,
                                    'role'        => $u->role,
                                    'status'      => $u->status,
                                    'first_name'  => $u->employee?->first_name ?? '',
                                    'middle_name' => $u->employee?->middle_name ?? '',
                                    'last_name'   => $u->employee?->last_name ?? '',
                                    'hire_date'   => $u->employee?->hire_date?->format('Y-m-d') ?? '',
                                    'shift_id'    => $u->employee?->shift_id ?? '',
                                ]) }})"
                                        class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition">
                                    Edit
                                </button>

                                @if($u->status === 'active' && $u->user_id !== auth()->id() && $u->role !== 'admin')
                                    <x-confirm-modal
                                        id="disable-{{ $u->user_id }}"
                                        title="Disable Account"
                                        message="Disable account for {{ $u->username }}?">
                                        <x-slot name="trigger">
                                            <button @click="$dispatch('open-modal-disable-{{ $u->user_id }}')"
                                                    class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition">
                                                Disable
                                            </button>
                                        </x-slot>
                                        <x-slot name="action">
                                            <form method="POST" action="{{ route('admin.accounts.disable', $u->user_id) }}">
                                                @csrf @method('PATCH')
                                                <div class="mb-3">
                                                    <label class="block text-sm text-slate-600 mb-1">Reason</label>
                                                    <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-red-400 focus:outline-none">
                                                        <option value="resigned">Resigned</option>
                                                        <option value="terminated">Terminated</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg">Confirm Disable</button>
                                            </form>
                                        </x-slot>
                                    </x-confirm-modal>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- ── CREATE MODAL ─────────────────────────────────────────── --}}
    <div x-show="showCreate" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
         @keydown.escape.window="showCreate = false"
         style="display:none">
        <div x-show="showCreate" x-transition.scale
             @click.outside="showCreate = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-slate-800">Create New Account</h3>
                <button @click="showCreate = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" required
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="employee">Employee</option>
                            <option value="hr">HR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="resigned">Resigned</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                        <input type="text" name="first_name" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Hire Date</label>
                        <input type="date" name="hire_date" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
                        <select name="shift_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">No shift assigned</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->shift_id }}">
                                    {{ $shift->shift_name }}
                                    ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="showCreate = false"
                            class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── EDIT MODAL ───────────────────────────────────────────── --}}
    <div x-show="showEdit" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
         @keydown.escape.window="showEdit = false"
         style="display:none">
        <div x-show="showEdit" x-transition.scale
             @click.outside="showEdit = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-slate-800">Edit Account — <span class="text-teal-600" x-text="editUser.username"></span></h3>
                <button @click="showEdit = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <form method="POST" :action="`/admin/accounts/${editUser.user_id}`" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" :value="editUser.username" required
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">New Password <span class="text-slate-400 font-normal">(leave blank to keep)</span></label>
                        <input type="password" name="password"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <template x-if="editUser.role === 'admin'">
                            <div>
                                <input type="hidden" name="role" value="admin" />
                                <div class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-100 text-slate-400 cursor-not-allowed">Admin</div>
                            </div>
                        </template>
                        <template x-if="editUser.role !== 'admin'">
                            <select name="role" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                <option value="employee" :selected="editUser.role === 'employee'">Employee</option>
                                <option value="hr"       :selected="editUser.role === 'hr'">HR</option>
                            </select>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <template x-if="editUser.role === 'admin'">
                            <div>
                                <input type="hidden" name="status" :value="editUser.status" />
                                <div class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-100 text-slate-400 cursor-not-allowed" x-text="editUser.status"></div>
                                <p class="mt-1 text-xs text-amber-600">Admin status cannot be changed.</p>
                            </div>
                        </template>
                        <template x-if="editUser.role !== 'admin'">
                            <select name="status" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                                <option value="active"     :selected="editUser.status === 'active'">Active</option>
                                <option value="resigned"   :selected="editUser.status === 'resigned'">Resigned</option>
                                <option value="terminated" :selected="editUser.status === 'terminated'">Terminated</option>
                            </select>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                        <input type="text" name="first_name" :value="editUser.first_name"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" :value="editUser.middle_name"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" :value="editUser.last_name"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Hire Date</label>
                        <input type="date" name="hire_date" :value="editUser.hire_date"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
                        <select name="shift_id" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                            <option value="">No shift assigned</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->shift_id }}"
                                        :selected="editUser.shift_id == {{ $shift->shift_id }}">
                                    {{ $shift->shift_name }}
                                    ({{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="showEdit = false"
                            class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>{{-- end x-data --}}
</x-app-layout>
