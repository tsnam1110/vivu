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
    <article>
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-sm font-medium text-teal-700">{{ $experience->category?->icon }} {{ $experience->category?->name }}</div>
                <h1 class="mt-1 text-3xl font-bold">{{ $experience->title }}</h1>
                <p class="mt-2 text-stone-600">
                    {{ $experience->place_name }}
                    @if ($experience->address)
                        · {{ $experience->address }}
                    @endif
                </p>
                <p class="mt-1 text-sm text-stone-500">
                    bởi
                    <a href="{{ route('profile.show', $experience->user->username) }}" class="font-medium text-teal-700 hover:underline">
                        {{ $experience->user->name }}
                    </a>
                    · ★ {{ number_format((float) $experience->rating_avg, 1) }}
                    · ♥ <span id="reaction-count">{{ $experience->reaction_count }}</span>
                    · {{ $experience->view_count }} lượt xem
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @auth('web')
                    @if (auth('web')->id() === $experience->user_id)
                        <a href="{{ route('experiences.edit', $experience) }}" class="rounded-xl border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">Sửa</a>
                    @endif
                    <div x-data="reactions({{ $experience->id }})" class="flex gap-2">
                        <button type="button" @click="react('like')" class="rounded-xl border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50" :class="type === 'like' && 'border-teal-500 bg-teal-50'">👍 Like</button>
                        <button type="button" @click="react('love')" class="rounded-xl border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50" :class="type === 'love' && 'border-rose-500 bg-rose-50'">❤️ Tim</button>
                    </div>
                @endauth
                <button type="button"
                        onclick="navigator.share ? navigator.share({title: @js($experience->title), url: location.href}) : navigator.clipboard.writeText(location.href)"
                        class="rounded-xl border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">
                    Chia sẻ
                </button>
                @if ($experience->latitude && $experience->longitude)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $experience->latitude }},{{ $experience->longitude }}"
                       target="_blank" rel="noopener"
                       class="rounded-xl border border-stone-300 px-3 py-2 text-sm hover:bg-stone-50">Chỉ đường</a>
                @endif
            </div>
        </div>

        @if ($experience->media->isNotEmpty())
            <div class="mb-6 grid gap-3 sm:grid-cols-2">
                @foreach ($experience->media as $media)
                    <img src="{{ $media->url() }}" alt="" class="rounded-2xl object-cover {{ $media->is_cover ? 'sm:col-span-2 max-h-96 w-full' : 'h-48 w-full' }}">
                @endforeach
            </div>
        @endif

        <div class="prose prose-stone max-w-none whitespace-pre-wrap">{{ $experience->content }}</div>

        @if ($experience->tags->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($experience->tags as $tag)
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs text-stone-600">#{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($experience->latitude && $experience->longitude)
            <div class="mt-8">
                <h2 class="mb-3 text-lg font-semibold">Vị trí</h2>
                <div id="map" class="h-72 rounded-2xl border border-stone-200 bg-stone-100"
                     data-lat="{{ $experience->latitude }}"
                     data-lng="{{ $experience->longitude }}"
                     data-title="{{ $experience->place_name ?? $experience->title }}"></div>
                @if (config('services.google.maps_key'))
                    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&callback=initExperienceMap" async defer></script>
                @else
                    <p class="mt-2 text-sm text-stone-500">
                        Toạ độ: {{ $experience->latitude }}, {{ $experience->longitude }}
                        (cấu hình GOOGLE_MAPS_API_KEY để hiện bản đồ)
                    </p>
                @endif
            </div>
        @endif
    </article>

    <section class="mt-12 border-t border-stone-200 pt-8" x-data="comments({{ $experience->id }})">
        <h2 class="text-xl font-semibold">Bình luận & đánh giá</h2>

        @auth('web')
            <form @submit.prevent="submit" class="mt-4 space-y-3 rounded-2xl border border-stone-200 bg-white p-4">
                <textarea x-model="body" rows="3" required placeholder="Chia sẻ cảm nhận của bạn..."
                          class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200"></textarea>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-sm">Sao:
                        <select x-model="rating" class="ml-1 rounded-lg border border-stone-300 px-2 py-1">
                            <option value="">Không chấm</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </label>
                    <button type="submit" class="rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Gửi</button>
                </div>
                <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
            </form>
        @else
            <p class="mt-3 text-sm text-stone-500"><a href="{{ route('login') }}" class="text-teal-700 hover:underline">Đăng nhập</a> để bình luận.</p>
        @endauth

        <div class="mt-6 space-y-4">
            @foreach ($comments as $comment)
                <div class="rounded-2xl border border-stone-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('profile.show', $comment->user->username) }}" class="font-medium text-teal-700 hover:underline">
                            {{ $comment->user->name }}
                        </a>
                        @if ($comment->rating)
                            <span class="text-sm text-amber-600">★ {{ $comment->rating }}</span>
                        @endif
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-stone-700">{{ $comment->body }}</p>
                    @foreach ($comment->replies as $reply)
                        <div class="mt-3 ml-4 rounded-xl bg-stone-50 p-3">
                            <a href="{{ route('profile.show', $reply->user->username) }}" class="text-sm font-medium text-teal-700">{{ $reply->user->name }}</a>
                            <p class="mt-1 text-sm text-stone-700">{{ $reply->body }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
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
