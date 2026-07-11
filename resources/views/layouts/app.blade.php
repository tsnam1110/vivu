<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ViVu — Lưu trữ trải nghiệm')</title>
    <meta name="description" content="@yield('meta_description', 'ViVu — lưu trữ trải nghiệm cá nhân, gắn bản đồ và tìm người cùng gu.')">
    <meta name="theme-color" content="#f2f2f7">
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php
    $navItem = function (bool $active) {
        $base = 'flex min-w-[3.25rem] flex-col items-center justify-center gap-0.5 rounded-2xl px-2.5 py-1.5 text-[10px] font-medium transition-all duration-200 sm:min-w-[4.25rem] sm:px-3 sm:text-[11px]';
        $on = 'bg-stone-900 text-white shadow-sm';
        $off = 'text-stone-500 hover:bg-stone-100/80 hover:text-stone-800';

        return $base.' '.($active ? $on : $off);
    };
@endphp
<body class="flex min-h-dvh flex-col bg-[#f2f2f7] text-stone-900 antialiased">
    {{-- Soft ambient (desktop) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-teal-200/25 blur-3xl"></div>
        <div class="absolute -right-16 top-40 h-64 w-64 rounded-full bg-cyan-200/20 blur-3xl"></div>
    </div>

    {{-- Brand chip --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-40 flex justify-center pt-[max(0.75rem,env(safe-area-inset-top))]">
        <a href="{{ route('home') }}"
           class="pointer-events-auto inline-flex items-center gap-1.5 rounded-full border border-white/70 bg-white/75 px-3.5 py-1.5 text-sm font-bold tracking-tight text-teal-700 shadow-[0_4px_20px_rgba(0,0,0,0.06)] backdrop-blur-xl transition hover:bg-white/90">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-teal-600 text-[10px] font-bold text-white">V</span>
            ViVu
        </a>
    </div>

    <div class="flex flex-1 flex-col pt-16 pb-[calc(5.5rem+env(safe-area-inset-bottom))] sm:pb-[calc(6rem+env(safe-area-inset-bottom))]">
        @if (session('success'))
            <div class="mx-auto w-full max-w-6xl px-4">
                <div class="flex items-start gap-2 rounded-2xl border border-teal-200/80 bg-teal-50/95 px-4 py-3 text-sm text-teal-900 shadow-sm backdrop-blur">
                    <span class="mt-0.5 text-teal-600" aria-hidden="true">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-auto w-full max-w-6xl px-4 {{ session('success') ? 'mt-3' : '' }}">
                <div class="rounded-2xl border border-red-200/80 bg-red-50/95 px-4 py-3 text-sm text-red-900 shadow-sm backdrop-blur">
                    <p class="font-medium">Có lỗi cần sửa</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-4 text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <main class="vivu-page-enter mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:py-8">
            @yield('content')
        </main>

        <footer class="mx-auto mt-auto w-full max-w-6xl px-4 py-5 text-center text-xs text-stone-400">
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2">
                <span class="font-medium text-stone-500">&copy; {{ date('Y') }} ViVu</span>
                <span class="hidden text-stone-300 sm:inline" aria-hidden="true">·</span>
                <a href="{{ route('pages.terms') }}" class="transition hover:text-teal-700">Điều khoản</a>
                <a href="{{ route('pages.privacy') }}" class="transition hover:text-teal-700">Bảo vệ dữ liệu</a>
                <a href="{{ route('pages.community') }}" class="transition hover:text-teal-700">Cộng đồng</a>
                <a href="{{ route('pages.cookies') }}" class="transition hover:text-teal-700">Cookie</a>
            </div>
        </footer>
    </div>

    {{-- Floating tab bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-50 flex justify-center px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2"
         aria-label="Điều hướng chính">
        <div class="flex w-full max-w-md items-center justify-between gap-0.5 rounded-[1.75rem] border border-white/70 bg-white/80 p-1.5 shadow-[0_8px_32px_rgba(0,0,0,0.12)] backdrop-blur-2xl sm:max-w-lg sm:gap-1 sm:p-2">
            @auth('web')
                <a href="{{ route('home') }}" class="{{ $navItem(request()->routeIs('home')) }}" @if(request()->routeIs('home')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M4.5 10.5v8.25A1.5 1.5 0 0 0 6 20.25h3.75v-4.5h4.5v4.5H18a1.5 1.5 0 0 0 1.5-1.5V10.5" />
                    </svg>
                    <span>Của tôi</span>
                </a>
                <a href="{{ route('explore') }}" class="{{ $navItem(request()->routeIs('explore')) }}" @if(request()->routeIs('explore')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                    </svg>
                    <span>Khám phá</span>
                </a>
                <a href="{{ route('experiences.create') }}"
                   class="relative -mt-3 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-b from-teal-500 to-teal-700 text-white shadow-lg shadow-teal-600/35 ring-4 ring-[#f2f2f7] transition hover:from-teal-400 hover:to-teal-600 active:scale-95"
                   title="Đăng trải nghiệm"
                   @if(request()->routeIs('experiences.create')) aria-current="page" @endif>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span class="sr-only">Đăng trải nghiệm</span>
                </a>
                <a href="{{ route('matches.index') }}" class="{{ $navItem(request()->routeIs('matches.*')) }}" @if(request()->routeIs('matches.*')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962A3.75 3.75 0 1 1 12 8.25a3.75 3.75 0 0 1-2.441 3.508M15.75 18.75a9 9 0 1 0-7.5 0" />
                    </svg>
                    <span>Cùng gu</span>
                </a>
                <a href="{{ route('profile.me') }}" class="{{ $navItem(request()->routeIs('profile.me') || request()->routeIs('profile.edit') || request()->routeIs('profile.account.*') || request()->routeIs('profile.premium-avatar')) }}" @if(request()->routeIs('profile.me')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0" />
                    </svg>
                    <span>Profile</span>
                </a>
            @else
                <a href="{{ route('home') }}" class="{{ $navItem(request()->routeIs('home')) }}" @if(request()->routeIs('home')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 4.5l8.25 5.25M4.5 10.5v8.25A1.5 1.5 0 0 0 6 20.25h12a1.5 1.5 0 0 0 1.5-1.5V10.5" />
                    </svg>
                    <span>Trang chủ</span>
                </a>
                <a href="{{ route('explore') }}" class="{{ $navItem(request()->routeIs('explore')) }}" @if(request()->routeIs('explore')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.15 6.15a7.5 7.5 0 0 0 10.5 10.5Z" />
                    </svg>
                    <span>Khám phá</span>
                </a>
                <a href="{{ route('login') }}" class="{{ $navItem(request()->routeIs('login')) }}" @if(request()->routeIs('login')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H9" />
                    </svg>
                    <span>Đăng nhập</span>
                </a>
                <a href="{{ route('register') }}"
                   class="flex items-center justify-center rounded-2xl bg-teal-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm shadow-teal-600/25 transition hover:bg-teal-700 sm:px-5"
                   @if(request()->routeIs('register')) aria-current="page" @endif>
                    Đăng ký
                </a>
            @endauth
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
