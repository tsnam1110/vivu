@extends('layouts.app')

@section('title', 'Khám phá — ViVu')

@section('content')
    <section class="mb-10 rounded-3xl bg-gradient-to-br from-teal-600 to-cyan-700 px-6 py-10 text-white shadow-lg">
        <h1 class="text-3xl font-bold md:text-4xl">Khám phá trải nghiệm</h1>
        <p class="mt-3 max-w-2xl text-teal-50">Xem những gì cộng đồng đã chia sẻ — quán ăn, cà phê, du lịch, homestay.</p>
        <form method="GET" action="{{ route('explore') }}" class="mt-6 flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm quán, địa điểm..."
                   class="min-w-[220px] flex-1 rounded-2xl border-0 px-4 py-2.5 text-stone-900 shadow">
            <select name="category" class="rounded-2xl border-0 px-3 py-2.5 text-stone-900 shadow">
                <option value="">Tất cả danh mục</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                        {{ $category->icon }} {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-white px-5 py-2.5 font-semibold text-teal-700 hover:bg-teal-50">Tìm</button>
        </form>
    </section>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($categories as $category)
            <a href="{{ route('explore', ['category' => $category->slug]) }}"
               class="rounded-full border px-3 py-1 text-sm {{ request('category') === $category->slug ? 'border-teal-600 bg-teal-50 text-teal-800' : 'border-stone-200 bg-white hover:border-teal-300' }}">
                {{ $category->icon }} {{ $category->name }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            <a href="{{ route('experiences.show', $experience->slug) }}"
               class="group overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="aspect-[16/10] bg-stone-100">
                    @php $cover = $experience->media->firstWhere('is_cover', true) ?? $experience->media->first(); @endphp
                    @if ($cover)
                        <img src="{{ $cover->url() }}" alt="{{ $experience->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-4xl">{{ $experience->category?->icon ?? '📍' }}</div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="mb-1 text-xs font-medium text-teal-700">{{ $experience->category?->name }}</div>
                    <h2 class="line-clamp-2 font-semibold group-hover:text-teal-700">{{ $experience->title }}</h2>
                    <p class="mt-1 line-clamp-1 text-sm text-stone-500">{{ $experience->place_name ?? $experience->address }}</p>
                    <div class="mt-3 flex items-center justify-between text-xs text-stone-500">
                        <span>★ {{ number_format((float) $experience->rating_avg, 1) }} ({{ $experience->rating_count }})</span>
                        <span>♥ {{ $experience->reaction_count }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-500">
                Chưa có trải nghiệm công khai.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $experiences->withQueryString()->links() }}
    </div>
@endsection
