@extends('layouts.app')

@section('title', 'ViVu — Lưu trữ trải nghiệm của bạn')

@section('content')
    <section class="mx-auto max-w-2xl text-center">
        <div class="inline-flex items-center rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-800">
            Không gian lưu trữ trải nghiệm
        </div>
        <h1 class="mt-5 text-3xl font-bold tracking-tight text-stone-900 md:text-4xl">
            Lưu lại những nơi bạn đã đến
        </h1>
        <p class="mt-4 text-base text-stone-500 md:text-lg">
            ViVu giúp bạn ghi lại quán ăn, cà phê, chuyến đi… gắn bản đồ và gu cá nhân.
            Ưu tiên <strong class="font-semibold text-stone-700">kho của bạn</strong>, khám phá cộng đồng là phần phụ.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('register') }}"
               class="rounded-full bg-teal-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-teal-700">
                Tạo tài khoản miễn phí
            </a>
            <a href="{{ route('login') }}"
               class="rounded-full border border-stone-200 bg-white px-6 py-3 text-sm font-semibold text-stone-800 hover:border-teal-300">
                Đăng nhập
            </a>
        </div>
        <p class="mt-6 text-sm text-stone-400">
            Hoặc
            <a href="{{ route('explore') }}" class="font-medium text-teal-700 hover:underline">khám phá trải nghiệm công khai</a>
        </p>
    </section>

    <section class="mx-auto mt-14 grid max-w-3xl gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 text-left shadow-sm backdrop-blur">
            <div class="text-2xl">📍</div>
            <h2 class="mt-3 font-semibold">Gắn địa điểm</h2>
            <p class="mt-1 text-sm text-stone-500">Lưu địa chỉ + toạ độ trên bản đồ cho mỗi trải nghiệm.</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 text-left shadow-sm backdrop-blur">
            <div class="text-2xl">🗂️</div>
            <h2 class="mt-3 font-semibold">Kho cá nhân</h2>
            <p class="mt-1 text-sm text-stone-500">Nháp, chờ duyệt, công khai — quản lý trong một chỗ.</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 text-left shadow-sm backdrop-blur">
            <div class="text-2xl">✨</div>
            <h2 class="mt-3 font-semibold">Tìm người cùng gu</h2>
            <p class="mt-1 text-sm text-stone-500">Khai báo sở thích để gặp người có gu tương đồng.</p>
        </div>
    </section>
@endsection
