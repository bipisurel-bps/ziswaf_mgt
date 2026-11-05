<?php

namespace App\Http\Middleware;

use App\Models\Fundraiser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Persists ?ref=CODE into a cookie so later checkouts are attributed to the fundraiser. */
class CaptureReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $code = strtoupper(trim((string) $request->query('ref', '')));
        if ($code !== '' && Fundraiser::where('referral_code', $code)->exists()) {
            $response->headers->setCookie(cookie('ref', $code, 60 * 24 * config('ziswaf.referral_cookie_days')));
        }

        return $response;
    }
}
