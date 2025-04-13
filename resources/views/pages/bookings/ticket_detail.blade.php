@extends('layouts.app')

@section('title', 'Chi Tiết Đặt Vé - Phương Thanh Express')

@section('styles')
.seat {
    width: 40px;
    height: 40px;
    margin: 5px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ccc;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.seat.available {
    background-color: #e5e7eb;
}
.seat.selected {
    background-color: #f97316;
    color: white;
    border-color: #ea580c;
}
.seat.taken {
    background-color: #d1d5db;
    border-color: #9ca3af;
    cursor: not-allowed;
}
@endsection

@section('content')
    <section class="container mx-auto my-8 px-4">
        <h2 class="text-3xl font-bold text-orange-600 text-center">CHI TIẾT ĐẶT VÉ</h2>
        <div class="mt-6 bg-white shadow-2xl rounded-2xl p-6 max-w-2xl mx-auto">
            <h3 class="text-xl font-bold text-orange-600">Thông tin chuyến xe</h3>
            <p id="bus-info" class="text-gray-700 mt-2">
                Chuyến xe: {{ $bus->name }} | Giờ khởi hành: {{ $bus->departure_time }} | Giá vé: {{ number_format($bus->price) }} VND/vé
            </p>
            <p id="total-price" class="text-gray-700 mt-2 font-bold">Tổng tiền: <span id="price-display">{{ number_format($bus->price * $seat_count) }}</span> VND</p>

            <!-- Sơ đồ chọn ghế -->
            <h3 class="text-xl font-bold text-orange-600 mt-6">Chọn ghế ngồi</h3>
            <div id="seat-map" class="mt-4 grid grid-cols-5 gap-2 justify-center">
                @for($i = 1; $i <= $bus->total_seats; $i++)
                    <div
                        class="seat {{ in_array($i, $taken_seats) ? 'taken' : 'available' }}"
                        data-seat="{{ $i }}"
                        {{ in_array($i, $taken_seats) ? 'disabled' : '' }}
                    >
                        {{ $i }}
                    </div>
                @endfor
            </div>
            <p id="selected-seats" class="text-gray-700 mt-2">Ghế đã chọn: <span id="selected-list"></span> (<span id="selected-count">0</span>)</p>

            <h3 class="text-xl font-bold text-orange-600 mt-6">Thông tin hành khách</h3>
            <form id="bookingForm" action="{{ route('booking.store') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="bus_id" value="{{ $bus->id }}">
                <input type="hidden" name="selected_seats" id="selected-seats-input">
                <input type="hidden" name="total_price" id="total-price-input" value="{{ $bus->price * $seat_count }}">
                <input type="hidden" name="departure" value="{{ $departure }}">
                <input type="hidden" name="destination" value="{{ $destination }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="mb-4">
                    <label for="passengerName" class="block text-left font-semibold text-gray-700">Họ và tên:</label>
                    <input type="text" id="passengerName" name="passenger_name" required class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Nhập họ và tên" value="{{ auth()->user() ? auth()->user()->name : '' }}">
                </div>
                <div class="mb-4">
                    <label for="passengerPhone" class="block text-left font-semibold text-gray-700">Số điện thoại:</label>
                    <input type="tel" id="passengerPhone" name="passenger_phone" required class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Nhập số điện thoại" value="{{ auth()->user() ? auth()->user()->phone : '' }}">
                </div>
                <div class="mb-4">
                    <label for="passengerEmail" class="block text-left font-semibold text-gray-700">Email:</label>
                    <input type="email" id="passengerEmail" name="passenger_email" required class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Nhập email" value="{{ auth()->user() ? auth()->user()->email : '' }}">
                </div>
                <div class="mb-4">
                    <label for="paymentMethod" class="block text-left font-semibold text-gray-700">Phương thức thanh toán:</label>
                    <select id="paymentMethod" name="payment_method" class="w-full p-2 border rounded-lg">
                        <option value="cod">Thanh toán khi lên xe</option>
                        <option value="vnpay">VNPAY</option>
                    </select>
                </div>
                <button type="submit" class="btn-modern text-white px-6 py-3 rounded-lg w-full">Xác nhận đặt vé</button>
            </form>

            @if(session('error'))
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const seatMap = document.getElementById("seat-map");
        const busPrice = {{ $bus->price }};
        let selectedSeats = [];

        // Chọn/hủy ghế
        const seats = document.querySelectorAll('.seat');
        seats.forEach(seat => {
            if (!seat.classList.contains('taken')) {
                seat.addEventListener('click', function() {
                    const seatNumber = parseInt(this.getAttribute('data-seat'));

                    if (this.classList.contains('selected')) {
                        // Hủy chọn ghế
                        this.classList.remove('selected');
                        selectedSeats = selectedSeats.filter(num => num !== seatNumber);
                    } else {
                        // Chọn ghế
                        this.classList.add('selected');
                        selectedSeats.push(seatNumber);
                    }

                    updateSelection();
                });
            }
        });

        // Cập nhật thông tin ghế và tổng tiền
        function updateSelection() {
            document.getElementById("selected-count").textContent = selectedSeats.length;
            document.getElementById("selected-list").textContent = selectedSeats.length > 0 ? selectedSeats.sort((a, b) => a - b).join(", ") : "Chưa chọn";

            const totalPrice = selectedSeats.length * busPrice;
            document.getElementById("price-display").textContent = totalPrice.toLocaleString();
            document.getElementById("total-price-input").value = totalPrice;
            document.getElementById("selected-seats-input").value = JSON.stringify(selectedSeats);
        }

        // Xử lý form đặt vé
        document.getElementById("bookingForm").addEventListener("submit", function(event) {
            if (selectedSeats.length === 0) {
                event.preventDefault();
                alert("Vui lòng chọn ít nhất một ghế!");
                return;
            }
        });
    });
</script>
@endsection
