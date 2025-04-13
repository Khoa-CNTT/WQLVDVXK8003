@extends('layouts.app')

@section('title', 'Trang Chủ - Phương Thanh Express')

@section('styles')
.slider-image {
    transition: opacity 0.5s ease-in-out, transform 0.5s ease;
}
.fade-out {
    opacity: 0;
    transform: scale(0.95);
}
@endsection

@section('content')
    <!-- Giới thiệu -->
    <section class="container mx-auto p-8 bg-white shadow-xl rounded-2xl my-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div>
                <h2 class="text-4xl font-bold section-title">Giới thiệu <span class="text-black">PHƯƠNG THANH EXPRESS</span></h2>
                <p class="mt-4 text-gray-600 text-lg leading-relaxed">
                    Trải qua <strong class="text-orange-600">hơn 10 năm</strong> hoạt động, Công ty Cổ phần vận tải <strong>Phương Thanh</strong> với thương hiệu <strong class="text-orange-600">Phương Thanh Express</strong> đã góp phần thúc đẩy giao thương giữa <strong class="text-orange-600">TP. Đà Nẵng</strong> và các tỉnh phía Bắc.
                </p>
                <p class="mt-2 text-gray-600 text-lg leading-relaxed">
                    Chúng tôi cam kết <strong>sự an toàn</strong>, <strong>chất lượng dịch vụ</strong> và luôn lựa chọn <strong>đội ngũ nhân viên</strong> tận tâm để đảm bảo khách hàng có <strong>sự hài lòng, an tâm</strong> và <strong>trải nghiệm tốt nhất</strong>.
                </p>
                <p class="mt-2 text-gray-600 text-lg leading-relaxed">
                    Cảm ơn Quý khách đã <strong class="text-orange-600">luôn tin cậy</strong> và ủng hộ chúng tôi. Chúng tôi không ngừng <strong>cải thiện</strong> để phục vụ Quý khách tốt hơn.
                </p>
                <p class="mt-4 font-bold text-orange-600 italic text-lg">Kính chúc Quý Khách luôn <span class="text-red-600">Bình An Trên Vạn Dặm.</span></p>
                <div class="mt-6 p-6 border-2 border-orange-500 rounded-xl bg-gray-50">
                    <h3 class="text-2xl font-bold text-orange-600">📞 Thông tin liên hệ</h3>
                    <ul class="mt-4 space-y-3 text-lg text-gray-700">
                        <li>📌 <strong>Đặt vé:</strong> <span class="text-red-600">0905.3333.33</span></li>
                        <li>📦 <strong>Gửi hàng:</strong> <span class="text-red-600">0905.888.888</span> (Mạnh)</li>
                        <li>🚛 <strong>Thuê xe chở hàng:</strong> <span class="text-red-600">0905.1111.11</span></li>
                        <li>📜 <strong>Hợp đồng thuê xe:</strong> <span class="text-red-600">0905.2222.22</span> (Hùng)</li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col items-center">
                <div class="relative w-full max-w-lg h-80 overflow-hidden rounded-xl shadow-lg">
                    <img id="animatedImage" src="https://phongvevip.com/wp-content/uploads/2021/10/95a266c38a9a71c4288b-768x576.jpg" alt="Xe khách Phương Thanh Express" class="slider-image w-full h-full object-cover">
                </div>
                <div class="mt-6 flex space-x-4">
                    <button id="prevBtn" class="bg-gray-200 px-4 py-2 rounded-full hover:bg-gray-300">◀</button>
                    <button id="playBtn" class="bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600">▶</button>
                    <button id="pauseBtn" class="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600">⏸</button>
                    <button id="nextBtn" class="bg-gray-200 px-4 py-2 rounded-full hover:bg-gray-300">▶</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Đặt vé xe trực tuyến -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">ĐẶT VÉ XE TRỰC TUYẾN</h2>
        <p class="text-lg font-semibold text-gray-600 mt-2">📞 Tổng đài hỗ trợ: <span class="text-orange-600">0905.999999</span></p>
        <form action="{{ route('booking.search') }}" method="GET" class="mt-8 bg-white shadow-2xl rounded-2xl p-8 max-w-lg mx-auto" id="booking-form">
            <div class="mb-6">
                <label for="departure" class="block text-left font-semibold text-gray-700">Nơi đi:</label>
                <select id="departure" name="departure" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="Đà Nẵng">Đà Nẵng</option>
                    <option value="Quảng Bình">Quảng Bình</option>
                    <option value="Nghệ An">Nghệ An</option>
                    <option value="Hà Giang">Hà Giang</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="destination" class="block text-left font-semibold text-gray-700">Nơi đến:</label>
                <select id="destination" name="destination" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="Quảng Bình">Quảng Bình</option>
                    <option value="Nghệ An">Nghệ An</option>
                    <option value="Hà Giang">Hà Giang</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Đà Nẵng">Đà Nẵng</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="date" class="block text-left font-semibold text-gray-700">Ngày đi:</label>
                <input type="date" id="date" name="date" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
            </div>
            <button type="submit" class="btn-modern text-white px-6 py-3 rounded-lg w-full">Tìm chuyến xe</button>
        </form>
        <div id="booking-result" class="mt-4 text-green-600 font-bold"></div>
    </section>

    <!-- Hướng dẫn đón xe không đợi chờ -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">ĐÓN XE KHÔNG ĐỢI CHỜ</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 1</h3>
                <p class="mt-2"><strong>KHTT.EXPRESS.COM</strong></p>
                <button class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Đi tới KHTT</button>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 2</h3>
                <p class="mt-2">Đăng ký tài khoản</p>
                <a href="{{ route('register') }}" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg inline-block">Đăng ký</a>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 3</h3>
                <p class="mt-2">Quản lý vé xe</p>
                <a href="{{ route('bookings') }}" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg inline-block">Quản lý vé xe</a>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 4</h3>
                <p class="mt-2">Xem vị trí trên Google Maps</p>
                <a href="{{ route('location') }}" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg inline-block">Xem vị trí</a>
            </div>
        </div>
    </section>

    <!-- Cơ sở vật chất -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">CƠ SỞ VẬT CHẤT</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-1.jpg" alt="Nội thất xe" class="w-full h-64 object-cover rounded-xl shadow-lg card">
            <img src="https://th.bing.com/th/id/OIP.vzTEjN22_1836csGgK-HSQHaER?w=292&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7" alt="Nội thất xe giường nằm" class="w-full h-64 object-cover rounded-xl shadow-lg card">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/xe-trung-chuyen-hieu-hoa.jpg" alt="Xe trung chuyển" class="w-full h-64 object-cover rounded-xl shadow-lg card">
        </div>
    </section>

    <!-- Danh sách tuyến hoạt động -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">DANH SÁCH TUYẾN HOẠT ĐỘNG</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - Quảng Bình</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteDetail('Đà Nẵng', 'Quảng Bình')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - Nghệ An</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteDetail('Đà Nẵng', 'Nghệ An')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - Hà Giang</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteDetail('Đà Nẵng', 'Hà Giang')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - HCM</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteDetail('Đà Nẵng', 'Hồ Chí Minh')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
        </div>
    </section>

    <!-- Chương trình khuyến mãi -->
    <section class="container mx-auto my-10 px-6 text-center">
        <h2 class="text-4xl font-bold section-title">CHƯƠNG TRÌNH KHUYẾN MÃI</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">KHÁCH HÀNG THÂN THIẾT</h3>
                <ul class="text-left mt-4 space-y-2">
                    <li class="flex items-center">🎁 <span class="ml-2">Giảm giá <strong>40%</strong> khi tích lũy được từ <strong>20 chuyến</strong>.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Giảm giá <strong>20%</strong> khi tích lũy được từ <strong>15 chuyến</strong>.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Giảm giá <strong>10%</strong> khi tích lũy được từ <strong>10 chuyến</strong>.</span></li>
                </ul>
                <a href="{{ route('promotions') }}" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg inline-block">Tìm hiểu ngay</a>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BLIND BOX</h3>
                <ul class="text-left mt-4 space-y-2">
                    <li class="flex items-center">🎁 <span class="ml-2"><strong>1 iPhone 15</strong> phiên bản mới nhất.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Hơn <strong>5000</strong> mã giảm giá có mệnh giá lên tới <strong>100.000đ</strong>.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Nhiều <strong>phần quà nhỏ khác</strong> đang chờ bạn khám phá.</span></li>
                </ul>
                <a href="{{ route('promotions') }}" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg inline-block">Tìm hiểu ngay</a>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const imageElement = document.getElementById("animatedImage");
        const images = [
            "https://img.pikbest.com/wp/202405/bus-station-white-coach-touring-parked-by-a-against-backdrop-3d-illustration_9847285.jpg!w700wp",
            "https://lh4.googleusercontent.com/proxy/Zwn6io0vGPYQPl0qVTFsH86pmMVf4LZBhO8h7LoZ-x3j2wpWPgJiWM-t6dsso_m2JFRnpwjOH0YqY72gcQStK_rBTDrU_7B5",
            "https://carshop.vn/wp-content/uploads/2022/07/anh-xe-giuong-nam-37.jpg"
        ];
        let index = 0;
        let interval;

        function changeImage(next = true) {
            imageElement.classList.add("fade-out");
            setTimeout(() => {
                index = next ? (index + 1) % images.length : (index - 1 + images.length) % images.length;
                imageElement.src = images[index];
                imageElement.classList.remove("fade-out");
            }, 500);
        }

        function startSlider() {
            interval = setInterval(() => changeImage(true), 3000);
        }

        function stopSlider() {
            clearInterval(interval);
        }

        document.getElementById("prevBtn").addEventListener("click", function () {
            stopSlider();
            changeImage(false);
        });

        document.getElementById("nextBtn").addEventListener("click", function () {
            stopSlider();
            changeImage(true);
        });

        document.getElementById("playBtn").addEventListener("click", function () {
            startSlider();
        });

        document.getElementById("pauseBtn").addEventListener("click", function () {
            stopSlider();
        });

        startSlider();
    });

    // Kiểm tra form tìm kiếm
    document.getElementById("booking-form").addEventListener("submit", function(event) {
        const departure = document.getElementById("departure").value;
        const destination = document.getElementById("destination").value;
        const date = document.getElementById("date").value;
        const today = new Date().toISOString().split("T")[0];

        if (departure === destination) {
            event.preventDefault();
            document.getElementById("booking-result").innerText = "Điểm đi và điểm đến không được trùng nhau.";
            document.getElementById("booking-result").classList.remove("text-green-600");
            document.getElementById("booking-result").classList.add("text-red-600");
            return;
        }

        if (date < today) {
            event.preventDefault();
            document.getElementById("booking-result").innerText = "Không được chọn ngày trong quá khứ.";
            document.getElementById("booking-result").classList.remove("text-green-600");
            document.getElementById("booking-result").classList.add("text-red-600");
            return;
        }
    });
</script>
@endsection
