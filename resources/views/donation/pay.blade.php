@extends('layouts.app')
@section('title', 'Pembayaran '.$donation->invoice_number)

@section('content')
<div class="mx-auto max-w-md px-4 py-14 text-center">
    <h1 class="text-xl font-semibold">Menyiapkan pembayaran…</h1>
    <p class="mt-2 text-sm text-stone-500">Invoice {{ $donation->invoice_number }} · Rp {{ number_format($donation->total_charge, 0, ',', '.') }}</p>
    <button id="pay-button" class="mt-6 rounded-full bg-emerald-700 px-6 py-3 font-medium text-white">Buka jendela pembayaran</button>
    <p class="mt-4 text-xs text-stone-400">Status donasi diperbarui otomatis setelah pembayaran terkonfirmasi.</p>
</div>

<script src="{{ $snapJs }}" data-client-key="{{ $clientKey }}"></script>
<script>
    const token = @js($donation->snap_token);
    const thanks = @js(route('donation.thanks', $donation->invoice_number));
    function pay() {
        window.snap.pay(token, {
            onSuccess: () => location.href = thanks,
            onPending: () => location.href = thanks,
            onError: () => alert('Pembayaran gagal. Silakan coba lagi.'),
        });
    }
    document.getElementById('pay-button').addEventListener('click', pay);
    window.addEventListener('load', pay);
</script>
@endsection
