<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\DonationService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGatewayService $gateway, DonationService $donations): JsonResponse
    {
        $payload = $request->all();

        if (! $gateway->verifySignature($payload)) {
            Log::warning('[Midtrans webhook] signature tidak valid', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'invalid signature'], 403);
        }

        $changed = $donations->handleGatewayNotification($payload);

        // Always 200 for valid signatures so Midtrans stops retrying (replays are no-ops).
        return response()->json(['message' => 'ok', 'processed' => $changed]);
    }
}
