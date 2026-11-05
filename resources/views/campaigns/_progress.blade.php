<div class="mt-4 pt-3 border-t border-stone-100">
    @if ($campaign->target_amount)
        <div class="flex items-baseline justify-between text-xs mb-1.5">
            <span class="text-stone-500 font-medium">Terkumpul</span>
            <span class="font-bold text-emerald-800 text-xs">{{ $campaign->progress_percent }}%</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-stone-100">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 transition-all duration-500" style="width: {{ min(100, $campaign->progress_percent) }}%"></div>
        </div>
        <div class="mt-2.5 flex items-center justify-between text-xs">
            <span class="font-bold text-stone-900 text-sm">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</span>
            <span class="text-stone-400">dari Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
        </div>
    @else
        <div class="flex items-baseline justify-between text-xs mb-1">
            <span class="text-stone-500 font-medium">Total Terhimpun</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                Penyaluran Berkelanjutan
            </span>
        </div>
        <p class="font-bold text-stone-900 text-sm">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
        <p class="text-[11px] text-stone-400 mt-0.5">Donasi terbuka tanpa batas target waktu</p>
    @endif
</div>
