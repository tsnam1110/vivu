@extends('layouts.app')

@section('title', 'Người cùng gu — ViVu')

@section('content')
    <h1 class="text-2xl font-bold">Người cùng gu</h1>
    <p class="mt-1 text-sm text-stone-500">Gợi ý dựa trên tính cách & sở thích bạn đã khai báo.</p>

    <div class="mt-6 space-y-4">
        @forelse ($matches as $match)
            <a href="{{ route('profile.show', $match['user']->username) }}"
               class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm hover:border-teal-300 hover:shadow-md">
                <div>
                    <div class="font-semibold">{{ $match['user']->name }}</div>
                    <div class="text-sm text-stone-500">@{{ $match['user']->username }}</div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($match['shared_traits'] as $trait)
                            <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs text-teal-800">{{ $trait }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-teal-700">{{ number_format($match['match_score'] * 100, 0) }}%</div>
                    <div class="text-xs text-stone-500">độ tương đồng</div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-500">
                Chưa có gợi ý phù hợp.
                <a href="{{ route('profile.edit') }}" class="block mt-2 text-teal-700 hover:underline">Hoàn thiện hồ sơ gu</a>
            </div>
        @endforelse
    </div>
@endsection
