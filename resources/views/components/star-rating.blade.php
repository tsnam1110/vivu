@props([
    /** int|null: thang 1–10 (½ sao) hoặc 1–5 nếu $scale === 5 */
    'value' => null,
    'scale' => 10,
    'size' => 'sm', // sm | md
])

@php
    if ($value === null || $value === '') {
        $starValue = 0;
    } elseif ((int) $scale === 5) {
        // 1–5 sao đầy (comment rating)
        $starValue = (int) round((float) $value * 2);
    } else {
        // 1–10 = 5 sao × nửa sao
        $starValue = (int) $value;
    }
    $sizeClass = $size === 'md' ? 'h-4 w-4' : 'h-3.5 w-3.5';
    $label = number_format($starValue / 2, 1);
@endphp

@if ($starValue > 0)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 align-middle']) }}
          title="{{ $label }} / 5 sao"
          aria-label="{{ $label }} trên 5 sao">
        @for ($i = 1; $i <= 5; $i++)
            @php
                $fullAt = $i * 2;
                $halfAt = $fullAt - 1;
                $fill = $starValue >= $fullAt ? 'full' : ($starValue >= $halfAt ? 'half' : 'empty');
            @endphp
            <span class="relative inline-block {{ $sizeClass }}">
                <svg class="absolute inset-0 h-full w-full fill-stone-200 text-stone-200" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                </svg>
                @if ($fill !== 'empty')
                    <svg class="absolute inset-0 h-full w-full fill-amber-400 text-amber-400"
                         viewBox="0 0 24 24" aria-hidden="true"
                         @if ($fill === 'half') style="clip-path: inset(0 50% 0 0)" @endif>
                        <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                    </svg>
                @endif
            </span>
        @endfor
    </span>
@endif
