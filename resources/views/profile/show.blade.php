@extends('layouts.app')

@section('title', $user->name.' (@'.$user->username.') — ViVu')

@section('content')
    <div class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm backdrop-blur-sm sm:p-7">
        <div class="flex flex-wrap items-start gap-5">
            <x-user-avatar :user="$user" size="xl" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->hasActivePremium())
                        <span class="rounded-full bg-gradient-to-r from-amber-400 to-amber-500 px-2.5 py-0.5 text-[11px] font-semibold text-amber-950 shadow-sm">
                            Premium
                        </span>
                    @endif
                </div>
                <p class="text-stone-500">{{ '@'.$user->username }}</p>
                @if ($user->profile?->bio)
                    <p class="mt-3 text-[15px] leading-relaxed text-stone-700">{{ $user->profile->bio }}</p>
                @endif
                @if ($user->profile?->location_city)
                    <p class="mt-2 text-sm text-stone-500">📍 {{ $user->profile->location_city }}</p>
                @endif
                @if (($user->profile?->personality || $user->profile?->interests))
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach ($user->profile?->personality ?? [] as $trait)
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-800 ring-1 ring-teal-100">{{ $trait }}</span>
                        @endforeach
                        @foreach ($user->profile?->interests ?? [] as $trait)
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-900 ring-1 ring-amber-100">{{ $trait }}</span>
                        @endforeach
                    </div>
                @endif
                @auth('web')
                    @if (auth('web')->id() === $user->id)
                        <a href="{{ route('profile.me') }}" class="mt-4 inline-flex text-sm font-medium text-teal-700 hover:underline">
                            Chỉnh sửa trang cá nhân →
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="mb-3 mt-8 flex items-center justify-between">
        <h2 class="text-lg font-semibold tracking-tight">Trải nghiệm đã đăng</h2>
        <span class="text-xs text-stone-400">{{ $experiences->total() }} mục</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            <x-experience-card :experience="$experience" />
        @empty
            <x-empty-state
                icon="📭"
                title="Chưa có trải nghiệm công khai"
                description="Người dùng này chưa đăng trải nghiệm nào."
            />
        @endforelse
    </div>
    <div class="mt-6">{{ $experiences->links() }}</div>
@endsection
