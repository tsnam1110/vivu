@extends('layouts.app')

@section('title', 'Chính sách cookie — ViVu')
@section('meta_description', 'Chính sách cookie và công nghệ lưu trữ trên thiết bị của ViVu.')

@section('content')
    @include('pages._legal-styles')

    <article class="legal-doc mx-auto max-w-3xl">
        <header class="mb-8">
            <p class="text-sm font-medium text-teal-700">Văn bản pháp lý</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-900">Chính sách cookie</h1>
            <p class="mt-2 text-sm text-stone-500">
                Có hiệu lực từ: <time datetime="2026-07-11">11/07/2026</time>
                · Cập nhật lần cuối: <time datetime="2026-07-11">11/07/2026</time>
            </p>
        </header>

        <div class="space-y-8 text-[15px] leading-relaxed text-stone-700">
            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">1. Cookie là gì?</h2>
                <p class="mt-3">
                    Cookie là tệp nhỏ được trình duyệt lưu trên thiết bị của bạn. ViVu cũng có thể dùng công nghệ tương tự
                    như local storage, session storage (ví dụ lưu token phiên bản admin) để vận hành Dịch vụ.
                </p>
                <p class="mt-3">
                    Việc sử dụng cookie gắn với nghĩa vụ minh bạch và tôn trọng quyền chủ thể dữ liệu theo
                    <strong>Nghị định 13/2023/NĐ-CP</strong>, Luật An toàn thông tin mạng và các quy định liên quan.
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">2. Loại cookie / lưu trữ ViVu sử dụng</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[28rem] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-stone-200 text-stone-500">
                                <th class="py-2 pr-3 font-medium">Loại</th>
                                <th class="py-2 pr-3 font-medium">Mục đích</th>
                                <th class="py-2 font-medium">Bắt buộc?</th>
                            </tr>
                        </thead>
                        <tbody class="align-top">
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Phiên & xác thực</td>
                                <td class="py-2 pr-3">Duy trì đăng nhập người dùng (session), CSRF token bảo vệ form</td>
                                <td class="py-2">Có — cần để Dịch vụ hoạt động</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Bảo mật</td>
                                <td class="py-2 pr-3">Chống giả mạo yêu cầu, ổn định phiên</td>
                                <td class="py-2">Có</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Tuỳ chọn giao diện (nếu có)</td>
                                <td class="py-2 pr-3">Ghi nhớ tuỳ chọn hiển thị không định danh</td>
                                <td class="py-2">Không bắt buộc chức năng cốt lõi</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-3 font-medium text-stone-800">Phân tích / quảng cáo (nếu triển khai sau)</td>
                                <td class="py-2 pr-3">Đo lường lưu lượng, cải thiện sản phẩm</td>
                                <td class="py-2">Chỉ khi thông báo/đồng ý theo quy định</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-sm text-stone-500">
                    Admin SPA có thể lưu token xác thực trên trình duyệt (local storage) để gọi API — đây không phải cookie HTTP
                    nhưng được nêu tại đây để minh bạch.
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">3. Thời hạn</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    <li><strong>Cookie phiên:</strong> hết hạn khi bạn đóng trình duyệt hoặc hết thời gian session cấu hình.</li>
                    <li><strong>Cookie/lưu trữ lâu dài:</strong> theo thời hạn kỹ thuật từng loại hoặc đến khi bạn xoá.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">4. Quản lý cookie</h2>
                <p class="mt-3">Bạn có thể:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Chặn hoặc xoá cookie trong cài đặt trình duyệt;</li>
                    <li>Dùng chế độ ẩn danh (hạn chế lưu trữ lâu dài);</li>
                    <li>Đăng xuất để vô hiệu hoá phiên đăng nhập.</li>
                </ul>
                <p class="mt-3 text-sm text-stone-500">
                    Nếu chặn cookie thiết yếu, một số chức năng (đăng nhập, gửi form) có thể không hoạt động.
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">5. Bên thứ ba</h2>
                <p class="mt-3">
                    Khi tích hợp bản đồ, phông chữ, hoặc công cụ phân tích của bên thứ ba, các bên đó có thể đặt cookie
                    theo chính sách riêng. Bạn nên tham khảo chính sách của Google Maps / nhà cung cấp tương ứng.
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">6. Liên hệ</h2>
                <p class="mt-3">
                    Câu hỏi về cookie:
                    <a href="mailto:privacy@vivu.test" class="text-teal-700 hover:underline">privacy@vivu.test</a>
                    · Xem thêm
                    <a href="{{ route('pages.privacy') }}" class="text-teal-700 hover:underline">Chính sách bảo vệ dữ liệu cá nhân</a>.
                </p>
            </section>
        </div>
    </article>
@endsection
