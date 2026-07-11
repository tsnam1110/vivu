@props([
    'name' => 'icon',
    'value' => '✨',
    'icons' => [],
    'id' => null,
])

@php
    $icons = $icons !== [] ? $icons : \App\Models\UserHabitItem::ICONS;
    $selected = old($name, $value) ?: '✨';
    // Keep current value selectable even if not in preset (template-adopted icons).
    if ($selected && ! in_array($selected, $icons, true)) {
        $icons = array_values(array_unique(array_merge([$selected], $icons)));
    }
    $pickerId = $id ?? 'icon-picker-'.uniqid();
@endphp

<div
    class="space-y-2"
    x-data="{
        open: false,
        icon: @js($selected),
        toggle() { this.open = !this.open },
        pick(v) { this.icon = v; this.open = false },
    }"
    @keydown.escape.window="open = false"
>
    <input type="hidden" name="{{ $name }}" :value="icon">

    <div class="flex items-center gap-2">
        <button
            type="button"
            id="{{ $pickerId }}-btn"
            @click="toggle()"
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-stone-200 bg-white text-xl shadow-sm transition hover:border-teal-400 hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500/30"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-label="Chọn icon"
            title="Chọn icon"
        >
            <span x-text="icon" class="leading-none"></span>
        </button>
        <div class="min-w-0 flex-1 text-xs text-stone-500">
            <p class="font-medium text-stone-700">Icon</p>
            <p>Chạm để chọn từ bảng icon</p>
        </div>
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="rounded-2xl border border-stone-200 bg-white p-2.5 shadow-md"
        role="listbox"
        aria-label="Bảng icon"
    >
        <div class="mb-2 flex items-center justify-between gap-2 px-0.5">
            <span class="text-[11px] font-medium text-stone-500">Chọn icon</span>
            <button type="button" @click="open = false" class="text-[11px] font-medium text-stone-400 hover:text-stone-700">
                Đóng
            </button>
        </div>
        <div class="grid max-h-44 grid-cols-8 gap-1 overflow-y-auto sm:grid-cols-10">
            @foreach ($icons as $emoji)
                <button
                    type="button"
                    role="option"
                    @click="pick(@js($emoji))"
                    :aria-selected="icon === @js($emoji)"
                    :class="icon === @js($emoji)
                        ? 'border-teal-500 bg-teal-50 ring-2 ring-teal-200'
                        : 'border-transparent bg-stone-50 hover:bg-stone-100'"
                    class="flex h-9 w-full items-center justify-center rounded-lg border text-lg transition active:scale-95"
                    title="{{ $emoji }}"
                >
                    {{ $emoji }}
                </button>
            @endforeach
        </div>
    </div>
</div>
