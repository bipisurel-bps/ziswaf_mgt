<?php

use App\Http\Controllers\Webhook\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

// POST /api/webhooks/midtrans  (no CSRF on api group; protected by signature verification)
Route::post('/webhooks/midtrans', MidtransWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.midtrans');
