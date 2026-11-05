<?php

namespace App\Jobs;

use App\Models\Donation;
use App\Services\Notification\NotificationService;
use App\Services\ReceiptService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDonationReceiptJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(public int $donationId) {}

    public function handle(NotificationService $notifier, ReceiptService $receipts): void
    {
        $donation = Donation::with('donor', 'campaign')->find($this->donationId);
        if (! $donation || ! $donation->is_paid) {
            return;
        }

        $receipts->store($donation);

        $message = sprintf(
            "Assalamu'alaikum %s,\n\nJazakumullahu khairan. %s sebesar Rp %s untuk \"%s\" telah kami terima.\nNo. Invoice: %s\n\nKwitansi: %s\n\nSemoga menjadi amal jariyah dan berkah.\n— %s",
            $donation->display_name,
            $receipts->akad($donation),
            number_format($donation->amount, 0, ',', '.'),
            $donation->campaign?->title ?? 'Donasi Umum',
            $donation->invoice_number,
            $receipts->downloadUrl($donation),
            config('ziswaf.org.name')
        );

        if (! $notifier->send($donation->donor->phone_number, $message, $receipts->downloadUrl($donation))) {
            $this->fail(new \RuntimeException('Notifikasi kwitansi gagal dikirim: '.$donation->invoice_number));
        }
    }
}
