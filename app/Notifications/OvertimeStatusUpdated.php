<?php

namespace App\Notifications;

use App\Models\OvertimeRequest;
use Illuminate\Notifications\Notification;

class OvertimeStatusUpdated extends Notification
{
    public function __construct(public OvertimeRequest $ot) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'overtime',
            'status'    => $this->ot->status,
            'message'   => 'Your overtime request for ' . $this->ot->work_date->format('M d, Y') . ' has been ' . $this->ot->status . '.',
            'ot_id'     => $this->ot->ot_id,
            'work_date' => $this->ot->work_date->toDateString(),
        ];
    }
}
