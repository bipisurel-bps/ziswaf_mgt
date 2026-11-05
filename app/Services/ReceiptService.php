<?php

namespace App\Services;

use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceiptService
{
    public function verificationUrl(Donation $donation): string
    {
        return URL::signedRoute('receipt.verify', ['invoice' => $donation->invoice_number]);
    }

    /** Akad text per donation type. */
    public function akad(Donation $donation): string
    {
        return match ($donation->donation_type) {
            'zakat' => 'Zakat',
            'wakaf' => 'Wakaf',
            'kemanusiaan' => 'Sedekah / Donasi Kemanusiaan',
            default => 'Infaq / Sedekah',
        };
    }

    public function renderPdf(Donation $donation)
    {
        $donation->loadMissing('donor', 'campaign');

        // SVG QR avoids requiring imagick; dompdf renders inline SVG via data URI.
        $qr = 'data:image/svg+xml;base64,'.base64_encode(
            QrCode::format('svg')->size(120)->margin(0)->generate($this->verificationUrl($donation))
        );

        return Pdf::loadView('receipt.pdf', [
            'donation' => $donation,
            'org' => config('ziswaf.org'),
            'akad' => $this->akad($donation),
            'qr' => $qr,
        ])->setPaper('a5', 'landscape');
    }

    /** Store PDF on the private disk; returns the relative path. */
    public function store(Donation $donation): string
    {
        $path = 'receipts/'.$donation->invoice_number.'.pdf';
        Storage::disk('local')->put($path, $this->renderPdf($donation)->output());

        return $path;
    }

    /** Short-lived signed URL for downloading the receipt. */
    public function downloadUrl(Donation $donation): string
    {
        return URL::temporarySignedRoute('receipt.download', now()->addDays(7), ['invoice' => $donation->invoice_number]);
    }
}
