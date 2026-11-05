@extends('layouts.app')
@section('title', 'Verifikasi Keaslian Kwitansi Donasi')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-16 md:py-20 text-center">
    @if ($donation)
        <div class="rounded-3xl bg-white border border-blue-200 shadow-2xl p-8 sm:p-10 space-y-6 relative overflow-hidden">
            <!-- Top Certificate Badge -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-blue-50 rounded-full flex items-end justify-start p-6 text-blue-200">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 4.8 5.3.8-3.8 3.8.9 5.3L12 16.2l-4.8 2.5.9-5.3-3.8-3.8 5.3-.8L12 2z"/></svg>
            </div>

            <div class="w-16 h-16 mx-auto rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-3xl shadow-xs border border-blue-300">
                ✓
            </div>

            <div class="space-y-1">
                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-300">
                    ✔ Kwitansi Valid
                </span>
                <h1 class="text-2xl font-bold font-serif text-slate-900 pt-2">
                    Dokumen Asli Lembaga
                </h1>
                <p class="text-xs text-slate-500">
                    Sistem Integritas Digital {{ config('ziswaf.org.name') }}
                </p>
            </div>

            <!-- Receipt Metadata Table -->
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 text-left text-xs space-y-3">
                <div class="flex justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Nomor Invoice</span>
                    <span class="font-mono font-bold text-slate-900">{{ $donation->invoice_number }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Nama Donatur</span>
                    <span class="font-bold text-slate-900">{{ $donation->display_name }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Program</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[200px]">{{ $donation->campaign ? $donation->campaign->title : 'Donasi Umum' }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-200">
                    <span class="text-slate-500 font-medium">Waktu Transaksi</span>
                    <span class="font-medium text-slate-800">{{ $donation->paid_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
                </div>
                <div class="flex justify-between items-baseline pt-1">
                    <span class="text-slate-700 font-bold">Nominal Donasi</span>
                    <span class="text-blue-800 font-bold text-base">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <a href="{{ app(\App\Services\ReceiptService::class)->downloadUrl($donation) }}" target="_blank"
                   class="inline-flex items-center justify-center w-full py-3 px-4 rounded-xl font-bold text-xs text-white bg-blue-800 hover:bg-blue-900 shadow-sm transition gap-2">
                    <span>Lihat / Cetak Kwitansi PDF</span>
                </a>
                <p class="text-[10px] text-slate-400">
                    © {{ date('Y') }} jaddadeveloper (part of jadda ecosystem).
                </p>
                <a href="{{ route('home') }}" class="inline-block text-xs text-slate-400 hover:text-slate-700 transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    @else
        <div class="rounded-3xl bg-white border border-red-200 shadow-xl p-8 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-2xl border border-red-200">
                ✕
            </div>
            <h1 class="text-2xl font-bold font-serif text-slate-900">
                Kwitansi Tidak Ditemukan
            </h1>
            <p class="text-xs text-slate-500">
                Nomor invoice <span class="font-mono font-bold">{{ $invoice }}</span> tidak terdaftar dalam basis data kami atau belum berstatus lunas.
            </p>
            <a href="{{ route('home') }}" class="inline-block pt-2 text-xs font-semibold text-blue-800 hover:underline">
                Kembali ke Beranda
            </a>
        </div>
    @endif
</div>
@endsection
