<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendance;
use App\Http\Controllers\Employee\OvertimeController as EmployeeOvertime;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeave;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfile;
use App\Http\Controllers\HR\DashboardController as HRDashboard;
use App\Http\Controllers\HR\AttendanceController as HRAttendance;
use App\Http\Controllers\HR\OvertimeController as HROvertime;
use App\Http\Controllers\HR\LeaveController as HRLeave;
use App\Http\Controllers\HR\WorkingDayController;
use App\Http\Controllers\HR\EmployeeController as HREmployee;
use App\Http\Controllers\HR\ReportController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    if (auth()->check()) {
        return match(auth()->user()->role) {
            'admin'    => redirect()->route('admin.accounts.index'),
            'hr'       => redirect()->route('hr.dashboard'),
            default    => redirect()->route('employee.dashboard'),
        };
    }
    return redirect()->route('login');
});

// ── Employee Routes ───────────────────────────────────────────────
Route::middleware(['auth', 'role:employee,hr'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');
    Route::get('/attendance', [EmployeeAttendance::class, 'index'])->name('attendance.index');
    Route::post('/attendance/time-in', [EmployeeAttendance::class, 'timeIn'])->name('attendance.time-in');
    Route::post('/attendance/time-out', [EmployeeAttendance::class, 'timeOut'])->name('attendance.time-out');
    Route::get('/requests', [EmployeeLeave::class, 'index'])->name('requests.index');
    Route::post('/requests/leave', [EmployeeLeave::class, 'store'])->name('requests.leave.store');
    Route::post('/requests/overtime', [EmployeeOvertime::class, 'store'])->name('requests.overtime.store');
    Route::get('/profile', [EmployeeProfile::class, 'index'])->name('profile.index');
});

// ── HR Routes ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', [HRDashboard::class, 'index'])->name('dashboard');

    // Attendance
    Route::get('/attendance', [HRAttendance::class, 'index'])->name('attendance.index');
    Route::post('/attendance/time-in', [HRAttendance::class, 'timeIn'])->name('attendance.time-in');
    Route::post('/attendance/time-out', [HRAttendance::class, 'timeOut'])->name('attendance.time-out');

    // Requests - own
    Route::get('/requests', [HRLeave::class, 'index'])->name('requests.index');
    Route::post('/requests/leave', [HRLeave::class, 'store'])->name('requests.leave.store');
    Route::post('/requests/overtime', [HROvertime::class, 'storeSelf'])->name('requests.overtime.store');

    // Validate leave
    Route::post('/requests/leave/{leave}/approve', [HRLeave::class, 'approve'])->name('requests.leave.approve');
    Route::post('/requests/leave/{leave}/reject', [HRLeave::class, 'reject'])->name('requests.leave.reject');

    // Validate overtime
    Route::post('/requests/overtime/{ot}/approve', [HROvertime::class, 'approve'])->name('requests.overtime.approve');
    Route::post('/requests/overtime/{ot}/reject', [HROvertime::class, 'reject'])->name('requests.overtime.reject');

    // Working day
    Route::post('/working-day/open', [WorkingDayController::class, 'open'])->name('working-day.open');
    Route::post('/working-day/close', [WorkingDayController::class, 'close'])->name('working-day.close');
    Route::post('/working-day/reopen', [WorkingDayController::class, 'reopen'])->name('working-day.reopen');
    Route::patch('/attendance/{attendance}/undo-timeout', [HRAttendance::class, 'undoTimeout'])->name('attendance.undo-timeout');

    // Employees
    Route::get('/employees', [HREmployee::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [HREmployee::class, 'show'])->name('employees.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
    Route::get('/reports/leave/pdf', [ReportController::class, 'leavePdf'])->name('reports.leave.pdf');
    Route::get('/reports/overtime/pdf', [ReportController::class, 'overtimePdf'])->name('reports.overtime.pdf');
});

// ── Admin Routes ──────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
    Route::patch('/accounts/{user}/disable', [AccountController::class, 'disable'])->name('accounts.disable');
});

// ── Notifications ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

require __DIR__.'/auth.php';
