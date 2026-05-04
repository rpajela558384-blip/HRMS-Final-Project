<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $primaryKey = 'leave_type_id';

    protected $fillable = [
        'name',
        'default_balance',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id', 'leave_type_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id', 'leave_type_id');
    }
}
