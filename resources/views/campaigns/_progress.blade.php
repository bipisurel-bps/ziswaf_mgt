<div class="mt-3">
    @if ($campaign->target_amount)
        <div class="h-2 overflow-hidden rounded-full bg-stone-200">
            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $campaign->progress_percent }}%"></div>
        </div>
        <div class="mt-2 flex justify-between text-sm">
            <span><b>Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</b> terkumpul</span>
            <span class="text-stone-500">{{ $campaign->progress_percent }}%</span>
        </div>
        <p class="text-xs text-stone-500">Target Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
    @else
        <p class="text-sm"><b>Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</b> terkumpul</p>
        <p class="text-xs text-stone-500">Donasi terbuka tanpa target</p>
    @endif
</div>
