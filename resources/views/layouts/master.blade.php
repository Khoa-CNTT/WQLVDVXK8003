<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Phương Thanh Express')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="{{ route('home') }}">Trang Chủ</a></li>
                <li><a href="{{ route('bookings') }}">Đặt Vé</a></li>
                <li><a href="{{ route('promotions') }}">Khuyến Mãi</a></li>
                <li><a href="{{ route('recruitment') }}">Tuyển Dụng</a></li>
                <li><a href="{{ route('utilities') }}">Tiện Ích</a></li>
                <li><a href="{{ route('login') }}">Đăng Nhập</a></li>
            </ul>
        </nav>
    </header>

    @yield('content')

    <footer>
        © {{ date('Y') }} Phương Thanh Express
    </footer>
</body>
</html>
