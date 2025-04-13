<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé xe #{{ $booking->booking_code }} - Phương Thanh Express</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:border">
        <!-- Header -->
        <div class="bg-orange-600 text-white p-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Phương Thanh Express</h1>
            <div class="text-right">
                <p class="text-lg font-semibold">VÉ XE KHÁCH</p>
                <p>Mã vé: {{ $booking->booking_code }}</p>
            </div>
        </div>

        <!-- QR Code -->
        <div class="p-6 flex justify-center">
            <div class="text-center">
                <div class="w-48 h-48 border mx-auto mb-2">
                    { QrCode::size(180)->generate(route('bookings.verify', $booking->booking_code)) !!}
                </div>
                <p class="text-sm text-gray-600">Quét mã QR để xác thực vé</p>
            </div>
        </div>

        <!-- Thông tin chuyến đi -->
        <div class="px-6 py-4 border-t border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Thông tin chuyến đi</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
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
                    <p class="text-sm text-gray-600">Số ghế</p>
                    <p class="font-medium">{{ implode(', ', json_decode($booking->seat_numbers)) }}</p>
                </div>
            </div>
        </div>

        <!-- Thông tin hành khách -->
        <div class="px-6 py-4 border-t border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Thông tin hành khách</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Họ và tên</p>
                    <p class="font-medium">{{ $booking->passenger_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Số điện thoại</p>
                    <p class="font-medium">{{ $booking->passenger_phone }}</p>
                </div>
            </div>
        </div>

        <!-- Thông tin thanh toán -->
        <div class="px-6 py-4 border-t border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Thông tin thanh toán</h2>
            <div class="flex justify-between">
                <p>Tổng tiền:</p>
                <p class="font-bold">{{ number_format($booking->total_amount) }} VND</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center text-sm text-gray-600">
            <p>Vui lòng đến trước giờ khởi hành 30 phút. Hotline: 0905.999999</p>
            <p class="mt-1">Cảm ơn quý khách đã sử dụng dịch vụ của Phương Thanh Express!</p>
        </div>

        <!-- Print Button -->
        <div class="p-6 text-center no-print">
            <button onclick="window.print()" class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-6 rounded">
                In vé
            </button>
        </div>
    </div>
</body>
</html>
