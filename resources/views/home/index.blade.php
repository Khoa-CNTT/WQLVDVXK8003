<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phương Thanh Express</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }
    .group:hover .group-hover\:scale-y-100 {
        transform: scaleY(1);
    }
    nav ul ul {
        background: linear-gradient(to bottom, #ffffff, #f9fafb);
        border: 1px solid #f97316;
        padding: 0.5rem 0;
    }
    nav ul ul li a {
        font-size: 0.95rem;
        color: #333;
    }
    nav ul ul li a:hover {
        background: #f97316;
        color: white;
    }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .slider-image {
            transition: opacity 0.5s ease-in-out, transform 0.5s ease;
        }
        .fade-out {
            opacity: 0;
            transform: scale(0.95);
        }
        .btn-modern {
            transition: all 0.3s ease;
            background: linear-gradient(to right, #f97316, #ea580c);
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        .section-title {
            background: linear-gradient(to right, #f97316, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        header, footer {
            background: linear-gradient(to right, #f97316, #ea580c);
        }
    </style>
</head>
<body>
<!-- Header -->
<header class="text-white py-6 shadow-lg">
    <div class="container mx-auto flex justify-between items-center px-6">
        <h1 class="text-3xl font-bold tracking-tight">Phương Thanh Express</h1>
        <nav>
            <ul class="flex space-x-8">
                <li><a href="#" class="hover:text-orange-200 transition duration-300">Trang Chủ</a></li>
                <!-- Menu Tuyến Hoạt Động với Dropdown -->
                <li class="relative group">
                    <a href="#" class="hover:text-orange-200 transition duration-300 flex items-center">Tuyến Hoạt Động
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                    <ul class="absolute left-0 mt-2 w-56 bg-white text-gray-800 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transform group-hover:scale-y-100 scale-y-0 transition-all duration-300 origin-top z-50">
                        <li><a href="#route1" onclick="viewRouteDetail('Đà Nẵng', 'Quảng Bình')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Quảng Bình</a></li>
                        <li><a href="#route2" onclick="viewRouteDetail('Đà Nẵng', 'Nghệ An')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Nghệ An</a></li>
                        <li><a href="#route3" onclick="viewRouteDetail('Đà Nẵng', 'Hà Giang')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Hà Giang</a></li>
                        <li><a href="#route4" onclick="viewRouteDetail('Đà Nẵng', 'Hồ Chí Minh')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - HCM</a></li>
                    </ul>
                </li>
                <li><a href="promotions.html" class="hover:text-orange-200 transition duration-300">Khuyến Mãi</a></li>
                <li><a href="recruitment.html" class="hover:text-orange-200 transition duration-300">Tuyển Dụng</a></li>
                <li><a href="utilities.html" class="hover:text-orange-200 transition duration-300">Tiện ích</a></li>
            </ul>
        </nav>
    </div>
</header>

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
                        <li>📦 <strong>Gửi hàng:</strong> <span class="text-red-600">0905.888.888</span> (Trí)</li>
                        <li>🚛 <strong>Thuê xe chở hàng:</strong> <span class="text-red-600">0905.1111.11</span></li>
                        <li>📜 <strong>Hợp đồng thuê xe:</strong> <span class="text-red-600">0905.2222.22</span> (Huy)</li>
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
        <form class="mt-8 bg-white shadow-2xl rounded-2xl p-8 max-w-lg mx-auto" id="booking-form">
            <div class="mb-6">
                <label for="departure" class="block text-left font-semibold text-gray-700">Nơi đi:</label>
                <select id="departure" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="Đà Nẵng">Đà Nẵng</option>
                    <option value="Quảng Bình">Quảng Bình</option>
                    <option value="Nghệ An">Nghệ An</option>
                    <option value="Hà Giang">Hà Giang</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="destination" class="block text-left font-semibold text-gray-700">Nơi đến:</label>
                <select id="destination" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500">
                    <option value="Quảng Bình">Quảng Bình</option>
                    <option value="Nghệ An">Nghệ An</option>
                    <option value="Hà Giang">Hà Giang</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Đà Nẵng">Đà Nẵng</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="date" class="block text-left font-semibold text-gray-700">Ngày đi:</label>
                <input type="date" id="date" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-500">
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
                <button onclick="window.location.href='register.html'" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Đăng ký</button>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 3</h3>
                <p class="mt-2">Quản lý vé xe</p>
                <button class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Quản lý vé xe</button>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BƯỚC 4</h3>
                <p class="mt-2">Xem vị trí trên Google Maps</p>
                <button onclick="window.location.href='location.html'"class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem vị trí</button>
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
                <button onclick="viewRouteList('Đà Nẵng', 'Quảng Bình')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - Nghệ An</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteList('Đà Nẵng', 'Nghệ An')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - Hà Giang</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteList('Đà Nẵng', 'Hà Giang')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
            </div>
            <div class="card bg-orange-600 p-6 rounded-xl text-white">
                <p class="font-bold text-lg">Đà Nẵng - HCM</p>
                <p class="text-sm mt-2">Thông tin xe, lịch trình, giá vé</p>
                <button onclick="viewRouteList('Đà Nẵng', 'Hồ Chí Minh')" class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Xem chi tiết</button>
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
                <button class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Tìm hiểu ngay</button>
            </div>
            <div class="card border-2 border-orange-600 p-6 bg-white rounded-xl">
                <h3 class="font-bold text-xl text-orange-600">BLIND BOX</h3>
                <ul class="text-left mt-4 space-y-2">
                    <li class="flex items-center">🎁 <span class="ml-2"><strong>1 iPhone 15</strong> phiên bản mới nhất.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Hơn <strong>5000</strong> mã giảm giá có mệnh giá lên tới <strong>100.000đ</strong>.</span></li>
                    <li class="flex items-center">🎁 <span class="ml-2">Nhiều <strong>phần quà nhỏ khác</strong> đang chờ bạn khám phá.</span></li>
                </ul>
                <button class="mt-4 btn-modern text-white px-6 py-2 rounded-lg">Tìm hiểu ngay</button>
            </div>
        </div>
    </section>

    <!-- Chatbot -->
    <div id="chatbot" class="fixed bottom-5 right-5 w-80 bg-white shadow-2xl rounded-xl hidden flex-col z-50">
        <div class="bg-gradient-to-r from-orange-600 to-orange-800 text-white p-4 rounded-t-xl flex justify-between items-center">
            <h3 class="font-bold">Hỗ trợ Phương Thanh Express</h3>
            <button id="closeChatbot" class="text-white hover:text-orange-200">✖</button>
        </div>
        <div id="chatbotMessages" class="p-4 h-64 overflow-y-auto bg-gray-50 flex flex-col gap-2"></div>
        <div class="p-4 border-t">
            <input id="chatbotInput" type="text" placeholder="Nhập câu hỏi của bạn..." class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-orange-500">
        </div>
    </div>
    <button id="openChatbot" class="fixed bottom-5 right-5 bg-gradient-to-r from-orange-600 to-orange-800 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition-all">
        💬 Chat
    </button>

    <!-- Footer -->
    <footer class="text-white py-10">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 px-6">
            <div>
                <h3 class="font-bold text-xl">NHÀ XE PHƯƠNG THANH ĐÀ NẴNG</h3>
                <div class="flex space-x-6 mt-4">
                    <a href="#" class="text-white text-2xl hover:text-orange-200 transition">📘</a>
                    <a href="#" class="text-white text-2xl hover:text-orange-200 transition">❌</a>
                    <a href="#" class="text-white text-2xl hover:text-orange-200 transition">▶️</a>
                    <a href="#" class="text-white text-2xl hover:text-orange-200 transition">🔗</a>
                </div>
                <div class="mt-4">
                    <iframe src="https://www.google.com/maps/embed?..." class="w-full h-32 rounded-xl"></iframe>
                </div>
            </div>
            <div>
                <h3 class="font-bold text-xl">CHÍNH SÁCH CÔNG TY</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="#" class="hover:text-orange-200 transition">Giới thiệu</a></li>
                    <li><a href="#" class="hover:text-orange-200 transition">Liên hệ</a></li>
                    <li><a href="#" class="hover:text-orange-200 transition">Điều khoản sử dụng</a></li>
                    <li><a href="#" class="hover:text-orange-200 transition">Chính sách vận chuyển</a></li>
                    <li><a href="security.html" class="hover:text-orange-200 transition">Chính sách bảo mật</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold text-xl">PHƯƠNG THỨC THANH TOÁN</h3>
                <div class="mt-4 flex space-x-4">
                    <img src="https://th.bing.com/th?q=Momo+Icon+App+PNG&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" class="w-16">
                    <img src="https://th.bing.com/th?q=Vnpay+Logo.png&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" class="w-16">
                </div>
            </div>
            <div>
                <h3 class="font-bold text-xl">LIÊN HỆ</h3>
                <p class="mt-4">Công ty TNHH Vận Tải <strong>Phương Thanh</strong></p>
                <p class="mt-2">12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.</p>
                <p class="mt-2">📞 Mã số thuế: <strong>1111111</strong></p>
                <p class="mt-2">📞 Hotline: <strong>0905.999999</strong></p>
                <p class="mt-2">✉️ Email: <strong>phuongthanh@gmail.com</strong></p>
            </div>
        </div>
        <div class="text-center mt-6 border-t border-orange-400 pt-4">© Copyright 2025. Phương Thanh Express</div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Hàm xem chi tiết tuyến
    function viewRouteDetail(departure, destination) {
        const today = new Date().toISOString().split("T")[0];
        const url = `booking-results.html?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(today)}`;
        window.location.href = url;
    }
        document.getElementById("booking-form").addEventListener("submit", function(event) {
            event.preventDefault();
            const departure = document.getElementById("departure").value;
            const destination = document.getElementById("destination").value;
            const date = document.getElementById("date").value;
            const today = new Date().toISOString().split("T")[0];

            if (date < today) {
                document.getElementById("booking-result").innerText = "Không được chọn ngày trong quá khứ.";
                document.getElementById("booking-result").classList.remove("text-green-600");
                document.getElementById("booking-result").classList.add("text-red-600");
                return;
            }

            if (departure && destination && date) {
                const url = `booking-results.html?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(date)}`;
                window.location.href = url;
            } else {
                document.getElementById("booking-result").innerText = "Vui lòng chọn đầy đủ thông tin.";
                document.getElementById("booking-result").classList.remove("text-green-600");
                document.getElementById("booking-result").classList.add("text-red-600");
            }
        });

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

        // Hàm xem danh sách chuyến xe theo tuyến
        function viewRouteList(departure, destination) {
            const today = new Date().toISOString().split("T")[0]; // Ngày hiện tại làm mặc định
            const url = `booking-results.html?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(today)}`;
            window.location.href = url;
        }

        // Chatbot functionality
        document.addEventListener("DOMContentLoaded", function () {
            const chatbot = document.getElementById("chatbot");
            const openChatbotBtn = document.getElementById("openChatbot");
            const closeChatbotBtn = document.getElementById("closeChatbot");
            const chatbotInput = document.getElementById("chatbotInput");
            const chatbotMessages = document.getElementById("chatbotMessages");

            openChatbotBtn.addEventListener("click", function () {
                chatbot.classList.remove("hidden");
                openChatbotBtn.classList.add("hidden");
            });

            closeChatbotBtn.addEventListener("click", function () {
                chatbot.classList.add("hidden");
                openChatbotBtn.classList.remove("hidden");
            });

            const responses = {
                "xin chào": "Chào bạn! Phương Thanh Express rất vui được hỗ trợ bạn hôm nay.",
                "đặt vé thế nào": "Bạn có thể đặt vé trực tuyến qua mục 'ĐẶT VÉ XE TRỰC TUYẾN' trên trang chủ hoặc gọi hotline 0905.3333.33.",
                "gửi hàng ở đâu": "Để gửi hàng, vui lòng liên hệ 0905.888.888 (Anh Mạnh) hoặc đến trực tiếp văn phòng tại Đà Nẵng.",
                "tuyến nào đang hoạt động": "Hiện tại chúng tôi có các tuyến: Đà Nẵng - Quảng Bình, Đà Nẵng - Nghệ An, Đà Nẵng - Hà Giang, Đà Nẵng - HCM.",
                "khuyến mãi có gì": "Chúng tôi có chương trình giảm giá cho khách hàng thân thiết (10-40%) và Blind Box với quà tặng hấp dẫn như iPhone 15.",
                "default": "Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi cụ thể hơn hoặc liên hệ hotline 0905.999999 để được hỗ trợ!"
            };

            chatbotInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter" && chatbotInput.value.trim()) {
                    const userMessage = chatbotInput.value.trim().toLowerCase();
                    addMessage(userMessage, "user");
                    chatbotInput.value = "";

                    setTimeout(() => {
                        const botResponse = responses[userMessage] || responses["default"];
                        addMessage(botResponse, "bot");
                    }, 500);
                }
            });

            function addMessage(text, type) {
                const messageDiv = document.createElement("div");
                messageDiv.classList.add("message", type);
                messageDiv.textContent = text;
                chatbotMessages.appendChild(messageDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        });
    </script>
</body>
</html>
