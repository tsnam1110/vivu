@extends('layouts.app')

@section('title', 'Hồ sơ gu — ViVu')

@section('content')
    @php
        $profile = $user->profile;
        $calorieEstimate = app(\App\Services\DailyCalorieEstimator::class)->estimateDaily($profile);
    @endphp
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold">Hồ sơ gu của bạn</h1>
        <p class="mt-1 text-sm text-stone-500">Càng đầy đủ, gợi ý người cùng gu và calo «Hôm nay ăn gì» càng chính xác.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-5 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')
            <div>
                <label class="mb-1 block text-sm font-medium">Giới thiệu</label>
                <textarea name="bio" rows="3" class="w-full rounded-xl border border-stone-300 px-3 py-2">{{ old('bio', $profile?->bio) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Thành phố</label>
                <input type="text" name="location_city" value="{{ old('location_city', $profile?->location_city) }}"
                       class="w-full rounded-xl border border-stone-300 px-3 py-2" placeholder="Đà Nẵng">
            </div>

            <section class="rounded-2xl border border-teal-100 bg-teal-50/40 p-4">
                <h2 class="text-sm font-semibold text-teal-900">{{ __('profile.body_section') }}</h2>
                <p class="mt-0.5 text-xs text-teal-800/80">{{ __('profile.body_hint') }}</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('profile.weight_kg') }}</label>
                        <input type="number" name="weight_kg" step="0.1" min="20" max="300"
                               value="{{ old('weight_kg', $profile?->weight_kg) }}"
                               class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm"
                               placeholder="60">
                        @error('weight_kg') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('profile.height_cm') }}</label>
                        <input type="number" name="height_cm" min="80" max="250"
                               value="{{ old('height_cm', $profile?->height_cm) }}"
                               class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm"
                               placeholder="165">
                        @error('height_cm') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('profile.gender') }}</label>
                        <select name="gender" class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm">
                            <option value="">—</option>
                            @foreach (\App\Enums\Gender::cases() as $g)
                                <option value="{{ $g->value }}" @selected(old('gender', $profile?->gender?->value) === $g->value)>
                                    {{ $g->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('profile.birth_year') }}</label>
                        <input type="number" name="birth_year" min="1920" max="{{ now()->year - 10 }}"
                               value="{{ old('birth_year', $profile?->birth_year) }}"
                               class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm"
                               placeholder="1995">
                        @error('birth_year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('profile.activity_level') }}</label>
                        <select name="activity_level" class="w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm">
                            <option value="">—</option>
                            @foreach (\App\Enums\ActivityLevel::cases() as $a)
                                <option value="{{ $a->value }}" @selected(old('activity_level', $profile?->activity_level?->value) === $a->value)>
                                    {{ $a->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($profile?->weight_kg)
                    <p class="mt-3 text-xs font-medium text-teal-900">
                        {{ __('profile.estimated_daily_kcal', ['kcal' => $calorieEstimate['kcal']]) }}
                        <span class="font-normal text-teal-800/80">
                            ({{ $calorieEstimate['source'] === 'mifflin'
                                ? __('profile.estimated_from_mifflin')
                                : __('profile.estimated_from_weight') }})
                        </span>
                    </p>
                @endif
            </section>

            <div>
                <label class="mb-2 block text-sm font-medium">Tính cách</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($personalities as $trait)
                        <label class="inline-flex items-center gap-1 rounded-full border border-stone-200 px-3 py-1 text-sm">
                            <input type="checkbox" name="personality[]" value="{{ $trait->slug }}"
                                   @checked(in_array($trait->slug, old('personality', $profile?->personality ?? []), true))>
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
                                   @checked(in_array($trait->slug, old('interests', $profile?->interests ?? []), true))>
                            {{ $trait->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_matchable" value="0">
                <input type="checkbox" name="is_matchable" value="1" class="rounded border-stone-300 text-teal-600"
                       @checked(old('is_matchable', $profile?->is_matchable ?? true))>
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
