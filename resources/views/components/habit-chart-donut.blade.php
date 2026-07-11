@props([
    'done' => 0,
    'missed' => 0,
    'empty' => 0,
    'size' => 140,
    /** Alpine-bound mode: when true, use :attrs from parent x-data */
    'live' => false,
])

@php
    $done = (int) $done;
    $missed = (int) $missed;
    $empty = (int) $empty;
    $total = max(1, $done + $missed + $empty);
    $r = 15.9155; // circumference ≈ 100 for easy %
    $donePct = round($done / $total * 100, 2);
    $missedPct = round($missed / $total * 100, 2);
    $emptyPct = max(0, 100 - $donePct - $missedPct);
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }}
     style="width: {{ $size }}px; height: {{ $size }}px">
    @if ($live)
        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" aria-hidden="true">
            <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-stone-100" stroke-width="3.2" />
            {{-- empty ring base --}}
            <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-stone-200" stroke-width="3.2"
                    stroke-dasharray="100 0" stroke-linecap="butt" />
            {{-- done --}}
            <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-teal-500 transition-all duration-500"
                    stroke-width="3.2" stroke-linecap="round"
                    :stroke-dasharray="donut.doneDash"
                    :stroke-dashoffset="donut.doneOffset" />
            {{-- missed --}}
            <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-red-400 transition-all duration-500"
                    stroke-width="3.2" stroke-linecap="round"
                    :stroke-dasharray="donut.missedDash"
                    :stroke-dashoffset="donut.missedOffset" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold tabular-nums text-stone-900" x-text="donut.rateLabel">0%</span>
            <span class="text-[10px] font-medium text-stone-400">tỷ lệ đạt</span>
        </div>
    @else
        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" aria-hidden="true">
            <circle cx="18" cy="18" r="{{ $r }}" fill="none" class="stroke-stone-100" stroke-width="3.2" />
            <circle cx="18" cy="18" r="{{ $r }}" fill="none" class="stroke-stone-200" stroke-width="3.2"
                    stroke-dasharray="100 0" />
            <circle cx="18" cy="18" r="{{ $r }}" fill="none" class="stroke-teal-500" stroke-width="3.2"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $donePct }} {{ 100 - $donePct }}"
                    stroke-dashoffset="0" />
            <circle cx="18" cy="18" r="{{ $r }}" fill="none" class="stroke-red-400" stroke-width="3.2"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $missedPct }} {{ 100 - $missedPct }}"
                    stroke-dashoffset="{{ -$donePct }}" />
        </svg>
        @php
            $filled = $done + $missed;
            $rateLabel = $filled > 0 ? (int) round($done / $filled * 100).'%' : '—';
        @endphp
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            <span class="text-xl font-bold tabular-nums text-stone-900">{{ $rateLabel }}</span>
            <span class="text-[10px] font-medium text-stone-400">tỷ lệ đạt</span>
        </div>
    @endif
</div>
