<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Throwable;

class DonationController extends Controller
{
    public function create(Request $request)
    {
        $campaign = $request->filled('campaign')
            ? Campaign::active()->where('slug', $request->query('campaign'))->first()
            : null;

        return view('donation.checkout', [
            'campaign' => $campaign,
            'campaigns' => Campaign::active()->orderBy('title')->get(['id', 'title', 'category']),
            'presets' => config('ziswaf.preset_amounts'),
            'methods' => config('ziswaf.payment_methods'),
            'initialAmount' => (int) $request->query('amount', 0),
            'initialType' => $request->query('type'),
        ]);
    }

    public function store(StoreDonationRequest $request, DonationService $donations)
    {
        try {
            $donation = $donations->create($request->validated() + [
                'referral_code' => $request->input('referral_code', $request->cookie('ref')),
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['payment' => 'Pembayaran belum bisa diproses. Silakan coba lagi sebentar lagi.']);
        }

        return redirect()->route('donation.pay', $donation->invoice_number);
    }

    public function pay(string $invoice, PaymentGatewayService $gateway)
    {
        $donation = Donation::with('campaign')->where('invoice_number', $invoice)->firstOrFail();

        if ($donation->payment_status === PaymentStatus::Paid) {
            return redirect()->route('donation.thanks', $invoice);
        }

        return view('donation.pay', [
            'donation' => $donation,
            'snapJs' => $gateway->snapJsUrl(),
            'clientKey' => config('ziswaf.midtrans.client_key'),
        ]);
    }

    public function thanks(string $invoice)
    {
        $donation = Donation::with('campaign', 'donor')->where('invoice_number', $invoice)->firstOrFail();

        return view('donation.thanks', compact('donation'));
    }
}
