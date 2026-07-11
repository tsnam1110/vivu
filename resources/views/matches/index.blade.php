@extends('layouts.app')

@section('title', 'Người cùng gu — ViVu')

@section('content')
    <header class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Gợi ý</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">Người cùng gu</h1>
        <p class="mt-1.5 max-w-lg text-sm text-stone-500">
            Dựa trên tính cách & sở thích bạn đã khai báo trong hồ sơ gu.
        </p>
    </header>

    <div class="space-y-3">
        @forelse ($matches as $match)
            <a href="{{ route('profile.show', $match['user']->username) }}"
               class="group flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md sm:p-5">
                <div class="flex min-w-0 items-center gap-3">
                    <x-user-avatar :user="$match['user']" size="md" />
                    <div class="min-w-0">
                        <div class="truncate font-semibold text-stone-900 group-hover:text-teal-800">
                            {{ $match['user']->name }}
                        </div>
                        <div class="text-sm text-stone-500">{{ '@'.$match['user']->username }}</div>
                        @if (! empty($match['shared_traits']))
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($match['shared_traits'] as $trait)
                                    <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-medium text-teal-800 ring-1 ring-teal-100">
                                        {{ $trait }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:flex-col sm:items-end">
                    <div class="rounded-2xl bg-teal-50 px-3 py-2 text-center ring-1 ring-teal-100">
                        <div class="text-xl font-bold tabular-nums text-teal-800 sm:text-2xl">
                            {{ number_format($match['match_score'] * 100, 0) }}%
                        </div>
                        <div class="text-[10px] font-medium uppercase tracking-wide text-teal-700/70">tương đồng</div>
                    </div>
                </div>
            </a>
        @empty
            <x-empty-state
                icon="🤝"
                title="Chưa có gợi ý phù hợp"
                description="Hoàn thiện hồ sơ gu để ViVu tìm người có gu gần với bạn."
                :action-href="route('profile.me', ['tab' => 'taste'])"
                action-label="Hoàn thiện hồ sơ gu"
            />
        @endforelse
    </div>
@endsection
