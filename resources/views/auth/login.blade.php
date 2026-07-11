@extends('layouts.app')

@section('title', 'Đăng nhập — ViVu')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Đăng nhập</h1>
        <p class="mt-1 text-sm text-stone-500">Chào mừng trở lại ViVu</p>
        <form method="POST" action="{{ url('/login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </div>
            <x-password-input name="password" label="Mật khẩu" autocomplete="current-password" />
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-teal-600">
                Ghi nhớ đăng nhập
            </label>
            <button class="w-full rounded-xl bg-teal-600 py-2.5 font-semibold text-white hover:bg-teal-700">Đăng nhập</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-500">
            Chưa có tài khoản?
            <a href="{{ route('register') }}" class="font-medium text-teal-700 hover:underline">Đăng ký</a>
        </p>
    </div>
@endsection
