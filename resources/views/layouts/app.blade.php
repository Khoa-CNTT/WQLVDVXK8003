<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Phương Thanh Express')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
        @yield('styles')
    </style>
    @yield('head-scripts')
</head>
<body>
    <!-- Header -->
    <header class="text-white py-6 shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-3xl font-bold tracking-tight">Phương Thanh Express</h1>
            <nav>
                <ul class="flex space-x-8">
                    <li><a href="{{ route('home') }}" class="hover:text-orange-200 transition duration-300">Trang Chủ</a></li>
                    <li class="relative group">
                        <a href="#" class="hover:text-orange-200 transition duration-300 flex items-center">Tuyến Hoạt Động
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul class="absolute left-0 mt-2 w-56 bg-white text-gray-800 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transform group-hover:scale-y-100 scale-y-0 transition-all duration-300 origin-top z-50">
                            <li><a href="#" onclick="viewRouteDetail('Đà Nẵng', 'Quảng Bình')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Quảng Bình</a></li>
                            <li><a href="#" onclick="viewRouteDetail('Đà Nẵng', 'Nghệ An')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Nghệ An</a></li>
                            <li><a href="#" onclick="viewRouteDetail('Đà Nẵng', 'Hà Giang')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - Hà Giang</a></li>
                            <li><a href="#" onclick="viewRouteDetail('Đà Nẵng', 'Hồ Chí Minh')" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Đà Nẵng - HCM</a></li>
                        </ul>
                    </li>
                    <li><a href="{{ route('promotions') }}" class="hover:text-orange-200 transition duration-300">Khuyến Mãi</a></li>
                    <li><a href="{{ route('recruitment') }}" class="hover:text-orange-200 transition duration-300">Tuyển Dụng</a></li>
                    <li><a href="{{ route('utilities') }}" class="hover:text-orange-200 transition duration-300">Tiện ích</a></li>
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-orange-200 transition duration-300">Đăng nhập</a></li>
                    @else
                        <li class="relative group">
                            <a href="#" class="hover:text-orange-200 transition duration-300 flex items-center">
                                {{ Auth::user()->name }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </a>
                            <ul class="absolute right-0 mt-2 w-56 bg-white text-gray-800 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transform group-hover:scale-y-100 scale-y-0 transition-all duration-300 origin-top z-50">
                                <li><a href="{{ route('profile') }}" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Thông tin cá nhân</a></li>
                                <li><a href="{{ route('bookings') }}" class="block px-4 py-2 hover:bg-orange-100 transition duration-200">Vé đã đặt</a></li>
                                <li>
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="block px-4 py-2 hover:bg-orange-100 transition duration-200">
                                        Đăng xuất
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

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
                    <li><a href="{{ route('home') }}" class="hover:text-orange-200 transition">Giới thiệu</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-orange-200 transition">Liên hệ</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-orange-200 transition">Điều khoản sử dụng</a></li>
                    <li><a href="{{ route('shipping') }}" class="hover:text-orange-200 transition">Chính sách vận chuyển</a></li>
                    <li><a href="{{ route('security') }}" class="hover:text-orange-200 transition">Chính sách bảo mật</a></li>
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
        <div class="text-center mt-6 border-t border-orange-400 pt-4">© Copyright {{ date('Y') }}. Phương Thanh Express</div>
    </footer>

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

    <!-- Scripts -->
    <script>
        // Hàm xem chi tiết tuyến
        function viewRouteDetail(departure, destination) {
            const today = new Date().toISOString().split("T")[0];
            window.location.href = "{{ route('booking.search') }}?departure=" + encodeURIComponent(departure) + "&destination=" + encodeURIComponent(destination) + "&date=" + today;
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

            chatbotInput.addEventListener("keypress", function (e) {
                if (e.key === "Enter" && chatbotInput.value.trim()) {
                    const userMessage = chatbotInput.value.trim();
                    addMessage(userMessage, "user");
                    chatbotInput.value = "";

                    // Gửi message đến server và nhận phản hồi
                    fetch("{{ route('chatbot.ask') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ message: userMessage })
                    })
                    .then(response => response.json())
                    .then(data => {
                        addMessage(data.response, "bot");
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        addMessage("Xin lỗi, hiện tại tôi không thể xử lý yêu cầu của bạn. Vui lòng thử lại sau.", "bot");
                    });
                }
            });

            function addMessage(text, type) {
                const messageDiv = document.createElement("div");
                messageDiv.classList.add("message", "p-2", "rounded", "mb-2");

                if (type === "user") {
                    messageDiv.classList.add("bg-blue-100", "text-right", "ml-auto");
                } else {
                    messageDiv.classList.add("bg-gray-100");
                }

                messageDiv.textContent = text;
                chatbotMessages.appendChild(messageDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
