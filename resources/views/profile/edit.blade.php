@extends('layouts.app')

@section('title', 'Hồ sơ gu — ViVu')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold">Hồ sơ gu của bạn</h1>
        <p class="mt-1 text-sm text-stone-500">Càng đầy đủ, gợi ý người cùng gu càng chính xác.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-medium">Giới thiệu</label>
                <textarea name="bio" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2">{{ old('bio', $user->profile?->bio) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Thành phố</label>
                <input type="text" name="location_city" value="{{ old('location_city', $user->profile?->location_city) }}"
                       class="w-full rounded-xl border border-stone-300 px-3 py-2" placeholder="Đà Nẵng">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Tính cách</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($personalities as $trait)
                        <label class="inline-flex items-center gap-1 rounded-full border border-stone-200 px-3 py-1 text-sm">
                            <input type="checkbox" name="personality[]" value="{{ $trait->slug }}"
                                   @checked(in_array($trait->slug, old('personality', $user->profile?->personality ?? []), true))>
                            {{ $trait->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Sở thích</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($interests as $trait)
                        <label class="inline-flex items-center gap-1 rounded-full border border-stone-200 px-3 py-1 text-sm">
                            <input type="checkbox" name="interests[]" value="{{ $trait->slug }}"
                                   @checked(in_array($trait->slug, old('interests', $user->profile?->interests ?? []), true))>
                            {{ $trait->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_matchable" value="0">
                <input type="checkbox" name="is_matchable" value="1" class="rounded border-stone-300 text-teal-600"
                       @checked(old('is_matchable', $user->profile?->is_matchable ?? true))>
                Cho phép gợi ý tôi trong "Người cùng gu"
            </label>
            <button class="rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">Lưu hồ sơ</button>
        </form>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-4 text-sm">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('profile.me') }}" class="font-medium text-teal-700 hover:underline">
                    ← Trang cá nhân & avatar
                </a>
                <a href="{{ route('profile.show', $user->username) }}" class="font-medium text-stone-600 hover:underline">
                    Xem công khai
                </a>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-stone-500 hover:text-red-600">Đăng xuất</button>
            </form>
        </div>
    </div>
@endsection
