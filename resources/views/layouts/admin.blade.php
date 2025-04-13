<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Phương Thanh Express')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }
        .sidebar {
            background: linear-gradient(to bottom, #f97316, #ea580c);
        }
        .active-nav-link {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        @yield('styles')
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 text-white flex flex-col fixed h-full">
        <div class="p-4 border-b border-white/20">
            <h1 class="text-2xl font-bold">Admin Panel</h1>
            <p class="text-sm opacity-70">Phương Thanh Express</p>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.dashboard') ? 'active-nav-link' : '' }}">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.routes') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.routes*') ? 'active-nav-link' : '' }}">
                        🛣️ Quản lý tuyến đường
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.buses') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.buses*') ? 'active-nav-link' : '' }}">
                        🚌 Quản lý xe
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.drivers') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.drivers*') ? 'active-nav-link' : '' }}">
                        👨‍✈️ Quản lý tài xế
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.trips') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.trips*') ? 'active-nav-link' : '' }}">
                        🗓️ Quản lý chuyến đi
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.bookings') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.bookings*') ? 'active-nav-link' : '' }}">
                        🎫 Quản lý vé
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.users*') ? 'active-nav-link' : '' }}">
                        👥 Quản lý người dùng
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.promotions') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.promotions*') ? 'active-nav-link' : '' }}">
                        🎁 Quản lý khuyến mãi
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.reports*') ? 'active-nav-link' : '' }}">
                        📝 Báo cáo thống kê
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.settings') }}" class="nav-link block py-2 px-4 {{ request()->routeIs('admin.settings*') ? 'active-nav-link' : '' }}">
                        ⚙️ Cài đặt
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-white/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/30 flex items-center justify-center">
                    <span class="text-lg">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <p class="font-medium">{{ Auth::user()->name }}</p>
                    <div class="flex space-x-3 text-sm mt-1">
                        <a href="{{ route('home') }}" class="opacity-70 hover:opacity-100">Trang chủ</a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="opacity-70 hover:opacity-100">Đăng xuất</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 ml-64">
        <!-- Top Bar -->
        <div class="bg-white p-4 shadow flex justify-between items-center">
            <h2 class="text-xl font-semibold">@yield('page-title', 'Dashboard')</h2>
            <div>
                <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded">
                    Admin
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
