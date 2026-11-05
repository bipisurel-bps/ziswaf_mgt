<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    /** Public QR target (signed). Shows minimal, non-sensitive proof of the donation. */
    public function verify(string $invoice)
    {
        $donation = Donation::with('campaign')->where('invoice_number', $invoice)->where('payment_status', 'paid')->first();

        return view('receipt.verify', ['donation' => $donation, 'invoice' => $invoice]);
    }

    public function download(string $invoice, ReceiptService $receipts)
    {
        $donation = Donation::with('donor', 'campaign')->where('invoice_number', $invoice)->where('payment_status', 'paid')->firstOrFail();

        $path = 'receipts/'.$donation->invoice_number.'.pdf';
        if (! Storage::disk('local')->exists($path)) {
            $receipts->store($donation);
        }

        return Storage::disk('local')->download($path, 'Kwitansi-'.$donation->invoice_number.'.pdf');
    }
}
