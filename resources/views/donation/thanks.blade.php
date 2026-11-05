@extends('layouts.app')
@section('title', 'Konfirmasi Donasi ' . $donation->invoice_number)

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 py-14 md:py-20 text-center">
    <div class="rounded-3xl bg-white border border-slate-200 shadow-xl p-8 sm:p-10 space-y-6">
        
        @if ($donation->is_paid)
            <!-- Paid Success State in Royal Blue -->
            <div class="w-16 h-16 mx-auto rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-3xl shadow-xs border border-blue-200">
                ✓
            </div>

            <div class="space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    Pembayaran Berhasil Dikonfirmasi
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold font-serif text-slate-900">
                    Jazakumullahu Khairan
                </h1>
                <p class="text-sm font-semibold text-blue-900">
                    {{ $donation->display_name }}
                </p>
                <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto">
                    Donasi sebesar <strong class="text-slate-800">Rp {{ number_format($donation->amount, 0, ',', '.') }}</strong> telah kami terima secara sah dan otomatis dibukukan ke dalam Buku Besar Akuntansi Syariah PSAK 109.
                </p>
            </div>

            <!-- Receipt & Details Card -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-2.5 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Nomor Invoice:</span>
                    <span class="font-mono font-bold text-slate-800">{{ $donation->invoice_number }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Program:</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[220px]">{{ $donation->campaign ? $donation->campaign->title : 'Donasi Umum' }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Waktu Pembayaran:</span>
                    <span class="font-medium text-slate-800">{{ $donation->paid_at ? $donation->paid_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Metode:</span>
                    <span class="uppercase font-semibold text-slate-800">{{ $donation->payment_method ?? 'QRIS' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="font-bold text-slate-900">Total Diterima:</span>
                    <span class="font-bold text-blue-800 text-base">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3 pt-2">
                <a href="{{ app(\App\Services\ReceiptService::class)->downloadUrl($donation) }}" target="_blank"
                   class="inline-flex items-center justify-center w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-gradient-to-r from-blue-700 to-indigo-800 hover:from-blue-600 hover:to-indigo-700 shadow-md shadow-blue-800/20 active:scale-95 transition-all duration-200 gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh Kwitansi PDF Resmi (Ber-QR Code)</span>
                </a>

                <a href="{{ route('home') }}" 
                   class="inline-block text-xs font-semibold text-slate-500 hover:text-blue-800 transition">
                    Kembali ke Halaman Beranda ➔
                </a>
            </div>

        @elseif ($donation->payment_status->value === 'pending')
            <!-- Pending State -->
            <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-2xl border border-amber-200">
                ⏳
            </div>
            <div class="space-y-2">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                    Menunggu Penyelesaian Pembayaran
                </span>
                <h1 class="text-2xl font-bold font-serif text-slate-900">
                    Menunggu Pembayaran
                </h1>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Invoice <span class="font-mono font-bold">{{ $donation->invoice_number }}</span> sebesar <strong class="text-slate-800">Rp {{ number_format($donation->total_charge, 0, ',', '.') }}</strong> siap dibayarkan.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('donation.pay', $donation->invoice_number) }}" 
                   class="block w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-blue-700 hover:bg-blue-800 shadow-md transition">
                    Lanjutkan ke Pembayaran ➔
                </a>
            </div>

        @else
            <!-- Expired / Failed State -->
            <div class="w-16 h-16 mx-auto rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-2xl border border-red-200">
                ✕
            </div>
            <div class="space-y-2">
                <h1 class="text-2xl font-bold font-serif text-slate-900">
                    Pembayaran {{ $donation->payment_status->label() }}
                </h1>
                <p class="text-xs text-slate-500">
                    Waktu transaksi telah habis atau dibatalkan. Anda dapat mengulang donasi baru kapan saja.
                </p>
            </div>
            <div class="pt-4">
                <a href="{{ route('donation.create') }}" 
                   class="block w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-blue-700 hover:bg-blue-800 transition">
                    Ulangi Donasi Baru ➔
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
