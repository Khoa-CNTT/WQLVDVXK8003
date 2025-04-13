@extends('layouts.app')

@section('title', 'Vé của tôi - Phương Thanh Express')

@section('content')
<div class="container mx-auto my-10 px-4">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl font-bold text-orange-600 mb-6">Vé của tôi</h2>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Sidebar -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full bg-orange-500 flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-xl">{{ Auth::user()->name }}</h3>
                            <p class="text-gray-600">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <ul>
                        <li>
                            <a href="{{ route('profile') }}" class="flex items-center py-2 px-4 rounded hover:bg-orange-50 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Thông tin cá nhân
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('bookings') }}" class="flex items-center py-2 px-4 rounded hover:bg-orange-50 text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Vé của tôi
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('password.change') }}" class="flex items-center py-2 px-4 rounded hover:bg-orange-50 text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Đổi mật khẩu
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="flex items-center py-2 px-4 rounded hover:bg-orange-50 text-gray-700 w-full text-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Đăng xuất
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="md:col-span-2 bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold">Danh sách vé của tôi</h3>
                    <p class="text-gray-600 text-sm">Quản lý tất cả vé xe bạn đã đặt</p>
                </div>
                <div class="overflow-x-auto">
                    @if(count($bookings) > 0)
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã vé</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tuyến đường</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày đi</th>
                                <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="py-3 px-6 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($bookings as $booking)
                            <tr>
                                <td class="py-4 px-6 text-sm font-medium text-gray-900">{{ $booking->booking_code }}</td>
                                <td class="py-4 px-6 text-sm text-gray-500">{{ $booking->trip->route->departure }} - {{ $booking->trip->route->destination }}</td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    <div>{{ \Carbon\Carbon::parse($booking->trip->departure_date)->format('d/m/Y') }}</div>
                                    <div class="text-xs">{{ $booking->trip->departure_time }}</div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-500">
                                    @if($booking->status == 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Chờ thanh toán
                                        </span>
                                    @elseif($booking->status == 'confirmed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Đã xác nhận
                                        </span>
                                    @elseif($booking->status == 'cancelled')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Đã hủy
                                        </span>
                                    @elseif($booking->status == 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Hoàn thành
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-center">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Xem</a>

                                    @if($booking->status == 'pending' || ($booking->status == 'confirmed' && \Carbon\Carbon::parse($booking->trip->departure_date)->subHours(24)->isFuture()))
                                    <a href="{{ route('bookings.cancel', $booking->id) }}" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc chắn muốn hủy vé này?')">Hủy vé</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <p class="mt-4 text-gray-500">Bạn chưa có vé nào. Hãy đặt vé để bắt đầu hành trình!</p>
                        <a href="{{ route('home') }}" class="mt-4 inline-block btn-modern text-white px-6 py-2 rounded-lg">Đặt vé ngay</a>
                    </div>
                    @endif
                </div>

                @if(count($bookings) > 0 && $bookings->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $bookings->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
