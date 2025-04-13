@extends('layouts.app')

@section('title', 'Chính sách bảo mật - Phương Thanh Express')

@section('content')
    <!-- Nội dung Chính sách bảo mật -->
    <section class="container mx-auto p-8 bg-white shadow-xl rounded-2xl my-10">
        <h2 class="text-4xl font-bold section-title text-center mb-8">CHÍNH SÁCH BẢO MẬT</h2>
        <div class="text-gray-700 leading-relaxed space-y-6">
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">1. Mục đích thu thập thông tin</h3>
                <p class="mt-2">
                    Tại <strong>Phương Thanh Express</strong>, chúng tôi cam kết bảo vệ thông tin cá nhân của Quý khách. Thông tin được thu thập nhằm mục đích cung cấp dịch vụ vận chuyển, đặt vé, gửi hàng và hỗ trợ khách hàng một cách hiệu quả nhất. Các thông tin này bao gồm: họ tên, số điện thoại, email, địa chỉ và thông tin thanh toán.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">2. Phạm vi thu thập thông tin</h3>
                <p class="mt-2">
                    Chúng tôi chỉ thu thập thông tin khi Quý khách sử dụng dịch vụ trên website, ứng dụng hoặc liên hệ trực tiếp qua hotline. Thông tin được thu thập sẽ được sử dụng trong phạm vi các hoạt động kinh doanh của <strong>Phương Thanh Express</strong> và không vượt quá mục đích đã nêu.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">3. Thời gian lưu trữ thông tin</h3>
                <p class="mt-2">
                    Thông tin cá nhân của Quý khách sẽ được lưu trữ trong thời gian cần thiết để thực hiện các dịch vụ hoặc theo yêu cầu của pháp luật. Sau khi không còn cần thiết, thông tin sẽ được xóa bỏ hoặc ẩn danh để đảm bảo an toàn.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">4. Bảo mật thông tin</h3>
                <p class="mt-2">
                    Chúng tôi áp dụng các biện pháp kỹ thuật và tổ chức tiên tiến để bảo vệ thông tin cá nhân khỏi truy cập trái phép, mất mát hoặc lạm dụng. Dữ liệu được mã hóa trong quá trình truyền tải và lưu trữ trên hệ thống an toàn.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">5. Chia sẻ thông tin</h3>
                <p class="mt-2">
                    <strong>Phương Thanh Express</strong> cam kết không bán, trao đổi hoặc chia sẻ thông tin cá nhân của Quý khách với bên thứ ba, trừ khi có sự đồng ý của Quý khách hoặc theo yêu cầu của cơ quan pháp luật.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">6. Quyền của khách hàng</h3>
                <p class="mt-2">
                    Quý khách có quyền yêu cầu xem, chỉnh sửa hoặc xóa thông tin cá nhân của mình bất kỳ lúc nào. Để thực hiện, vui lòng liên hệ qua email <strong>phuongthanh@gmail.com</strong> hoặc hotline <strong>0905.999999</strong>.
                </p>
            </div>
            <div>
                <h3 class="text-2xl font-semibold text-orange-600">7. Thay đổi chính sách</h3>
                <p class="mt-2">
                    Chính sách bảo mật này có thể được cập nhật để phù hợp với quy định pháp luật hoặc cải thiện dịch vụ. Mọi thay đổi sẽ được thông báo trên website chính thức của chúng tôi.
                </p>
            </div>
            <div class="text-center mt-8">
                <p class="font-bold text-orange-600 italic text-lg">
                    Cảm ơn Quý khách đã tin tưởng và đồng hành cùng <strong>Phương Thanh Express</strong>!
                </p>
                <a href="{{ route('home') }}" class="mt-6 inline-block btn-modern text-white px-6 py-3 rounded-lg">Quay lại Trang Chủ</a>
            </div>
        </div>
    </section>
@endsection
