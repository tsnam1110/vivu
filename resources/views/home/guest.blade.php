@extends('layouts.app')

@section('title', 'ViVu — Lưu trữ trải nghiệm của bạn')

@section('content')
    <section class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/80 px-6 py-12 text-center shadow-sm backdrop-blur-sm sm:px-10 sm:py-14">
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-teal-100/60 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-8 h-36 w-36 rounded-full bg-cyan-100/50 blur-2xl" aria-hidden="true"></div>

        <div class="relative">
            <div class="inline-flex items-center gap-1.5 rounded-full border border-teal-200/80 bg-teal-50 px-3 py-1 text-xs font-medium text-teal-800">
                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                Không gian lưu trữ trải nghiệm
            </div>
            <h1 class="mx-auto mt-5 max-w-xl text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl md:text-[2.75rem] md:leading-tight">
                Lưu lại những nơi<br class="hidden sm:block"> bạn đã đến
            </h1>
            <p class="mx-auto mt-4 max-w-lg text-[15px] leading-relaxed text-stone-500 sm:text-base">
                Ghi lại quán ăn, cà phê, chuyến đi… gắn bản đồ và gu cá nhân.
                Ưu tiên <strong class="font-semibold text-stone-700">kho của bạn</strong> — khám phá cộng đồng là phần phụ.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('register') }}" class="vivu-btn-primary px-6 py-3">
                    Tạo tài khoản miễn phí
                </a>
                <a href="{{ route('login') }}" class="vivu-btn-secondary px-6 py-3">
                    Đăng nhập
                </a>
            </div>
            <p class="mt-6 text-sm text-stone-400">
                Hoặc
                <a href="{{ route('explore') }}" class="font-medium text-teal-700 transition hover:underline">khám phá trải nghiệm công khai</a>
            </p>
        </div>
    </section>

    <section class="mt-8 grid gap-3 sm:mt-10 sm:grid-cols-3 sm:gap-4">
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-xl">📍</div>
            <h2 class="mt-3 font-semibold text-stone-900">Gắn địa điểm</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-stone-500">Lưu địa chỉ + toạ độ trên bản đồ cho mỗi trải nghiệm.</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-xl">🗂️</div>
            <h2 class="mt-3 font-semibold text-stone-900">Kho cá nhân</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-stone-500">Nháp, công khai — quản lý mọi kỷ niệm trong một chỗ.</p>
        </div>
        <div class="rounded-2xl border border-stone-200/80 bg-white/90 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-xl">✨</div>
            <h2 class="mt-3 font-semibold text-stone-900">Người cùng gu</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-stone-500">Khai báo sở thích để gặp người có gu tương đồng.</p>
        </div>
    </section>
@endsection
