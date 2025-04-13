@extends('layouts.app')

@section('title', 'Tiện ích - Phương Thanh Express')

@section('styles')
.utility-image {
    transition: transform 0.3s ease;
}
.utility-image:hover {
    transform: scale(1.05);
}
@endsection

@section('content')
    <!-- Giới thiệu tiện ích -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">TIỆN ÍCH TRÊN XE</h2>
        <p class="mt-4 text-lg text-gray-600">Chúng tôi luôn nỗ lực mang đến cho khách hàng những trải nghiệm thoải mái và tiện nghi nhất trên mọi hành trình.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            <!-- Tiện ích 1: Wi-Fi miễn phí -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="http://thietbiwifi4g.com/assets/shops/wifi_4g/wifi_xekhach/lap-dat-wifi-xe-khach-1.jpg" alt="Wi-Fi miễn phí" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Wi-Fi Miễn Phí</h3>
                <p class="mt-2 text-gray-600">Kết nối internet tốc độ cao suốt hành trình để bạn luôn giữ liên lạc hoặc giải trí.</p>
            </div>
            <!-- Tiện ích 2: Giường nằm cao cấp -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-1.jpg" alt="Giường nằm cao cấp" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Giường Nằm Cao Cấp</h3>
                <p class="mt-2 text-gray-600">Giường nằm êm ái, rộng rãi với chăn gối sạch sẽ, mang lại giấc ngủ trọn vẹn.</p>
            </div>
            <!-- Tiện ích 3: Nước uống miễn phí -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/09/noi-that-xe-hieu-hoa-1.jpg" alt="Nước uống miễn phí" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Nước Uống Miễn Phí</h3>
                <p class="mt-2 text-gray-600">Cung cấp nước uống miễn phí suốt chuyến đi để bạn luôn sảng khoái.</p>
            </div>
            <!-- Tiện ích 4: Điều hòa mát lạnh -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="https://tanquangdung.com/wp-content/uploads/2024/05/tan-quang-dung-6-1200x700.jpg" alt="Điều hòa mát lạnh" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Điều Hòa Mát Lạnh</h3>
                <p class="mt-2 text-gray-600">Hệ thống điều hòa hiện đại, đảm bảo không gian thoáng mát trong suốt hành trình.</p>
            </div>
            <!-- Tiện ích 5: Xe trung chuyển -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/xe-trung-chuyen-hieu-hoa.jpg" alt="Xe trung chuyển" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Xe Trung Chuyển</h3>
                <p class="mt-2 text-gray-600">Hỗ trợ đưa đón tận nơi tại các điểm trung chuyển thuận tiện.</p>
            </div>
            <!-- Tiện ích 6: Hệ thống giải trí -->
            <div class="card bg-white p-6 rounded-xl shadow-lg">
                <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-2.jpg" alt="Hệ thống giải trí" class="w-full h-48 object-cover rounded-t-xl utility-image">
                <h3 class="mt-4 font-bold text-xl text-orange-600">Hệ Thống Giải Trí</h3>
                <p class="mt-2 text-gray-600">Màn hình LED và tai nghe cá nhân để bạn thư giãn với phim ảnh, âm nhạc.</p>
            </div>
        </div>
    </section>

    <!-- Hỗ trợ khách hàng -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">HỖ TRỢ KHÁCH HÀNG</h2>
        <p class="mt-4 text-lg text-gray-600">Liên hệ với chúng tôi để được tư vấn thêm về các tiện ích và dịch vụ.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="tel:0905999999" class="btn-modern text-white px-6 py-3 rounded-lg inline-block">📞 Gọi hotline: 0905.999999</a>
            <button class="btn-modern text-white px-6 py-3 rounded-lg" onclick="document.getElementById('chatbot').classList.remove('hidden'); document.getElementById('openChatbot').classList.add('hidden');">💬 Chat ngay</button>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    // Thêm script cụ thể cho trang utilities nếu cần
</script>
@endsection
