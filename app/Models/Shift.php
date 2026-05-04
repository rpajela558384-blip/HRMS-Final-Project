<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time',
        'grace_minutes',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'shift_id', 'shift_id');
    }
}
