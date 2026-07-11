@extends('layouts.app')

@section('title', 'Đăng trải nghiệm — ViVu')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold">Đăng trải nghiệm</h1>
        <p class="mt-1 text-sm text-stone-500">Chia sẻ địa điểm bạn yêu thích với cộng đồng</p>

        <form method="POST" action="{{ route('experiences.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm"
              x-data="experienceForm()">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium">Tiêu đề *</label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="180"
                       class="w-full rounded-xl border border-stone-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Danh mục *</label>
                <select name="category_id" required class="w-full rounded-xl border border-stone-300 px-3 py-2">
                    <option value="">Chọn danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Nội dung</label>
                <textarea name="content" rows="6" class="w-full rounded-xl border border-stone-300 px-3 py-2">{{ old('content') }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Tên địa điểm</label>
                    <input type="text" name="place_name" value="{{ old('place_name') }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Địa chỉ</label>
                    <input type="text" name="address" x-model="address" value="{{ old('address') }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Vĩ độ (latitude) *</label>
                    <input type="number" step="any" name="latitude" x-model="latitude" value="{{ old('latitude', '16.0544') }}" required class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Kinh độ (longitude) *</label>
                    <input type="number" step="any" name="longitude" x-model="longitude" value="{{ old('longitude', '108.2022') }}" required class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
            </div>
            <input type="hidden" name="google_place_id" x-model="placeId">
            <div>
                <label class="mb-1 block text-sm font-medium">Thẻ</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <label class="inline-flex items-center gap-1 rounded-full border border-stone-200 px-3 py-1 text-sm">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(collect(old('tags'))->contains($tag->id))>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Ảnh</label>
                <input type="file" name="images[]" accept="image/*" multiple class="w-full text-sm">
            </div>
            <input type="hidden" name="status" value="published">
            <button class="rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">Đăng công khai</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('experienceForm', () => ({
        address: @js(old('address', '')),
        latitude: @js(old('latitude', '16.0544')),
        longitude: @js(old('longitude', '108.2022')),
        placeId: @js(old('google_place_id', '')),
    }));
});
</script>
@endpush
