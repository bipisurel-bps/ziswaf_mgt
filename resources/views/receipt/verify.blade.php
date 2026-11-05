@extends('layouts.app')
@section('title', 'Verifikasi Kwitansi')

@section('content')
<div class="mx-auto max-w-md px-4 py-14 text-center">
    @if ($donation)
        <h1 class="text-2xl font-semibold text-emerald-800">✔ Kwitansi Valid</h1>
        <dl class="mt-5 space-y-2 rounded-xl border bg-white p-5 text-left text-sm">
            <div><dt class="text-stone-500">Invoice</dt><dd class="font-medium">{{ $donation->invoice_number }}</dd></div>
            <div><dt class="text-stone-500">Donatur</dt><dd class="font-medium">{{ $donation->display_name }}</dd></div>
            <div><dt class="text-stone-500">Jumlah</dt><dd class="font-medium">Rp {{ number_format($donation->amount, 0, ',', '.') }}</dd></div>
            <div><dt class="text-stone-500">Tanggal</dt><dd class="font-medium">{{ $donation->paid_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</dd></div>
        </dl>
    @else
        <h1 class="text-2xl font-semibold text-red-700">Kwitansi tidak ditemukan</h1>
        <p class="mt-2">Invoice {{ $invoice }} tidak terdaftar atau belum dibayar.</p>
    @endif
</div>
@endsection
