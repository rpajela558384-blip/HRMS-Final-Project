<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    protected $primaryKey = 'ot_id';

    protected $fillable = [
        'employee_id',
        'work_date',
        'requested_end_time',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'work_date'          => 'date',
        'requested_end_time' => 'datetime',
        'approved_at'        => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
