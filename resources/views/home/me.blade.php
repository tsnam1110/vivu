@extends('layouts.app')

@section('title', 'Kho của tôi — ViVu')

@section('content')
    <section class="mb-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-teal-700">Kho cá nhân</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight md:text-3xl">Xin chào, {{ $user->name }}</h1>
                <p class="mt-2 max-w-xl text-sm text-stone-500">
                    Lưu và quản lý trải nghiệm của bạn — quán ăn, cà phê, chuyến đi…
                    ViVu là không gian lưu trữ của bạn trước hết.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('experiences.create') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
                    <span class="text-lg leading-none">+</span> Đăng trải nghiệm
                </a>
                <a href="{{ route('profile.show', $user->username) }}"
                   class="inline-flex items-center rounded-full border border-stone-200 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:border-teal-300">
                    Xem trang công khai
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
                $total = $experiences->total();
                $published = $user->experiences()->published()->count();
            @endphp
            <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-4 shadow-sm backdrop-blur">
                <div class="text-2xl font-bold text-stone-900">{{ $total }}</div>
                <div class="text-xs text-stone-500">Tổng lưu trữ</div>
            </div>
            <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-4 shadow-sm backdrop-blur">
                <div class="text-2xl font-bold text-teal-700">{{ $published }}</div>
                <div class="text-xs text-stone-500">Đã công khai</div>
            </div>
            <a href="{{ route('profile.edit') }}" class="rounded-2xl border border-stone-200/80 bg-white/90 p-4 shadow-sm backdrop-blur transition hover:border-teal-300">
                <div class="text-sm font-semibold text-stone-900">Hồ sơ gu</div>
                <div class="mt-1 text-xs text-stone-500">Chỉnh tính cách & sở thích</div>
            </a>
            <a href="{{ route('explore') }}" class="rounded-2xl border border-stone-200/80 bg-white/90 p-4 shadow-sm backdrop-blur transition hover:border-teal-300">
                <div class="text-sm font-semibold text-stone-900">Khám phá</div>
                <div class="mt-1 text-xs text-stone-500">Xem trải nghiệm cộng đồng</div>
            </a>
        </div>
    </section>

    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Trải nghiệm đã lưu</h2>
        <span class="text-xs text-stone-400">{{ $experiences->total() }} mục</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            @php
                $status = $experience->status;
                $badge = match ($status?->value ?? (string) $status) {
                    'published' => 'bg-teal-50 text-teal-800',
                    'pending' => 'bg-amber-50 text-amber-800',
                    'draft' => 'bg-stone-100 text-stone-600',
                    'hidden' => 'bg-red-50 text-red-700',
                    default => 'bg-stone-100 text-stone-600',
                };
                $statusLabel = match ($status?->value ?? (string) $status) {
                    'published' => 'Công khai',
                    'pending' => 'Chờ duyệt',
                    'draft' => 'Nháp',
                    'hidden' => 'Đã ẩn',
                    default => (string) $status,
                };
                $cover = $experience->media->firstWhere('is_cover', true) ?? $experience->media->first();
            @endphp
            <article class="group overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <a href="{{ route('experiences.show', $experience->slug) }}" class="block">
                    <div class="aspect-[16/10] bg-stone-100">
                        @if ($cover)
                            <img src="{{ $cover->url() }}" alt="{{ $experience->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-4xl">{{ $experience->category?->icon ?? '📍' }}</div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-teal-700">{{ $experience->category?->name }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $badge }}">{{ $statusLabel }}</span>
                        </div>
                        <h3 class="line-clamp-2 font-semibold group-hover:text-teal-700">{{ $experience->title }}</h3>
                        <p class="mt-1 line-clamp-1 text-sm text-stone-500">{{ $experience->place_name ?? $experience->address }}</p>
                    </div>
                </a>
                <div class="flex border-t border-stone-100 px-4 py-2">
                    <a href="{{ route('experiences.edit', $experience) }}" class="text-xs font-medium text-stone-500 hover:text-teal-700">Chỉnh sửa</a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-stone-300 bg-white/80 px-6 py-14 text-center">
                <div class="text-4xl">🗂️</div>
                <h3 class="mt-3 text-lg font-semibold">Kho còn trống</h3>
                <p class="mt-2 text-sm text-stone-500">Bắt đầu lưu trải nghiệm đầu tiên của bạn.</p>
                <a href="{{ route('experiences.create') }}"
                   class="mt-5 inline-flex rounded-full bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                    Đăng trải nghiệm
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $experiences->links() }}
    </div>
@endsection
