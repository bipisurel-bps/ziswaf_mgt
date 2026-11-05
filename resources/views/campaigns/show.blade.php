@extends('layouts.app')
@section('title', $campaign->title)
@section('description', strip_tags(Str::limit($campaign->description, 150)))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 md:py-14">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-stone-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
        <span>/</span>
        <a href="{{ route('home', ['kategori' => $campaign->category->value]) }}" class="hover:text-emerald-700">
            {{ $campaign->category->label() }}
        </a>
        <span>/</span>
        <span class="text-stone-800 font-medium truncate max-w-xs">{{ $campaign->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Article Column (2 Cols) -->
        <article class="lg:col-span-2 space-y-8">
            <!-- Cover Banner -->
            <div class="relative aspect-[16/9] w-full overflow-hidden rounded-3xl bg-stone-100 shadow-md">
                @if ($campaign->cover_url)
                    <img src="{{ $campaign->cover_url }}" alt="{{ $campaign->title }}" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-700">
                        <svg class="w-16 h-16 opacity-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 4.8 5.3.8-3.8 3.8.9 5.3L12 16.2l-4.8 2.5.9-5.3-3.8-3.8 5.3-.8L12 2z"/></svg>
                    </div>
                @endif
                <div class="absolute top-4 left-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase shadow-sm
                        @if($campaign->category->value === 'zakat') bg-amber-500 text-white
                        @elseif($campaign->category->value === 'wakaf') bg-teal-600 text-white
                        @elseif($campaign->category->value === 'kemanusiaan') bg-rose-600 text-white
                        @else bg-emerald-700 text-white @endif">
                        {{ $campaign->category->label() }}
                    </span>
                </div>
            </div>

            <!-- Campaign Title & Share Actions -->
            <div class="space-y-4">
                <h1 class="text-2xl sm:text-4xl font-bold font-serif text-stone-900 leading-tight">
                    {{ $campaign->title }}
                </h1>

                <!-- Share Buttons -->
                <div class="flex flex-wrap items-center gap-3 pt-2 pb-4 border-b border-stone-200">
                    <span class="text-xs text-stone-500 font-medium">Sebarkan Kebaikan:</span>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Mari bantu dan salurkan donasi untuk program: ' . $campaign->title . ' melalui ' . url()->current()) }}" 
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition">
                        <span>💬 WhatsApp</span>
                    </a>
                    <button type="button" 
                            @click="navigator.clipboard.writeText(window.location.href); notify('Tautan kampanye berhasil disalin!')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-stone-100 text-stone-700 hover:bg-stone-200 transition">
                        <span>🔗 Salin Tautan</span>
                    </button>
                </div>
            </div>

            <!-- Story Body -->
            <div class="prose prose-stone prose-emerald max-w-none text-stone-700 leading-relaxed text-sm sm:text-base">
                {!! \Illuminate\Support\Str::markdown((string) $campaign->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            </div>

            <!-- Penyaluran & Akuntabilitas Card -->
            <div class="p-6 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 space-y-3">
                <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Jaminan Penyaluran &amp; Pelaporan Terbuka</span>
                </div>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Dana yang terkumpul dikelola dengan verifikasi multi-tier. Setiap pengeluaran dicatat ke Buku Besar PSAK 109 dan laporan penyaluran dipublikasikan secara transparan untuk seluruh donatur.
                </p>
            </div>
        </article>

        <!-- Sticky Sidebar Column (1 Col) -->
        <aside class="space-y-8">
            <!-- Donation Progress Widget Card -->
            <div class="sticky top-24 rounded-3xl bg-white border border-stone-200/90 p-6 shadow-lg shadow-stone-200/50 space-y-6">
                <!-- Progress Component -->
                @include('campaigns._progress', ['campaign' => $campaign])

                @if ($campaign->status->value === 'active')
                    <a href="{{ route('donation.create', ['campaign' => $campaign->slug]) }}" 
                       class="block w-full py-4 text-center text-sm font-bold text-white bg-gradient-to-r from-emerald-700 to-emerald-800 rounded-2xl shadow-lg shadow-emerald-800/25 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl active:scale-95 transition-all duration-200">
                        Donasi Sekarang ➔
                    </a>
                @else
                    <div class="p-3.5 rounded-xl bg-stone-100 text-stone-500 text-xs text-center font-medium">
                        Kampanye ini telah selesai dihimpun. Jazakumullahu khairan.
                    </div>
                @endif

                <div class="pt-4 border-t border-stone-100 space-y-2 text-xs text-stone-500">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-700">✓</span>
                        <span>Verifikasi otomatis QRIS &amp; Virtual Account</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-700">✓</span>
                        <span>Kwitansi PDF instan dikirim ke WhatsApp</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-700">✓</span>
                        <span>100% amanah &amp; tercatat di PSAK 109</span>
                    </div>
                </div>

                <!-- Recent Donors & Heartfelt Prayers Section -->
                <div class="pt-6 border-t border-stone-100">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-stone-900 text-sm">Doa Donatur Terkini</h4>
                        <span class="text-[11px] text-stone-400 font-medium">{{ count($donors) }} Donatur</span>
                    </div>

                    <div class="space-y-4 max-h-96 overflow-y-auto pr-1">
                        @forelse ($donors as $d)
                            <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-100 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-stone-800">{{ $d->display_name }}</span>
                                    <span class="font-semibold text-emerald-800">Rp {{ number_format($d->amount, 0, ',', '.') }}</span>
                                </div>
                                @if ($d->doa_harapan)
                                    <p class="text-stone-600 italic bg-white p-2 rounded-lg border border-stone-100">
                                        “{{ $d->doa_harapan }}”
                                    </p>
                                @endif
                                <p class="text-[10px] text-stone-400">{{ $d->paid_at ? $d->paid_at->diffForHumans() : 'Baru saja' }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-stone-400 text-center py-4">
                                Jadilah donatur pertama untuk program mulia ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
