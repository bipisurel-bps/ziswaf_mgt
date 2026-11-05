@php
    $record = $getRecord();
    $pct = $record->progress_percent;
@endphp
<div class="w-40 px-3 py-2">
    @if ($pct === null)
        <span class="text-xs text-gray-500">Open-ended</span>
    @else
        <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $pct }}%"></div>
        </div>
        <span class="text-xs text-gray-500">{{ $pct }}%</span>
    @endif
</div>
