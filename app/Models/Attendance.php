<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'employee_id',
        'work_date',
        'time_in',
        'time_out',
        'is_late',
        'is_undertime',
        'overtime_hours',
        'is_auto_timeout',
        'is_off_shift',
    ];

    protected $casts = [
        'work_date'       => 'date',
        'time_in'         => 'datetime',
        'time_out'        => 'datetime',
        'is_late'         => 'boolean',
        'is_undertime'    => 'boolean',
        'is_auto_timeout' => 'boolean',
        'is_off_shift'    => 'boolean',
        'overtime_hours'  => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'attendance_id';
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function getTotalHoursAttribute(): float
    {
        if (!$this->time_in || !$this->time_out) return 0;
        return round($this->time_in->diffInMinutes($this->time_out) / 60, 2);
    }
}
