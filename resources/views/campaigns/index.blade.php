@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-950 via-emerald-900 to-[#043428] text-white pt-16 pb-24 md:pt-20 md:pb-28">
    <!-- Geometric Islamic Overlay -->
    <div class="absolute inset-0 bg-hero-pattern opacity-80 pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:24px_24px] opacity-15 pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
        <div class="max-w-3xl">
            <!-- Pill Highlight -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/80 border border-emerald-700/60 text-emerald-200 text-xs font-medium mb-6 backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Platform Filantropi Islam Terpercaya &amp; Terintegrasi</span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold font-serif tracking-tight leading-[1.15] text-white">
                Tunaikan Amanah ZISWAF, <br>
                <span class="gold-gradient-text">Berdayakan Umat</span> Mandiri.
            </h1>

            <p class="mt-5 text-base sm:text-lg text-emerald-100/90 leading-relaxed font-normal max-w-2xl">
                Salurkan Zakat, Infaq, Sedekah, dan Wakaf dengan aman dan akuntabel. Dilengkapi pembukuan otomatis standar <strong class="text-white">PSAK 109</strong>, survei mustahik <strong class="text-white">Had Kifayah</strong>, serta kwitansi digital resmi ber-QR Code ke WhatsApp Anda.
            </p>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a href="{{ route('donation.create') }}" 
                   class="inline-flex items-center justify-center px-7 py-3.5 text-sm sm:text-base font-semibold text-emerald-950 bg-amber-400 rounded-full shadow-lg shadow-amber-400/20 hover:bg-amber-300 hover:shadow-xl hover:shadow-amber-400/30 active:scale-95 transition-all duration-200">
                    <span>Donasi Cepat Sekarang</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
                <a href="{{ route('zakat') }}" 
                   class="inline-flex items-center justify-center px-6 py-3.5 text-sm sm:text-base font-medium text-white border border-emerald-600/80 rounded-full bg-emerald-900/40 hover:bg-emerald-800/60 backdrop-blur-sm transition-all duration-200">
                    <svg class="w-4 h-4 mr-2 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Kalkulator Zakat Otomatis</span>
                </a>
            </div>
        </div>

        <!-- Live Impact Stats Ticker -->
        <div class="mt-14 pt-8 border-t border-emerald-800/60 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="space-y-1">
                <p class="text-xs font-medium text-emerald-300/80 uppercase tracking-wider">Total Donasi Terhimpun</p>
                <p class="text-2xl sm:text-3xl font-bold font-serif text-white tracking-tight" id="stat-collected">
                    Rp 650.250.000
                </p>
                <p class="text-[11px] text-emerald-400/70">Tersebar di 6 program aktif</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-medium text-emerald-300/80 uppercase tracking-wider">Mustahik &amp; Asnaf</p>
                <p class="text-2xl sm:text-3xl font-bold font-serif text-white tracking-tight" id="stat-mustahik">
                    500+ Jiwa
                </p>
                <p class="text-[11px] text-emerald-400/70">Terverifikasi Had Kifayah</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-medium text-emerald-300/80 uppercase tracking-wider">Akuntabilitas Finansial</p>
                <p class="text-2xl sm:text-3xl font-bold font-serif text-amber-300 tracking-tight">
                    100% Balanced
                </p>
                <p class="text-[11px] text-emerald-400/70">PSAK 109 Double-Entry</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs font-medium text-emerald-300/80 uppercase tracking-wider">Kecepatan Verifikasi</p>
                <p class="text-2xl sm:text-3xl font-bold font-serif text-white tracking-tight">
                    Instan (Real-Time)
                </p>
                <p class="text-[11px] text-emerald-400/70">QRIS &amp; Virtual Account</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Catalog Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16">
    <!-- Header with Category Filter -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-stone-200">
        <div>
            <span class="text-xs font-semibold tracking-wider text-emerald-800 uppercase">Program Pilihan</span>
            <h2 class="text-2xl sm:text-3xl font-bold font-serif text-stone-900 mt-1">Katalog Program ZISWAF Aktif</h2>
            <p class="text-sm text-stone-500 mt-1">Pilih program kemaslahatan yang ingin Anda dukung hari ini.</p>
        </div>

        <!-- Filter Chips -->
        <div class="flex flex-wrap gap-2 text-xs sm:text-sm">
            <a href="{{ route('home') }}" 
               class="px-4 py-2 rounded-full font-medium transition-all {{ ! $category ? 'bg-emerald-800 text-white shadow-sm' : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-600' }}">
                Semua Program
            </a>
            @foreach (\App\Enums\CampaignCategory::cases() as $c)
                <a href="{{ route('home', ['kategori' => $c->value]) }}" 
                   class="px-4 py-2 rounded-full font-medium transition-all flex items-center gap-1.5 {{ $category === $c->value ? 'bg-emerald-800 text-white shadow-sm' : 'bg-white border border-stone-200 text-stone-600 hover:border-emerald-600' }}">
                    <span>{{ $c->label() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Campaign Cards Grid -->
    <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($campaigns as $campaign)
            <div class="group flex flex-col justify-between overflow-hidden rounded-2xl bg-white border border-stone-200/90 shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div>
                    <!-- Image Banner with Category Badge -->
                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-stone-100">
                        @if ($campaign->cover_url)
                            <img src="{{ $campaign->cover_url }}" alt="{{ $campaign->title }}" 
                                 class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @else
                            <div class="h-full w-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-700">
                                <svg class="w-12 h-12 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 4.8 5.3.8-3.8 3.8.9 5.3L12 16.2l-4.8 2.5.9-5.3-3.8-3.8 5.3-.8L12 2z"/></svg>
                            </div>
                        @endif

                        <!-- Floating Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase shadow-sm
                                @if($campaign->category->value === 'zakat') bg-amber-500 text-white
                                @elseif($campaign->category->value === 'wakaf') bg-teal-600 text-white
                                @elseif($campaign->category->value === 'kemanusiaan') bg-rose-600 text-white
                                @else bg-emerald-700 text-white @endif">
                                {{ $campaign->category->label() }}
                            </span>
                        </div>

                        @if ($campaign->days_left !== null)
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-stone-900/70 text-white backdrop-blur-xs">
                                    {{ $campaign->days_left }} hari lagi
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Content Body -->
                    <div class="p-5 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-stone-900 group-hover:text-emerald-800 transition line-clamp-2 leading-snug">
                            <a href="{{ route('campaigns.show', $campaign) }}">
                                {{ $campaign->title }}
                            </a>
                        </h3>

                        <p class="mt-2 text-xs text-stone-500 line-clamp-2 leading-relaxed">
                            {{ strip_tags($campaign->description) }}
                        </p>

                        <!-- Progress Bar Component -->
                        @include('campaigns._progress', ['campaign' => $campaign])
                    </div>
                </div>

                <!-- Card Footer CTA -->
                <div class="px-5 pb-5 sm:px-6 sm:pb-6 pt-0">
                    <a href="{{ route('donation.create', ['campaign' => $campaign->slug]) }}" 
                       class="block w-full py-2.5 text-center text-xs sm:text-sm font-semibold rounded-xl text-emerald-800 bg-emerald-50 hover:bg-emerald-700 hover:text-white border border-emerald-200 transition-all duration-200">
                        Donasi Program Ini ➔
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-stone-200">
                <p class="text-stone-500">Belum ada kampanye aktif dalam kategori ini.</p>
                <a href="{{ route('home') }}" class="mt-3 inline-block text-xs font-semibold text-emerald-700 hover:underline">
                    Kembali ke Semua Program
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-12 flex justify-center">
        {{ $campaigns->links() }}
    </div>
</section>

<!-- Trust & Syariah Accountability Section -->
<section id="tentang-kami" class="bg-white border-y border-stone-200/80 py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="max-w-2xl mx-auto text-center">
            <span class="text-xs font-semibold tracking-wider text-emerald-800 uppercase">Standar Kepercayaan</span>
            <h2 class="text-2xl sm:text-3xl font-bold font-serif text-stone-900 mt-1">Mengapa Menyalurkan Melalui Kami?</h2>
            <p class="text-sm text-stone-500 mt-2">Prinsip dasar tata kelola filantropi modern yang akuntabel di hadapan syariat dan hukum negara.</p>
        </div>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="p-6 rounded-2xl bg-stone-50/70 border border-stone-200/70 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-lg">
                    ⚖️
                </div>
                <h4 class="font-bold text-stone-900 text-base">PSAK 109 Akuntansi Syariah</h4>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Setiap transaksi otomatis dijurnal ke buku besar berpasangan (*double-entry*) memisahkan dana zakat, infaq, wakaf, dan amil tanpa campur aduk.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-stone-50/70 border border-stone-200/70 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg">
                    🔍
                </div>
                <h4 class="font-bold text-stone-900 text-base">Survei Had Kifayah</h4>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Verifikasi mustahik berbasis standar ilmiah BAZNAS menghitung rasio pemenuhan kebutuhan dasar (Fakir/Miskin) sehingga tepat sasaran.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-stone-50/70 border border-stone-200/70 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-lg">
                    📱
                </div>
                <h4 class="font-bold text-stone-900 text-base">Kwitansi Digital WhatsApp</h4>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Notifikasi instan tanda terima berformat PDF dilengkapi QR Code tanda tangan digital yang dapat diverifikasi keabsahannya kapan saja.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-stone-50/70 border border-stone-200/70 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-bold text-lg">
                    🔒
                </div>
                <h4 class="font-bold text-stone-900 text-base">Pembayaran Aman &amp; Idempoten</h4>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Mendukung QRIS, GoPay, dan Virtual Account bank terkemuka dengan webhook idempoten anti double-credit.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Zakat Banner CTA -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white p-8 sm:p-12 shadow-xl">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-400 text-emerald-950 mb-4">
                Fitur Unggulan
            </span>
            <h3 class="text-2xl sm:text-4xl font-bold font-serif leading-tight">
                Bingung Menghitung Kewajiban Zakat Anda?
            </h3>
            <p class="mt-3 text-sm sm:text-base text-emerald-100 leading-relaxed">
                Gunakan kalkulator zakat otomatis kami yang terhubung langsung dengan harga emas harian untuk menghitung nishab zakat profesi maupun zakat maal secara akurat.
            </p>
            <div class="mt-6 flex flex-wrap gap-4">
                <a href="{{ route('zakat') }}" 
                   class="px-6 py-3 rounded-full text-sm font-bold bg-white text-emerald-900 hover:bg-emerald-50 shadow-md transition">
                    Buka Kalkulator Zakat ➔
                </a>
                <a href="{{ route('donation.create', ['type' => 'zakat']) }}" 
                   class="px-6 py-3 rounded-full text-sm font-semibold border border-white/40 text-white hover:bg-white/10 transition">
                    Langsung Bayar Zakat
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.anime) {
            window.anime({
                targets: '.group',
                opacity: [0, 1],
                translateY: [15, 0],
                delay: window.anime.stagger(100),
                duration: 600,
                easing: 'easeOutQuad'
            });
        }
    });
</script>
@endsection
