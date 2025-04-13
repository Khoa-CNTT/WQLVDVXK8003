@extends('layouts.admin')

@section('title', 'Chỉnh sửa tuyến đường - Phương Thanh Express')

@section('page-title', 'Chỉnh sửa tuyến đường')

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.routes.update', $route->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Điểm đi -->
                <div>
                    <label for="departure" class="block text-sm font-medium text-gray-700 mb-1">Điểm đi <span class="text-red-500">*</span></label>
                    <input type="text" name="departure" id="departure" value="{{ old('departure', $route->departure) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('departure') border-red-500 @enderror" placeholder="VD: Đà Nẵng">
                    @error('departure')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Điểm đến -->
                <div>
                    <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Điểm đến <span class="text-red-500">*</span></label>
                    <input type="text" name="destination" id="destination" value="{{ old('destination', $route->destination) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('destination') border-red-500 @enderror" placeholder="VD: Hà Nội">
                    @error('destination')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Khoảng cách -->
                <div>
                    <label for="distance" class="block text-sm font-medium text-gray-700 mb-1">Khoảng cách (km) <span class="text-red-500">*</span></label>
                    <input type="number" name="distance" id="distance" value="{{ old('distance', $route->distance) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('distance') border-red-500 @enderror" placeholder="VD: 850">
                    @error('distance')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Thời gian ước tính -->
                <div>
                    <label for="estimated_time" class="block text-sm font-medium text-gray-700 mb-1">Thời gian di chuyển (giờ) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.5" name="estimated_time" id="estimated_time" value="{{ old('estimated_time', $route->estimated_time) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('estimated_time') border-red-500 @enderror" placeholder="VD: 12.5">
                    @error('estimated_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Giá vé tham khảo -->
                <div>
                    <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Giá vé tham khảo (VND) <span class="text-red-500">*</span></label>
                    <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $route->base_price) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('base_price') border-red-500 @enderror" placeholder="VD: 300000">
                    @error('base_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Trạng thái -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="active" {{ old('status', $route->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ old('status', $route->status) == 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
                    </select>
                </div>
            </div>

            <!-- Mô tả -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả tuyến đường</label>
                <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 @error('description') border-red-500 @enderror" placeholder="Nhập mô tả chi tiết về tuyến đường...">{{ old('description', $route->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Điểm dừng chân -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Điểm dừng chân</label>
                <div id="stops-container">
                    @if(count($route->stops) > 0)
                        @foreach($route->stops as $index => $stop)
                            <div class="flex gap-4 mb-2">
                                <input type="text" name="stops[]" value="{{ $stop->name }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Tên điểm dừng">
                                <input type="number" name="stop_durations[]" value="{{ $stop->duration }}" class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Phút dừng">
                                <button type="button" class="btn-remove-stop px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 {{ count($route->stops) === 1 ? 'hidden' : '' }}">Xóa</button>
                            </div>
                        @endforeach
                    @else
                        <div class="flex gap-4 mb-2">
                            <input type="text" name="stops[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Tên điểm dừng">
                            <input type="number" name="stop_durations[]" class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Phút dừng">
                            <button type="button" class="btn-remove-stop hidden px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Xóa</button>
                        </div>
                    @endif
                </div>
                <button type="button" id="add-stop" class="mt-2 px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">+ Thêm điểm dừng</button>
            </div>

            <!-- Nút submit và cancel -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('admin.routes.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Hủy</a>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Cập nhật tuyến đường</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stopsContainer = document.getElementById('stops-container');
        const addStopButton = document.getElementById('add-stop');

        // Xử lý thêm điểm dừng
        addStopButton.addEventListener('click', function() {
            const stopRow = document.createElement('div');
            stopRow.className = 'flex gap-4 mb-2';
            stopRow.innerHTML = `
                <input type="text" name="stops[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Tên điểm dừng">
                <input type="number" name="stop_durations[]" class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Phút dừng">
                <button type="button" class="btn-remove-stop px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Xóa</button>
            `;
            stopsContainer.appendChild(stopRow);

            // Hiển thị nút xóa cho tất cả các điểm dừng khi có từ 2 điểm trở lên
            const removeButtons = document.querySelectorAll('.btn-remove-stop');
            removeButtons.forEach(button => {
                button.classList.remove('hidden');
            });
        });

        // Xử lý xóa điểm dừng
        stopsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-stop')) {
                e.target.closest('.flex').remove();

                // Ẩn nút xóa cho dòng cuối cùng nếu chỉ còn 1 điểm dừng
                const removeButtons = document.querySelectorAll('.btn-remove-stop');
                if (removeButtons.length === 1) {
                    removeButtons[0].classList.add('hidden');
                }
            }
        });
    });
</script>
@endsection
