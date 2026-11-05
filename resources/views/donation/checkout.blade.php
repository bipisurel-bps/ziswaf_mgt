@extends('layouts.app')
@section('title', 'Donasi')

@section('content')
<div class="mx-auto max-w-xl px-4 py-10" x-data="checkout({ presets: @js($presets), initialAmount: {{ $initialAmount }} })">
    <h1 class="text-2xl font-semibold">Donasi{{ $campaign ? ': '.$campaign->title : '' }}</h1>
    <p class="mt-1 text-sm text-stone-500">Satu halaman, tanpa registrasi.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('donation.store') }}" class="mt-6 space-y-6">
        @csrf
        <input type="hidden" name="amount" :value="amount">
        <input type="hidden" name="referral_code" value="{{ old('referral_code', request('ref', request()->cookie('ref')) ?? '') }}">

        {{-- 1. Nominal --}}
        <fieldset>
            <legend class="mb-2 font-medium">1. Pilih nominal</legend>
            <div class="grid grid-cols-2 gap-3">
                <template x-for="p in presets" :key="p">
                    <button type="button" @click="pick(p)"
                        :class="amount === p && !custom ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'bg-white'"
                        class="rounded-lg border px-3 py-3 font-medium"
                        x-text="'Rp ' + p.toLocaleString('id-ID')"></button>
                </template>
            </div>
            <label class="mt-3 block text-sm">Atau nominal lain
                <div class="mt-1 flex items-center rounded-lg border bg-white px-3">
                    <span class="text-stone-500">Rp</span>
                    <input type="text" inputmode="numeric" x-model="custom" @input="onCustom()" placeholder="0"
                        class="w-full border-0 bg-transparent px-2 py-2.5 focus:outline-none">
                </div>
            </label>
        </fieldset>

        {{-- Tujuan --}}
        <fieldset class="space-y-3">
            <legend class="mb-1 font-medium">Tujuan donasi</legend>
            @if ($campaign)
                <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                <p class="rounded-lg bg-emerald-50 p-3 text-sm">{{ $campaign->title }}</p>
            @else
                <select name="campaign_id" class="w-full rounded-lg border bg-white px-3 py-2.5">
                    <option value="">Donasi umum</option>
                    @foreach ($campaigns as $c)
                        <option value="{{ $c->id }}" @selected(old('campaign_id') == $c->id)>{{ $c->title }}</option>
                    @endforeach
                </select>
            @endif
            <select name="donation_type" class="w-full rounded-lg border bg-white px-3 py-2.5">
                @foreach (['zakat' => 'Zakat', 'infaq' => 'Infaq / Sedekah', 'wakaf' => 'Wakaf', 'kemanusiaan' => 'Kemanusiaan'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('donation_type', $initialType ?? ($campaign?->category->value ?? 'infaq')) === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </fieldset>

        {{-- 2. Data diri --}}
        <fieldset class="space-y-3">
            <legend class="mb-1 font-medium">2. Data diri</legend>
            <input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Nama lengkap" class="w-full rounded-lg border bg-white px-3 py-2.5">
            <input name="phone_number" value="{{ old('phone_number') }}" required inputmode="tel" placeholder="Nomor WhatsApp (08xxxxxxxxxx)" class="w-full rounded-lg border bg-white px-3 py-2.5">
            <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (opsional)" class="w-full rounded-lg border bg-white px-3 py-2.5">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_anonim" value="1" x-model="anonim" @checked(old('is_anonim')) class="rounded">
                Sembunyikan nama saya (tampil sebagai “Hamba Allah”)
            </label>
            <textarea name="doa_harapan" rows="2" maxlength="500" placeholder="Doa / harapan (opsional)" class="w-full rounded-lg border bg-white px-3 py-2.5">{{ old('doa_harapan') }}</textarea>
        </fieldset>

        {{-- 3. Metode pembayaran --}}
        <fieldset>
            <legend class="mb-2 font-medium">3. Metode pembayaran</legend>
            <select name="payment_method" class="w-full rounded-lg border bg-white px-3 py-2.5">
                <option value="any">Pilih saat pembayaran (semua metode)</option>
                @foreach ($methods as $code => $label)
                    <option value="{{ $code }}" @selected(old('payment_method') === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </fieldset>

        <button type="submit" :disabled="amount < {{ config('ziswaf.min_donation') }}"
            class="w-full rounded-full bg-emerald-700 py-3.5 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50">
            Lanjut Bayar <span x-text="label"></span>
        </button>
    </form>
</div>
@endsection
