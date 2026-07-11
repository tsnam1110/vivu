@props([
    'icon' => '✨',
    'title' => 'Chưa có nội dung',
    'description' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'col-span-full rounded-3xl border border-dashed border-stone-300/90 bg-white/80 px-6 py-12 text-center shadow-sm backdrop-blur-sm']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-2xl">{{ $icon }}</div>
    <h3 class="mt-4 text-lg font-semibold tracking-tight text-stone-900">{{ $title }}</h3>
    @if ($description)
        <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-stone-500">{{ $description }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}"
           class="mt-5 inline-flex items-center justify-center rounded-full bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/20 transition hover:bg-teal-700">
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
