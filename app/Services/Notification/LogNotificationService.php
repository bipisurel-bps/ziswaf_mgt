<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Log;

/** Default driver for local development & tests: writes to the Laravel log. */
class LogNotificationService implements NotificationService
{
    public function send(string $to, string $message, ?string $attachmentUrl = null): bool
    {
        Log::info('[Notification:log]', compact('to', 'message', 'attachmentUrl'));

        return true;
    }
}
