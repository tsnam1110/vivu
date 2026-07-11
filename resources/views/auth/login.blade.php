@extends('layouts.app')

@section('title', 'Đăng nhập — ViVu')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-3xl border border-stone-200/80 bg-white p-7 shadow-sm sm:p-8">
            <div class="text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-600 text-sm font-bold text-white shadow-sm shadow-teal-600/30">
                    V
                </div>
                <h1 class="mt-4 text-2xl font-bold tracking-tight">Đăng nhập</h1>
                <p class="mt-1 text-sm text-stone-500">Chào mừng trở lại ViVu</p>
            </div>
            <form method="POST" action="{{ url('/login') }}" class="mt-7 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="vivu-input">
                </div>
                <x-password-input name="password" label="Mật khẩu" autocomplete="current-password" />
                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-teal-600 focus:ring-teal-500">
                    Ghi nhớ đăng nhập
                </label>
                <button type="submit" class="w-full rounded-xl bg-teal-600 py-2.5 text-sm font-semibold text-white shadow-sm shadow-teal-600/20 transition hover:bg-teal-700">
                    Đăng nhập
                </button>
            </form>
            <p class="mt-5 text-center text-sm text-stone-500">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="font-medium text-teal-700 hover:underline">Đăng ký</a>
            </p>
        </div>
    </div>
@endsection
