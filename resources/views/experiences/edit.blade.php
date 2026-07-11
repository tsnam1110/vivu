@extends('layouts.app')

@section('title', 'Sửa trải nghiệm — ViVu')

@section('content')
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold">Sửa trải nghiệm</h1>
        <form method="POST" action="{{ route('experiences.update', $experience) }}" class="mt-6 space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium">Tiêu đề *</label>
                <input type="text" name="title" value="{{ old('title', $experience->title) }}" required class="w-full rounded-xl border border-stone-300 px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Danh mục *</label>
                <select name="category_id" required class="w-full rounded-xl border border-stone-300 px-3 py-2">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $experience->category_id) == $category->id)>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Nội dung</label>
                <textarea name="content" rows="6" class="w-full rounded-xl border border-stone-300 px-3 py-2">{{ old('content', $experience->content) }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Tên địa điểm</label>
                    <input type="text" name="place_name" value="{{ old('place_name', $experience->place_name) }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Địa chỉ</label>
                    <input type="text" name="address" value="{{ old('address', $experience->address) }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Latitude</label>
                    <input type="number" step="any" name="latitude" value="{{ old('latitude', $experience->latitude) }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Longitude</label>
                    <input type="number" step="any" name="longitude" value="{{ old('longitude', $experience->longitude) }}" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Trạng thái</label>
                <select name="status" class="w-full rounded-xl border border-stone-300 px-3 py-2">
                    @foreach (['draft' => 'Nháp', 'published' => 'Công khai', 'hidden' => 'Ẩn'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $experience->status->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Thẻ</label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <label class="inline-flex items-center gap-1 rounded-full border border-stone-200 px-3 py-1 text-sm">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   @checked(collect(old('tags', $experience->tags->pluck('id')))->contains($tag->id))>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3">
                <button class="rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">Lưu</button>
                <a href="{{ route('experiences.show', $experience->slug) }}" class="rounded-xl border border-stone-300 px-5 py-2.5">Huỷ</a>
            </div>
        </form>
        <form method="POST" action="{{ route('experiences.destroy', $experience) }}" class="mt-4" onsubmit="return confirm('Xoá trải nghiệm này?')">
            @csrf
            @method('DELETE')
            <button class="text-sm text-red-600 hover:underline">Xoá trải nghiệm</button>
        </form>
    </div>
@endsection
