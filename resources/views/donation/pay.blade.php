@extends('layouts.app')
@section('title', 'Pembayaran Invoice ' . $donation->invoice_number)

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-16 md:py-24 text-center">
    <div class="rounded-3xl bg-white border border-stone-200 shadow-xl p-8 space-y-6">
        <!-- Animated Spinner / Badge -->
        <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-2xl border border-emerald-200">
            <svg class="w-8 h-8 animate-spin text-emerald-700" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>

        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                Menghubungkan Payment Gateway
            </span>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-stone-900">
                Menyiapkan Jendela Pembayaran…
            </h1>
            <p class="text-xs text-stone-500">
                Mohon selesaikan pembayaran untuk mengonfirmasi donasi Anda secara otomatis.
            </p>
        </div>

        <!-- Invoice Summary Box -->
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-left space-y-2 text-xs">
            <div class="flex justify-between text-stone-500">
                <span>Nomor Invoice:</span>
                <span class="font-mono font-bold text-stone-800">{{ $donation->invoice_number }}</span>
            </div>
            <div class="flex justify-between text-stone-500">
                <span>Program:</span>
                <span class="font-medium text-stone-800 truncate max-w-[200px]">{{ $donation->campaign ? $donation->campaign->title : 'Donasi Umum' }}</span>
            </div>
            <div class="flex justify-between text-stone-500">
                <span>Donatur:</span>
                <span class="font-medium text-stone-800">{{ $donation->display_name }}</span>
            </div>
            <div class="pt-2 border-t border-stone-200 flex justify-between items-baseline">
                <span class="font-bold text-stone-900">Total Nominal:</span>
                <span class="font-bold text-emerald-800 text-base">Rp {{ number_format($donation->total_charge, 0, ',', '.') }}</span>
            </div>
        </div>

        <button id="pay-button" 
                class="w-full py-3.5 px-6 rounded-2xl font-bold text-white bg-gradient-to-r from-emerald-700 to-emerald-800 hover:from-emerald-600 hover:to-emerald-700 shadow-lg shadow-emerald-800/20 active:scale-95 transition-all duration-200">
            Buka Jendela Pembayaran (Snap) ➔
        </button>

        <p class="text-[11px] text-stone-400 leading-relaxed">
            Jika jendela pembayaran otomatis tertutup atau diblokir browser, klik tombol di atas.
        </p>
    </div>
</div>

<script src="{{ $snapJs }}" data-client-key="{{ $clientKey }}"></script>
<script>
    const token = @js($donation->snap_token);
    const thanks = @js(route('donation.thanks', $donation->invoice_number));
    function pay() {
        if (!window.snap || !token) return;
        window.snap.pay(token, {
            onSuccess: () => location.href = thanks,
            onPending: () => location.href = thanks,
            onError: () => alert('Pembayaran belum berhasil. Silakan coba metode lain.'),
            onClose: () => console.log('Snap window closed'),
        });
    }
    document.getElementById('pay-button').addEventListener('click', pay);
    window.addEventListener('load', () => setTimeout(pay, 600));
</script>
@endsection
