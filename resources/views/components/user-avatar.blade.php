@props([
    'user',
    'size' => 'md', // sm | md | lg | xl
])

@php
    $sizes = [
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-16 w-16 text-lg',
        'lg' => 'h-24 w-24 text-2xl',
        'xl' => 'h-32 w-32 text-3xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
    $frame = $user->resolvedAvatarFrame();
    $url = $user->avatarUrl();
    $showBadge = $frame?->show_badge && $user->hasActivePremium();
@endphp

<div {{ $attributes->class(['relative inline-flex shrink-0']) }}>
    @if ($frame)
        <div
            class="{{ $frame->effectClass() }} af-preview-{{ $size }}"
            style="{{ $frame->cssVariablesString() }}"
        >
            <div class="af-inner {{ $box }}">
                @if ($url)
                    <img src="{{ $url }}" alt="{{ $user->name }}" class="h-full w-full object-cover" loading="lazy">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-600 font-bold text-white">
                        {{ $user->initials() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-full bg-stone-200 p-0.5">
            <div class="{{ $box }} overflow-hidden rounded-full bg-white ring-2 ring-white">
                @if ($url)
                    <img src="{{ $url }}" alt="{{ $user->name }}" class="h-full w-full object-cover" loading="lazy">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-600 font-bold text-white">
                        {{ $user->initials() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($showBadge)
        <span class="af-badge" title="Premium" aria-label="Premium">✦</span>
    @endif
</div>
