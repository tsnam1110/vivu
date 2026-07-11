@extends('layouts.app')

@section('title', 'Đầu mục Habit — ViVu')

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <a href="{{ route('habits.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">← Bảng Habit</a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900">Đầu mục của tôi</h1>
            <p class="mt-1 max-w-2xl text-sm text-stone-500">
                Lần đầu mở Habit, hệ thống gắn sẵn các <strong>mẫu mặc định</strong>. Bạn có thể
                <strong>xoá</strong> bất kỳ mục nào bên phải — chỉ thuộc tài khoản bạn.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-start">
            {{-- Cột trái: thêm mới --}}
            <div class="space-y-6">
                <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-stone-900">Tự tạo đầu mục</h2>
                    <form method="POST" action="{{ route('habits.items.store') }}" class="mt-3 space-y-3">
                        @csrf
                        <input type="hidden" name="mode" value="custom">

                        <x-habit-icon-picker
                            name="icon"
                            :value="old('icon', '✨')"
                            :icons="$icons"
                            id="create-icon"
                        />
                        @error('icon') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                        <div>
                            <label for="create-name" class="mb-1 block text-xs font-medium text-stone-600">Tên đầu mục</label>
                            <input type="text" name="name" id="create-name" value="{{ old('name') }}" required maxlength="120"
                                   placeholder="Ví dụ: Dậy lúc 6h, Học tiếng Nhật…"
                                   class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/30">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="create-desc" class="mb-1 block text-xs font-medium text-stone-600">Mô tả (tuỳ chọn)</label>
                            <input type="text" name="description" id="create-desc" value="{{ old('description') }}" maxlength="500"
                                   placeholder="Ghi chú ngắn cho bạn"
                                   class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/30">
                        </div>

                        <button type="submit" class="vivu-btn-primary w-full justify-center sm:w-auto">Thêm đầu mục</button>
                    </form>
                </section>

                <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-stone-900">Mẫu gợi ý (admin)</h2>
                    <p class="mt-1 text-xs text-stone-400">Chọn mẫu → sao chép vào danh sách cá nhân. Sửa tên/icon sau vẫn được.</p>
                    @if ($templates->isEmpty())
                        <p class="mt-3 text-sm text-stone-500">Không còn mẫu mới (đã thêm hết hoặc admin chưa cấu hình).</p>
                    @else
                        <ul class="mt-3 max-h-[28rem] space-y-2 overflow-y-auto pr-0.5">
                            @foreach ($templates as $tpl)
                                <li class="flex items-center gap-3 rounded-2xl bg-stone-50 px-3 py-2.5 ring-1 ring-stone-200/70">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-lg ring-1 ring-stone-200">{{ $tpl->icon ?: '•' }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-sm font-medium text-stone-900">{{ $tpl->name }}</div>
                                        @if ($tpl->description)
                                            <div class="truncate text-[11px] text-stone-400">{{ $tpl->description }}</div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('habits.items.store') }}" class="shrink-0">
                                        @csrf
                                        <input type="hidden" name="mode" value="template">
                                        <input type="hidden" name="template_habit_item_id" value="{{ $tpl->id }}">
                                        <button type="submit" class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                                            Thêm
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            {{-- Cột phải: đang theo dõi --}}
            <div class="space-y-6 lg:sticky lg:top-20">
                <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-stone-900">Đang theo dõi ({{ $myItems->count() }})</h2>
                        @if ($canRestoreStarters ?? true)
                            <form method="POST" action="{{ route('habits.items.store') }}">
                                @csrf
                                <input type="hidden" name="mode" value="starters">
                                <button type="submit"
                                        class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800 transition hover:bg-teal-100"
                                        title="Thêm lại các mẫu mặc định còn thiếu">
                                    Thêm mẫu sẵn
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="mt-1 text-[11px] text-stone-400">Mẫu sẵn có thể xoá bằng nút Xoá — không ảnh hưởng catalog admin.</p>
                    @if ($myItems->isEmpty())
                        <p class="mt-3 text-sm text-stone-500">
                            Chưa có đầu mục. Bấm <strong>Thêm mẫu sẵn</strong> hoặc tự tạo ở cột bên trái.
                        </p>
                    @else
                        <ul class="mt-3 max-h-[min(70vh,40rem)] space-y-3 overflow-y-auto pr-0.5">
                            @foreach ($myItems as $item)
                                <li class="rounded-2xl border border-stone-100 bg-stone-50/50 p-3 {{ $item->is_active ? '' : 'opacity-60' }}">
                                    <form method="POST" action="{{ route('habits.items.update', $item) }}" class="space-y-3">
                                        @csrf
                                        @method('PUT')

                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <x-habit-icon-picker
                                                    name="icon"
                                                    :value="old('icon', $item->icon ?: '✨')"
                                                    :icons="$icons"
                                                    :id="'edit-icon-'.$item->id"
                                                />
                                            </div>
                                            @if ($item->isCustom())
                                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-semibold text-violet-700">Tuỳ chỉnh</span>
                                            @else
                                                <span class="rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-800 ring-1 ring-teal-100">Mẫu sẵn</span>
                                            @endif
                                        </div>

                                        <input type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="120"
                                               class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-sm font-medium outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/30">
                                        <input type="text" name="description" value="{{ old('description', $item->description) }}" maxlength="500"
                                               placeholder="Mô tả"
                                               class="w-full rounded-lg border border-stone-200 bg-white px-2 py-1.5 text-xs outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-500/30">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <label class="flex items-center gap-1.5 text-xs text-stone-600">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))
                                                       class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                                                Hiện trên bảng
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <button type="submit" class="rounded-full bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                                                    Lưu
                                                </button>
                                                <button type="submit"
                                                        form="destroy-habit-item-{{ $item->id }}"
                                                        class="rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                                        onclick="return confirm('Xoá đầu mục và toàn bộ ô/lịch sử liên quan?')">
                                                    Xoá
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <form id="destroy-habit-item-{{ $item->id }}"
                                          method="POST"
                                          action="{{ route('habits.items.destroy', $item) }}"
                                          class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection
