<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('ziswaf.org.name')) — Lembaga Pengelola ZISWAF Amanah &amp; Akuntabel</title>
    <meta name="description" content="@yield('description', 'Salurkan zakat, infaq, sedekah, dan wakaf Anda secara aman, transparan, dan akuntabel sesuai standar syariah PSAK 109.')">
    
    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f8fafc] text-slate-800 font-sans antialiased selection:bg-blue-600 selection:text-white flex flex-col justify-between"
      x-data="{ 
          mobileMenu: false,
          toast: { show: false, message: '', type: 'success' },
          notify(msg, type = 'success') {
              this.toast.message = msg;
              this.toast.type = type;
              this.toast.show = true;
              setTimeout(() => { this.toast.show = false; }, 3500);
          }
      }">

    <!-- Top Institutional Ribbon -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-blue-950 text-slate-200 text-xs py-2 px-4 border-b border-blue-900/40">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    PSAK 109
                </span>
                <span class="hidden sm:inline">Pencatatan Akuntansi Syariah Terintegrasi &amp; Diaudit Terbuka</span>
                <span class="sm:hidden">Akuntansi Syariah Terintegrasi</span>
            </div>
            <div class="flex items-center gap-4 text-slate-300/80">
                <a href="{{ route('zakat') }}" class="hover:text-amber-300 transition flex items-center gap-1">
                    <span>✨ Cek Nishab Emas Hari Ini</span>
                </a>
                <span class="hidden md:inline">•</span>
                <a href="/admin" class="hidden md:inline hover:text-white transition">Masuk Pengurus (ERM)</a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all duration-200">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 py-3.5">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 flex items-center justify-center text-white shadow-md shadow-blue-900/20 group-hover:scale-105 transition-transform duration-300">
                    <!-- Geometric 8-pointed Islamic Star Geometry SVG in Sapphire & Gold -->
                    <svg class="w-6 h-6 text-amber-300" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l2.4 4.8 5.3.8-3.8 3.8.9 5.3L12 16.2l-4.8 2.5.9-5.3-3.8-3.8 5.3-.8L12 2z"/>
                        <circle cx="12" cy="12" r="3" fill="#0f172a" />
                    </svg>
                </div>
                <div>
                    <span class="block text-lg font-bold tracking-tight text-slate-900 font-serif leading-tight">
                        {{ config('ziswaf.org.name') }}
                    </span>
                    <span class="block text-[11px] font-semibold tracking-wide text-blue-700 uppercase">
                        Filantropi &amp; ZISWAF Terpercaya
                    </span>
                </div>
            </a>

            <!-- Desktop Nav Items -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-700">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition {{ request()->routeIs('home') ? 'text-blue-700 font-semibold' : '' }}">
                    Katalog Program
                </a>
                <a href="{{ route('zakat') }}" class="hover:text-blue-700 transition flex items-center gap-1.5 {{ request()->routeIs('zakat') ? 'text-blue-700 font-semibold' : '' }}">
                    <span>Kalkulator Zakat</span>
                    <span class="inline-block px-1.5 py-0.2 rounded-full text-[10px] bg-blue-100 text-blue-800 font-bold">Auto</span>
                </a>
                <a href="{{ route('home') }}#tentang-kami" class="hover:text-blue-700 transition">
                    Akuntabilitas Syariah
                </a>
            </nav>

            <!-- Desktop Action CTA -->
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('zakat') }}" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-blue-800 transition">
                    Hitung Zakat
                </a>
                <a href="{{ route('donation.create') }}" 
                   class="relative inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-700 to-indigo-800 rounded-full shadow-md shadow-blue-800/25 hover:from-blue-600 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-700/30 active:scale-95 transition-all duration-200">
                    <span>Tunaikan Donasi</span>
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            <!-- Mobile Hamburger Toggle -->
            <div class="flex items-center gap-2 md:hidden">
                <a href="{{ route('donation.create') }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-700 rounded-full">
                    Donasi
                </a>
                <button @click="mobileMenu = !mobileMenu" aria-label="Menu" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenu" x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="border-b border-slate-200 bg-white/98 px-4 pt-3 pb-6 space-y-3 md:hidden">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-800 hover:bg-blue-50 hover:text-blue-800">
                Katalog Program
            </a>
            <a href="{{ route('zakat') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-800 hover:bg-blue-50 hover:text-blue-800">
                Kalkulator Zakat (Profesi &amp; Maal)
            </a>
            <a href="/admin" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-800 hover:bg-blue-50 hover:text-blue-800">
                Portal Admin &amp; ERM
            </a>
            <div class="pt-2">
                <a href="{{ route('donation.create') }}" class="block w-full py-3 text-center font-semibold text-white bg-blue-700 rounded-xl shadow-md">
                    Donasi Cepat Sekarang
                </a>
            </div>
        </div>
    </header>

    <!-- Page Content Container -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Global Floating Toast Notification -->
    <div x-show="toast.show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl bg-slate-900 text-white px-5 py-3.5 shadow-2xl flex items-center gap-3 border border-slate-700">
        <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">
            ✓
        </div>
        <div class="text-sm font-medium" x-text="toast.message"></div>
    </div>

    <!-- Premium Institutional Footer with Mandated Copyright -->
    <footer class="mt-20 border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12">
                <!-- Col 1: Identity & Legal -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-800 flex items-center justify-center text-white">
                            <svg class="w-5 h-5 text-amber-300" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l2.4 4.8 5.3.8-3.8 3.8.9 5.3L12 16.2l-4.8 2.5.9-5.3-3.8-3.8 5.3-.8L12 2z"/>
                            </svg>
                        </div>
                        <span class="font-serif font-bold text-lg text-slate-900">{{ config('ziswaf.org.name') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Lembaga filantropi Islam independen yang mengelola dana Zakat, Infaq, Sedekah, dan Wakaf secara profesional, akuntabel, dan transparan sesuai pedoman fiqih syariah dan standar akuntansi PSAK 109 di Indonesia.
                    </p>
                    <div class="flex items-center gap-2 pt-2 text-[11px] font-semibold text-blue-800">
                        <span class="px-2 py-0.5 rounded bg-blue-50 border border-blue-200">PSAK 109 Certified</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-300">BAZNAS Standard</span>
                    </div>
                </div>

                <!-- Col 2: Program Utama -->
                <div class="text-xs space-y-2.5">
                    <p class="text-sm font-bold text-slate-900 tracking-wide uppercase">Program Penyaluran</p>
                    <ul class="space-y-2 text-slate-600">
                        <li><a href="{{ route('home', ['kategori' => 'zakat']) }}" class="hover:text-blue-700 transition">Zakat Maal &amp; Fitrah</a></li>
                        <li><a href="{{ route('home', ['kategori' => 'infaq']) }}" class="hover:text-blue-700 transition">Sedekah Pangan Dhuafa</a></li>
                        <li><a href="{{ route('home', ['kategori' => 'wakaf']) }}" class="hover:text-blue-700 transition">Wakaf Produktif Air Bersih</a></li>
                        <li><a href="{{ route('home', ['kategori' => 'kemanusiaan']) }}" class="hover:text-blue-700 transition">Tanggap Darurat Medis &amp; Bencana</a></li>
                        <li><a href="{{ route('zakat') }}" class="hover:text-blue-700 transition">Kalkulator Nishab Emas</a></li>
                    </ul>
                </div>

                <!-- Col 3: Transparansi & Keamanan -->
                <div class="text-xs space-y-2.5">
                    <p class="text-sm font-bold text-slate-900 tracking-wide uppercase">Jaminan Akuntabilitas</p>
                    <ul class="space-y-2 text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Kwitansi PDF Resmi ber-QR Code dikirim instan via WhatsApp</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Sistem Double-Entry Ledger PSAK 109 otomatis per transaksi</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Penilaian mustahik akurat dengan skor Had Kifayah BAZNAS</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-blue-600 font-bold">✓</span>
                            <span>Payment Gateway terverifikasi Midtrans Snap &amp; QRIS</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Kontak & Layanan -->
                <div class="text-xs space-y-2.5">
                    <p class="text-sm font-bold text-slate-900 tracking-wide uppercase">Layanan Muzakki</p>
                    <p class="text-slate-600 leading-relaxed">{{ config('ziswaf.org.address') }}</p>
                    <p class="text-slate-600">Konsultasi WhatsApp: <span class="font-semibold text-blue-800">{{ config('ziswaf.org.phone') }}</span></p>
                    <div class="pt-3">
                        <a href="{{ route('donation.create') }}" class="inline-block px-4 py-2 text-xs font-semibold text-white bg-blue-800 rounded-lg hover:bg-blue-900 shadow-xs transition">
                            Konsultasi / Donasi Sekarang
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mandated Copyright Section -->
            <div class="mt-12 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                <p>
                    © {{ date('Y') }} <strong class="font-bold text-slate-800">jaddadeveloper</strong> (part of jadda ecosystem). Seluruh hak cipta dilindungi.
                </p>
                <div class="flex items-center gap-4">
                    <span>Sistem Digital Fundraising &amp; Penyaluran ZISWAF</span>
                    <span>•</span>
                    <a href="/admin" class="hover:text-blue-700 font-medium">Akses Pengurus</a>
                </div>
            </div>
        </div>
    </footer>

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
