<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDay extends Model
{
    protected $primaryKey = 'working_day_id';

    protected $fillable = [
        'work_date',
        'status',
        'opened_by',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by', 'user_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function todayIsOpen(): bool
    {
        $today = static::whereDate('work_date', today())->first();
        return $today && $today->status === 'open';
    }

    public static function today(): ?self
    {
        return static::whereDate('work_date', today())->first();
    }
}
