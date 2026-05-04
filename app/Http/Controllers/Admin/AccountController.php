<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('username', 'like', '%' . $request->search . '%');
        }

        $users  = $query->latest('created_at')->paginate(10)->withQueryString();
        $shifts = Shift::all();

        return view('admin.accounts.index', compact('users', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'    => 'required|string|max:50|unique:users,username',
            'password'    => 'required|string|min:6',
            'role'        => 'required|in:employee,hr',
            'status'      => 'required|in:active,resigned,terminated',
            'first_name'  => 'nullable|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name'   => 'nullable|string|max:50',
            'hire_date'   => 'nullable|date',
            'shift_id'    => 'nullable|exists:shifts,shift_id',
        ]);

        $user = User::create([
            'username'      => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'status'        => $validated['status'],
        ]);

        if ($validated['role'] !== 'admin') {
            $employee = Employee::create([
                'user_id'     => $user->user_id,
                'first_name'  => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name'   => $validated['last_name'] ?? null,
                'hire_date'   => $validated['hire_date'] ?? null,
                'shift_id'    => $validated['shift_id'] ?? null,
            ]);

            // Assign default leave balances
            LeaveType::all()->each(function ($type) use ($employee) {
                LeaveBalance::create([
                    'employee_id'   => $employee->employee_id,
                    'leave_type_id' => $type->leave_type_id,
                    'remaining_days'=> $type->default_balance,
                ]);
            });
        }

        return back()->with('success', 'Account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username'    => 'required|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
            'role'        => 'required|in:employee,hr' . ($user->role === 'admin' ? ',admin' : ''),
            'status'      => 'required|in:active,resigned,terminated',
            'password'    => 'nullable|string|min:6',
            'first_name'  => 'nullable|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name'   => 'nullable|string|max:50',
            'hire_date'   => 'nullable|date',
            'shift_id'    => 'nullable|exists:shifts,shift_id',
        ]);

        // Admin cannot change their own status
        if ($user->user_id === auth()->id() && $user->role === 'admin' && $validated['status'] !== $user->status) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $user->username = $validated['username'];
        $user->role     = $validated['role'];
        $user->status   = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }
        $user->save();

        // Upsert employee record for any role so names can always be set
        Employee::updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'first_name'  => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name'   => $validated['last_name'] ?? null,
                'hire_date'   => $validated['hire_date'] ?? null,
                'shift_id'    => $validated['shift_id'] ?? null,
            ]
        );

        return back()->with('success', 'Account updated successfully.');
    }

    public function disable(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:resigned,terminated',
        ]);

        // Guard: must keep at least one active admin
        if ($user->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')->where('status', 'active')->count();
            if ($activeAdmins <= 1) {
                return back()->with('error', 'Cannot disable the last active admin account.');
            }
        }

        $user->update(['status' => $validated['status']]);

        return back()->with('success', 'Account has been ' . $validated['status'] . '.');
    }
}
