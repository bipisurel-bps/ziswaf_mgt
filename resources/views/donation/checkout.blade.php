@extends('layouts.app')
@section('title', 'Donasi Cepat — Satu Halaman Tanpa Registrasi')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 md:py-14" 
     x-data="checkout({ presets: @js($presets), initialAmount: {{ $initialAmount }} })">

    <!-- Header -->
    <div class="text-center space-y-2 mb-8">
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
            <span>⚡ One-Page Fast Checkout</span>
        </span>
        <h1 class="text-2xl sm:text-4xl font-bold font-serif text-stone-900">
            Tunaikan Kebaikan Hari Ini
        </h1>
        <p class="text-xs sm:text-sm text-stone-500">
            Donasi cepat, aman, tanpa registrasi. Kwitansi PDF resmi otomatis terbit.
        </p>
    </div>

    <!-- Active Campaign Highlight (If Selected) -->
    @if ($campaign)
        <div class="mb-6 p-4 rounded-2xl bg-white border border-stone-200/90 shadow-xs flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl overflow-hidden bg-stone-100 shrink-0">
                @if ($campaign->cover_url)
                    <img src="{{ $campaign->cover_url }}" alt="{{ $campaign->title }}" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-emerald-100 flex items-center justify-center text-emerald-700">★</div>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-[10px] font-bold uppercase tracking-wide text-emerald-700">{{ $campaign->category->label() }}</span>
                <h3 class="text-sm font-bold text-stone-900 truncate">{{ $campaign->title }}</h3>
                <p class="text-[11px] text-stone-400">Tersalurkan langsung untuk program ini</p>
            </div>
        </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-700 space-y-1">
            <div class="font-bold">Mohon periksa kembali isian form Anda:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Donation Form Card -->
    <div class="rounded-3xl bg-white border border-stone-200/90 shadow-xl p-6 sm:p-8">
        <form method="POST" action="{{ route('donation.store') }}" class="space-y-8">
            @csrf
            <input type="hidden" name="amount" :value="amount">
            <input type="hidden" name="referral_code" value="{{ old('referral_code', request('ref', request()->cookie('ref')) ?? '') }}">

            {{-- 1. PILIH NOMINAL --}}
            <fieldset class="space-y-3">
                <div class="flex items-center justify-between">
                    <legend class="text-xs font-bold text-stone-900 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">1</span>
                        <span>Pilih Nominal Donasi</span>
                    </legend>
                    <span class="text-xs font-semibold text-emerald-800" x-text="label"></span>
                </div>

                <!-- Preset Chips -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="p in presets" :key="p">
                        <button type="button" @click="pick(p)"
                                :class="amount === p && !custom ? 'border-emerald-600 bg-emerald-50 text-emerald-900 font-bold ring-2 ring-emerald-600/20' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 font-medium'"
                                class="rounded-xl border py-3 px-3 text-xs sm:text-sm text-center transition-all duration-150"
                                x-text="'Rp ' + p.toLocaleString('id-ID')">
                        </button>
                    </template>
                </div>

                <!-- Custom Nominal -->
                <div class="pt-1">
                    <label class="block text-xs text-stone-500 mb-1">Atau masukkan nominal khusus:</label>
                    <div class="relative flex items-center rounded-xl border border-stone-300 bg-white px-3 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20">
                        <span class="text-xs font-bold text-stone-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="custom" @input="onCustom()" placeholder="Contoh: 150.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-sm font-semibold text-stone-900 focus:outline-none">
                    </div>
                </div>
            </fieldset>

            {{-- TUJUAN & KATEGORI --}}
            <fieldset class="space-y-3 pt-6 border-t border-stone-100">
                <legend class="text-xs font-bold text-stone-900 uppercase tracking-wide flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">2</span>
                    <span>Alokasi &amp; Akad Donasi</span>
                </legend>

                @if ($campaign)
                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                @else
                    <div>
                        <label class="block text-xs text-stone-500 mb-1">Pilih Program / Kampanye:</label>
                        <select name="campaign_id" class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                            <option value="">Donasi Umum (Didistribusikan ke Program Prioritas)</option>
                            @foreach ($campaigns as $c)
                                <option value="{{ $c->id }}" @selected(old('campaign_id') == $c->id)>{{ $c->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-xs text-stone-500 mb-1">Jenis Akad:</label>
                    <select name="donation_type" class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                        @foreach (['zakat' => 'Zakat (Pembersih Harta)', 'infaq' => 'Infaq / Sedekah (Kemanusiaan & Dakwah)', 'wakaf' => 'Wakaf (Amal Jariyah Abadi)', 'kemanusiaan' => 'Bantuan Kemanusiaan Darurat'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('donation_type', $initialType ?? ($campaign?->category->value ?? 'infaq')) === $v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </fieldset>

            {{-- 2. DATA DIRI DONATUR --}}
            <fieldset class="space-y-4 pt-6 border-t border-stone-100">
                <legend class="text-xs font-bold text-stone-900 uppercase tracking-wide flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">3</span>
                    <span>Data Diri Donatur</span>
                </legend>

                <div class="space-y-1">
                    <label class="block text-xs text-stone-600 font-medium">Nama Lengkap *</label>
                    <input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Nama Anda"
                           class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs text-stone-600 font-medium">Nomor WhatsApp *</label>
                    <input name="phone_number" value="{{ old('phone_number') }}" required inputmode="tel" placeholder="08xxxxxxxxxx"
                           class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                    <p class="text-[11px] text-stone-400">Kwitansi donasi resmi format PDF akan dikirim otomatis ke nomor ini.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs text-stone-600 font-medium">Email (Opsional)</label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="email@anda.com"
                           class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                </div>

                <!-- Hamba Allah Checkbox -->
                <label class="flex items-center gap-3 p-3 rounded-xl bg-stone-50 border border-stone-200 cursor-pointer">
                    <input type="checkbox" name="is_anonim" value="1" x-model="anonim" @checked(old('is_anonim')) 
                           class="w-4 h-4 rounded text-emerald-700 focus:ring-emerald-600">
                    <span class="text-xs text-stone-700 font-medium">
                        Sembunyikan nama saya di publik (Tampil sebagai “Hamba Allah”)
                    </span>
                </label>

                <!-- Doa & Harapan -->
                <div class="space-y-1">
                    <label class="block text-xs text-stone-600 font-medium">Doa &amp; Harapan Kebaikan (Opsional)</label>
                    <textarea name="doa_harapan" rows="2" maxlength="500" placeholder="Tuliskan doa atau titipan harapan Anda..."
                              class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">{{ old('doa_harapan') }}</textarea>
                </div>
            </fieldset>

            {{-- 3. METODE PEMBAYARAN --}}
            <fieldset class="space-y-3 pt-6 border-t border-stone-100">
                <legend class="text-xs font-bold text-stone-900 uppercase tracking-wide flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-800 text-white flex items-center justify-center text-[10px]">4</span>
                    <span>Metode Pembayaran</span>
                </legend>

                <select name="payment_method" class="w-full rounded-xl border border-stone-300 bg-white px-3.5 py-3 text-xs sm:text-sm text-stone-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none">
                    <option value="any">⚡ Semua Metode Pembayaran (Pilih saat Pop-up Snap)</option>
                    @foreach ($methods as $code => $label)
                        <option value="{{ $code }}" @selected(old('payment_method') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] text-stone-400">
                    <span class="px-2 py-0.5 rounded bg-stone-100 font-semibold text-stone-600">QRIS Instan</span>
                    <span class="px-2 py-0.5 rounded bg-stone-100 font-semibold text-stone-600">BCA VA</span>
                    <span class="px-2 py-0.5 rounded bg-stone-100 font-semibold text-stone-600">BNI VA</span>
                    <span class="px-2 py-0.5 rounded bg-stone-100 font-semibold text-stone-600">GoPay</span>
                </div>
            </fieldset>

            <!-- SUBMIT ACTION BUTTON -->
            <div class="pt-4">
                <button type="submit" :disabled="amount < {{ config('ziswaf.min_donation') }}"
                        class="w-full py-4 px-6 text-sm sm:text-base font-bold text-white bg-gradient-to-r from-emerald-700 to-emerald-800 rounded-2xl shadow-xl shadow-emerald-800/30 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-2xl active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200 flex items-center justify-center gap-2">
                    <span>Lanjutkan Pembayaran</span>
                    <span class="font-extrabold text-amber-300" x-text="label"></span>
                    <span>➔</span>
                </button>
            </div>

            <!-- Trust assurance badges -->
            <div class="pt-4 flex items-center justify-center gap-6 text-[11px] text-stone-400 border-t border-stone-100">
                <span class="flex items-center gap-1">
                    <span class="text-emerald-700">🔒</span> Enkripsi SSL 256-Bit
                </span>
                <span class="flex items-center gap-1">
                    <span class="text-emerald-700">✓</span> Verifikasi Idempoten
                </span>
                <span class="flex items-center gap-1">
                    <span class="text-emerald-700">📄</span> Kwitansi PDF Sah
                </span>
            </div>
        </form>
    </div>
</div>
@endsection
