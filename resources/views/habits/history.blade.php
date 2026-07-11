@extends('layouts.app')

@section('title', 'Lịch sử Habit — ViVu')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('habits.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">← Habit Tracker</a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900">Lịch sử ghi nhận</h1>
                <p class="mt-1 text-sm text-stone-500">Mọi lần đổi trạng thái ô được lưu theo tài khoản của bạn.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-stone-200/80 bg-white/90 shadow-sm">
            @forelse ($histories as $h)
                @php
                    $from = $h->from_status?->symbol() ?? '·';
                    $to = $h->to_status?->symbol() ?? '·';
                    $fromLabel = $h->from_status?->label() ?? 'Trống';
                    $toLabel = $h->to_status?->label() ?? 'Trống';
                @endphp
                <div class="flex flex-wrap items-start gap-3 border-b border-stone-100 px-4 py-3 last:border-0">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-stone-50 text-lg ring-1 ring-stone-200">
                        {{ $h->userHabitItem?->icon ?: '•' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-stone-900">{{ $h->userHabitItem?->name ?? '—' }}</div>
                        <p class="mt-0.5 text-sm text-stone-600">
                            Ngày <span class="tabular-nums font-medium">{{ $h->entry_date?->format('d/m/Y') }}</span>:
                            <span class="text-stone-500">{{ $fromLabel }}</span>
                            <span class="mx-1 text-stone-300">→</span>
                            <span class="font-medium text-stone-800">{{ $toLabel }}</span>
                            <span class="ml-1 font-mono text-xs text-stone-400">({{ $from }}→{{ $to }})</span>
                        </p>
                        <p class="mt-0.5 text-[11px] text-stone-400">
                            {{ $h->changed_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}
                            · {{ $h->source }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-sm text-stone-500">
                    Chưa có thay đổi nào. Mở Habit Tracker và nhấn các ô để bắt đầu.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $histories->links() }}
        </div>
    </div>
@endsection
