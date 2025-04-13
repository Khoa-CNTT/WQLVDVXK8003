@extends('layouts.admin')

@section('title', 'Chi tiết vé - Phương Thanh Express')

@section('page-title', 'Chi tiết vé')

@section('content')
    <!-- Header thông tin vé -->
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Vé #{{ $booking->booking_code }}</h3>
        <div class="flex space-x-3">
            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Chỉnh sửa
            </a>
            <!-- Nút in vé -->
            <a href="{{ route('admin.bookings.print', $booking->id) }}" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                </svg>
                In vé
            </a>
            <!-- Dropdown menu cho các hành động khác -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    Thao tác
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <div class="py-1">
                        <!-- Gửi lại email xác nhận -->
                        <a href="{{ route('admin.bookings.resend-email', $booking->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Gửi lại email xác nhận</a>

                        <!-- Cập nhật trạng thái -->
                        @if($booking->status == 'pending')
                            <form action="{{ route('admin.bookings.update-status', [$booking->id, 'confirmed']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-gray-100">Xác nhận vé</button>
                            </form>
                        @endif

                        @if($booking->status != 'cancelled')
                            <form action="{{ route('admin.bookings.update-status', [$booking->id, 'cancelled']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Hủy vé</button>
                            </form>
                        @endif

                        @if($booking->status == 'confirmed' && !$booking->trip->is_completed)
                            <form action="{{ route('admin.bookings.update-status', [$booking->id, 'completed']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-gray-100">Hoàn thành</button>
                            </form>
                        @endif

                        <!-- Xóa vé -->
                        <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vé này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Xóa vé</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Thông tin vé và hành khách -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-orange-600">Thông tin vé</h4>
                        <div>
                            @if($booking->status == 'pending')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    Chờ thanh toán
                                </span>
                            @elseif($booking->status == 'confirmed')
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    Đã xác nhận
                                </span>
                            @elseif($booking->status == 'cancelled')
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    Đã hủy
                                </span>
                            @elseif($booking->status == 'completed')
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    Hoàn thành
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Mã vé</p>
                            <p class="font-medium">{{ $booking->booking_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Ngày đặt vé</p>
                            <p class="font-medium">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phương thức thanh toán</p>
                            <p class="font-medium">
                                @if($booking->payment_method == 'cod')
                                    Thanh toán khi lên xe
                                @elseif($booking->payment_method == 'vnpay')
                                    VNPAY
                                @else
                                    {{ $booking->payment_method }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Trạng thái thanh toán</p>
                            <p class="font-medium">
                                @if($booking->payment_status == 'paid')
                                    <span class="text-green-600">Đã thanh toán</span>
                                @else
                                    <span class="text-yellow-600">Chưa thanh toán</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-orange-600 mb-4">Thông tin hành khách</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Họ và tên</p>
                            <p class="font-medium">{{ $booking->passenger_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Số điện thoại</p>
                            <p class="font-medium">{{ $booking->passenger_phone }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $booking->passenger_email }}</p>
                        </div>
                        @if($booking->passenger_address)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Địa chỉ</p>
                            <p class="font-medium">{{ $booking->passenger_address }}</p>
                        </div>
                        @endif
                        @if($booking->passenger_notes)
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Ghi chú</p>
                            <p class="font-medium">{{ $booking->passenger_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thông tin chuyến đi -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-orange-600 mb-4">Thông tin chuyến đi</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Tuyến đường</p>
                            <p class="font-medium">{{ $booking->trip->route->departure }} - {{ $booking->trip->route->destination }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Ngày khởi hành</p>
                            <p class="font-medium">{{ \Carbon\Carbon::parse($booking->trip->departure_date)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Giờ khởi hành</p>
                            <p class="font-medium">{{ $booking->trip->departure_time }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Số xe</p>
                            <p class="font-medium">{{ $booking->trip->bus->license_plate }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Loại xe</p>
                            <p class="font-medium">{{ $booking->trip->bus->type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tài xế</p>
                            <p class="font-medium">{{ $booking->trip->driver->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">SĐT tài xế</p>
                            <p class="font-medium">{{ $booking->trip->driver->phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin ghế -->
                <div class="border-t border-gray-200 p-6">
                    <h4 class="text-lg font-semibold text-orange-600 mb-4">Thông tin ghế</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach(json_decode($booking->seat_numbers) as $seat)
                            <span class="bg-orange-100 text-orange-800 text-sm font-medium px-3 py-1 rounded">
                                Ghế {{ $seat }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin thanh toán và chi tiết vé -->
        <div>
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-orange-600 mb-4">Chi tiết thanh toán</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giá vé x {{ count(json_decode($booking->seat_numbers)) }}</span>
                            <span>{{ number_format($booking->price_per_ticket * count(json_decode($booking->seat_numbers))) }} đ</span>
                        </div>

                        @if($booking->discount_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Giảm giá</span>
                            <span class="text-red-600">-{{ number_format($booking->discount_amount) }} đ</span>
                        </div>
                        @endif

                        @if($booking->booking_fee > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí đặt vé</span>
                            <span>{{ number_format($booking->booking_fee) }} đ</span>
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between font-semibold">
                                <span>Tổng tiền</span>
                                <span class="text-orange-600 text-xl">{{ number_format($booking->total_amount) }} đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lịch sử hoạt động -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-orange-600 mb-4">Lịch sử hoạt động</h4>
                    <div class="space-y-4">
                        @foreach($booking->activities as $activity)
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 rounded-full bg-blue-600 mt-2"></div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                        @endforeach

                        @if(count($booking->activities) == 0)
                        <p class="text-gray-500 italic">Chưa có hoạt động nào được ghi nhận</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quay lại danh sách -->
    <div class="mt-6">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-gray-700 hover:text-orange-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách vé
        </a>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
@endsection
