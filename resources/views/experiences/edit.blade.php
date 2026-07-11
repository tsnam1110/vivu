@extends('layouts.app')

@section('title', 'Sửa trải nghiệm — ViVu')

@php
    $inputClass = 'w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200';
    $mapsKey = config('services.google.maps_key');
    $formConfig = [
        'address' => old('address', $experience->address ?? ''),
        'latitude' => old('latitude', $experience->latitude ?? '16.0544'),
        'longitude' => old('longitude', $experience->longitude ?? '108.2022'),
        'placeId' => old('google_place_id', $experience->google_place_id ?? ''),
        'placeName' => old('place_name', $experience->place_name ?? ''),
        'categoryId' => old('category_id', $experience->category_id),
        'title' => old('title', $experience->title),
        'content' => old('content', $experience->content ?? ''),
        'authorRating' => old('author_rating', $experience->author_rating),
        'selectedTags' => array_map('intval', (array) old('tags', $experience->tags->pluck('id')->all())),
        'customTags' => array_values(array_filter((array) old('new_tags', []))),
        'status' => old('status', $experience->status->value),
        'tags' => $tags->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'category_id' => $t->category_id,
            'status' => $t->status?->value ?? $t->status,
        ])->values(),
        'hasMaps' => (bool) $mapsKey,
        'enableImages' => false,
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-5xl">
        <header class="mb-4">
            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">Sửa trải nghiệm</h1>
            <p class="mt-0.5 text-xs text-stone-500">Cập nhật nội dung, vị trí và thẻ</p>
        </header>

        <form method="POST"
              action="{{ route('experiences.update', $experience) }}"
              x-data="experienceForm(@js($formConfig))"
              @submit="onSubmit($event)">
            @csrf
            @method('PUT')

            <div class="grid gap-4 lg:grid-cols-2 lg:gap-5">
                <div class="space-y-4">
                    <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div>
                            <label for="title" class="mb-1 block text-sm font-medium">Tiêu đề *</label>
                            <input id="title" type="text" name="title" x-model="title" required maxlength="180" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Danh mục *</label>
                            <input type="hidden" name="category_id" :value="categoryId">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($categories as $category)
                                    <button type="button"
                                            @click="selectCategory({{ $category->id }})"
                                            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition"
                                            :class="String(categoryId) === '{{ $category->id }}'
                                                ? 'border-teal-400 bg-teal-50 text-teal-800'
                                                : 'border-stone-200 bg-white text-stone-600 hover:border-stone-300'">
                                        {{ $category->icon }} {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label for="content" class="mb-1 block text-sm font-medium">Nội dung</label>
                            <textarea id="content" name="content" x-model="content" rows="3" class="{{ $inputClass }} resize-y"></textarea>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Đánh giá của bạn</label>
                            <input type="hidden" name="author_rating" :value="authorRating ?? ''">
                            <div class="flex items-center gap-0.5 sm:gap-1"
                                 role="group" aria-label="Đánh giá từ nửa sao đến 5 sao"
                                 @mouseleave="hoverRating = 0">
                                <template x-for="star in 5" :key="star">
                                    <div class="relative h-8 w-8 sm:h-9 sm:w-9"
                                         :class="ratingPulse === star * 2 || ratingPulse === star * 2 - 1 ? 'rate-pop' : ''">
                                        <svg class="pointer-events-none absolute inset-0 h-full w-full fill-stone-200 text-stone-200" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                                        </svg>
                                        <svg class="pointer-events-none absolute inset-0 h-full w-full fill-amber-400 text-amber-400 drop-shadow-sm transition-opacity duration-150"
                                             viewBox="0 0 24 24" aria-hidden="true"
                                             :class="starFill(star) === 'empty' ? 'opacity-0' : 'opacity-100'"
                                             :style="starFill(star) === 'half' ? 'clip-path: inset(0 50% 0 0)' : ''">
                                            <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                                        </svg>
                                        <button type="button"
                                                class="absolute inset-y-0 left-0 z-10 w-1/2 rounded-l-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                                @mouseenter="hoverRating = star * 2 - 1"
                                                @click="setRating(star * 2 - 1)"
                                                :aria-label="`${star - 0.5} sao`"
                                                :title="`${star - 0.5} sao`"></button>
                                        <button type="button"
                                                class="absolute inset-y-0 right-0 z-10 w-1/2 rounded-r-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                                @mouseenter="hoverRating = star * 2"
                                                @click="setRating(star * 2)"
                                                :aria-label="`${star} sao`"
                                                :title="`${star} sao`"></button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label class="text-sm font-medium">Thẻ</label>
                                <span class="text-[11px] text-stone-400" x-text="`${selectedCount}/10`"></span>
                            </div>
                            <template x-for="tagId in selectedTags" :key="'sel-'+tagId">
                                <input type="hidden" name="tags[]" :value="tagId">
                            </template>
                            <template x-for="name in customTags" :key="'new-'+name">
                                <input type="hidden" name="new_tags[]" :value="name">
                            </template>
                            <input type="search" x-model="tagQuery" @keydown.enter.prevent="addCustomTag()"
                                   placeholder="Tìm / thêm thẻ…" class="{{ $inputClass }}">
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <template x-for="tagId in selectedTags" :key="'chip-'+tagId">
                                    <button type="button" @click="toggleTag(tagId)"
                                            class="rounded-full border border-teal-300 bg-teal-50 px-2.5 py-0.5 text-xs text-teal-800">
                                        <span x-text="tagName(tagId)"></span> ×
                                    </button>
                                </template>
                                <template x-for="name in customTags" :key="'cchip-'+name">
                                    <button type="button" @click="removeCustomTag(name)"
                                            class="rounded-full border border-dashed border-amber-300 bg-amber-50 px-2.5 py-0.5 text-xs text-amber-900">
                                        <span x-text="name"></span> ×
                                    </button>
                                </template>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <template x-for="tag in filteredTags" :key="tag.id">
                                    <button type="button" @click="toggleTag(tag.id)"
                                            class="rounded-full border border-stone-200 px-2.5 py-0.5 text-xs text-stone-600"
                                            x-show="!selectedTags.includes(tag.id)" x-text="tag.name"></button>
                                </template>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-4">
                    <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold">Địa điểm</h2>
                            <button type="button" @click="useMyLocation" class="text-xs font-medium text-teal-700">Vị trí của tôi</button>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <input type="text" name="place_name" x-model="placeName" class="{{ $inputClass }}" placeholder="Tên địa điểm">
                            <input type="text" name="address" x-model="address" x-ref="addressInput" class="{{ $inputClass }}" placeholder="Địa chỉ">
                        </div>
                        <div class="relative overflow-hidden rounded-xl border border-stone-200 bg-stone-100">
                            <div id="experience-map" class="h-36 w-full sm:h-40"></div>
                        </div>
                        <details class="text-xs">
                            <summary class="cursor-pointer font-medium text-stone-600">Toạ độ</summary>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <input type="number" step="any" name="latitude" x-model="latitude" class="{{ $inputClass }}" @change="syncMarkerFromInputs">
                                <input type="number" step="any" name="longitude" x-model="longitude" class="{{ $inputClass }}" @change="syncMarkerFromInputs">
                            </div>
                        </details>
                        <input type="hidden" name="google_place_id" :value="placeId">
                    </section>

                    <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <label class="text-sm font-medium">Trạng thái</label>
                        <select name="status" x-model="status" class="{{ $inputClass }}">
                            @foreach (['draft' => 'Nháp', 'published' => 'Công khai', 'hidden' => 'Ẩn'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        @if ($experience->media->isNotEmpty())
                            <div class="grid grid-cols-4 gap-1.5">
                                @foreach ($experience->media as $media)
                                    <img src="{{ $media->url() }}" alt="" class="aspect-square rounded-lg object-cover">
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" :disabled="submitting"
                                    class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-60">
                                Lưu thay đổi
                            </button>
                            <a href="{{ route('experiences.show', $experience->slug) }}"
                               class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700">Huỷ</a>
                        </div>
                        <p x-show="clientError" x-text="clientError" x-cloak class="text-sm text-red-600"></p>
                    </section>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('experiences.destroy', $experience) }}" class="mt-4"
              onsubmit="return confirm('Xoá trải nghiệm này?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Xoá trải nghiệm</button>
        </form>
    </div>
@endsection

@if ($mapsKey)
    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&libraries=places&callback=onGoogleMapsReady&loading=async" async defer></script>
    @endpush
@endif

@push('scripts')
    @include('experiences.partials.form-alpine')
@endpush
