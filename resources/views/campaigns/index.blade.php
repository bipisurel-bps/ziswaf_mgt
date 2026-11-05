@extends('layouts.app')

@section('content')
<section class="bg-emerald-800 text-white">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <h1 class="max-w-2xl text-3xl font-semibold leading-tight md:text-4xl">Tunaikan zakat, infaq, sedekah &amp; wakaf dengan amanah.</h1>
        <p class="mt-3 max-w-xl text-emerald-100">Donasi Anda tercatat otomatis, kwitansi dikirim lewat WhatsApp, dan penyalurannya dilaporkan.</p>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('donation.create') }}" class="rounded-full bg-white px-5 py-2.5 font-medium text-emerald-800">Donasi Cepat</a>
            <a href="{{ route('zakat') }}" class="rounded-full border border-emerald-300 px-5 py-2.5 font-medium">Hitung Zakat</a>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-10">
    <div class="mb-6 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('home') }}" class="rounded-full border px-3 py-1 {{ ! $category ? 'bg-emerald-700 text-white' : 'bg-white' }}">Semua</a>
        @foreach (\App\Enums\CampaignCategory::cases() as $c)
            <a href="{{ route('home', ['kategori' => $c->value]) }}" class="rounded-full border px-3 py-1 {{ $category === $c->value ? 'bg-emerald-700 text-white' : 'bg-white' }}">{{ $c->label() }}</a>
        @endforeach
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($campaigns as $campaign)
            <a href="{{ route('campaigns.show', $campaign) }}" class="group overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:shadow-md">
                <div class="aspect-[16/9] bg-emerald-100">
                    @if ($campaign->cover_url)<img src="{{ $campaign->cover_url }}" alt="" class="h-full w-full object-cover" loading="lazy">@endif
                </div>
                <div class="p-4">
                    <span class="text-xs font-medium uppercase tracking-wide text-emerald-700">{{ $campaign->category->label() }}</span>
                    <h2 class="mt-1 line-clamp-2 font-semibold group-hover:text-emerald-800">{{ $campaign->title }}</h2>
                    @include('campaigns._progress', ['campaign' => $campaign])
                </div>
            </a>
        @empty
            <p class="col-span-full text-stone-500">Belum ada kampanye aktif.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $campaigns->links() }}</div>
</section>
@endsection
