@extends('layouts.app')
@section('title', 'Terima kasih')

@section('content')
<div class="mx-auto max-w-md px-4 py-14 text-center">
    @if ($donation->is_paid)
        <h1 class="text-2xl font-semibold text-emerald-800">Jazakumullahu khairan, {{ $donation->display_name }}</h1>
        <p class="mt-2">Donasi Rp {{ number_format($donation->amount, 0, ',', '.') }} telah kami terima. Kwitansi dikirim ke WhatsApp Anda.</p>
    @elseif ($donation->payment_status->value === 'pending')
        <h1 class="text-2xl font-semibold">Menunggu pembayaran</h1>
        <p class="mt-2">Selesaikan pembayaran Anda. Halaman ini akan menampilkan konfirmasi setelah dana diterima.</p>
        <a href="{{ route('donation.pay', $donation->invoice_number) }}" class="mt-5 inline-block rounded-full bg-emerald-700 px-6 py-3 text-white">Lanjutkan pembayaran</a>
    @else
        <h1 class="text-2xl font-semibold text-red-700">Pembayaran {{ $donation->payment_status->label() }}</h1>
        <a href="{{ route('donation.create') }}" class="mt-5 inline-block rounded-full bg-emerald-700 px-6 py-3 text-white">Coba lagi</a>
    @endif
    <p class="mt-6 text-xs text-stone-400">Invoice {{ $donation->invoice_number }}</p>
</div>
@endsection
