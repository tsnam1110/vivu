@extends('layouts.app')

@section('title', 'Không tìm thấy — ViVu')

@section('content')
    <div class="py-20 text-center">
        <p class="text-6xl font-bold text-stone-300">404</p>
        <h1 class="mt-4 text-2xl font-semibold">Trang không tồn tại</h1>
        <p class="mt-2 text-stone-500">Liên kết có thể đã bị xoá hoặc nhập sai.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700">
            Về trang chủ
        </a>
    </div>
@endsection
