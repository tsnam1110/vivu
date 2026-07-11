@extends('layouts.app')

@section('title', $experience->title.' — ViVu')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $experience->content), 160))

@push('meta')
    <meta property="og:title" content="{{ $experience->title }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $experience->content), 160) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @php $cover = $experience->media->firstWhere('is_cover', true) ?? $experience->media->first(); @endphp
    @if ($cover)
        <meta property="og:image" content="{{ $cover->url() }}">
    @endif
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Place',
            'name' => $experience->place_name ?: $experience->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $experience->content), 200),
            'address' => $experience->address,
            'geo' => $experience->latitude ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $experience->latitude,
                'longitude' => $experience->longitude,
            ] : null,
            'aggregateRating' => $experience->rating_count > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $experience->rating_avg,
                'reviewCount' => $experience->rating_count,
            ] : null,
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <article class="mx-auto max-w-3xl">
        {{-- Header card --}}
        <div class="rounded-3xl border border-stone-200/80 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    @if ($experience->category)
                        <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-800 ring-1 ring-teal-100">
                            {{ $experience->category->icon }} {{ $experience->category->name }}
                        </span>
                    @endif
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">{{ $experience->title }}</h1>
                    @if ($experience->place_name || $experience->address)
                        <p class="mt-2 text-sm text-stone-600 sm:text-[15px]">
                            <span class="text-stone-400">📍</span>
                            {{ $experience->place_name }}
                            @if ($experience->place_name && $experience->address)
                                <span class="text-stone-300">·</span>
                            @endif
                            {{ $experience->address }}
                        </p>
                    @endif
                    <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-stone-500">
                        <a href="{{ route('profile.show', $experience->user->username) }}" class="inline-flex items-center gap-2 font-medium text-stone-700 hover:text-teal-700">
                            <x-user-avatar :user="$experience->user" size="sm" />
                            {{ $experience->user->name }}
                        </a>
                        @if ($experience->author_rating)
                            <span class="text-stone-300">·</span>
                            <x-star-rating :value="$experience->author_rating" size="md" />
                        @endif
                        @if ($experience->rating_count > 0)
                            <span class="text-stone-300">·</span>
                            <span>Cộng đồng {{ number_format((float) $experience->rating_avg, 1) }}</span>
                        @endif
                        <span class="text-stone-300">·</span>
                        <span><span class="text-rose-400">♥</span> <span id="reaction-count">{{ $experience->reaction_count }}</span></span>
                        <span class="text-stone-300">·</span>
                        <span>{{ number_format($experience->view_count) }} lượt xem</span>
                    </div>
                    @if ($experience->tags->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($experience->tags as $tag)
                                <span class="inline-flex items-center rounded-full border border-stone-200 bg-stone-50 px-2.5 py-0.5 text-xs text-stone-600">
                                    #{{ $tag->name }}
                                    @if ($tag->isPending())
                                        <span class="ml-1 text-[10px] text-amber-700">chờ</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 border-t border-stone-100 pt-4">
                @auth('web')
                    @if (auth('web')->id() === $experience->user_id)
                        <a href="{{ route('experiences.edit', $experience) }}"
                           class="rounded-xl border border-stone-200 bg-white px-3.5 py-2 text-sm font-medium text-stone-700 transition hover:border-teal-300 hover:bg-teal-50/50">
                            Sửa
                        </a>
                    @endif
                    <div x-data="reactions({{ $experience->id }})" class="flex gap-2">
                        <button type="button" @click="react('like')"
                                class="rounded-xl border border-stone-200 px-3.5 py-2 text-sm transition hover:bg-stone-50"
                                :class="type === 'like' && 'border-teal-400 bg-teal-50 text-teal-800'">
                            👍 Like
                        </button>
                        <button type="button" @click="react('love')"
                                class="rounded-xl border border-stone-200 px-3.5 py-2 text-sm transition hover:bg-stone-50"
                                :class="type === 'love' && 'border-rose-400 bg-rose-50 text-rose-800'">
                            ❤️ Tim
                        </button>
                    </div>
                @endauth
                <button type="button"
                        onclick="navigator.share ? navigator.share({title: @js($experience->title), url: location.href}) : navigator.clipboard.writeText(location.href)"
                        class="rounded-xl border border-stone-200 px-3.5 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-50">
                    Chia sẻ
                </button>
                @if ($experience->latitude && $experience->longitude)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $experience->latitude }},{{ $experience->longitude }}"
                       target="_blank" rel="noopener"
                       class="rounded-xl border border-stone-200 px-3.5 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-50">
                        Chỉ đường
                    </a>
                @endif
            </div>
        </div>

        @if ($experience->media->isNotEmpty())
            <div class="mt-5 grid gap-2.5 sm:grid-cols-2">
                @foreach ($experience->media as $media)
                    <div class="overflow-hidden rounded-2xl {{ $media->is_cover ? 'sm:col-span-2' : '' }}">
                        <img src="{{ $media->url() }}" alt=""
                             class="w-full object-cover {{ $media->is_cover ? 'max-h-[28rem]' : 'h-48' }}">
                    </div>
                @endforeach
            </div>
        @endif

        @if ($experience->content)
            <div class="mt-5 rounded-3xl border border-stone-200/80 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-stone-400">Nội dung</h2>
                <div class="mt-3 whitespace-pre-wrap text-[15px] leading-relaxed text-stone-700">{{ $experience->content }}</div>
            </div>
        @endif

        @if ($experience->latitude && $experience->longitude)
            <div class="mt-5 overflow-hidden rounded-3xl border border-stone-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-stone-100 px-5 py-3">
                    <h2 class="text-sm font-semibold text-stone-800">Vị trí</h2>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $experience->latitude }},{{ $experience->longitude }}"
                       target="_blank" rel="noopener"
                       class="text-xs font-medium text-teal-700 hover:underline">Mở Google Maps</a>
                </div>
                <div id="map" class="h-64 w-full bg-stone-100 sm:h-72"
                     data-lat="{{ $experience->latitude }}"
                     data-lng="{{ $experience->longitude }}"
                     data-title="{{ $experience->place_name ?? $experience->title }}"></div>
                @if (config('services.google.maps_key'))
                    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initExperienceMap" async defer></script>
                @else
                    <p class="px-5 py-3 text-sm text-stone-500">
                        Toạ độ: {{ $experience->latitude }}, {{ $experience->longitude }}
                    </p>
                @endif
            </div>
        @endif
    </article>

    <section class="mx-auto mt-10 max-w-3xl" x-data="comments({{ $experience->id }})">
        <div class="flex items-end justify-between gap-2">
            <h2 class="text-xl font-bold tracking-tight">Bình luận</h2>
            <span class="text-xs text-stone-400">{{ $comments->total() }} bình luận</span>
        </div>

        @auth('web')
            <form @submit.prevent="submit" class="mt-4 space-y-3 rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm sm:p-5">
                <textarea x-model="body" rows="3" required placeholder="Chia sẻ cảm nhận của bạn…"
                          class="vivu-input resize-y"></textarea>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label class="flex items-center gap-2 text-sm text-stone-600">
                        <span>Sao</span>
                        <select x-model="rating" class="rounded-lg border border-stone-300 px-2 py-1.5 text-sm focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                            <option value="">Không chấm</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }} ★</option>
                            @endfor
                        </select>
                    </label>
                    <button type="submit" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-700">
                        Gửi
                    </button>
                </div>
                <p x-show="error" x-text="error" x-cloak class="text-sm text-red-600"></p>
            </form>
        @else
            <p class="mt-4 rounded-2xl border border-dashed border-stone-200 bg-white/80 px-4 py-5 text-center text-sm text-stone-500">
                <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Đăng nhập</a> để bình luận.
            </p>
        @endauth

        <div class="mt-5 space-y-3">
            @forelse ($comments as $comment)
                <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('profile.show', $comment->user->username) }}" class="font-medium text-stone-800 hover:text-teal-700">
                            {{ $comment->user->name }}
                        </a>
                        @if ($comment->rating)
                            <x-star-rating :value="$comment->rating" scale="5" />
                        @endif
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-stone-700">{{ $comment->body }}</p>
                    @foreach ($comment->replies as $reply)
                        <div class="mt-3 ml-3 rounded-xl border border-stone-100 bg-stone-50 p-3 sm:ml-4">
                            <a href="{{ route('profile.show', $reply->user->username) }}" class="text-sm font-medium text-teal-700">{{ $reply->user->name }}</a>
                            <p class="mt-1 text-sm text-stone-700">{{ $reply->body }}</p>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="py-6 text-center text-sm text-stone-400">Chưa có bình luận nào.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $comments->links() }}</div>
    </section>
@endsection

@push('scripts')
<script>
function initExperienceMap() {
    const el = document.getElementById('map');
    if (!el || !window.google) return;
    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);
    const map = new google.maps.Map(el, { center: { lat, lng }, zoom: 15 });
    new google.maps.Marker({ position: { lat, lng }, map, title: el.dataset.title });
}
document.addEventListener('alpine:init', () => {
    Alpine.data('reactions', (experienceId) => ({
        type: null,
        async react(type) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`/api/experiences/${experienceId}/reactions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ type }),
            });
            if (!res.ok) return;
            const json = await res.json();
            this.type = json.data.type;
            const countEl = document.getElementById('reaction-count');
            if (countEl) countEl.textContent = json.data.total;
        }
    }));
    Alpine.data('comments', (experienceId) => ({
        body: '',
        rating: '',
        error: '',
        async submit() {
            this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(`/api/experiences/${experienceId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    body: this.body,
                    rating: this.rating ? Number(this.rating) : null,
                }),
            });
            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                this.error = json.message || 'Không gửi được bình luận.';
                return;
            }
            location.reload();
        }
    }));
});
</script>
@endpush
