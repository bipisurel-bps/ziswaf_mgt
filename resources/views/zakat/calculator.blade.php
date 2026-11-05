@extends('layouts.app')
@section('title', 'Kalkulator Zakat Profesi & Maal Terintegrasi')
@section('description', 'Hitung kewajiban zakat profesi dan zakat maal Anda secara otomatis dan akurat berdasarkan harga emas hari ini dan standar nishab 85 gram.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 md:py-16"
     x-data="zakatCalc({ nishabYearly: {{ $nishabYearly }}, nishabMonthly: {{ $nishabMonthly }}, rate: {{ $rate }} })">

    <!-- Header Section -->
    <div class="text-center max-w-2xl mx-auto space-y-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
            <span>✨ Sesuai Fiqih Kontemporer &amp; Standar BAZNAS</span>
        </span>
        <h1 class="text-3xl sm:text-5xl font-bold font-serif text-slate-900 tracking-tight">
            Kalkulator Zakat Digital
        </h1>
        <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
            Hitung kewajiban zakat Anda secara akurat dengan standar nishab 85 gram emas. Begitu terhitung, Anda dapat langsung menunaikannya dalam 1 kali klik.
        </p>
    </div>

    <!-- Gold Price & Nishab Live Ticker Card -->
    <div class="mt-8 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-50 via-slate-50 to-blue-50 border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                🏆
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Acuan Harga Emas Hari Ini</p>
                <p class="text-sm font-bold text-slate-900">
                    Rp {{ number_format($goldPrice, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/ gram</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-6 text-xs text-slate-600">
            <div>
                <span class="block text-slate-400">Nishab Tahunan (85g):</span>
                <span class="font-bold text-blue-800 text-sm">Rp {{ number_format($nishabYearly, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="block text-slate-400">Nishab Bulanan:</span>
                <span class="font-bold text-blue-800 text-sm">Rp {{ number_format($nishabMonthly, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Interactive Calculator Box -->
    <div class="mt-8 rounded-3xl bg-white border border-slate-200 shadow-xl overflow-hidden">
        <!-- Tab Selector Switcher -->
        <div class="flex border-b border-slate-200 bg-slate-50 p-2">
            <button type="button" 
                    @click="tab = 'profesi'"
                    :class="tab === 'profesi' ? 'bg-white text-blue-950 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                    class="flex-1 py-3 px-4 rounded-2xl text-xs sm:text-sm text-center transition-all duration-200">
                💼 Zakat Profesi (Penghasilan)
            </button>
            <button type="button" 
                    @click="tab = 'maal'"
                    :class="tab === 'maal' ? 'bg-white text-blue-950 font-bold shadow-xs' : 'text-slate-500 hover:text-slate-800 font-medium'"
                    class="flex-1 py-3 px-4 rounded-2xl text-xs sm:text-sm text-center transition-all duration-200">
                💰 Zakat Maal (Harta Tersimpan)
            </button>
        </div>

        <div class="p-6 sm:p-10 space-y-8">
            <!-- Form Inputs: Zakat Profesi -->
            <div x-show="tab === 'profesi'" class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                        1. Penghasilan Bulanan Tetap
                    </label>
                    <div class="relative flex items-center rounded-xl border border-slate-300 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                        <span class="text-sm font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="income" @input="mask('income')" placeholder="Contoh: 12.000.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-base font-semibold text-slate-900 focus:outline-none">
                    </div>
                    <p class="text-[11px] text-slate-400">Gaji pokok atau omzet bersih bulanan</p>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                        2. Bonus / Tunjangan / Pendapatan Lain (Bulan ini)
                    </label>
                    <div class="relative flex items-center rounded-xl border border-slate-300 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                        <span class="text-sm font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="bonus" @input="mask('bonus')" placeholder="Contoh: 2.500.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-base font-semibold text-slate-900 focus:outline-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                        3. Pengeluaran Pokok &amp; Hutang Bulanan
                    </label>
                    <div class="relative flex items-center rounded-xl border border-slate-300 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                        <span class="text-sm font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="expenses" @input="mask('expenses')" placeholder="Contoh: 4.000.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-base font-semibold text-slate-900 focus:outline-none">
                    </div>
                    <p class="text-[11px] text-slate-400">Kebutuhan primer keluarga dan cicilan jatuh tempo bulan ini</p>
                </div>
            </div>

            <!-- Form Inputs: Zakat Maal -->
            <div x-show="tab === 'maal'" x-cloak class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                        1. Total Nilai Aset Tersimpan
                    </label>
                    <div class="relative flex items-center rounded-xl border border-slate-300 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                        <span class="text-sm font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="assets" @input="mask('assets')" placeholder="Contoh: 150.000.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-base font-semibold text-slate-900 focus:outline-none">
                    </div>
                    <p class="text-[11px] text-slate-400">Tabungan, deposito, perhiasan emas/perak, reksadana, dan saham</p>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">
                        2. Hutang / Kewajiban Jatuh Tempo Tahun Ini
                    </label>
                    <div class="relative flex items-center rounded-xl border border-slate-300 bg-white px-3 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20">
                        <span class="text-sm font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" x-model="debts" @input="mask('debts')" placeholder="Contoh: 10.000.000"
                               class="w-full border-0 bg-transparent px-2.5 py-3 text-base font-semibold text-slate-900 focus:outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <input type="checkbox" x-model="haul" class="w-4 h-4 rounded text-blue-700 focus:ring-blue-600">
                    <span class="text-xs sm:text-sm font-medium text-slate-700">
                        Aset di atas telah genap dimiliki &amp; tersimpan selama 1 tahun hijriyah (Haul)
                    </span>
                </label>
            </div>

            <!-- Dynamic Result Card -->
            <div class="rounded-2xl border p-6 sm:p-8 transition-all duration-300"
                 :class="result.ok ? 'bg-gradient-to-br from-blue-50 via-indigo-50/40 to-slate-50 border-blue-300' : 'bg-slate-50 border-slate-200'">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b"
                     :class="result.ok ? 'border-blue-200' : 'border-slate-200'">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dasar Pengenaan Zakat</span>
                        <p class="text-lg font-bold text-slate-900" x-text="fmt(result.base)"></p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ambang Batas Nishab</span>
                        <p class="text-sm font-semibold text-slate-700" x-text="fmt(result.nishab)"></p>
                    </div>
                </div>

                <!-- Case 1: Reached Nishab -->
                <template x-if="result.ok">
                    <div class="mt-6 space-y-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-blue-800 uppercase tracking-wide">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 animate-ping"></span>
                            <span>Alhamdulillah, Telah Mencapai Nishab Wajib Zakat (2,5%)</span>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Nominal Zakat yang Wajib Dikeluarkan:</p>
                            <p class="text-3xl sm:text-5xl font-bold font-serif text-blue-900 tracking-tight mt-1" x-text="fmt(result.zakat)"></p>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Zakat Anda akan disalurkan kepada 8 golongan asnaf yang berhak dan tercatat di buku besar akuntansi syariah PSAK 109.
                        </p>
                        <a :href="payUrl" 
                           class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-blue-700 to-indigo-800 rounded-full shadow-lg shadow-blue-800/30 hover:from-blue-600 hover:to-indigo-700 hover:shadow-xl active:scale-95 transition-all duration-200">
                            <span>Langsung Bayarkan Zakat Sekarang ➔</span>
                        </a>
                    </div>
                </template>

                <!-- Case 2: Not Yet Reached Nishab -->
                <template x-if="!result.ok">
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-semibold text-amber-800">
                            <span>ℹ️</span>
                            <span>Belum Wajib Zakat (Belum Mencapai Nishab<span x-show="tab==='maal' && !haul"> atau Haul</span>)</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Harta atau penghasilan bersih Anda saat ini belum memenuhi kriteria nishab wajib zakat. Namun, pintu pahala senantiasa terbuka lebar melalui amalan Infaq dan Sedekah.
                        </p>
                        <a href="{{ route('donation.create', ['type' => 'infaq']) }}" 
                           class="inline-flex items-center justify-center px-6 py-3 text-xs sm:text-sm font-semibold text-blue-800 bg-white border border-blue-300 rounded-full hover:bg-blue-50 transition">
                            Salurkan Sedekah / Infaq Sukarela ➔
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Educational Accordion / FAQ Section -->
    <div class="mt-16 space-y-4">
        <h3 class="text-xl font-bold font-serif text-slate-900 text-center mb-6">Panduan Ringkas Zakat Syariah</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm">
            <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-2">
                <h4 class="font-bold text-slate-900">Berapa Nishab Zakat Penghasilan?</h4>
                <p class="text-slate-600 leading-relaxed">
                    Nishab zakat penghasilan diqiyaskan dengan zakat emas 85 gram per tahun (atau setara 85g dibagi 12 bulan). Bila penghasilan bersih bulanan mencapai nishab tersebut, wajib zakat sebesar 2,5%.
                </p>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-slate-200 space-y-2">
                <h4 class="font-bold text-slate-900">Siapa Saja 8 Asnaf Penerima Zakat?</h4>
                <p class="text-slate-600 leading-relaxed">
                    Sesuai QS. At-Taubah ayat 60: Fakir, Miskin, Amil, Mualaf, Riqab (Hamba Sahaya), Gharimin (Terlilit Hutang), Fisabilillah (Pejuang Agama), dan Ibnu Sabil (Musafir kehabisan bekal).
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
