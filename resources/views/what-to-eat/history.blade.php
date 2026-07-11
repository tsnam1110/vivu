@extends('layouts.app')

@section('title', __('what_to_eat.history').' — ViVu')

@section('content')
    <div class="mx-auto max-w-2xl">
        <header class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Hôm nay ăn gì</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-stone-900">{{ __('what_to_eat.history') }}</h1>
            <p class="mt-1 text-sm text-stone-500">Riêng tư — chỉ bạn thấy các lần gợi ý gần đây.</p>
            <a href="{{ route('home') }}" class="mt-3 inline-flex text-sm font-medium text-teal-700 hover:text-teal-800">← Về Kho</a>
        </header>

        <section class="mb-6 rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm"
                 x-data="{
                    flags: @js($preference?->diet_flags ?? []),
                    balance: @js((bool) ($preference?->balance_elements ?? false)),
                    maxKcal: @js($preference?->max_calories_default),
                    preferredElements: @js($preference?->preferred_elements ?? []),
                    dislikedRaw: @js(implode(',', $preference?->disliked_dish_ids ?? [])),
                    elementOpts: ['wood','fire','earth','metal','water'],
                    elementLabels: {
                        wood: @js(__('what_to_eat.element_wood') ?: 'Mộc'),
                        fire: @js(__('what_to_eat.element_fire') ?: 'Hoả'),
                        earth: @js(__('what_to_eat.element_earth') ?: 'Thổ'),
                        metal: @js(__('what_to_eat.element_metal') ?: 'Kim'),
                        water: @js(__('what_to_eat.element_water') ?: 'Thuỷ'),
                    },
                    saving: false,
                    msg: null,
                    async save() {
                        this.saving = true;
                        this.msg = null;
                        const disliked = String(this.dislikedRaw || '')
                            .split(/[\s,;]+/)
                            .map(s => parseInt(s, 10))
                            .filter(n => Number.isFinite(n) && n > 0);
                        try {
                            const res = await fetch(@js(route('what-to-eat.preferences.update')), {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    diet_flags: this.flags,
                                    balance_elements: this.balance,
                                    max_calories_default: this.maxKcal || null,
                                    preferred_elements: this.preferredElements,
                                    disliked_dish_ids: disliked,
                                }),
                            });
                            const json = await res.json();
                            this.msg = res.ok ? (json.meta?.message || 'Đã lưu') : (json.message || 'Lỗi lưu');
                        } catch (e) {
                            this.msg = 'Lỗi lưu';
                        } finally {
                            this.saving = false;
                        }
                    },
                    toggleFlag(f) {
                        if (this.flags.includes(f)) this.flags = this.flags.filter(x => x !== f);
                        else this.flags = [...this.flags, f];
                    },
                    toggleElement(e) {
                        if (this.preferredElements.includes(e)) {
                            this.preferredElements = this.preferredElements.filter(x => x !== e);
                        } else if (this.preferredElements.length < 5) {
                            this.preferredElements = [...this.preferredElements, e];
                        }
                    }
                 }">
            <h2 class="text-sm font-semibold text-stone-900">{{ __('what_to_eat.pref_title') }}</h2>
            <div class="mt-3 space-y-3">
                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="checkbox" class="rounded border-stone-300 text-teal-600"
                           :checked="flags.includes('vegetarian')"
                           @change="toggleFlag('vegetarian')">
                    {{ __('what_to_eat.pref_vegetarian') }}
                </label>
                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="checkbox" class="rounded border-stone-300 text-teal-600" x-model="balance">
                    {{ __('what_to_eat.pref_balance') }}
                </label>
                <div>
                    <label class="text-sm text-stone-700">{{ __('what_to_eat.pref_max_kcal') }}</label>
                    <input type="number" x-model.number="maxKcal" min="50" max="3000"
                           class="mt-1 w-full max-w-xs rounded-xl border border-stone-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <p class="text-sm text-stone-700">{{ __('what_to_eat.pref_elements') }}</p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        <template x-for="el in elementOpts" :key="el">
                            <button type="button" @click="toggleElement(el)"
                                    class="rounded-full px-2.5 py-1 text-xs font-medium ring-1 transition"
                                    :class="preferredElements.includes(el) ? 'bg-teal-600 text-white ring-teal-600' : 'bg-white text-stone-600 ring-stone-200'"
                                    x-text="elementLabels[el] || el"></button>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="text-sm text-stone-700">{{ __('what_to_eat.pref_disliked') }}</label>
                    <input type="text" x-model="dislikedRaw"
                           class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"
                           placeholder="12, 34, 56">
                    <p class="mt-1 text-[11px] text-stone-500">{{ __('what_to_eat.pref_disliked_hint') }}</p>
                </div>
                <button type="button" @click="save()" :disabled="saving"
                        class="rounded-full bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-60">
                    Lưu sở thích
                </button>
                <p x-show="msg" x-text="msg" class="text-xs text-teal-800"></p>
            </div>
        </section>

        @if (empty($items))
            <x-empty-state
                icon="🍜"
                :title="__('what_to_eat.history_empty')"
                description="Mở popup Hôm nay ăn gì trên Kho để bắt đầu."
                :action-href="route('home')"
                action-label="Về Kho"
            />
        @else
            <ul class="space-y-3">
                @foreach ($items as $item)
                    <li class="rounded-3xl border border-stone-200/80 bg-white/90 p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-medium text-stone-500">
                                {{ $item['created_at'] ?? '' }}
                            </p>
                            <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[11px] font-medium text-stone-600">
                                {{ $item['meal_slot'] }} · {{ $item['meal_size'] }} · {{ $item['meal_mode'] }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($item['suggested'] as $d)
                                <span class="inline-flex items-center gap-1 rounded-full bg-stone-50 px-2.5 py-1 text-xs ring-1 ring-stone-200">
                                    {{ $d['emoji'] }} {{ $d['name'] }}
                                </span>
                            @endforeach
                        </div>
                        @if (!empty($item['chosen']))
                            <p class="mt-2 text-sm font-medium text-teal-800">
                                ✓ {{ __('what_to_eat.chosen') }}: {{ $item['chosen']['emoji'] }} {{ $item['chosen']['name'] }}
                            </p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
