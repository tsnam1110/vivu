@props([
    'days' => [], // list of {label, done, missed?}
    'max' => null,
    'height' => 120,
])

@php
    $days = is_array($days) ? $days : [];
    $maxVal = $max;
    if ($maxVal === null) {
        $maxVal = 1;
        foreach ($days as $d) {
            $maxVal = max($maxVal, (int) ($d['done'] ?? 0) + (int) ($d['missed'] ?? 0));
        }
    }
    $maxVal = max(1, (int) $maxVal);
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex items-end justify-between gap-1.5 sm:gap-2" style="height: {{ $height }}px">
        @foreach ($days as $d)
            @php
                $done = (int) ($d['done'] ?? 0);
                $missed = (int) ($d['missed'] ?? 0);
                $total = $done + $missed;
                $pct = $total > 0 ? ($total / $maxVal) * 100 : 0;
                $doneShare = $total > 0 ? ($done / $total) * 100 : 0;
            @endphp
            <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1">
                <div class="flex w-full max-w-[2.25rem] flex-1 flex-col justify-end">
                    <div class="relative mx-auto flex w-full max-w-[1.75rem] flex-col justify-end overflow-hidden rounded-t-lg bg-stone-100"
                         style="height: {{ max(6, $pct) }}%"
                         title="{{ $d['label'] ?? '' }}: {{ $done }}✓ {{ $missed }}✗">
                        @if ($total > 0)
                            <div class="w-full bg-gradient-to-t from-teal-600 to-teal-400 transition-all"
                                 style="height: {{ $doneShare }}%"></div>
                            <div class="w-full bg-gradient-to-t from-red-500 to-red-400 transition-all"
                                 style="height: {{ 100 - $doneShare }}%"></div>
                        @endif
                    </div>
                </div>
                <span class="truncate text-[10px] font-medium text-stone-400">{{ $d['label'] ?? '' }}</span>
            </div>
        @endforeach
    </div>
</div>
