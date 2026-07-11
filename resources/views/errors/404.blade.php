@extends('layouts.app')

@section('title', 'Không tìm thấy — ViVu')

@section('content')
    <div class="mx-auto max-w-md py-16 text-center">
        <div class="rounded-3xl border border-stone-200/80 bg-white/90 px-6 py-12 shadow-sm backdrop-blur">
            <p class="text-6xl font-bold tracking-tight text-stone-200">404</p>
            <h1 class="mt-4 text-2xl font-semibold tracking-tight">Trang không tồn tại</h1>
            <p class="mt-2 text-sm text-stone-500">Liên kết có thể đã bị xoá hoặc nhập sai.</p>
            <a href="{{ route('home') }}" class="vivu-btn-primary mt-6">
                Về trang chủ
            </a>
        </div>
    </div>
@endsection
