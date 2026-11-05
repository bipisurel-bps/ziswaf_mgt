<?php

namespace App\Services\Notification;

interface NotificationService
{
    /**
     * Send a text message (optionally with a document attachment URL).
     *
     * @param  string  $to  Phone number in 628xxx format
     * @return bool true when accepted by the provider
     */
    public function send(string $to, string $message, ?string $attachmentUrl = null): bool;
}
