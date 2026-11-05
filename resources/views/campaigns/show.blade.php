@extends('layouts.app')
@section('title', $campaign->title)

@section('content')
<div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 lg:grid-cols-3">
    <article class="lg:col-span-2">
        <div class="aspect-[16/9] overflow-hidden rounded-xl bg-emerald-100">
            @if ($campaign->cover_url)<img src="{{ $campaign->cover_url }}" alt="" class="h-full w-full object-cover">@endif
        </div>
        <span class="mt-5 block text-xs font-medium uppercase tracking-wide text-emerald-700">{{ $campaign->category->label() }}</span>
        <h1 class="mt-1 text-2xl font-semibold">{{ $campaign->title }}</h1>
        <div class="prose mt-4 max-w-none text-stone-700">{!! \Illuminate\Support\Str::markdown((string) $campaign->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
    </article>

    <aside class="space-y-6">
        <div class="rounded-xl border border-stone-200 bg-white p-5">
            @include('campaigns._progress', ['campaign' => $campaign])
            @if ($campaign->status->value === 'active')
                <a href="{{ route('donation.create', ['campaign' => $campaign->slug]) }}" class="mt-4 block rounded-full bg-emerald-700 py-3 text-center font-medium text-white hover:bg-emerald-800">Donasi Sekarang</a>
            @else
                <p class="mt-4 rounded-lg bg-stone-100 p-3 text-center text-sm">Kampanye telah selesai. Jazakumullahu khairan.</p>
            @endif
        </div>
        <div class="rounded-xl border border-stone-200 bg-white p-5">
            <h3 class="font-semibold">Donatur terbaru</h3>
            <ul class="mt-3 space-y-3 text-sm">
                @forelse ($donors as $d)
                    <li>
                        <p class="font-medium">{{ $d->display_name }} <span class="font-normal text-stone-500">— Rp {{ number_format($d->amount, 0, ',', '.') }}</span></p>
                        @if ($d->doa_harapan)<p class="text-stone-500">“{{ $d->doa_harapan }}”</p>@endif
                    </li>
                @empty
                    <li class="text-stone-500">Jadilah donatur pertama.</li>
                @endforelse
            </ul>
        </div>
    </aside>
</div>
@endsection
