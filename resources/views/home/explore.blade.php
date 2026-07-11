@extends('layouts.app')

@section('title', 'Khám phá — ViVu')

@section('content')
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 via-teal-600 to-cyan-700 px-5 py-8 text-white shadow-lg shadow-teal-700/15 sm:px-8 sm:py-10">
        <div class="pointer-events-none absolute -right-8 top-0 h-40 w-40 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-teal-100/90">Cộng đồng</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl md:text-4xl">Khám phá trải nghiệm</h1>
            <p class="mt-2 max-w-xl text-sm text-teal-50/95 sm:text-[15px]">
                Quán ăn, cà phê, du lịch, homestay — những gì mọi người đã chia sẻ.
            </p>
            <form method="GET" action="{{ route('explore') }}" class="mt-5 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm quán, địa điểm…"
                       class="min-w-0 flex-1 rounded-2xl border-0 bg-white px-4 py-2.5 text-sm text-stone-900 shadow-sm placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-white/50">
                <select name="category" class="rounded-2xl border-0 bg-white/95 px-3 py-2.5 text-sm text-stone-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-white/50 sm:w-44">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-semibold text-teal-800 shadow-sm transition hover:bg-teal-50">
                    Tìm
                </button>
            </form>
        </div>
    </section>

    <div class="mt-5 flex gap-2 overflow-x-auto pb-1 scrollbar-none sm:flex-wrap sm:overflow-visible">
        <a href="{{ route('explore', array_filter(['q' => request('q')])) }}"
           class="shrink-0 rounded-full border px-3 py-1.5 text-sm transition {{ ! request('category') ? 'border-teal-500 bg-teal-50 font-medium text-teal-800' : 'border-stone-200 bg-white text-stone-600 hover:border-teal-300' }}">
            Tất cả
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('explore', array_filter(['category' => $category->slug, 'q' => request('q')])) }}"
               class="shrink-0 rounded-full border px-3 py-1.5 text-sm transition {{ request('category') === $category->slug ? 'border-teal-500 bg-teal-50 font-medium text-teal-800' : 'border-stone-200 bg-white text-stone-600 hover:border-teal-300' }}">
                {{ $category->icon }} {{ $category->name }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-between gap-2">
        <p class="text-sm text-stone-500">
            <span class="font-medium text-stone-700">{{ $experiences->total() }}</span> trải nghiệm
            @if (request('q') || request('category'))
                <span class="text-stone-400">· đã lọc</span>
            @endif
        </p>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            <x-experience-card :experience="$experience" />
        @empty
            <x-empty-state
                icon="🔎"
                title="Không tìm thấy"
                description="Thử đổi từ khoá hoặc chọn danh mục khác."
                :action-href="route('explore')"
                action-label="Xem tất cả"
            />
        @endforelse
    </div>

    <div class="mt-8">
        {{ $experiences->withQueryString()->links() }}
    </div>
@endsection
