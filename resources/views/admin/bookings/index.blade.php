@extends('layouts.admin')

@section('title', 'Quản lý vé - Phương Thanh Express')

@section('page-title', 'Quản lý vé')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Danh sách vé</h3>
        <a href="{{ route('admin.bookings.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tạo vé mới
        </a>
    </div>

    <!-- Bộ lọc tìm kiếm -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Mã vé, tên khách hàng, SĐT..." class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="route" class="block text-sm font-medium text-gray-700 mb-1">Tuyến đường</label>
                <select id="route" name="route" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Tất cả</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ request('route') == $route->id ? 'selected' : '' }}>
                            {{ $route->departure }} - {{ $route->destination }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Ngày đi</label>
                <input type="date" id="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng dữ liệu vé -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Mã vé</th>
                    <th class="py-3 px-6 text-left">Tuyến đường</th>
                    <th class="py-3 px-6 text-left">Ngày & Giờ</th>
                    <th class="py-3 px-6 text-left">Khách hàng</th>
                    <th class="py-3 px-6 text-left">Số ghế</th>
                    <th class="py-3 px-6 text-right">Tổng tiền</th>
                    <th class="py-3 px-6 text-center">Trạng thái</th>
                    <th class="py-3 px-6 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                @forelse($bookings as $booking)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6 text-left font-medium">{{ $booking->booking_code }}</td>
                    <td class="py-3 px-6 text-left">{{ $booking->trip->route->departure }} - {{ $booking->trip->route->destination }}</td>
                    <td class="py-3 px-6 text-left">
                        <div>{{ \Carbon\Carbon::parse($booking->trip->departure_date)->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->trip->departure_time }}</div>
                    </td>
<td class="py-3 px-6 text-left">
    <div>{{ $booking->passenger_name }}</div>
    <div class="text-xs text-gray-500">{{ $booking->passenger_phone }}</div>
</td>
<td class="py-3 px-6 text-left">
    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
        {{ count(json_decode($booking->seat_numbers)) }} ghế
    </span>
</td>
<td class="py-3 px-6 text-right font-medium">{{ number_format($booking->total_amount) }} đ</td>
<td class="py-3 px-6 text-center">
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
</td>
<td class="py-3 px-6 text-center">
    <div class="flex item-center justify-center">
        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="w-4 mr-4 transform hover:text-blue-600 hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </a>
        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="w-4 mr-4 transform hover:text-yellow-600 hover:scale-110">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
        </a>
        <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" class="inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-4 transform hover:text-red-600 hover:scale-110" onclick="return confirm('Bạn có chắc chắn muốn xóa vé này?')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </form>
    </div>
</td>
</tr>
@empty
<tr class="border-b border-gray-200">
<td class="py-3 px-6 text-center" colspan="8">Không có dữ liệu</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<!-- Phân trang -->
<div class="mt-4">
{{ $bookings->appends(request()->query())->links() }}
</div>
@endsection
