@extends('layouts.app')

@section('title', $user->name.' (@'.$user->username.') — ViVu')

@section('content')
    <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
        <p class="text-stone-500">@{{ $user->username }}</p>
        @if ($user->profile?->bio)
            <p class="mt-3 text-stone-700">{{ $user->profile->bio }}</p>
        @endif
        @if ($user->profile?->location_city)
            <p class="mt-2 text-sm text-stone-500">📍 {{ $user->profile->location_city }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($user->profile?->personality ?? [] as $trait)
                <span class="rounded-full bg-teal-50 px-3 py-1 text-xs text-teal-800">{{ $trait }}</span>
            @endforeach
            @foreach ($user->profile?->interests ?? [] as $trait)
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs text-amber-800">{{ $trait }}</span>
            @endforeach
        </div>
    </div>

    <h2 class="mt-8 text-xl font-semibold">Trải nghiệm đã đăng</h2>
    <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($experiences as $experience)
            <a href="{{ route('experiences.show', $experience->slug) }}" class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm hover:shadow-md">
                <div class="text-xs text-teal-700">{{ $experience->category?->name }}</div>
                <h3 class="mt-1 font-semibold">{{ $experience->title }}</h3>
                <p class="mt-1 text-sm text-stone-500">{{ $experience->place_name }}</p>
            </a>
        @empty
            <p class="col-span-full text-stone-500">Chưa có trải nghiệm công khai.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $experiences->links() }}</div>
@endsection
