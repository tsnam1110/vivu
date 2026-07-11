@props([
    'experience',
    'showStatus' => false,
    'showFooter' => false,
    'showMeta' => true,
])

@php
    $status = $experience->status;
    $statusValue = $status?->value ?? (string) $status;
    $badge = match ($statusValue) {
        'published' => 'bg-teal-50 text-teal-800 ring-1 ring-teal-100',
        'pending' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-100',
        'draft' => 'bg-stone-100 text-stone-600 ring-1 ring-stone-200',
        'hidden' => 'bg-red-50 text-red-700 ring-1 ring-red-100',
        default => 'bg-stone-100 text-stone-600 ring-1 ring-stone-200',
    };
    $statusLabel = match ($statusValue) {
        'published' => 'Công khai',
        'pending' => 'Chờ duyệt',
        'draft' => 'Nháp',
        'hidden' => 'Đã ẩn',
        default => (string) $status,
    };
    $cover = $experience->media->firstWhere('is_cover', true) ?? $experience->media->first();
@endphp

<article {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md']) }}>
    <a href="{{ route('experiences.show', $experience->slug) }}" class="flex min-h-0 flex-1 flex-col">
        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-stone-100 to-stone-200">
            @if ($cover)
                <img src="{{ $cover->url() }}"
                     alt="{{ $experience->title }}"
                     class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                     loading="lazy">
            @else
                <div class="flex h-full flex-col items-center justify-center gap-1 text-stone-400">
                    <span class="text-4xl opacity-90">{{ $experience->category?->icon ?? '📍' }}</span>
                    <span class="text-[11px] font-medium">Chưa có ảnh</span>
                </div>
            @endif
            @if ($showStatus)
                <span class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-[11px] font-medium shadow-sm backdrop-blur {{ $badge }}">
                    {{ $statusLabel }}
                </span>
            @endif
            @if ($experience->category)
                <span class="absolute left-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-medium text-teal-800 shadow-sm backdrop-blur">
                    {{ $experience->category->icon }} {{ $experience->category->name }}
                </span>
            @endif
        </div>
        <div class="flex flex-1 flex-col p-4">
            <h3 class="line-clamp-2 text-[15px] font-semibold leading-snug text-stone-900 group-hover:text-teal-700">
                {{ $experience->title }}
            </h3>
            @if ($experience->place_name || $experience->address)
                <p class="mt-1.5 line-clamp-1 text-sm text-stone-500">
                    <span class="text-stone-400">📍</span>
                    {{ $experience->place_name ?? $experience->address }}
                </p>
            @endif
            @if ($showMeta)
                <div class="mt-auto flex items-center justify-between gap-2 pt-3 text-xs text-stone-500">
                    <span class="inline-flex items-center gap-1">
                        @if ($experience->author_rating)
                            <x-star-rating :value="$experience->author_rating" />
                        @elseif ($experience->rating_count > 0)
                            <span class="text-amber-500">★</span>
                            {{ number_format((float) $experience->rating_avg, 1) }}
                            <span class="text-stone-400">({{ $experience->rating_count }})</span>
                        @else
                            <span class="text-stone-400">Chưa có đánh giá</span>
                        @endif
                    </span>
                    <span class="inline-flex items-center gap-1 text-stone-400">
                        <span class="text-rose-400">♥</span> {{ $experience->reaction_count }}
                    </span>
                </div>
            @endif
        </div>
    </a>
    @if ($showFooter)
        <div class="flex items-center border-t border-stone-100 px-4 py-2.5">
            <a href="{{ route('experiences.edit', $experience) }}"
               class="text-xs font-medium text-stone-500 transition hover:text-teal-700">
                Chỉnh sửa
            </a>
        </div>
    @endif
</article>
