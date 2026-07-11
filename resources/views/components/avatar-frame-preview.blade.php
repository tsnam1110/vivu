@props([
    'frame' => null,
    'size' => 'md',
    'imageUrl' => null,
    'initials' => 'VV',
])

@php
    $sizes = [
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-16 w-16 text-lg',
        'lg' => 'h-24 w-24 text-2xl',
        'xl' => 'h-32 w-32 text-3xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($frame)
    <div
        {{ $attributes->class([$frame->effectClass(), 'af-preview-'.$size, 'relative inline-flex shrink-0']) }}
        style="{{ $frame->cssVariablesString() }}"
    >
        <div class="af-inner {{ $box }}">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-600 font-bold text-white">
                    {{ $initials }}
                </div>
            @endif
        </div>
        @if ($frame->show_badge)
            <span class="af-badge" aria-hidden="true">✦</span>
        @endif
    </div>
@else
    <div {{ $attributes->class(['relative inline-flex shrink-0 rounded-full bg-stone-200 p-0.5']) }}>
        <div class="{{ $box }} overflow-hidden rounded-full bg-white ring-2 ring-white">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="" class="h-full w-full object-cover">
            @else
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-600 font-bold text-white">
                    {{ $initials }}
                </div>
            @endif
        </div>
    </div>
@endif
