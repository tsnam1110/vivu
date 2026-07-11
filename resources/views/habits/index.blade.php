@extends('layouts.app')

@section('title', 'Habit Tracker — ViVu')

@section('content')
    @php
        $cellsJson = $grid['cells'];
        $csrf = csrf_token();
        $cycleUrl = route('habits.cycle');
        $statsJson = $grid['stats'];
        $itemsMeta = collect($grid['items'])->map(fn ($i) => [
            'id' => (string) $i['id'],
            'name' => $i['name'],
            'icon' => $i['icon'] ?: '•',
        ])->values()->all();
    @endphp

    @if ($itemCount === 0)
        <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm backdrop-blur-sm sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Kho cá nhân</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900 md:text-3xl">Habit Tracker</h1>
                    <p class="mt-1.5 max-w-lg text-sm text-stone-500">
                        Bảng theo ngày — ấn ô: trống → <span class="font-semibold text-teal-700">✓</span> →
                        <span class="font-semibold text-red-600">✗</span> → trống. Đầu mục do <strong>bạn</strong> chọn/tạo.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('habits.items') }}" class="vivu-btn-primary">Đầu mục</a>
                    <a href="{{ route('habits.history') }}" class="vivu-btn-secondary">Lịch sử</a>
                </div>
            </div>
        </section>
        <div class="mt-6">
            <x-empty-state
                icon="📋"
                title="Chưa có đầu mục nào"
                description="Chọn mẫu có sẵn hoặc tự nhập đầu mục theo gu của bạn — chỉ hiện trên tài khoản bạn."
                :action-href="route('habits.items')"
                action-label="Thêm đầu mục"
            />
        </div>
    @else
        <div
            class="space-y-5"
            x-data="habitGrid({
                cells: @js($cellsJson),
                cycleUrl: @js($cycleUrl),
                csrf: @js($csrf),
                stats: @js($statsJson),
                items: @js($itemsMeta),
            })"
            x-init="recomputeStats()"
        >
            <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm backdrop-blur-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Kho cá nhân</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900 md:text-3xl">Habit Tracker</h1>
                        <p class="mt-1.5 max-w-lg text-sm text-stone-500">
                            Bảng theo ngày — ấn ô: trống → <span class="font-semibold text-teal-700">✓</span> →
                            <span class="font-semibold text-red-600">✗</span> → trống. Đầu mục do <strong>bạn</strong> chọn/tạo.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('habits.items') }}" class="vivu-btn-primary">Đầu mục</a>
                        <a href="{{ route('habits.history') }}" class="vivu-btn-secondary">Lịch sử</a>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    @include('habits.partials.month-nav')
                    <div class="flex flex-wrap gap-3 text-xs text-stone-500">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-5 w-5 items-center justify-center rounded border border-stone-200 bg-white text-[10px]"></span>
                            Trống (<span class="tabular-nums font-medium text-stone-700" x-text="stats.empty">{{ $grid['stats']['empty'] }}</span>)
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-teal-600 text-[10px] font-bold text-white">✓</span>
                            Đạt (<span class="tabular-nums font-semibold text-teal-800" x-text="stats.done">{{ $grid['stats']['done'] }}</span>)
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-5 w-5 items-center justify-center rounded bg-red-500 text-[10px] font-bold text-white">✗</span>
                            Không đạt (<span class="tabular-nums font-semibold text-red-700" x-text="stats.missed">{{ $grid['stats']['missed'] }}</span>)
                        </span>
                    </div>
                </div>
            </section>

            {{-- 2 charts --}}
            <div class="grid gap-4 lg:grid-cols-2">
                {{-- Chart 1: Donut composition --}}
                <section class="rounded-3xl border border-stone-200/80 bg-gradient-to-br from-white via-white to-teal-50/40 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-stone-900">Cơ cấu tháng này</h2>
                            <p class="mt-0.5 text-xs text-stone-400">Tỷ lệ đạt trên các ô đã ghi nhận</p>
                        </div>
                        <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-semibold text-teal-800 ring-1 ring-teal-100">Live</span>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-6">
                        <x-habit-chart-donut :live="true" :size="148" />
                        <ul class="min-w-[8rem] space-y-2.5 text-sm">
                            <li class="flex items-center justify-between gap-4">
                                <span class="inline-flex items-center gap-2 text-stone-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> Đạt
                                </span>
                                <span class="font-semibold tabular-nums text-stone-900" x-text="stats.done">0</span>
                            </li>
                            <li class="flex items-center justify-between gap-4">
                                <span class="inline-flex items-center gap-2 text-stone-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span> Không đạt
                                </span>
                                <span class="font-semibold tabular-nums text-stone-900" x-text="stats.missed">0</span>
                            </li>
                            <li class="flex items-center justify-between gap-4">
                                <span class="inline-flex items-center gap-2 text-stone-600">
                                    <span class="h-2.5 w-2.5 rounded-full bg-stone-300"></span> Trống
                                </span>
                                <span class="font-semibold tabular-nums text-stone-900" x-text="stats.empty">0</span>
                            </li>
                        </ul>
                    </div>
                </section>

                {{-- Chart 2: Per-item completion bars --}}
                <section class="rounded-3xl border border-stone-200/80 bg-gradient-to-br from-white via-white to-amber-50/30 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-stone-900">Hiệu suất theo đầu mục</h2>
                            <p class="mt-0.5 text-xs text-stone-400">% đạt trong các ngày đã tích</p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-semibold text-amber-900 ring-1 ring-amber-100">Live</span>
                    </div>
                    <ul class="mt-4 space-y-3">
                        <template x-for="row in byItem" :key="row.id">
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-2 text-xs">
                                    <span class="flex min-w-0 items-center gap-1.5 font-medium text-stone-700">
                                        <span x-text="row.icon" class="shrink-0"></span>
                                        <span class="truncate" x-text="row.name"></span>
                                    </span>
                                    <span class="shrink-0 tabular-nums text-stone-500">
                                        <span x-text="row.done"></span>/<span x-text="row.filled"></span>
                                        · <span class="font-semibold text-teal-700" x-text="row.rateLabel"></span>
                                    </span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-stone-100 ring-1 ring-stone-200/60">
                                    <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-400 transition-all duration-500"
                                         :style="`width: ${row.ratePct}%`"></div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </section>
            </div>

            <div class="overflow-hidden rounded-3xl border border-stone-200/80 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-max min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-stone-50">
                                <th class="sticky left-0 z-20 min-w-[10rem] border-b border-r border-stone-200 bg-stone-50 px-3 py-2 text-left text-xs font-semibold text-stone-600 sm:min-w-[12rem]">
                                    Đầu mục
                                </th>
                                @foreach ($grid['days'] as $d)
                                    <th class="min-w-[2rem] border-b border-stone-200 px-0.5 py-1.5 text-center {{ $d['is_today'] ? 'bg-teal-50' : '' }}">
                                        <div class="text-[10px] font-medium text-stone-400">{{ $weekdayLabels[$d['weekday']] }}</div>
                                        <div class="text-xs font-semibold tabular-nums {{ $d['is_today'] ? 'text-teal-800' : 'text-stone-700' }}">{{ $d['day'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grid['items'] as $item)
                                <tr class="hover:bg-stone-50/50">
                                    <td class="sticky left-0 z-10 border-b border-r border-stone-100 bg-white px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base" aria-hidden="true">{{ $item['icon'] ?: '•' }}</span>
                                            <div class="min-w-0">
                                                <div class="truncate font-medium text-stone-900" title="{{ $item['name'] }}">
                                                    {{ $item['name'] }}
                                                    @if (! empty($item['is_custom']))
                                                        <span class="ml-1 text-[10px] font-normal text-violet-600">tuỳ chỉnh</span>
                                                    @endif
                                                </div>
                                                @if ($item['description'])
                                                    <div class="truncate text-[10px] text-stone-400" title="{{ $item['description'] }}">{{ $item['description'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($grid['days'] as $d)
                                        @php $cellKey = $item['id'].'|'.$d['date']; @endphp
                                        <td class="border-b border-stone-100 p-0.5 text-center {{ $d['is_today'] ? 'bg-teal-50/40' : '' }}">
                                            @if ($d['is_future'])
                                                <span class="mx-auto flex h-7 w-7 items-center justify-center rounded border border-dashed border-stone-200 bg-stone-50 text-stone-300"
                                                      title="Ngày tương lai" aria-disabled="true">·</span>
                                            @else
                                                <button
                                                    type="button"
                                                    class="mx-auto flex h-7 w-7 items-center justify-center rounded border text-xs font-bold transition active:scale-95 disabled:opacity-50"
                                                    :class="cellClass(cells['{{ $item['id'] }}']?.['{{ $d['date'] }}'])"
                                                    :disabled="busy['{{ $cellKey }}']"
                                                    @click="cycle({{ $item['id'] }}, '{{ $d['date'] }}')"
                                                    :title="cellTitle(cells['{{ $item['id'] }}']?.['{{ $d['date'] }}'], '{{ $d['date'] }}')"
                                                    :aria-label="'{{ addslashes($item['name']) }} ngày {{ $d['day'] }}'"
                                                >
                                                    <span x-text="cellSymbol(cells['{{ $item['id'] }}']?.['{{ $d['date'] }}'])"></span>
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-stone-100 px-4 py-2 text-[11px] text-stone-400" x-show="error" x-text="error" x-cloak></p>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('habitGrid', (config) => ({
        cells: config.cells,
        cycleUrl: config.cycleUrl,
        csrf: config.csrf,
        items: config.items || [],
        stats: {
            done: config.stats?.done ?? 0,
            missed: config.stats?.missed ?? 0,
            empty: config.stats?.empty ?? 0,
            total_cells: config.stats?.total_cells ?? 0,
        },
        byItem: [],
        donut: {
            doneDash: '0 100',
            doneOffset: 0,
            missedDash: '0 100',
            missedOffset: 0,
            rateLabel: '—',
        },
        busy: {},
        error: null,

        cellSymbol(status) {
            if (status === 'done') return '✓';
            if (status === 'missed') return '✗';
            return '';
        },

        cellClass(status) {
            if (status === 'done') {
                return 'border-teal-600 bg-teal-600 text-white shadow-sm hover:bg-teal-700';
            }
            if (status === 'missed') {
                return 'border-red-500 bg-red-500 text-white shadow-sm hover:bg-red-600';
            }
            return 'border-stone-200 bg-white text-stone-400 hover:border-teal-400 hover:bg-teal-50';
        },

        cellTitle(status, date) {
            const label = status === 'done' ? 'Đạt' : (status === 'missed' ? 'Không đạt' : 'Trống');
            return date + ' — ' + label + ' (nhấn để đổi)';
        },

        recomputeStats() {
            let done = 0;
            let missed = 0;
            let empty = 0;
            for (const row of Object.values(this.cells || {})) {
                for (const status of Object.values(row || {})) {
                    if (status === 'done') done++;
                    else if (status === 'missed') missed++;
                    else empty++;
                }
            }
            this.stats = {
                done,
                missed,
                empty,
                total_cells: done + missed + empty,
            };

            // Donut (circumference scaled to 100)
            const total = Math.max(1, done + missed + empty);
            const donePct = (done / total) * 100;
            const missedPct = (missed / total) * 100;
            const filled = done + missed;
            this.donut = {
                doneDash: `${donePct} ${100 - donePct}`,
                doneOffset: 0,
                missedDash: `${missedPct} ${100 - missedPct}`,
                missedOffset: -donePct,
                rateLabel: filled > 0 ? Math.round((done / filled) * 100) + '%' : '—',
            };

            // Per-item bars
            this.byItem = (this.items || []).map((item) => {
                const row = this.cells[item.id] || {};
                let d = 0;
                let m = 0;
                for (const status of Object.values(row)) {
                    if (status === 'done') d++;
                    else if (status === 'missed') m++;
                }
                const filledItem = d + m;
                const rate = filledItem > 0 ? d / filledItem : 0;
                return {
                    id: item.id,
                    name: item.name,
                    icon: item.icon,
                    done: d,
                    filled: filledItem,
                    ratePct: Math.round(rate * 100),
                    rateLabel: filledItem > 0 ? Math.round(rate * 100) + '%' : '—',
                };
            });
        },

        async cycle(itemId, date) {
            const key = itemId + '|' + date;
            if (this.busy[key]) return;
            this.busy[key] = true;
            this.error = null;

            const id = String(itemId);
            const prev = this.cells[id]?.[date] ?? null;
            const next = prev === null || prev === undefined ? 'done' : (prev === 'done' ? 'missed' : null);

            if (!this.cells[id]) {
                this.cells[id] = {};
            }
            this.cells[id] = { ...this.cells[id], [date]: next };
            this.recomputeStats();

            try {
                const res = await fetch(this.cycleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ user_habit_item_id: itemId, date }),
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.cells[id] = { ...this.cells[id], [date]: prev };
                    this.recomputeStats();
                    this.error = json.message || (json.errors && Object.values(json.errors).flat()[0]) || 'Không lưu được.';
                    return;
                }
                const serverStatus = json.data?.status ?? null;
                this.cells[id] = { ...this.cells[id], [date]: serverStatus };
                this.recomputeStats();
            } catch (e) {
                this.cells[id] = { ...this.cells[id], [date]: prev };
                this.recomputeStats();
                this.error = 'Lỗi mạng — thử lại.';
            } finally {
                this.busy[key] = false;
            }
        },
    }));
});
</script>
@endpush
