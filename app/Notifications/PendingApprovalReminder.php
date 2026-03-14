<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PendingApprovalReminder extends Notification
{
    public function __construct(public $requests) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'reminder',
            'message' => "Ada {$this->requests->count()} pengajuan cuti yang menunggu persetujuan Anda lebih dari 2 hari.",
            'count' => $this->requests->count(),
        ];
    }
}
