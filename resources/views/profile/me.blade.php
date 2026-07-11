@extends('layouts.app')

@section('title', 'Trang cá nhân — ViVu')

@section('content')
    @php
        $currentFrameId = old('avatar_frame_id', $user->avatar_frame_id);
        $currentSampleId = old('sample_avatar_id', $user->sample_avatar_id);
        $hasPremium = $user->hasActivePremium();
        $openAvatar = $errors->has('sample_avatar_id') || $errors->has('avatar');
        $openFrame = $errors->has('avatar_frame_id');
        $openPassword = $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation');
        $avatarUrl = $user->avatarUrl();
        $initials = $user->initials();
        $initialModal = $openPassword ? 'password' : ($openAvatar ? 'avatar' : ($openFrame ? 'frame' : null));
        $passwordErrorSummary = collect([
            $errors->first('current_password'),
            $errors->first('password'),
            $errors->first('password_confirmation'),
        ])->filter()->first();
    @endphp

    <div
        class="mx-auto max-w-2xl space-y-6"
        x-data="{
            modal: @js($initialModal),
            frameId: @js($currentFrameId),
            sampleId: @js($currentSampleId),
            mode: @js($user->avatar_path ? 'upload' : 'sample'),
            toast: @js(session('success') ? ['type' => 'success', 'message' => session('success')] : ($passwordErrorSummary ? ['type' => 'error', 'message' => $passwordErrorSummary] : (session('error') ? ['type' => 'error', 'message' => session('error')] : null))),
            open(name) { this.modal = name; document.body.classList.add('overflow-hidden'); },
            close() { this.modal = null; document.body.classList.remove('overflow-hidden'); },
            dismissToast() { this.toast = null; },
        }"
        x-init="
            if (modal) document.body.classList.add('overflow-hidden');
            if (toast) setTimeout(() => toast = null, 5000);
        "
        @keydown.escape.window="if (modal) close()"
    >
        <div>
            <p class="text-sm font-medium text-teal-700">Profile</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight md:text-3xl">Trang cá nhân</h1>
            <p class="mt-1 text-sm text-stone-500">Ảnh đại diện, khung và thông tin hiển thị công khai.</p>
        </div>

        {{-- Toast thành công / thất bại --}}
        <div
            x-show="toast"
            x-cloak
            x-transition.opacity.duration.200ms
            class="rounded-2xl border px-4 py-3 text-sm shadow-sm"
            :class="toast?.type === 'success'
                ? 'border-teal-200 bg-teal-50 text-teal-900'
                : 'border-red-200 bg-red-50 text-red-900'"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-start justify-between gap-3">
                <p class="min-w-0 flex-1" x-text="toast?.message"></p>
                <button type="button" @click="dismissToast()"
                        class="shrink-0 rounded-full p-0.5 opacity-60 hover:opacity-100"
                        aria-label="Đóng">✕</button>
            </div>
        </div>

        {{-- Preview card --}}
        <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-6 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-start gap-5">
                <div class="flex flex-col items-center gap-3">
                    <x-user-avatar :user="$user" size="xl" />

                    {{-- 2 nút gọn dưới avatar --}}
                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="open('avatar')"
                                class="rounded-full border border-stone-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-stone-700 shadow-sm transition hover:border-teal-300 hover:text-teal-800">
                            Avatar
                        </button>
                        <button type="button"
                                @click="open('frame')"
                                class="rounded-full border border-stone-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-stone-700 shadow-sm transition hover:border-amber-300 hover:text-amber-900">
                            Khung
                        </button>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate text-xl font-bold">{{ $user->name }}</h2>
                        @if ($hasPremium)
                            <span class="rounded-full bg-gradient-to-r from-amber-400 to-amber-500 px-2.5 py-0.5 text-[11px] font-semibold text-amber-950 shadow-sm">
                                Premium
                            </span>
                        @endif
                    </div>
                    <p class="text-stone-500">{{ '@'.$user->username }}</p>
                    @if ($hasPremium && $user->premium_expires_at)
                        <p class="mt-1 text-xs text-amber-700">
                            Premium đến {{ $user->premium_expires_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                        </p>
                    @endif
                    @if ($user->profile?->bio)
                        <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $user->profile->bio }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-stone-500">
                        <span><strong class="text-stone-800">{{ $stats['experiences'] }}</strong> lưu trữ</span>
                        <span><strong class="text-stone-800">{{ $stats['published'] }}</strong> công khai</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('profile.show', $user->username) }}"
                   class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-medium hover:border-teal-300">
                    Xem công khai
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-medium hover:border-teal-300">
                    Hồ sơ gu
                </a>
                <a href="{{ route('home') }}"
                   class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-medium hover:border-teal-300">
                    Kho của tôi
                </a>
            </div>
        </section>

        {{-- Premium unlock (gọn) --}}
        @unless ($hasPremium)
            <section class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3">
                <p class="text-sm text-stone-700">
                    <span class="font-semibold text-amber-800">Premium</span>
                    — mở khoá khung hiệu ứng cao cấp
                </p>
                <form method="POST" action="{{ route('profile.premium-avatar') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-full bg-gradient-to-r from-amber-400 to-amber-500 px-4 py-1.5 text-xs font-semibold text-amber-950 shadow-sm hover:from-amber-300 hover:to-amber-400">
                        Dùng thử 30 ngày
                    </button>
                </form>
            </section>
        @endunless

        {{-- Tên / username --}}
        <section class="rounded-3xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Thông tin hiển thị</h2>
            <form method="POST" action="{{ route('profile.account.update') }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Tên hiển thị</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                               class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                        @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-center pt-1">
                    <button type="submit" class="rounded-full bg-teal-600 px-8 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </section>

        {{-- Bảo mật: nút mở popup đổi mật khẩu --}}
        <section class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200/80 bg-white/90 px-4 py-3.5 shadow-sm">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-stone-900">Mật khẩu</p>
                <p class="text-xs text-stone-500">Bảo vệ tài khoản bằng mật khẩu mạnh</p>
            </div>
            <button type="button"
                    @click="open('password')"
                    class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-teal-300 hover:text-teal-800">
                Đổi mật khẩu
            </button>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-4 text-sm">
            <span class="text-stone-500">{{ $user->email }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-stone-500 hover:text-red-600">Đăng xuất</button>
            </form>
        </div>

        {{-- ========== POPUP AVATAR ========== --}}
        <div
            x-show="modal === 'avatar'"
            x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="avatar-modal-title"
        >
            <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-[2px]" @click="close()"></div>

            <div
                class="relative z-10 flex max-h-[min(90vh,640px)] w-full max-w-md flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                    <h3 id="avatar-modal-title" class="text-base font-semibold text-stone-900">Đổi avatar</h3>
                    <button type="button" @click="close()"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-700"
                            aria-label="Đóng">✕</button>
                </div>

                <form method="POST" action="{{ route('profile.account.update') }}" enctype="multipart/form-data"
                      class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="username" value="{{ $user->username }}">

                    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                        <div class="flex justify-center">
                            <x-user-avatar :user="$user" size="lg" />
                        </div>

                        <div class="flex justify-center gap-2">
                            <button type="button" @click="mode = 'sample'"
                                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                    :class="mode === 'sample' ? 'bg-teal-600 text-white' : 'bg-stone-100 text-stone-600'">
                                Chọn mẫu
                            </button>
                            <button type="button" @click="mode = 'upload'; sampleId = null"
                                    class="rounded-full px-3 py-1.5 text-xs font-semibold transition"
                                    :class="mode === 'upload' ? 'bg-teal-600 text-white' : 'bg-stone-100 text-stone-600'">
                                Tải ảnh
                            </button>
                        </div>

                        <div x-show="mode === 'sample'" x-cloak>
                            <input type="hidden" name="sample_avatar_id" :value="sampleId ?? ''" :disabled="mode !== 'sample'">
                            <div class="grid grid-cols-3 gap-2.5">
                                @foreach ($sampleAvatars as $sample)
                                    <button type="button"
                                            @click="sampleId = {{ $sample->id }}"
                                            class="flex flex-col items-center gap-1.5 rounded-2xl border p-2 transition"
                                            :class="sampleId === {{ $sample->id }} ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-200' : 'border-stone-200 hover:border-teal-300'">
                                        <img src="{{ $sample->url() }}" alt="{{ $sample->name }}"
                                             class="h-14 w-14 rounded-full object-cover shadow-sm">
                                        <span class="text-center text-[10px] font-medium text-stone-600">{{ $sample->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('sample_avatar_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="mode === 'upload'" x-cloak>
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                                   :disabled="mode !== 'upload'"
                                   class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-full file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-teal-800">
                            <p class="mt-1 text-xs text-stone-400">JPG, PNG, WebP · tối đa 2MB</p>
                            @error('avatar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        @if ($user->avatar_path || $user->sample_avatar_id)
                            <label class="flex items-center gap-2 text-sm text-stone-600">
                                <input type="checkbox" name="remove_avatar" value="1" class="rounded border-stone-300 text-teal-600">
                                Xoá ảnh (dùng chữ cái tên)
                            </label>
                        @endif
                    </div>

                    <div class="flex gap-2 border-t border-stone-100 px-5 py-4">
                        <button type="button" @click="close()"
                                class="flex-1 rounded-full border border-stone-200 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">
                            Huỷ
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-full bg-teal-600 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                            Lưu avatar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========== POPUP KHUNG ========== --}}
        <div
            x-show="modal === 'frame'"
            x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="frame-modal-title"
        >
            <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-[2px]" @click="close()"></div>

            <div
                class="relative z-10 flex max-h-[min(90vh,640px)] w-full max-w-md flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                    <h3 id="frame-modal-title" class="text-base font-semibold text-stone-900">Chọn khung</h3>
                    <button type="button" @click="close()"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-700"
                            aria-label="Đóng">✕</button>
                </div>

                <form method="POST" action="{{ route('profile.account.update') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="username" value="{{ $user->username }}">
                    <input type="hidden" name="avatar_frame_id" :value="frameId ?? ''">

                    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                        <div class="flex justify-center py-2">
                            {{-- Live preview theo frameId: map frame bằng Alpine khó vì Blade; hiển thị avatar hiện tại --}}
                            <x-user-avatar :user="$user" size="lg" />
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-medium text-stone-500">Cơ bản</p>
                            <div class="grid grid-cols-3 gap-2.5">
                                <button type="button" @click="frameId = null"
                                        class="flex flex-col items-center gap-1.5 rounded-2xl border p-2.5 transition"
                                        :class="!frameId ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-200' : 'border-stone-200 hover:border-stone-300'">
                                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-stone-100 ring-2 ring-stone-200">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-sm font-bold text-stone-500">{{ $initials }}</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-medium">Không</span>
                                </button>

                                @foreach ($freeFrames as $f)
                                    <button type="button" @click="frameId = {{ $f->id }}"
                                            class="flex flex-col items-center gap-1.5 rounded-2xl border p-2.5 transition"
                                            :class="frameId === {{ $f->id }} ? 'border-teal-600 bg-teal-50 ring-2 ring-teal-200' : 'border-stone-200 hover:border-stone-300'">
                                        <x-avatar-frame-preview :frame="$f" size="sm" :initials="$initials" :image-url="$avatarUrl" />
                                        <span class="text-[10px] font-medium">{{ $f->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 flex items-center gap-2 text-xs font-medium text-amber-700">
                                Premium
                                @unless ($hasPremium)
                                    <span class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-normal text-stone-500">cần Premium</span>
                                @endunless
                            </p>
                            <div class="grid grid-cols-3 gap-2.5">
                                @foreach ($premiumFrames as $f)
                                    <button type="button"
                                            @click="{{ $hasPremium ? "frameId = {$f->id}" : '' }}"
                                            @disabled(! $hasPremium)
                                            class="flex flex-col items-center gap-1.5 rounded-2xl border p-2.5 transition disabled:cursor-not-allowed disabled:opacity-45"
                                            :class="frameId === {{ $f->id }} ? 'border-amber-500 bg-amber-50 ring-2 ring-amber-200' : 'border-stone-200 hover:border-amber-300'">
                                        <x-avatar-frame-preview :frame="$f" size="sm" :initials="$initials" :image-url="$avatarUrl" />
                                        <span class="text-[10px] font-medium">{{ $f->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('avatar_frame_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        @unless ($hasPremium)
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                Khung Premium cần gói còn hạn. Dùng thử miễn phí 30 ngày ở banner phía trên.
                            </p>
                        @endunless
                    </div>

                    <div class="flex gap-2 border-t border-stone-100 px-5 py-4">
                        <button type="button" @click="close()"
                                class="flex-1 rounded-full border border-stone-200 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">
                            Huỷ
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-full bg-teal-600 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                            Lưu khung
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========== POPUP ĐỔI MẬT KHẨU ========== --}}
        <div
            x-show="modal === 'password'"
            x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="password-modal-title"
        >
            <div class="absolute inset-0 bg-stone-900/40 backdrop-blur-[2px]" @click="close()"></div>

            <div
                class="relative z-10 flex max-h-[min(90vh,640px)] w-full max-w-md flex-col overflow-hidden rounded-t-3xl bg-white shadow-2xl sm:rounded-3xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                    <h3 id="password-modal-title" class="text-base font-semibold text-stone-900">Đổi mật khẩu</h3>
                    <button type="button" @click="close()"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-stone-400 hover:bg-stone-100 hover:text-stone-700"
                            aria-label="Đóng">✕</button>
                </div>

                <form method="POST" action="{{ route('profile.password.update') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PATCH')

                    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                        @if ($openPassword && $passwordErrorSummary)
                            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800">
                                {{ $passwordErrorSummary }}
                            </div>
                        @endif

                        <p class="text-sm text-stone-500">Mật khẩu mới tối thiểu 8 ký tự. Không chia sẻ với người khác.</p>

                        <div>
                            <x-password-input
                                name="current_password"
                                label="Mật khẩu hiện tại"
                                autocomplete="current-password"
                            />
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-password-input
                                name="password"
                                label="Mật khẩu mới"
                                autocomplete="new-password"
                            />
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-password-input
                                name="password_confirmation"
                                label="Xác nhận mật khẩu mới"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <div class="flex gap-2 border-t border-stone-100 px-5 py-4">
                        <button type="button" @click="close()"
                                class="flex-1 rounded-full border border-stone-200 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">
                            Huỷ
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-full bg-teal-600 py-2.5 text-sm font-semibold text-white hover:bg-teal-700">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
