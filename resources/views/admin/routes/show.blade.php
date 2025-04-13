@extends('layouts.admin')

@section('title', 'Chi tiết tuyến đường - Phương Thanh Express')

@section('page-title', 'Chi tiết tuyến đường')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Tuyến: {{ $route->departure }} - {{ $route->destination }}</h3>
        <div class="flex space-x-3">
            <a href="{{ route('admin.routes.edit', $route->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Chỉnh sửa
            </a>
            <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tuyến đường này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Xóa
                </button>
            </form>
        </div>
    </div>

    <!-- Thông tin tuyến đường -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold mb-4 text-orange-600">Thông tin cơ bản</h4>
            <div class="space-y-3">
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">ID:</div>
                    <div class="col-span-2 font-medium">{{ $route->id }}</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Điểm đi:</div>
                    <div class="col-span-2 font-medium">{{ $route->departure }}</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Điểm đến:</div>
                    <div class="col-span-2 font-medium">{{ $route->destination }}</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Khoảng cách:</div>
                    <div class="col-span-2 font-medium">{{ $route->distance }} km</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Thời gian di chuyển:</div>
                    <div class="col-span-2 font-medium">{{ $route->estimated_time }} giờ</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Giá vé tham khảo:</div>
                    <div class="col-span-2 font-medium">{{ number_format($route->base_price) }} VND</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Trạng thái:</div>
                    <div class="col-span-2">
                        @if($route->status == 'active')
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Hoạt động
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Ngừng hoạt động
                            </span>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Ngày tạo:</div>
                    <div class="col-span-2 font-medium">{{ $route->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-gray-600">Cập nhật lần cuối:</div>
                    <div class="col-span-2 font-medium">{{ $route->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold mb-4 text-orange-600">Mô tả tuyến đường</h4>
            <div class="prose">
                {{ $route->description ?? 'Không có mô tả chi tiết' }}
            </div>

            <h4 class="text-lg font-semibold mt-6 mb-4 text-orange-600">Điểm dừng chân</h4>
            @if(count($route->stops) > 0)
                <div class="space-y-2">
                    @foreach($route->stops as $stop)
                        <div class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                            <div>
                                <span class="font-medium">{{ $stop->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $stop->duration }} phút</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Chưa có thông tin điểm dừng chân</p>
            @endif
        </div>
    </div>

    <!-- Danh sách các chuyến xe thuộc tuyến đường này -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h4 class="text-lg font-semibold mb-4 text-orange-600">Chuyến xe gần đây</h4>

        @if(count($trips) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">ID</th>
                            <th class="py-3 px-6 text-left">Ngày khởi hành</th>
                            <th class="py-3 px-6 text-left">Giờ khởi hành</th>
                            <th class="py-3 px-6 text-left">Biển số xe</th>
                            <th class="py-3 px-6 text-left">Tài xế</th>
                            <th class="py-3 px-6 text-center">Số ghế còn</th>
                            <th class="py-3 px-6 text-center">Trạng thái</th>
                            <th class="py-3 px-6 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @foreach($trips as $trip)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $trip->id }}</td>
                            <td class="py-3 px-6 text-left">{{ \Carbon\Carbon::parse($trip->departure_date)->format('d/m/Y') }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->departure_time }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->bus_number }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->driver_name }}</td>
                            <td class="py-3 px-6 text-center">{{ $trip->available_seats }}/{{ $trip->total_seats }}</td>
                            <td class="py-3 px-6 text-center">
                                @if($trip->status == 'scheduled')
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        Đã lên lịch
                                    </span>
                                @elseif($trip->status == 'in_progress')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        Đang chạy
                                    </span>
                                @elseif($trip->status == 'completed')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        Hoàn thành
                                    </span>
                                @elseif($trip->status == 'cancelled')
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        Đã hủy
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('admin.trips.show', $trip->id) }}" class="text-blue-600 hover:text-blue-900">Xem</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $trips->links() }}
            </div>
        @else
            <p class="text-gray-500 italic">Chưa có chuyến xe nào thuộc tuyến đường này</p>
        @endif
    </div>

    <!-- Quay lại danh sách -->
    <div class="mt-4">
        <a href="{{ route('admin.routes.index') }}" class="inline-flex items-center text-gray-700 hover:text-orange-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Quay lại danh sách tuyến đường
        </a>
    </div>
@endsection
