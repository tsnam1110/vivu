{{-- Month/year navigator: from (today − 15 years) through current month — no future --}}
@php
    $selectClass = 'rounded-xl border border-stone-200 bg-white px-2.5 py-1.5 text-sm font-semibold text-stone-800 shadow-sm outline-none transition hover:border-teal-300 focus:border-teal-400 focus:ring-2 focus:ring-teal-500/30';
    $btnClass = 'inline-flex h-9 w-9 items-center justify-center rounded-full border border-stone-200 bg-white text-sm font-medium text-stone-700 shadow-sm transition hover:border-teal-300 hover:text-teal-800';
    $btnDisabled = 'inline-flex h-9 w-9 items-center justify-center rounded-full border border-stone-100 bg-stone-50 text-sm text-stone-300';
    $nowYear = (int) now(config('app.timezone'))->year;
    $nowMonth = (int) now(config('app.timezone'))->month;
@endphp

<div class="flex flex-wrap items-center gap-2">
    @if ($canGoPrev)
        <a href="{{ route('habits.index', ['year' => $prevYear, 'month' => $prevMonth]) }}"
           class="{{ $btnClass }}"
           title="Tháng trước"
           aria-label="Tháng trước">←</a>
    @else
        <span class="{{ $btnDisabled }}" aria-disabled="true">←</span>
    @endif

    <form method="GET" action="{{ route('habits.index') }}" class="flex items-center gap-1.5">
        <label class="sr-only" for="habit-nav-month">Tháng</label>
        <select name="month" id="habit-nav-month" class="{{ $selectClass }}"
                onchange="this.form.submit()"
                aria-label="Chọn tháng">
            @foreach ($months as $m => $label)
                <option value="{{ $m }}" @selected((int) $month === (int) $m)>{{ $label }}</option>
            @endforeach
        </select>

        <label class="sr-only" for="habit-nav-year">Năm</label>
        <select name="year" id="habit-nav-year" class="{{ $selectClass }} min-w-[5.5rem]"
                onchange="this.form.submit()"
                aria-label="Chọn năm">
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected((int) $year === (int) $y)>{{ $y }}</option>
            @endforeach
        </select>
    </form>

    @if ($canGoNext)
        <a href="{{ route('habits.index', ['year' => $nextYear, 'month' => $nextMonth]) }}"
           class="{{ $btnClass }}"
           title="Tháng sau"
           aria-label="Tháng sau">→</a>
    @else
        <span class="{{ $btnDisabled }}" aria-disabled="true">→</span>
    @endif

    @if ((int) $year !== $nowYear || (int) $month !== $nowMonth)
        <a href="{{ route('habits.index') }}"
           class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-800 transition hover:bg-teal-100">
            Hôm nay
        </a>
    @endif
</div>
