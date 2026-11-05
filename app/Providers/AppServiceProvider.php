<?php

namespace App\Providers;

use App\Services\Notification\FonnteNotificationService;
use App\Services\Notification\LogNotificationService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationService::class, function () {
            return match (config('ziswaf.notifications.driver')) {
                'fonnte' => new FonnteNotificationService(
                    config('ziswaf.notifications.fonnte.token'),
                    config('ziswaf.notifications.fonnte.endpoint'),
                ),
                default => new LogNotificationService,
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
