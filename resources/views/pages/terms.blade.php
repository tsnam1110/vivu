@extends('layouts.app')

@section('title', 'Điều khoản sử dụng dịch vụ — ViVu')
@section('meta_description', 'Điều khoản sử dụng dịch vụ ViVu theo pháp luật Việt Nam hiện hành.')

@section('content')
    @include('pages._legal-styles')

    <article class="legal-doc mx-auto max-w-3xl">
        <header class="mb-8">
            <p class="text-sm font-medium text-teal-700">Văn bản pháp lý</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-stone-900">Điều khoản sử dụng dịch vụ</h1>
            <p class="mt-2 text-sm text-stone-500">
                Có hiệu lực từ: <time datetime="2026-07-11">11/07/2026</time>
                · Cập nhật lần cuối: <time datetime="2026-07-11">11/07/2026</time>
            </p>
        </header>

        <nav class="mb-10 rounded-2xl border border-stone-200/80 bg-white/90 p-4 text-sm shadow-sm" aria-label="Mục lục">
            <p class="mb-2 font-semibold text-stone-800">Mục lục</p>
            <ol class="list-decimal space-y-1 pl-5 text-stone-600">
                <li><a href="#s1" class="hover:text-teal-700">Giới thiệu & căn cứ pháp lý</a></li>
                <li><a href="#s2" class="hover:text-teal-700">Định nghĩa</a></li>
                <li><a href="#s3" class="hover:text-teal-700">Điều kiện sử dụng & đăng ký tài khoản</a></li>
                <li><a href="#s4" class="hover:text-teal-700">Quyền và nghĩa vụ của người dùng</a></li>
                <li><a href="#s5" class="hover:text-teal-700">Nội dung do người dùng tạo (UGC)</a></li>
                <li><a href="#s6" class="hover:text-teal-700">Hành vi bị cấm</a></li>
                <li><a href="#s7" class="hover:text-teal-700">Sở hữu trí tuệ</a></li>
                <li><a href="#s8" class="hover:text-teal-700">Miễn trừ & giới hạn trách nhiệm</a></li>
                <li><a href="#s9" class="hover:text-teal-700">Chấm dứt / tạm ngưng dịch vụ</a></li>
                <li><a href="#s10" class="hover:text-teal-700">Giải quyết tranh chấp</a></li>
                <li><a href="#s11" class="hover:text-teal-700">Sửa đổi điều khoản</a></li>
                <li><a href="#s12" class="hover:text-teal-700">Liên hệ</a></li>
            </ol>
        </nav>

        <div class="space-y-8 text-[15px] leading-relaxed text-stone-700">
            <section id="s1" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">1. Giới thiệu & căn cứ pháp lý</h2>
                <p class="mt-3">
                    Nền tảng <strong>ViVu</strong> (sau đây gọi là “Dịch vụ”, “Chúng tôi”) cung cấp không gian để người dùng
                    lưu trữ, chia sẻ trải nghiệm gắn địa điểm và kết nối theo sở thích (“gu”).
                </p>
                <p class="mt-3">
                    Bằng việc truy cập, đăng ký hoặc sử dụng Dịch vụ, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc
                    bởi Điều khoản này, cùng
                    <a href="{{ route('pages.privacy') }}" class="font-medium text-teal-700 hover:underline">Chính sách bảo vệ dữ liệu cá nhân</a>,
                    <a href="{{ route('pages.community') }}" class="font-medium text-teal-700 hover:underline">Quy tắc cộng đồng</a>
                    và
                    <a href="{{ route('pages.cookies') }}" class="font-medium text-teal-700 hover:underline">Chính sách cookie</a>.
                </p>
                <p class="mt-3">Điều khoản được soạn thảo phù hợp với pháp luật Việt Nam hiện hành, bao gồm nhưng không giới hạn:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Bộ luật Dân sự 2015;</li>
                    <li>Luật Giao dịch điện tử số 20/2023/QH15;</li>
                    <li>Luật An ninh mạng 2018; Luật An toàn thông tin mạng 2015;</li>
                    <li>Luật Bảo vệ quyền lợi người tiêu dùng 2023;</li>
                    <li>Luật Sở hữu trí tuệ (sửa đổi, bổ sung);</li>
                    <li>Luật Quảng cáo; Luật Báo chí; Luật Xuất bản (nếu liên quan nội dung);</li>
                    <li>Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân;</li>
                    <li>Các văn bản hướng dẫn thi hành và sửa đổi, bổ sung (nếu có).</li>
                </ul>
                <p class="mt-3 text-sm text-stone-500">
                    Văn bản này mang tính khung vận hành. Chủ thể kinh doanh vận hành ViVu cần hoàn thiện thông tin
                    định danh (tên, MST, địa chỉ, đại diện pháp luật) và rà soát với tư vấn pháp lý trước khi áp dụng chính thức.
                </p>
            </section>

            <section id="s2" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">2. Định nghĩa</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li><strong>Người dùng / Bạn:</strong> cá nhân truy cập hoặc sử dụng Dịch vụ.</li>
                    <li><strong>Tài khoản:</strong> tài khoản đăng ký trên ViVu (guard người dùng cuối).</li>
                    <li><strong>Nội dung:</strong> trải nghiệm, bình luận, đánh giá, ảnh, thẻ, hồ sơ gu và mọi dữ liệu bạn tải lên.</li>
                    <li><strong>Địa điểm công cộng:</strong> toạ độ/địa chỉ gắn với địa điểm trong trải nghiệm (không phải GPS theo dõi cá nhân).</li>
                    <li><strong>Quản trị viên:</strong> tài khoản quản trị hệ thống, tách biệt với tài khoản người dùng.</li>
                </ul>
            </section>

            <section id="s3" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">3. Điều kiện sử dụng & đăng ký tài khoản</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5">
                    <li>Bạn phải đủ <strong>16 tuổi</strong> trở lên. Nếu dưới 16 tuổi, việc đăng ký/sử dụng cần có sự đồng ý và giám sát của cha mẹ hoặc người giám hộ hợp pháp (tham chiếu tinh thần Nghị định 13/2023/NĐ-CP về chủ thể dữ liệu là trẻ em).</li>
                    <li>Thông tin đăng ký (họ tên hiển thị, username, email, mật khẩu) phải <strong>chính xác, đầy đủ, cập nhật</strong>. Cung cấp thông tin giả mạo có thể dẫn đến tạm khoá hoặc chấm dứt tài khoản.</li>
                    <li>Bạn chịu trách nhiệm bảo mật mật khẩu và mọi hoạt động diễn ra dưới tài khoản của mình. Thông báo ngay cho Chúng tôi khi nghi ngờ bị xâm nhập.</li>
                    <li>Mỗi cá nhân chỉ nên duy trì tài khoản trong phạm vi hợp lý; cấm tạo hàng loạt tài khoản nhằm spam, gian lận hoặc thao túng hệ thống.</li>
                    <li>Chúng tôi có quyền từ chối đăng ký, yêu cầu xác minh hoặc tạm ngưng tài khoản khi có dấu hiệu vi phạm pháp luật hoặc Điều khoản này.</li>
                </ol>
            </section>

            <section id="s4" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">4. Quyền và nghĩa vụ của người dùng</h2>
                <h3 class="mt-4 font-semibold text-stone-800">4.1. Quyền</h3>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Sử dụng các tính năng Dịch vụ theo đúng mục đích đã công bố.</li>
                    <li>Truy cập, chỉnh sửa hồ sơ; bật/tắt tham gia gợi ý “người cùng gu”.</li>
                    <li>Yêu cầu hỗ trợ, khiếu nại theo quy định pháp luật và mục Liên hệ.</li>
                    <li>Thực hiện các quyền đối với dữ liệu cá nhân theo
                        <a href="{{ route('pages.privacy') }}" class="text-teal-700 hover:underline">Chính sách bảo vệ dữ liệu cá nhân</a>.
                    </li>
                </ul>
                <h3 class="mt-4 font-semibold text-stone-800">4.2. Nghĩa vụ</h3>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Tuân thủ pháp luật Việt Nam và các chính sách của ViVu.</li>
                    <li>Chịu trách nhiệm pháp lý về Nội dung do mình tạo, đăng tải, chia sẻ.</li>
                    <li>Không xâm phạm quyền, lợi ích hợp pháp của cá nhân, tổ chức khác.</li>
                    <li>Hợp tác khi cơ quan nhà nước có thẩm quyền yêu cầu theo quy định pháp luật.</li>
                </ul>
            </section>

            <section id="s5" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">5. Nội dung do người dùng tạo (UGC)</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5">
                    <li>Bạn giữ quyền sở hữu đối với Nội dung gốc do mình tạo, đồng thời cấp cho ViVu <strong>giấy phép không độc quyền, miễn phí bản quyền, có thể chuyển nhượng một phần cho nhà thầu kỹ thuật</strong> để lưu trữ, hiển thị, phân phối, sao chép kỹ thuật (cache, CDN, backup) và hiển thị công khai (khi bạn chọn công khai) trên Dịch vụ.</li>
                    <li>Giấy phép chấm dứt khi bạn xoá Nội dung hoặc xoá tài khoản, trừ các bản sao lưu kỹ thuật còn tồn tại trong thời hạn lưu trữ hợp lý hoặc khi pháp luật yêu cầu giữ lại.</li>
                    <li>Nội dung có thể bị kiểm duyệt, ẩn, gỡ bỏ nếu vi phạm pháp luật, Điều khoản, Quy tắc cộng đồng hoặc theo yêu cầu của cơ quan có thẩm quyền.</li>
                    <li>Trải nghiệm gắn địa điểm: toạ độ/địa chỉ là thông tin về <strong>địa điểm công cộng</strong> bạn mô tả, không đồng nghĩa Chúng tôi thu thập vị trí GPS liên tục của thiết bị bạn.</li>
                    <li>Đánh giá, bình luận phải trung thực, không nhằm bôi nhọ, cạnh tranh không lành mạnh trái pháp luật.</li>
                </ol>
            </section>

            <section id="s6" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">6. Hành vi bị cấm</h2>
                <p class="mt-3">Nghiêm cấm sử dụng Dịch vụ để thực hiện hoặc hỗ trợ các hành vi sau (không đầy đủ):</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Xuyên tạc, chống phá Nhà nước Cộng hoà xã hội chủ nghĩa Việt Nam; kích động bạo lực, chia rẽ dân tộc, tôn giáo;</li>
                    <li>Tuyên truyền đồi trụy, tội phạm, mê tín dị đoan gây hại; phát tán tin giả gây hoang mang dư luận;</li>
                    <li>Xâm phạm đời tư, danh dự, nhân phẩm; quấy rối, đe doạ, phân biệt đối xử;</li>
                    <li>Xâm phạm sở hữu trí tuệ, bí mật kinh doanh, dữ liệu cá nhân của người khác trái pháp luật;</li>
                    <li>Gian lận, lừa đảo, spam, phishing, phát tán mã độc, tấn công hệ thống;</li>
                    <li>Thu thập dữ liệu người dùng khác bằng công cụ tự động trái phép (scraping) vượt quá giới hạn hợp lý;</li>
                    <li>Mạo danh cá nhân, tổ chức; thao túng đánh giá, reaction, hoặc hệ thống gợi ý;</li>
                    <li>Sử dụng Dịch vụ cho mục đích thương mại trái quy định (quảng cáo lậu, bán hàng cấm) nếu không được cho phép.</li>
                </ul>
                <p class="mt-3">Vi phạm có thể dẫn đến gỡ nội dung, tạm khoá, chấm dứt tài khoản và/hoặc chuyển thông tin cho cơ quan có thẩm quyền theo Luật An ninh mạng và quy định liên quan.</p>
            </section>

            <section id="s7" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">7. Sở hữu trí tuệ</h2>
                <p class="mt-3">
                    Nhãn hiệu, logo, giao diện, mã nguồn, cơ sở dữ liệu (trong phạm vi được bảo hộ) và tài sản trí tuệ
                    khác của ViVu thuộc về chủ sở hữu Dịch vụ hoặc bên cấp phép. Bạn không được sao chép, sửa đổi,
                    phân phối, đảo ngược (reverse engineer) trái phép.
                </p>
                <p class="mt-3">
                    Nếu bạn cho rằng Nội dung trên Dịch vụ xâm phạm quyền SHTT của mình, hãy gửi thông báo kèm bằng chứng
                    theo mục Liên hệ. Chúng tôi sẽ xem xét và xử lý theo quy định pháp luật.
                </p>
            </section>

            <section id="s8" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">8. Miễn trừ & giới hạn trách nhiệm</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5">
                    <li>Dịch vụ được cung cấp theo nguyên tắc <strong>“như hiện có” (as is)</strong> trong phạm vi pháp luật cho phép. Chúng tôi nỗ lực duy trì ổn định nhưng không cam kết không gián đoạn, không lỗi.</li>
                    <li>Nội dung do người dùng đăng là ý kiến/trải nghiệm cá nhân; ViVu không xác nhận tính chính xác tuyệt đối của mọi đánh giá địa điểm.</li>
                    <li>Trong phạm vi pháp luật cho phép, Chúng tôi không chịu trách nhiệm đối với thiệt hại gián tiếp, mất lợi nhuận, mất dữ liệu phát sinh từ việc sử dụng hoặc không thể sử dụng Dịch vụ, trừ trường hợp do lỗi cố ý hoặc vi phạm nghĩa vụ theo luật bắt buộc.</li>
                    <li>Đối với quan hệ tiêu dùng, quyền của người tiêu dùng theo Luật Bảo vệ quyền lợi người tiêu dùng 2023 vẫn được tôn trọng; các điều khoản miễn trừ không được giải thích theo hướng tước bỏ quyền bất khả xâm phạm của người tiêu dùng.</li>
                </ol>
            </section>

            <section id="s9" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">9. Chấm dứt / tạm ngưng dịch vụ</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5">
                    <li>Bạn có thể ngừng sử dụng và yêu cầu xoá tài khoản theo quy trình hỗ trợ (xem Chính sách dữ liệu).</li>
                    <li>Chúng tôi có thể tạm ngưng hoặc chấm dứt quyền truy cập khi bạn vi phạm Điều khoản, pháp luật, hoặc khi cần bảo vệ an toàn hệ thống / người dùng khác.</li>
                    <li>Chúng tôi có thể thay đổi, tạm ngừng một phần tính năng với thông báo hợp lý khi điều kiện kỹ thuật/kinh doanh thay đổi.</li>
                </ul>
            </section>

            <section id="s10" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">10. Giải quyết tranh chấp</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5">
                    <li>Ưu tiên giải quyết thông qua thương lượng, khiếu nại nội bộ trong thời hạn hợp lý sau khi nhận được yêu cầu hợp lệ.</li>
                    <li>Nếu không đạt được thoả thuận, tranh chấp được giải quyết tại cơ quan có thẩm quyền của Việt Nam theo quy định pháp luật.</li>
                    <li>Luật áp dụng: <strong>pháp luật nước Cộng hoà xã hội chủ nghĩa Việt Nam</strong>.</li>
                </ol>
            </section>

            <section id="s11" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">11. Sửa đổi điều khoản</h2>
                <p class="mt-3">
                    Chúng tôi có thể cập nhật Điều khoản để phù hợp pháp luật hoặc thay đổi Dịch vụ. Phiên bản mới sẽ
                    được đăng trên trang này kèm ngày hiệu lực. Việc tiếp tục sử dụng sau khi Điều khoản có hiệu lực
                    được hiểu là bạn chấp nhận phiên bản cập nhật, trừ khi pháp luật yêu cầu hình thức đồng ý khác.
                </p>
            </section>

            <section id="s12" class="rounded-2xl border border-stone-200/80 bg-white/90 p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-stone-900">12. Liên hệ</h2>
                <p class="mt-3">Mọi thắc mắc, khiếu nại về Điều khoản sử dụng, vui lòng liên hệ:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Email hỗ trợ: <a href="mailto:support@vivu.test" class="text-teal-700 hover:underline">support@vivu.test</a> (cập nhật email chính thức khi triển khai production)</li>
                    <li>Kênh trong ứng dụng / form liên hệ (nếu được cung cấp)</li>
                </ul>
            </section>
        </div>

        <p class="mt-10 text-center text-sm text-stone-500">
            Xem thêm:
            <a href="{{ route('pages.privacy') }}" class="text-teal-700 hover:underline">Bảo vệ dữ liệu cá nhân</a> ·
            <a href="{{ route('pages.community') }}" class="text-teal-700 hover:underline">Quy tắc cộng đồng</a> ·
            <a href="{{ route('pages.cookies') }}" class="text-teal-700 hover:underline">Cookie</a>
        </p>
    </article>
@endsection
