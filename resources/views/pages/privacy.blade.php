@extends('layouts.app')

@section('title', 'Chính sách bảo vệ dữ liệu cá nhân — ViVu')
@section('meta_description', 'Chính sách bảo vệ dữ liệu cá nhân của ViVu theo Nghị định 13/2023/NĐ-CP và pháp luật Việt Nam.')

@section('content')
    @include('pages._legal-styles')

    <article class="legal-doc mx-auto max-w-3xl">
        <header class="mb-8">
            <p class="text-sm font-medium text-teal-700">Văn bản pháp lý</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-900">Chính sách bảo vệ dữ liệu cá nhân</h1>
            <p class="mt-2 text-sm text-stone-500">
                Có hiệu lực từ: <time datetime="2026-07-11">11/07/2026</time>
                · Cập nhật lần cuối: <time datetime="2026-07-11">11/07/2026</time>
            </p>
        </header>

        <nav class="mb-10 rounded-2xl border border-stone-200/80 bg-white/90 p-4 text-sm shadow-sm" aria-label="Mục lục">
            <p class="mb-2 font-semibold text-stone-800">Mục lục</p>
            <ol class="list-decimal space-y-1 pl-5 text-stone-600">
                <li><a href="#p1" class="hover:text-teal-700">Giới thiệu & căn cứ</a></li>
                <li><a href="#p2" class="hover:text-teal-700">Vai trò của ViVu</a></li>
                <li><a href="#p3" class="hover:text-teal-700">Loại dữ liệu thu thập</a></li>
                <li><a href="#p4" class="hover:text-teal-700">Mục đích & cơ sở xử lý</a></li>
                <li><a href="#p5" class="hover:text-teal-700">Cách thức thu thập</a></li>
                <li><a href="#p6" class="hover:text-teal-700">Chia sẻ & chuyển giao dữ liệu</a></li>
                <li><a href="#p7" class="hover:text-teal-700">Lưu trữ & bảo mật</a></li>
                <li><a href="#p8" class="hover:text-teal-700">Quyền của chủ thể dữ liệu</a></li>
                <li><a href="#p9" class="hover:text-teal-700">Dữ liệu trẻ em</a></li>
                <li><a href="#p10" class="hover:text-teal-700">Cookie & công nghệ tương tự</a></li>
                <li><a href="#p11" class="hover:text-teal-700">Thay đổi chính sách</a></li>
                <li><a href="#p12" class="hover:text-teal-700">Liên hệ / khiếu nại</a></li>
            </ol>
        </nav>

        <div class="space-y-8 text-[15px] leading-relaxed text-stone-700">
            <section id="p1" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">1. Giới thiệu & căn cứ</h2>
                <p class="mt-3">
                    Chính sách này giải thích cách ViVu thu thập, sử dụng, lưu trữ, chia sẻ và bảo vệ
                    <strong>dữ liệu cá nhân</strong> khi bạn sử dụng Dịch vụ.
                </p>
                <p class="mt-3">Căn cứ pháp lý chính:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li><strong>Nghị định 13/2023/NĐ-CP</strong> về bảo vệ dữ liệu cá nhân;</li>
                    <li>Luật An ninh mạng 2018; Luật An toàn thông tin mạng 2015;</li>
                    <li>Luật Giao dịch điện tử 2023; Luật Bảo vệ quyền lợi người tiêu dùng 2023;</li>
                    <li>Bộ luật Dân sự 2015 và văn bản hướng dẫn liên quan.</li>
                </ul>
            </section>

            <section id="p2" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">2. Vai trò của ViVu</h2>
                <p class="mt-3">
                    Đối với dữ liệu tài khoản, hồ sơ gu, nhật ký sử dụng do Dịch vụ tạo ra, ViVu đóng vai trò
                    <strong>Bên Kiểm soát dữ liệu cá nhân</strong> (hoặc Kiểm soát viên) theo Nghị định 13/2023/NĐ-CP,
                    trừ khi có thoả thuận khác bằng văn bản.
                </p>
                <p class="mt-3">
                    Khi sử dụng nhà cung cấp hạ tầng (hosting, email, CDN, bản đồ…), các bên có thể đóng vai trò
                    Bên Xử lý dữ liệu theo hợp đồng / điều khoản của họ, trong phạm vi được pháp luật cho phép.
                </p>
            </section>

            <section id="p3" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">3. Loại dữ liệu thu thập</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[28rem] border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-stone-200 text-stone-500">
                                <th class="py-2 pr-3 font-medium">Nhóm</th>
                                <th class="py-2 font-medium">Ví dụ</th>
                            </tr>
                        </thead>
                        <tbody class="align-top">
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Định danh tài khoản</td>
                                <td class="py-2">Họ tên hiển thị, username, email, mật khẩu (đã băm), trạng thái tài khoản</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Hồ sơ</td>
                                <td class="py-2">Bio, thành phố, nhãn tính cách/sở thích, tuỳ chọn tham gia gợi ý match</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Nội dung do bạn tạo</td>
                                <td class="py-2">Trải nghiệm, ảnh, bình luận, đánh giá, cảm xúc (like/tim), thẻ</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Dữ liệu địa điểm trong trải nghiệm</td>
                                <td class="py-2">Tên địa điểm, địa chỉ, toạ độ gắn với địa điểm công cộng bạn mô tả</td>
                            </tr>
                            <tr class="border-b border-stone-100">
                                <td class="py-2 pr-3 font-medium text-stone-800">Dữ liệu kỹ thuật</td>
                                <td class="py-2">Địa chỉ IP, user-agent, cookie/session, nhật ký truy cập, lỗi hệ thống</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-3 font-medium text-stone-800">Dữ liệu tương tác</td>
                                <td class="py-2">Lịch sử đăng nhập, thao tác kiểm duyệt (phía admin), phản hồi hỗ trợ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-sm text-stone-500">
                    <strong>Lưu ý:</strong> Chúng tôi <strong>không</strong> theo dõi vị trí GPS thời gian thực của thiết bị bạn
                    để lập hồ sơ di chuyển. Toạ độ trên trải nghiệm là thông tin địa điểm bạn chủ động gắn kèm nội dung.
                </p>
                <p class="mt-2 text-sm text-stone-500">
                    Email của bạn <strong>không</strong> hiển thị công khai trên hồ sơ. Mật khẩu được lưu dưới dạng băm một chiều.
                </p>
            </section>

            <section id="p4" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">4. Mục đích & cơ sở xử lý</h2>
                <p class="mt-3">Chúng tôi xử lý dữ liệu cho các mục đích sau, với cơ sở pháp lý phù hợp (đồng ý; hợp đồng/cung cấp dịch vụ; nghĩa vụ pháp lý; lợi ích hợp pháp trong phạm vi pháp luật cho phép):</p>
                <ul class="mt-2 list-disc space-y-2 pl-5">
                    <li><strong>Cung cấp Dịch vụ:</strong> tạo tài khoản, đăng nhập, lưu trải nghiệm, bình luận, gợi ý người cùng gu.</li>
                    <li><strong>An toàn & chống lạm dụng:</strong> phát hiện gian lận, spam, tấn công; thực hiện nghĩa vụ theo Luật An ninh mạng khi có yêu cầu hợp pháp.</li>
                    <li><strong>Cải thiện sản phẩm:</strong> thống kê tổng hợp, sửa lỗi, tối ưu hiệu năng (ưu tiên ẩn danh/tổng hợp khi có thể).</li>
                    <li><strong>Liên lạc:</strong> thông báo dịch vụ, phản hồi hỗ trợ (không spam quảng cáo trái quy định).</li>
                    <li><strong>Tuân thủ pháp luật:</strong> lưu trữ, cung cấp thông tin theo yêu cầu của cơ quan nhà nước có thẩm quyền.</li>
                </ul>
                <p class="mt-3">
                    Đối với hoạt động cần <strong>sự đồng ý</strong> (ví dụ bật gợi ý “người cùng gu”, cookie không thiết yếu),
                    bạn có thể rút lại đồng ý bất cứ lúc nào; việc rút đồng ý không ảnh hưởng tính hợp pháp của xử lý trước đó.
                </p>
            </section>

            <section id="p5" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">5. Cách thức thu thập</h2>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    <li>Bạn cung cấp trực tiếp khi đăng ký, cập nhật hồ sơ, đăng nội dung.</li>
                    <li>Tự động qua trình duyệt/ứng dụng (cookie, session, log máy chủ).</li>
                    <li>Từ nhà cung cấp dịch vụ bản đồ khi bạn sử dụng chức năng tìm địa điểm (theo điều khoản của nhà cung cấp đó).</li>
                </ul>
            </section>

            <section id="p6" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">6. Chia sẻ & chuyển giao dữ liệu</h2>
                <p class="mt-3">Chúng tôi <strong>không bán</strong> dữ liệu cá nhân của bạn.</p>
                <p class="mt-3">Dữ liệu có thể được chia sẻ trong các trường hợp:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li><strong>Công khai theo thiết kế sản phẩm:</strong> username, tên hiển thị, bio (nếu có), trải nghiệm/bình luận bạn chọn công khai, toạ độ địa điểm trong trải nghiệm công khai.</li>
                    <li><strong>Nhà cung cấp xử lý hộ:</strong> hosting, email, lưu trữ đám mây, dịch vụ bản đồ — chỉ trong phạm vi cần thiết và theo biện pháp bảo mật phù hợp.</li>
                    <li><strong>Nghĩa vụ pháp lý:</strong> cơ quan nhà nước có thẩm quyền theo trình tự, thủ tục luật định.</li>
                    <li><strong>Bảo vệ quyền lợi hợp pháp:</strong> phòng chống gian lận, bảo vệ an toàn người dùng, giải quyết tranh chấp.</li>
                </ul>
                <p class="mt-3">
                    Nếu có <strong>chuyển dữ liệu ra nước ngoài</strong> (ví dụ hạ tầng cloud đặt ngoài Việt Nam), Chúng tôi thực hiện
                    theo quy định tại Nghị định 13/2023/NĐ-CP và văn bản hướng dẫn (đánh giá tác động, thông báo/đăng ký khi pháp luật yêu cầu).
                </p>
            </section>

            <section id="p7" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">7. Lưu trữ & bảo mật</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>Lưu trữ trong thời gian cần thiết cho mục đích xử lý, hoặc theo thời hạn pháp luật / giải quyết tranh chấp yêu cầu.</li>
                    <li>Áp dụng biện pháp kỹ thuật và tổ chức phù hợp: mã hoá đường truyền (HTTPS ở môi trường production), băm mật khẩu, phân quyền, sao lưu, nhật ký truy cập quản trị.</li>
                    <li>Không biện pháp nào an toàn tuyệt đối; bạn cũng cần bảo vệ thiết bị và mật khẩu của mình.</li>
                    <li>Khi xảy ra sự cố an ninh dữ liệu cá nhân theo ngưỡng pháp luật quy định, Chúng tôi sẽ thông báo theo nghĩa vụ tại Nghị định 13/2023/NĐ-CP.</li>
                </ul>
            </section>

            <section id="p8" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">8. Quyền của chủ thể dữ liệu</h2>
                <p class="mt-3">Theo Nghị định 13/2023/NĐ-CP, bạn có các quyền (và nghĩa vụ liên quan), bao gồm:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Được biết, đồng ý, truy cập, chỉnh sửa dữ liệu cá nhân;</li>
                    <li>Xoá dữ liệu / yêu cầu hạn chế xử lý trong các trường hợp luật định;</li>
                    <li>Phản đối xử lý, khiếu nại, tố cáo, khởi kiện theo pháp luật;</li>
                    <li>Yêu cầu bồi thường thiệt hại nếu có căn cứ theo quy định;</li>
                    <li>Tự bảo vệ theo Bộ luật Dân sự và quy định khác.</li>
                </ul>
                <p class="mt-3">
                    Bạn có thể tự chỉnh sửa nhiều thông tin trong phần hồ sơ. Các yêu cầu khác gửi về email tại mục Liên hệ;
                    Chúng tôi phản hồi trong thời hạn phù hợp với quy định (tham chiếu các mốc thời gian tại Nghị định 13
                    và điều kiện xác minh danh tính hợp lệ).
                </p>
                <p class="mt-3 text-sm text-stone-500">
                    Một số yêu cầu có thể bị từ chối hoặc hạn chế nếu pháp luật cho phép/require giữ lại dữ liệu
                    (ví dụ nghĩa vụ an ninh mạng, giải quyết tranh chấp, chống gian lận).
                </p>
            </section>

            <section id="p9" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">9. Dữ liệu trẻ em</h2>
                <p class="mt-3">
                    Dịch vụ không hướng tới trẻ em dưới 16 tuổi. Việc xử lý dữ liệu cá nhân của trẻ em được thực hiện
                    theo quy định riêng tại Nghị định 13/2023/NĐ-CP (gồm yêu cầu đồng ý của trẻ và/hoặc cha mẹ, người giám hộ
                    trong các trường hợp luật định). Nếu phát hiện tài khoản của trẻ em đăng ký trái quy định, Chúng tôi
                    có thể xoá hoặc hạn chế tài khoản đó.
                </p>
            </section>

            <section id="p10" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">10. Cookie & công nghệ tương tự</h2>
                <p class="mt-3">
                    Chi tiết tại
                    <a href="{{ route('pages.cookies') }}" class="font-medium text-teal-700 hover:underline">Chính sách cookie</a>.
                    Tóm tắt: cookie phiên đăng nhập và bảo mật là cần thiết để Dịch vụ hoạt động; cookie phân tích (nếu triển khai)
                    sẽ được thông báo và/hoặc xin đồng ý khi pháp luật yêu cầu.
                </p>
            </section>

            <section id="p11" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">11. Thay đổi chính sách</h2>
                <p class="mt-3">
                    Chính sách có thể được cập nhật khi pháp luật hoặc Dịch vụ thay đổi. Phiên bản mới được đăng trên trang này
                    kèm ngày hiệu lực. Với thay đổi trọng yếu, Chúng tôi có thể thông báo nổi bật trên Dịch vụ hoặc qua email
                    đã đăng ký.
                </p>
            </section>

            <section id="p12" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">12. Liên hệ / khiếu nại</h2>
                <p class="mt-3">Về bảo vệ dữ liệu cá nhân, vui lòng liên hệ:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Email: <a href="mailto:privacy@vivu.test" class="text-teal-700 hover:underline">privacy@vivu.test</a> (cập nhật email chính thức khi production)</li>
                    <li>Tiêu đề gợi ý: “Yêu cầu chủ thể dữ liệu — [Họ tên / username]”</li>
                </ul>
                <p class="mt-3 text-sm text-stone-500">
                    Bạn cũng có quyền khiếu nại tới cơ quan nhà nước có thẩm quyền theo pháp luật Việt Nam khi quyền
                    của mình bị xâm phạm.
                </p>
            </section>
        </div>

        <p class="mt-10 text-center text-sm text-stone-500">
            Xem thêm:
            <a href="{{ route('pages.terms') }}" class="text-teal-700 hover:underline">Điều khoản</a> ·
            <a href="{{ route('pages.community') }}" class="text-teal-700 hover:underline">Quy tắc cộng đồng</a> ·
            <a href="{{ route('pages.cookies') }}" class="text-teal-700 hover:underline">Cookie</a>
        </p>
    </article>
@endsection
