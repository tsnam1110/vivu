@extends('layouts.app')

@section('title', 'Đăng ký — ViVu')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-3xl border border-stone-200/80 bg-white p-7 shadow-sm sm:p-8">
            <div class="text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-600 text-sm font-bold text-white shadow-sm shadow-teal-600/30">
                    V
                </div>
                <h1 class="mt-4 text-2xl font-bold tracking-tight">Tạo tài khoản</h1>
                <p class="mt-1 text-sm text-stone-500">Bắt đầu lưu trải nghiệm của bạn</p>
            </div>
            <form method="POST" action="{{ url('/register') }}" class="mt-7 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">Tên hiển thị</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="vivu-input">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required class="vivu-input" autocomplete="username">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="vivu-input" autocomplete="email">
                </div>
                <div x-data="{ showPassword: false }" class="space-y-4">
                    <x-password-input
                        name="password"
                        label="Mật khẩu"
                        autocomplete="new-password"
                        :scope="false"
                        :toggle="true"
                    />
                    <x-password-input
                        name="password_confirmation"
                        label="Xác nhận mật khẩu"
                        autocomplete="new-password"
                        :scope="false"
                        :toggle="false"
                    />
                </div>
                <p class="text-xs leading-relaxed text-stone-500">
                    Bằng việc đăng ký, bạn đồng ý với
                    <a href="{{ route('pages.terms') }}" class="font-medium text-teal-700 hover:underline" target="_blank" rel="noopener">Điều khoản</a>,
                    <a href="{{ route('pages.privacy') }}" class="font-medium text-teal-700 hover:underline" target="_blank" rel="noopener">Bảo vệ dữ liệu</a>
                    và
                    <a href="{{ route('pages.community') }}" class="font-medium text-teal-700 hover:underline" target="_blank" rel="noopener">Quy tắc cộng đồng</a>.
                </p>
                <button type="submit" class="w-full rounded-xl bg-teal-600 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/20 transition hover:bg-teal-700">
                    Đăng ký
                </button>
            </form>
            <p class="mt-5 text-center text-sm text-stone-500">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Đăng nhập</a>
            </p>
        </div>
    </div>
@endsection
