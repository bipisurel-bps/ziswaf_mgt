<div class="mt-4 pt-3 border-t border-slate-100">
    @if ($campaign->target_amount)
        <div class="flex items-baseline justify-between text-xs mb-1.5">
            <span class="text-slate-500 font-medium">Terkumpul</span>
            <span class="font-bold text-blue-800 text-xs">{{ $campaign->progress_percent }}%</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 transition-all duration-500" style="width: {{ min(100, $campaign->progress_percent) }}%"></div>
        </div>
        <div class="mt-2.5 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</span>
            <span class="text-slate-400">dari Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
        </div>
    @else
        <div class="flex items-baseline justify-between text-xs mb-1">
            <span class="text-slate-500 font-medium">Total Terhimpun</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-800 border border-blue-200/60">
                Penyaluran Berkelanjutan
            </span>
        </div>
        <p class="font-bold text-slate-900 text-sm">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Donasi terbuka tanpa batas target waktu</p>
    @endif
</div>
