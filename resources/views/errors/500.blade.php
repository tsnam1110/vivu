@extends('layouts.app')

@section('title', 'Lỗi máy chủ — ViVu')

@section('content')
    <div class="py-20 text-center">
        <p class="text-6xl font-bold text-stone-300">500</p>
        <h1 class="mt-4 text-2xl font-semibold">Đã xảy ra lỗi</h1>
        <p class="mt-2 text-stone-500">Chúng tôi đang khắc phục. Vui lòng thử lại sau.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">
            Về trang chủ
        </a>
    </div>
@endsection
