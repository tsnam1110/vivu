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

        <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3">
            <div class="rounded-2xl bg-stone-50 px-4 py-3 ring-1 ring-stone-200/80">
                <div class="text-2xl font-bold tabular-nums text-stone-900">{{ $total }}</div>
                <div class="text-xs text-stone-500">Tổng lưu trữ</div>
            </div>
            <div class="rounded-2xl bg-teal-50/80 px-4 py-3 ring-1 ring-teal-100">
                <div class="text-2xl font-bold tabular-nums text-teal-800">{{ $published }}</div>
                <div class="text-xs text-teal-700/80">Đã công khai</div>
            </div>
            <x-what-to-eat-modal />
            <a href="{{ route('habits.index') }}"
               class="rounded-2xl bg-white px-4 py-3 ring-1 ring-stone-200/80 transition hover:ring-teal-300">
                <div class="text-sm font-semibold text-stone-900">Thói quen</div>
                <div class="mt-0.5 text-xs text-stone-500">Check-in & streak</div>
            </a>
            <a href="{{ route('profile.me', ['tab' => 'taste']) }}"
               class="rounded-2xl bg-white px-4 py-3 ring-1 ring-stone-200/80 transition hover:ring-teal-300">
                <div class="text-sm font-semibold text-stone-900">{{ __('profile.title') }}</div>
                <div class="mt-0.5 text-xs text-stone-500">Tài khoản & gu</div>
            </a>
        </div>
    </section>

    @if (isset($habitCharts))
        @php
            $month = $habitCharts['month'];
            $last7 = $habitCharts['last_7_days'];
            $topItems = $habitCharts['top_items'];
            $maxBar = max(1, ...array_map(fn ($d) => (int) $d['done'] + (int) $d['missed'], $last7));
        @endphp
        <section class="mt-5 overflow-hidden rounded-3xl border border-stone-200/80 bg-white/90 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-stone-900">Tổng quan Habit</h2>
                    <p class="text-xs text-stone-500">
                        Tháng {{ $month['month_label'] }} · {{ $month['items_count'] }} đầu mục
                    </p>
                </div>
                <a href="{{ route('habits.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">Mở bảng →</a>
            </div>

            <div class="grid gap-0 lg:grid-cols-2">
                {{-- Chart 1: 7 ngày gần nhất --}}
                <div class="border-b border-stone-100 p-5 lg:border-b-0 lg:border-r">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-stone-900">7 ngày gần nhất</h3>
                            <p class="text-[11px] text-stone-400">Cột = số ô đã tích (✓ xanh · ✗ đỏ)</p>
                        </div>
                    </div>
                    @if ($month['items_count'] === 0)
                        <p class="py-8 text-center text-sm text-stone-400">Chưa có đầu mục — <a href="{{ route('habits.items') }}" class="font-medium text-teal-700 hover:underline">thêm ngay</a></p>
                    @else
                        <x-habit-chart-bars :days="$last7" :max="$maxBar" :height="132" />
                        <div class="mt-3 flex flex-wrap gap-3 text-[11px] text-stone-400">
                            <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-teal-500"></span> Đạt</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-sm bg-red-400"></span> Không đạt</span>
                        </div>
                    @endif
                </div>

                {{-- Chart 2: Month ring + top items --}}
                <div class="bg-gradient-to-br from-white to-teal-50/30 p-5">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-stone-900">Tháng này & top đầu mục</h3>
                        <p class="text-[11px] text-stone-400">Tỷ lệ đạt và xếp hạng hiệu suất</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-5">
                        <x-habit-chart-donut
                            :done="$month['done']"
                            :missed="$month['missed']"
                            :empty="$month['empty']"
                            :size="128"
                        />
                        <div class="min-w-0 flex-1 space-y-2.5">
                            @forelse ($topItems as $row)
                                <div>
                                    <div class="mb-0.5 flex items-center justify-between gap-2 text-xs">
                                        <span class="flex min-w-0 items-center gap-1 truncate font-medium text-stone-700">
                                            <span>{{ $row['icon'] ?: '•' }}</span>
                                            <span class="truncate">{{ $row['name'] }}</span>
                                        </span>
                                        <span class="shrink-0 tabular-nums text-teal-700">
                                            {{ $row['filled'] > 0 ? (int) round($row['rate'] * 100).'%' : '—' }}
                                        </span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-stone-100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400"
                                             style="width: {{ (int) round($row['rate'] * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-stone-400">Chưa có dữ liệu tích lũy.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

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
