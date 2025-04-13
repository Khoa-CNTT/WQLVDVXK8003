<!-- Thông tin ghế -->
<h3 class="text-lg font-semibold text-orange-600 mb-4">Thông tin ghế</h3>
<div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach(json_decode($booking->seat_numbers) as $seat)
            <span class="bg-orange-100 text-orange-800 text-sm font-medium px-3 py-1 rounded">
                Ghế {{ $seat }}
            </span>
        @endforeach
    </div>
</div>

<!-- Chi tiết thanh toán -->
<div class="md:col-span-1">
    <h3 class="text-lg font-semibold text-orange-600 mb-4">Chi tiết thanh toán</h3>
    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
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
            <div class="border-t border-gray-200 pt-3 mt-3">
                <div class="flex justify-between font-semibold">
                    <span>Tổng tiền</span>
                    <span class="text-orange-600 text-xl">{{ number_format($booking->total_amount) }} đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Thao tác -->
    @if($booking->status == 'pending' || ($booking->status == 'confirmed' && \Carbon\Carbon::parse($booking->trip->departure_date)->subHours(24)->isFuture()))
    <div class="mt-6">
        <a href="{{ route('bookings.cancel', $booking->id) }}" class="w-full inline-block text-center bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded" onclick="return confirm('Bạn có chắc chắn muốn hủy vé này?')">
            Hủy vé
        </a>
    </div>
    @endif
</div>
