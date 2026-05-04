<x-guest-layout>
    <h2 class="text-2xl font-bold text-slate-800 mb-1">Welcome back</h2>
    <p class="text-sm text-slate-500 mb-6">Sign in to your HRMS account</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input id="username"
                   name="username"
                   type="text"
                   value="{{ old('username') }}"
                   required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition @error('username') border-red-400 @enderror"
                   placeholder="Enter your username" />
            @error('username')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input id="password"
                   name="password"
                   type="password"
                   required autocomplete="current-password"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition @error('password') border-red-400 @enderror"
                   placeholder="Enter your password" />
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                Remember me
            </label>
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">
            Sign In
        </button>
    </form>
</x-guest-layout>
