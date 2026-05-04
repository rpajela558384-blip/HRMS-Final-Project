@php
    $navUser       = auth()->user();
    $unreadCount   = $navUser ? $navUser->unreadNotifications()->count() : 0;
    $recentNotifs  = $navUser ? $navUser->notifications()->latest()->take(8)->get() : collect();
@endphp

<nav x-data="{
        mobileOpen: false,
        notifOpen: false,
        showLogout: false,
        unread: {{ $unreadCount }},
        markRead(id, el) {
            fetch('{{ url('notifications') }}/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            }).then(() => {
                const row = el.closest('[data-notif-row]');
                row.classList.add('opacity-50');
                el.remove();
                if (this.unread > 0) this.unread--;
            });
        },
        markAllRead() {
            fetch('{{ route('notifications.read-all') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            }).then(() => {
                document.querySelectorAll('[data-notif-row]').forEach(r => r.classList.add('opacity-50'));
                document.querySelectorAll('[data-read-btn]').forEach(b => b.remove());
                document.getElementById('mark-all-btn')?.remove();
                this.unread = 0;
            });
        }
     }"
     class="bg-gradient-to-r from-slate-900 to-slate-800 text-white shadow-lg sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center font-bold text-white text-sm group-hover:bg-teal-400 transition">HR</div>
                    <span class="font-bold text-lg tracking-wide text-white">HRMS</span>
                </a>
            </div>

            {{-- Desktop Nav Links --}}
            <div class="hidden md:flex items-center gap-1">
                @if($navUser)
                    @if($navUser->role === 'admin')
                        <x-nav-link :href="route('admin.accounts.index')" :active="request()->routeIs('admin.*')">Accounts</x-nav-link>
                    @elseif($navUser->role === 'hr')
                        <x-nav-link :href="route('hr.dashboard')"          :active="request()->routeIs('hr.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('hr.attendance.index')"   :active="request()->routeIs('hr.attendance.*')">Attendance</x-nav-link>
                        <x-nav-link :href="route('hr.requests.index')"     :active="request()->routeIs('hr.requests.*')">Requests</x-nav-link>
                        <x-nav-link :href="route('hr.employees.index')"    :active="request()->routeIs('hr.employees.*')">Employees</x-nav-link>
                        <x-nav-link :href="route('hr.reports.index')"      :active="request()->routeIs('hr.reports.*')">Reports</x-nav-link>
                    @else
                        <x-nav-link :href="route('employee.dashboard')"       :active="request()->routeIs('employee.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('employee.attendance.index')" :active="request()->routeIs('employee.attendance.*')">Attendance</x-nav-link>
                        <x-nav-link :href="route('employee.requests.index')"   :active="request()->routeIs('employee.requests.*')">Requests</x-nav-link>
                        <x-nav-link :href="route('employee.profile.index')"    :active="request()->routeIs('employee.profile.*')">Profile</x-nav-link>
                    @endif
                @endif
            </div>

            {{-- Right side: Bell + User --}}
            <div class="hidden md:flex items-center gap-3">
                @if($navUser)
                    {{-- Notification Bell --}}
                    <div class="relative" @click.outside="notifOpen = false">
                        <button @click="notifOpen = !notifOpen"
                                class="relative p-2 rounded-lg hover:bg-white/10 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="unread > 0"
                                  x-text="unread > 9 ? '9+' : unread"
                                  class="absolute top-1 right-1 min-w-[1rem] h-4 px-0.5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                            </span>
                        </button>

                        {{-- Notification Dropdown --}}
                        <div x-show="notifOpen" x-transition
                             class="absolute right-0 mt-2 w-80 bg-white text-slate-800 rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-50">
                            <div class="flex items-center justify-between px-4 py-3 border-b bg-slate-50">
                                <span class="font-semibold text-sm">Notifications</span>
                                <button id="mark-all-btn" x-show="unread > 0"
                                        @click="markAllRead()"
                                        class="text-xs text-teal-600 hover:underline">
                                    Mark all read
                                </button>
                            </div>
                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                @forelse($recentNotifs as $notif)
                                    @php $data = $notif->data; $isRead = $notif->read_at !== null; @endphp
                                    <div data-notif-row class="flex gap-3 px-4 py-3 hover:bg-slate-50 transition {{ $isRead ? 'opacity-50' : '' }}">
                                        <div class="flex-shrink-0 mt-0.5">
                                            @if(($data['status'] ?? '') === 'approved')
                                                <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                                            @elseif(($data['status'] ?? '') === 'rejected')
                                                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs">✗</span>
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xs">⏳</span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm {{ !$isRead ? 'font-medium' : '' }}">{{ $data['message'] ?? '' }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                        @if(!$isRead)
                                            <button data-read-btn
                                                    @click="markRead('{{ $notif->id }}', $el)"
                                                    class="text-xs text-teal-600 hover:underline whitespace-nowrap self-start mt-0.5">
                                                Read
                                            </button>
                                        @endif
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-slate-400">No notifications</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- User Info + Logout --}}
                    <div class="flex items-center gap-2 pl-3 border-l border-white/20">
                        <div class="text-right hidden lg:block">
                            <p class="text-sm font-medium leading-none">{{ $navUser->username }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ ucfirst($navUser->role) }}</p>
                        </div>
                        <button @click="showLogout = true"
                                class="ml-2 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-sm font-medium transition">
                            Logout
                        </button>
                    </div>
                @endif
            </div>

            {{-- Mobile Hamburger --}}
            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileOpen" x-transition class="md:hidden border-t border-white/10 bg-slate-800">
        <div class="px-4 py-3 space-y-1">
            @if($navUser)
                @if($navUser->role === 'admin')
                    <a href="{{ route('admin.accounts.index') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('admin.*') ? 'bg-teal-600 font-semibold' : '' }}">Accounts</a>
                @elseif($navUser->role === 'hr')
                    <a href="{{ route('hr.dashboard') }}"        class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('hr.dashboard') ? 'bg-teal-600 font-semibold' : '' }}">Dashboard</a>
                    <a href="{{ route('hr.attendance.index') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('hr.attendance.*') ? 'bg-teal-600 font-semibold' : '' }}">Attendance</a>
                    <a href="{{ route('hr.requests.index') }}"   class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('hr.requests.*') ? 'bg-teal-600 font-semibold' : '' }}">Requests</a>
                    <a href="{{ route('hr.employees.index') }}"  class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('hr.employees.*') ? 'bg-teal-600 font-semibold' : '' }}">Employees</a>
                    <a href="{{ route('hr.reports.index') }}"    class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('hr.reports.*') ? 'bg-teal-600 font-semibold' : '' }}">Reports</a>
                @else
                    <a href="{{ route('employee.dashboard') }}"         class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('employee.dashboard') ? 'bg-teal-600 font-semibold' : '' }}">Dashboard</a>
                    <a href="{{ route('employee.attendance.index') }}"  class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('employee.attendance.*') ? 'bg-teal-600 font-semibold' : '' }}">Attendance</a>
                    <a href="{{ route('employee.requests.index') }}"    class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('employee.requests.*') ? 'bg-teal-600 font-semibold' : '' }}">Requests</a>
                    <a href="{{ route('employee.profile.index') }}"     class="block px-3 py-2 rounded-lg text-sm hover:bg-white/10 {{ request()->routeIs('employee.profile.*') ? 'bg-teal-600 font-semibold' : '' }}">Profile</a>
                @endif
                <button @click="showLogout = true; mobileOpen = false"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm hover:bg-white/10 pt-2 border-t border-white/10 mt-2">
                    Logout
                </button>
            @endif
        </div>
    </div>

    {{-- Logout Confirmation Modal (inside nav so it shares x-data scope) --}}
    <div x-show="showLogout" x-transition.opacity
         class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4"
         @keydown.escape.window="showLogout = false"
         style="display:none">
        <div x-show="showLogout" x-transition.scale
             @click.outside="showLogout = false"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-slate-800">
            <h3 class="text-lg font-semibold mb-2">Confirm Logout</h3>
            <p class="text-sm text-slate-500 mb-6">Are you sure you want to log out?</p>
            <div class="flex gap-3 justify-end">
                <button @click="showLogout = false"
                        class="px-4 py-2 border border-slate-200 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl text-sm transition">
                        Yes, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
