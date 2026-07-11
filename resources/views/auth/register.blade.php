@extends('layouts.app')

@section('title', 'Đăng ký — ViVu')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Tạo tài khoản</h1>
        <p class="mt-1 text-sm text-stone-500">Tham gia cộng đồng chia sẻ trải nghiệm</p>
        <form method="POST" action="{{ url('/register') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium">Tên hiển thị</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required
                       class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border border-stone-300 px-3 py-2 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </div>
            {{-- Một state showPassword cho cả 2 ô; chỉ 1 nút mắt (Tab bỏ qua) --}}
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
            <button class="w-full rounded-xl bg-teal-600 py-2.5 font-semibold text-white hover:bg-teal-700">Đăng ký</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-500">
            Đã có tài khoản?
            <a href="{{ route('login') }}" class="font-medium text-teal-700 hover:underline">Đăng nhập</a>
        </p>
    </div>
@endsection
