@extends('layouts.app')
@section('title', 'Kalkulator Zakat')

@section('content')
<div class="mx-auto max-w-xl px-4 py-10"
     x-data="zakatCalc({ nishabYearly: {{ $nishabYearly }}, nishabMonthly: {{ $nishabMonthly }}, rate: {{ $rate }} })">
    <h1 class="text-2xl font-semibold">Kalkulator Zakat</h1>
    <p class="mt-1 text-sm text-stone-500">Nishab 85 gram emas (harga emas Rp {{ number_format($goldPrice, 0, ',', '.') }}/gram) = Rp {{ number_format($nishabYearly, 0, ',', '.') }}/tahun.</p>

    <div class="mt-5 flex rounded-full bg-stone-200 p-1 text-sm font-medium">
        <button class="flex-1 rounded-full py-2" :class="tab==='profesi' && 'bg-white shadow'" @click="tab='profesi'">Zakat Profesi</button>
        <button class="flex-1 rounded-full py-2" :class="tab==='maal' && 'bg-white shadow'" @click="tab='maal'">Zakat Maal</button>
    </div>

    <div class="mt-6 space-y-3" x-show="tab==='profesi'">
        @foreach (['income' => 'Penghasilan bulanan', 'bonus' => 'Bonus / penghasilan lain (bulan ini)', 'expenses' => 'Pengeluaran pokok bulanan'] as $k => $l)
            <label class="block text-sm">{{ $l }}
                <input type="text" inputmode="numeric" x-model="{{ $k }}" @input="mask('{{ $k }}')" placeholder="Rp 0" class="mt-1 w-full rounded-lg border bg-white px-3 py-2.5">
            </label>
        @endforeach
        <p class="text-xs text-stone-500">Nishab bulanan: <span x-text="fmt(nishabMonthly)"></span> (nishab tahunan ÷ 12)</p>
    </div>

    <div class="mt-6 space-y-3" x-show="tab==='maal'" x-cloak>
        @foreach (['assets' => 'Total aset tersimpan (tabungan, emas, investasi)', 'debts' => 'Hutang jatuh tempo'] as $k => $l)
            <label class="block text-sm">{{ $l }}
                <input type="text" inputmode="numeric" x-model="{{ $k }}" @input="mask('{{ $k }}')" placeholder="Rp 0" class="mt-1 w-full rounded-lg border bg-white px-3 py-2.5">
            </label>
        @endforeach
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" x-model="haul" class="rounded"> Aset sudah tersimpan 1 tahun (haul)</label>
    </div>

    <div class="mt-6 rounded-xl border bg-white p-5">
        <p class="text-sm text-stone-500">Dasar perhitungan: <span x-text="fmt(result.base)"></span></p>
        <template x-if="result.ok">
            <div>
                <p class="mt-2 text-sm">Zakat yang wajib dibayarkan (2,5%)</p>
                <p class="text-3xl font-semibold text-emerald-800" x-text="fmt(result.zakat)"></p>
                <a :href="payUrl" class="mt-4 block rounded-full bg-emerald-700 py-3 text-center font-medium text-white hover:bg-emerald-800">Langsung Bayarkan Zakat</a>
            </div>
        </template>
        <template x-if="!result.ok">
            <p class="mt-2 text-sm">Belum mencapai nishab<span x-show="tab==='maal' && !haul"> / haul</span>, sehingga belum wajib zakat. Anda tetap dapat berinfaq.</p>
        </template>
    </div>
</div>
@endsection
