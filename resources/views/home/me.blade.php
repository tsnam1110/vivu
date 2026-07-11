@extends('layouts.app')

@section('title', 'Kho của tôi — ViVu')

@section('content')
    @php
        $total = $experiences->total();
        $published = $user->experiences()->published()->count();
    @endphp

    <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm backdrop-blur-sm sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('profile.me') }}" class="shrink-0 transition hover:opacity-90">
                    <x-user-avatar :user="$user" size="lg" />
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Kho cá nhân</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900 md:text-3xl">
                        Xin chào, {{ $user->name }}
                    </h1>
                    <p class="mt-1.5 max-w-md text-sm text-stone-500">
                        Lưu và quản lý trải nghiệm — quán ăn, cà phê, chuyến đi…
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('experiences.create') }}" class="vivu-btn-primary">
                    <span class="text-base leading-none">+</span> Đăng trải nghiệm
                </a>
                <a href="{{ route('profile.me') }}" class="vivu-btn-secondary">
                    Profile
                </a>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-4 sm:gap-3">
            <div class="rounded-2xl bg-stone-50 px-4 py-3 ring-1 ring-stone-200/80">
                <div class="text-2xl font-bold tabular-nums text-stone-900">{{ $total }}</div>
                <div class="text-xs text-stone-500">Tổng lưu trữ</div>
            </div>
            <div class="rounded-2xl bg-teal-50/80 px-4 py-3 ring-1 ring-teal-100">
                <div class="text-2xl font-bold tabular-nums text-teal-800">{{ $published }}</div>
                <div class="text-xs text-teal-700/80">Đã công khai</div>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="rounded-2xl bg-white px-4 py-3 ring-1 ring-stone-200/80 transition hover:ring-teal-300">
                <div class="text-sm font-semibold text-stone-900">Hồ sơ gu</div>
                <div class="mt-0.5 text-xs text-stone-500">Tính cách & sở thích</div>
            </a>
            <a href="{{ route('explore') }}"
               class="rounded-2xl bg-white px-4 py-3 ring-1 ring-stone-200/80 transition hover:ring-teal-300">
                <div class="text-sm font-semibold text-stone-900">Khám phá</div>
                <div class="mt-0.5 text-xs text-stone-500">Cộng đồng chia sẻ</div>
            </a>
        </div>
    </section>

    <div class="mb-3 mt-8 flex items-center justify-between gap-3">
        <h2 class="text-lg font-semibold tracking-tight">Trải nghiệm đã lưu</h2>
        <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ $total }} mục</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            <x-experience-card :experience="$experience" :show-status="true" :show-footer="true" />
        @empty
            <x-empty-state
                icon="🗂️"
                title="Kho còn trống"
                description="Bắt đầu lưu trải nghiệm đầu tiên của bạn."
                :action-href="route('experiences.create')"
                action-label="Đăng trải nghiệm"
            />
        @endforelse
    </div>

    <div class="mt-8">
        {{ $experiences->links() }}
    </div>
@endsection
