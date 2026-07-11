@extends('layouts.app')

@section('title', 'Quy tắc cộng đồng — ViVu')
@section('meta_description', 'Quy tắc cộng đồng và chính sách nội dung ViVu theo pháp luật Việt Nam.')

@section('content')
    @include('pages._legal-styles')

    <article class="legal-doc mx-auto max-w-3xl">
        <header class="mb-8">
            <p class="text-sm font-medium text-teal-700">Văn bản pháp lý</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-900">Quy tắc cộng đồng & chính sách nội dung</h1>
            <p class="mt-2 text-sm text-stone-500">
                Có hiệu lực từ: <time datetime="2026-07-11">11/07/2026</time>
                · Cập nhật lần cuối: <time datetime="2026-07-11">11/07/2026</time>
            </p>
        </header>

        <div class="space-y-8 text-[15px] leading-relaxed text-stone-700">
            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">1. Mục tiêu</h2>
                <p class="mt-3">
                    ViVu ưu tiên là <strong>kho lưu trữ trải nghiệm cá nhân</strong>, đồng thời là không gian chia sẻ
                    có trách nhiệm. Quy tắc này giúp cộng đồng an toàn, trung thực và tuân thủ pháp luật Việt Nam
                    (Luật An ninh mạng, Luật An toàn thông tin mạng, pháp luật về báo chí/xuất bản/quảng cáo khi liên quan,
                    Bộ luật Hình sự đối với hành vi nghiêm trọng…).
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">2. Nội dung được khuyến khích</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    <li>Trải nghiệm chân thực về quán ăn, cà phê, du lịch, homestay… kèm địa điểm rõ ràng.</li>
                    <li>Hình ảnh tự chụp, mô tả hữu ích, tôn trọng chủ địa điểm và người khác.</li>
                    <li>Bình luận, đánh giá xây dựng; phản hồi lịch sự khi bất đồng quan điểm.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">3. Nội dung & hành vi không được phép</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>Chống phá Nhà nước CHXHCN Việt Nam; kích động bạo lực, thù hận, phân biệt chủng tộc/tôn giáo/giới.</li>
                    <li>Khiêu dâm, bạo lực cực đoan, nội dung độc hại với trẻ em.</li>
                    <li>Tin giả gây hoang mang; lừa đảo; quảng cáo hàng cấm, thuốc, vũ khí, chất cấm…</li>
                    <li>Xúc phạm danh dự, nhân phẩm; doxxing; phát tán dữ liệu cá nhân người khác trái phép.</li>
                    <li>Ảnh/video không có quyền sử dụng; đạo nhái thương hiệu; spam link độc hại.</li>
                    <li>Đánh giá ảo, mua bán tương tác, thao túng hệ thống gợi ý “cùng gu”.</li>
                    <li>Nội dung thuần tuý quảng cáo trá hình nếu không được phép hoặc gây khó chịu hàng loạt.</li>
                </ul>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">4. Kiểm duyệt & xử lý vi phạm</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5">
                    <li>Hệ thống và/hoặc quản trị viên có thể ẩn, gỡ nội dung, hạn chế tính năng, tạm khoá hoặc khoá tài khoản.</li>
                    <li>Mức độ xử lý cân nhắc tính chất, mức độ, tần suất vi phạm và thiệt hại tiềm tàng.</li>
                    <li>Vi phạm pháp luật nghiêm trọng có thể được chuyển cơ quan nhà nước có thẩm quyền theo Luật An ninh mạng và quy định liên quan.</li>
                    <li>Bạn có thể khiếu nại quyết định kiểm duyệt qua email hỗ trợ trong thời hạn hợp lý, kèm bằng chứng.</li>
                </ol>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">5. Báo cáo vi phạm</h2>
                <p class="mt-3">
                    Nếu gặp nội dung vi phạm, hãy sử dụng cơ chế báo cáo trong sản phẩm (khi có) hoặc gửi email
                    <a href="mailto:report@vivu.test" class="text-teal-700 hover:underline">report@vivu.test</a>
                    kèm URL nội dung, mô tả vi phạm và bằng chứng (nếu có).
                </p>
            </section>

            <section class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">6. Quan hệ với các chính sách khác</h2>
                <p class="mt-3">
                    Quy tắc này là một phần không tách rời của
                    <a href="{{ route('pages.terms') }}" class="text-teal-700 hover:underline">Điều khoản sử dụng</a>
                    và
                    <a href="{{ route('pages.privacy') }}" class="text-teal-700 hover:underline">Chính sách bảo vệ dữ liệu cá nhân</a>.
                    Trường hợp mâu thuẫn về xử lý nội dung, quy tắc chuyên biệt này được ưu tiên áp dụng cho vấn đề cộng đồng.
                </p>
            </section>
        </div>
    </article>
@endsection
