<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdated extends Notification
{
    public function __construct(public LeaveRequest $leave) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'leave',
            'status'     => $this->leave->status,
            'message'    => 'Your leave request (' . $this->leave->leaveType->name . ') has been ' . $this->leave->status . '.',
            'leave_id'   => $this->leave->leave_id,
            'start_date' => $this->leave->start_date->toDateString(),
            'end_date'   => $this->leave->end_date->toDateString(),
        ];
    }
}
