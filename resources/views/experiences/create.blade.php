@extends('layouts.app')

@section('title', 'Đăng trải nghiệm — ViVu')

@php
    $inputClass = 'w-full rounded-xl border border-stone-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200';
    $mapsKey = config('services.google.maps_key');
    $formConfig = [
        'address' => old('address', ''),
        'latitude' => old('latitude', '16.0544'),
        'longitude' => old('longitude', '108.2022'),
        'placeId' => old('google_place_id', ''),
        'placeName' => old('place_name', ''),
        'categoryId' => old('category_id', ''),
        'title' => old('title', ''),
        'content' => old('content', ''),
        'authorRating' => old('author_rating'),
        'selectedTags' => array_map('intval', (array) old('tags', [])),
        'customTags' => array_values(array_filter((array) old('new_tags', []))),
        'status' => old('status', 'published'),
        'tags' => $tags->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'category_id' => $t->category_id,
            'status' => $t->status?->value ?? $t->status,
        ])->values(),
        'hasMaps' => (bool) $mapsKey,
        'enableImages' => true,
    ];
@endphp

@section('content')
    <div class="mx-auto max-w-5xl">
        <header class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl">Đăng trải nghiệm</h1>
                <p class="mt-0.5 text-xs text-stone-500 sm:text-sm">Nhanh gọn — chia sẻ địa điểm bạn thích</p>
            </div>
        </header>

        <form method="POST"
              action="{{ route('experiences.store') }}"
              enctype="multipart/form-data"
              x-data="experienceForm(@js($formConfig))"
              @submit="onSubmit($event)">
            @csrf

            <div class="grid gap-4 lg:grid-cols-2 lg:gap-5">
                {{-- Cột trái: nội dung --}}
                <div class="space-y-4">
                    <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <label for="title" class="text-sm font-medium">Tiêu đề <span class="text-red-500">*</span></label>
                                <span class="text-[11px] text-stone-400" x-text="`${title.length}/180`"></span>
                            </div>
                            <input id="title" type="text" name="title" x-model="title" required maxlength="180" autofocus
                                   placeholder="Bún chả ngon nhất quận 1"
                                   class="{{ $inputClass }} @error('title') border-red-400 @enderror">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Danh mục <span class="text-red-500">*</span></label>
                            <input type="hidden" name="category_id" :value="categoryId">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($categories as $category)
                                    <button type="button"
                                            @click="selectCategory({{ $category->id }})"
                                            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium transition sm:text-sm"
                                            :class="String(categoryId) === '{{ $category->id }}'
                                                ? 'border-teal-400 bg-teal-50 text-teal-800 shadow-sm'
                                                : 'border-stone-200 bg-white text-stone-600 hover:border-stone-300'">
                                        <span>{{ $category->icon }}</span>
                                        <span>{{ $category->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('category_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="content" class="mb-1 block text-sm font-medium">Nội dung</label>
                            <textarea id="content" name="content" x-model="content" rows="3"
                                      placeholder="Món ngon, không gian, mẹo nhỏ…"
                                      class="{{ $inputClass }} resize-y"></textarea>
                        </div>

                        {{-- Đánh giá 5 sao, từng nửa sao (lưu 1–10) --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Đánh giá của bạn</label>
                            <input type="hidden" name="author_rating" :value="authorRating ?? ''">
                            <div class="flex items-center gap-0.5 sm:gap-1"
                                 role="group"
                                 aria-label="Đánh giá từ nửa sao đến 5 sao"
                                 @mouseleave="hoverRating = 0">
                                <template x-for="star in 5" :key="star">
                                    <div class="relative h-8 w-8 sm:h-9 sm:w-9"
                                         :class="ratingPulse === star * 2 || ratingPulse === star * 2 - 1 ? 'rate-pop' : ''">
                                        {{-- Nền sao xám --}}
                                        <svg class="pointer-events-none absolute inset-0 h-full w-full fill-stone-200 text-stone-200" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                                        </svg>
                                        {{-- Sao vàng: full hoặc nửa (clip) --}}
                                        <svg class="pointer-events-none absolute inset-0 h-full w-full fill-amber-400 text-amber-400 drop-shadow-sm transition-opacity duration-150"
                                             viewBox="0 0 24 24"
                                             aria-hidden="true"
                                             :class="starFill(star) === 'empty' ? 'opacity-0' : 'opacity-100'"
                                             :style="starFill(star) === 'half' ? 'clip-path: inset(0 50% 0 0)' : ''">
                                            <path d="M12 2.5l2.9 5.88 6.49.94-4.7 4.58 1.11 6.47L12 17.77l-5.8 3.05 1.11-6.47-4.7-4.58 6.49-.94L12 2.5z"/>
                                        </svg>
                                        {{-- Nửa trái = ½ sao --}}
                                        <button type="button"
                                                class="absolute inset-y-0 left-0 z-10 w-1/2 rounded-l-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                                @mouseenter="hoverRating = star * 2 - 1"
                                                @click="setRating(star * 2 - 1)"
                                                :aria-label="`${star - 0.5} sao`"
                                                :title="`${star - 0.5} sao`"></button>
                                        {{-- Nửa phải = full sao đó --}}
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

                        {{-- Thẻ --}}
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

                            <div class="relative">
                                <input type="search"
                                       x-model="tagQuery"
                                       @keydown.enter.prevent="addCustomTag()"
                                       :disabled="!categoryId"
                                       placeholder="Tìm thẻ hoặc Enter để thêm mới…"
                                       class="{{ $inputClass }} pr-9 disabled:bg-stone-50 disabled:text-stone-400">
                                <button type="button"
                                        x-show="canCreateTag"
                                        x-cloak
                                        @click="addCustomTag()"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg bg-teal-600 px-2 py-1 text-[11px] font-semibold text-white hover:bg-teal-700">
                                    Thêm
                                </button>
                            </div>

                            <p x-show="!categoryId" class="mt-2 text-xs text-stone-400">Chọn danh mục để gắn thẻ</p>

                            {{-- Đã chọn --}}
                            <div class="mt-2 flex flex-wrap gap-1.5" x-show="selectedCount > 0" x-cloak>
                                <template x-for="tagId in selectedTags" :key="'chip-'+tagId">
                                    <button type="button"
                                            @click="toggleTag(tagId)"
                                            class="inline-flex items-center gap-1 rounded-full border border-teal-300 bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-800">
                                        <span x-text="tagName(tagId)"></span>
                                        <span x-show="isPendingTag(tagId)" class="rounded bg-amber-100 px-1 text-[10px] text-amber-800">chờ</span>
                                        <span class="text-teal-500">×</span>
                                    </button>
                                </template>
                                <template x-for="name in customTags" :key="'cchip-'+name">
                                    <button type="button"
                                            @click="removeCustomTag(name)"
                                            class="inline-flex items-center gap-1 rounded-full border border-dashed border-amber-300 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-900">
                                        <span x-text="name"></span>
                                        <span class="rounded bg-amber-100 px-1 text-[10px]">chờ duyệt</span>
                                        <span>×</span>
                                    </button>
                                </template>
                            </div>

                            {{-- Gợi ý --}}
                            <div class="mt-2 flex flex-wrap gap-1.5" x-show="categoryId" x-cloak>
                                <template x-for="tag in filteredTags" :key="tag.id">
                                    <button type="button"
                                            @click="toggleTag(tag.id)"
                                            class="rounded-full border px-2.5 py-0.5 text-xs transition"
                                            :class="selectedTags.includes(tag.id)
                                                ? 'border-teal-400 bg-teal-50 text-teal-800'
                                                : 'border-stone-200 text-stone-600 hover:border-stone-300'"
                                            x-show="!selectedTags.includes(tag.id)">
                                        <span x-text="tag.name"></span>
                                        <span x-show="tag.status === 'pending'" class="text-amber-600">·</span>
                                    </button>
                                </template>
                                <p x-show="filteredTags.length === 0 && tagQuery && canCreateTag"
                                   class="text-xs text-stone-500">
                                    Enter hoặc bấm <strong>Thêm</strong> để tạo thẻ tạm
                                </p>
                            </div>
                            <p class="mt-1.5 text-[11px] text-stone-400">Thẻ mới chỉ bạn thấy đến khi admin duyệt.</p>
                        </div>
                    </section>
                </div>

                {{-- Cột phải: địa điểm + ảnh + CTA --}}
                <div class="space-y-4">
                    <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-stone-900">Địa điểm</h2>
                            <button type="button" @click="useMyLocation" :disabled="locating"
                                    class="text-xs font-medium text-teal-700 hover:underline disabled:opacity-50">
                                <span x-text="locating ? 'Đang lấy…' : 'Vị trí của tôi'"></span>
                            </button>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <input type="text" name="place_name" x-model="placeName" maxlength="180"
                                   placeholder="Tên quán / điểm đến" class="{{ $inputClass }}">
                            <input type="text" name="address" x-model="address" maxlength="255"
                                   x-ref="addressInput" autocomplete="off"
                                   placeholder="Gõ địa chỉ…" class="{{ $inputClass }}">
                        </div>

                        <div class="relative overflow-hidden rounded-xl border border-stone-200 bg-stone-100">
                            <div id="experience-map" class="h-36 w-full sm:h-40" role="img" aria-label="Bản đồ"></div>
                            <div x-show="hasMaps && !mapReady" x-cloak
                                 class="absolute inset-0 flex items-center justify-center bg-stone-100/90 text-xs text-stone-500">
                                Đang tải bản đồ…
                            </div>
                            @unless ($mapsKey)
                                <div class="absolute inset-0 flex items-center justify-center bg-stone-100 px-3 text-center text-xs text-stone-500">
                                    Chưa có Maps key — mở toạ độ bên dưới
                                </div>
                            @endunless
                        </div>
                        <p x-show="mapHint" x-text="mapHint" x-cloak class="text-[11px] text-amber-700"></p>
                        <p x-show="locationError" x-text="locationError" x-cloak class="text-[11px] text-red-600"></p>

                        <details class="text-xs text-stone-500">
                            <summary class="cursor-pointer select-none font-medium text-stone-600">Toạ độ thủ công</summary>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <input type="number" step="any" name="latitude" x-model="latitude"
                                       class="{{ $inputClass }}" @change="syncMarkerFromInputs" placeholder="Lat">
                                <input type="number" step="any" name="longitude" x-model="longitude"
                                       class="{{ $inputClass }}" @change="syncMarkerFromInputs" placeholder="Lng">
                            </div>
                        </details>
                        <input type="hidden" name="google_place_id" :value="placeId">
                    </section>

                    <section class="space-y-2 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold">Ảnh</h2>
                            <span class="text-[11px] text-stone-400" x-text="previews.length ? `${previews.length}/10` : 'tuỳ chọn'"></span>
                        </div>
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-stone-300 bg-stone-50/80 px-3 py-4 text-center text-xs text-stone-600 transition hover:border-teal-400 hover:bg-teal-50/30">
                            <svg class="h-4 w-4 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            <span>Thêm ảnh (tối đa 5MB/ảnh)</span>
                            <input type="file" name="images[]" accept="image/*" multiple class="sr-only"
                                   x-ref="fileInput" @change="onImagesChange">
                        </label>
                        <div class="grid grid-cols-4 gap-1.5" x-show="previews.length" x-cloak>
                            <template x-for="(preview, index) in previews" :key="preview.url">
                                <div class="group relative overflow-hidden rounded-lg border border-stone-200">
                                    <img :src="preview.url" :alt="preview.name" class="aspect-square w-full object-cover">
                                    <button type="button" @click="removePreview(index)"
                                            class="absolute right-0.5 top-0.5 rounded bg-black/50 px-1 text-[10px] text-white">×</button>
                                    <button type="button" @click="coverIndex = index"
                                            class="absolute inset-x-0 bottom-0 bg-black/45 py-0.5 text-center text-[9px] text-white"
                                            x-text="coverIndex === index ? 'Bìa' : 'Bìa?'"></button>
                                </div>
                            </template>
                        </div>
                        <input type="hidden" name="cover_index" :value="coverIndex">
                    </section>

                    <section class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <button type="submit" :disabled="submitting" @click="status = 'draft'"
                                    class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50 disabled:opacity-60">
                                Lưu cá nhân
                            </button>
                            <button type="submit" :disabled="submitting" @click="status = 'published'"
                                    class="rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/20 hover:bg-teal-700 disabled:opacity-60">
                                <span x-show="!submitting">Đăng công khai</span>
                                <span x-show="submitting" x-cloak>Đang gửi…</span>
                            </button>
                        </div>
                        <input type="hidden" name="status" :value="status">
                        <p x-show="clientError" x-text="clientError" x-cloak class="mt-2 text-center text-sm text-red-600 sm:text-right"></p>
                    </section>
                </div>
            </div>
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
