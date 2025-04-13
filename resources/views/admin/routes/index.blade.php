@extends('layouts.admin')

@section('title', 'Quản lý tuyến đường - Phương Thanh Express')

@section('page-title', 'Quản lý tuyến đường')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Danh sách tuyến đường</h3>
        <a href="{{ route('admin.routes.create') }}" class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Thêm tuyến mới
        </a>
    </div>

    <!-- Bộ lọc tìm kiếm -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form action="{{ route('admin.routes.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Nhập tên tuyến đường..." class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="departure" class="block text-sm font-medium text-gray-700 mb-1">Điểm đi</label>
                <select id="departure" name="departure" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Tất cả</option>
                    @foreach($departures as $city)
                        <option value="{{ $city }}" {{ request('departure') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Điểm đến</label>
                <select id="destination" name="destination" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Tất cả</option>
                    @foreach($destinations as $city)
                        <option value="{{ $city }}" {{ request('destination') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng dữ liệu tuyến đường -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Điểm đi</th>
                    <th class="py-3 px-6 text-left">Điểm đến</th>
                    <th class="py-3 px-6 text-left">Khoảng cách</th>
                    <th class="py-3 px-6 text-left">Thời gian</th>
                    <th class="py-3 px-6 text-center">Trạng thái</th>
                    <th class="py-3 px-6 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                @forelse($routes as $route)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6 text-left">{{ $route->id }}</td>
                    <td class="py-3 px-6 text-left">{{ $route->departure }}</td>
                    <td class="py-3 px-6 text-left">{{ $route->destination }}</td>
                    <td class="py-3 px-6 text-left">{{ $route->distance }} km</td>
                    <td class="py-3 px-6 text-left">{{ $route->estimated_time }} giờ</td>
                    <td class="py-3 px-6 text-center">
                        @if($route->status == 'active')
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Hoạt động
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                Ngừng hoạt động
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex item-center justify-center">
                            <a href="{{ route('admin.routes.show', $route->id) }}" class="w-4 mr-4 transform hover:text-blue-600 hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('admin.routes.edit', $route->id) }}" class="w-4 mr-4 transform hover:text-yellow-600 hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.routes.destroy', $route->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-4 transform hover:text-red-600 hover:scale-110" onclick="return confirm('Bạn có chắc chắn muốn xóa tuyến đường này?')">
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
                    <td class="py-3 px-6 text-center" colspan="7">Không có dữ liệu</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <div class="mt-4">
        {{ $routes->appends(request()->query())->links() }}
    </div>
@endsection
