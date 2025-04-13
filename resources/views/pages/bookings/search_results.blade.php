@extends('layouts.app')

@section('title', 'Kết Quả Tìm Kiếm - Phương Thanh Express')

@section('styles')
.bus-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.bus-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
@endsection

@section('content')
    <!-- Kết quả tìm kiếm -->
    <section class="container mx-auto my-8 px-4 text-center">
        <h2 class="text-3xl font-bold text-orange-600">KẾT QUẢ TÌM KIẾM</h2>
        <p class="text-lg text-gray-700 mt-2" id="search-info">
            Tuyến đường: {{ $departure }} → {{ $destination }} | Ngày: {{ $date }}
        </p>

        <!-- Bộ lọc và sắp xếp -->
        <div class="mt-4 flex flex-wrap justify-center space-x-4 gap-4">
            <select id="filter-price" class="p-2 border rounded">
                <option value="all">Tất cả giá vé</option>
                <option value="below300">Dưới 300.000 VND</option>
                <option value="above300">Trên 300.000 VND</option>
            </select>
            <select id="filter-seats" class="p-2 border rounded">
                <option value="all">Tất cả số ghế</option>
                <option value="available">Còn chỗ</option>
                <option value="full">Hết chỗ</option>
            </select>
            <select id="filter-time" class="p-2 border rounded">
                <option value="all">Tất cả khung giờ</option>
                <option value="morning">Sáng (00:00 - 11:59)</option>
                <option value="afternoon">Chiều (12:00 - 17:59)</option>
                <option value="evening">Tối (18:00 - 23:59)</option>
            </select>
            <select id="sort-options" class="p-2 border rounded">
                <option value="default">Sắp xếp mặc định</option>
                <option value="price-asc">Giá thấp nhất</option>
                <option value="price-desc">Giá cao nhất</option>
                <option value="time-asc">Giờ sớm nhất</option>
                <option value="seats-desc">Ghế còn nhiều nhất</option>
            </select>
            <button id="apply-filters" class="btn-modern text-white px-4 py-2 rounded">Lọc</button>
        </div>

        <div id="results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            @if(count($buses) > 0)
                @foreach($buses as $bus)
                <div class="bus-card bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-xl font-bold text-orange-600">{{ $bus->name }}</h3>
                    <p class="text-gray-700">⏰ Giờ khởi hành: <strong>{{ $bus->departure_time }}</strong></p>
                    <p class="text-gray-700">💰 Giá vé: <strong>{{ number_format($bus->price) }} VND</strong></p>
                    <p class="text-gray-700">🪑 Tình trạng ghế:
                        <strong>
                            @if($bus->available_seats > 0)
                                Còn chỗ ({{ $bus->available_seats }} chỗ trống)
                            @else
                                Hết chỗ
                            @endif
                        </strong>
                    </p>
                    <label for="seat-count-{{ $bus->id }}" class="block mt-2 text-gray-700">Chọn số vé:</label>
                    <input type="number" id="seat-count-{{ $bus->id }}" class="p-2 border rounded w-full" min="1" max="{{ $bus->available_seats }}" value="1" {{ $bus->available_seats == 0 ? 'disabled' : '' }}>
                    <a href="{{ route('booking.detail', [
                        'bus_id' => $bus->id,
                        'departure' => $departure,
                        'destination' => $destination,
                        'date' => $date,
                        'seat_count' => 1
                    ]) }}"
                    onclick="this.href = this.href.replace(/seat_count=\d+/, 'seat_count=' + document.getElementById('seat-count-{{ $bus->id }}').value)"
                    class="mt-4 btn-modern text-white px-6 py-2 rounded inline-block {{ $bus->available_seats == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $bus->available_seats == 0 ? 'disabled' : '' }}>
                        Đặt vé ngay
                    </a>
                </div>
                @endforeach
            @else
                <div class="col-span-3 text-center py-6">
                    <p class="text-gray-700 text-lg">Không tìm thấy chuyến xe phù hợp. Vui lòng thử lại với bộ lọc khác!</p>
                </div>
            @endif
        </div>

        <!-- Phân trang -->
        <div class="mt-6">
            {{ $buses->links() }}
        </div>
    </section>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const applyFiltersBtn = document.getElementById("apply-filters");
        const filterPrice = document.getElementById("filter-price");
        const filterSeats = document.getElementById("filter-seats");
        const filterTime = document.getElementById("filter-time");
        const sortOptions = document.getElementById("sort-options");

        applyFiltersBtn.addEventListener("click", function() {
            let url = new URL(window.location.href);

            // Thêm các tham số lọc vào URL
            url.searchParams.set("price_filter", filterPrice.value);
            url.searchParams.set("seats_filter", filterSeats.value);
            url.searchParams.set("time_filter", filterTime.value);
            url.searchParams.set("sort", sortOptions.value);

            // Chuyển hướng đến URL mới với các tham số lọc
            window.location.href = url.toString();
        });

        // Thiết lập giá trị cho các bộ lọc từ tham số URL
        const params = new URLSearchParams(window.location.search);
        if (params.has("price_filter")) filterPrice.value = params.get("price_filter");
        if (params.has("seats_filter")) filterSeats.value = params.get("seats_filter");
        if (params.has("time_filter")) filterTime.value = params.get("time_filter");
        if (params.has("sort")) sortOptions.value = params.get("sort");
    });
</script>
@endsection
