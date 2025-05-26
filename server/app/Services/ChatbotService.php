<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Models\Line;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Promotion;
use App\Models\Seat;
use App\Models\Ticket;

class ChatbotService
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $cacheTtl;
    protected $model;

    public function __construct()
    {
        $this->apiKey = Config::get('chatbot.api_key');
        $this->baseUrl = Config::get('chatbot.base_url');
        $this->timeout = Config::get('chatbot.timeout');
        $this->cacheTtl = Config::get('chatbot.cache_ttl');
        $this->model = Config::get('chatbot.model');

        if (empty($this->apiKey) || $this->apiKey === 'your_api_key_here') {
            throw new \RuntimeException('Anthropic API key is not configured');
        }
    }

    /**
     * Gửi câu hỏi đến chatbot và nhận phản hồi
     *
     * @param string $query Câu hỏi của người dùng
     * @param string $sessionId ID phiên chat (có thể là user_id hoặc một unique identifier)
     * @return array
     */
    public function sendQuery(string $query, string $sessionId): array
    {
        $query = mb_strtolower($query);

        Log::info('Chatbot query received', ['query' => $query]);

        // Trả lời giá vé tuyến xe dựa trên database
        if (str_contains($query, 'giá vé')) {
            $lines = Line::all();
            foreach ($lines as $line) {
                if (
                    str_contains($query, mb_strtolower($line->departure)) &&
                    str_contains($query, mb_strtolower($line->destination))
                ) {
                    return [
                        'success' => true,
                        'data' => [
                            'message' => "Giá vé từ {$line->departure} đến {$line->destination} là: " . number_format($line->base_price) . "đ"
                        ]
                    ];
                }
            }
            return [
                'success' => true,
                'data' => [
                    'message' => "Xin lỗi, tôi không tìm thấy thông tin giá vé cho tuyến bạn hỏi."
                ]
            ];
        }

        // Trả lời đặt vé, tuyến xe, lịch trình dựa trên database
        if (
            (str_contains($query, 'đặt vé') || str_contains($query, 'mua vé') || str_contains($query, 'tuyến xe') || str_contains($query, 'lịch trình') || str_contains($query, 'giờ chạy')) &&
            (str_contains($query, 'đà nẵng') && str_contains($query, 'quảng bình'))
        ) {
            $line = Line::where('departure', 'like', '%Đà Nẵng%')
                        ->where('destination', 'like', '%Quảng Bình%')
                        ->first();
            if ($line) {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Tuyến xe {$line->departure} - {$line->destination}: Giá vé " . number_format($line->base_price) . "đ. Bạn có thể đặt vé trên website hoặc liên hệ hotline 0905.999999."
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Xin lỗi, hiện tại chưa có tuyến xe Đà Nẵng - Quảng Bình."
                    ]
                ];
            }
        }

        // Trả lời danh sách các tuyến xe của nhà xe
        if (
            str_contains($query, 'tuyến xe') || str_contains($query, 'các tuyến') || str_contains($query, 'những tuyến')
        ) {
            $lines = Line::all();
            if ($lines->count()) {
                $list = [];
                foreach ($lines as $line) {
                    $list[] = "{$line->departure} - {$line->destination}";
                }
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Các tuyến xe hiện có của Phương Thanh Express:<br>" . implode('<br>', $list)
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Hiện tại chưa có dữ liệu tuyến xe."
                    ]
                ];
            }
        }

        // Trả lời danh sách chuyến xe
        if (
            str_contains($query, 'chuyến xe') || str_contains($query, 'các chuyến') || str_contains($query, 'lịch trình chuyến')
        ) {
            $trips = Trip::all();
            if ($trips->count()) {
                $list = [];
                foreach ($trips as $trip) {
                    $list[] = "{$trip->departure} - {$trip->destination} | Xuất phát: {$trip->departure_time} | Đến nơi: {$trip->arrival_time}";
                }
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Các chuyến xe hiện có:<br>" . implode('<br>', $list)
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Hiện tại chưa có dữ liệu chuyến xe."
                    ]
                ];
            }
        }

        // Trả lời danh sách xe
        if (
            str_contains($query, 'loại xe') || str_contains($query, 'xe nào') || str_contains($query, 'phương tiện')
        ) {
            $vehicles = Vehicle::all();
            if ($vehicles->count()) {
                $list = [];
                foreach ($vehicles as $vehicle) {
                    $list[] = "{$vehicle->name} - Biển số: {$vehicle->license_plate} - Tiện nghi: {$vehicle->features}";
                }
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Các xe hiện có:<br>" . implode('<br>', $list)
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Hiện tại chưa có dữ liệu xe."
                    ]
                ];
            }
        }

        // Trả lời danh sách tài xế
        if (
            str_contains($query, 'tài xế') || str_contains($query, 'lái xe') || str_contains($query, 'driver')
        ) {
            $drivers = Driver::all();
            if ($drivers->count()) {
                $list = [];
                foreach ($drivers as $driver) {
                    $list[] = "{$driver->name} - SĐT: {$driver->phone}";
                }
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Danh sách tài xế:<br>" . implode('<br>', $list)
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Hiện tại chưa có dữ liệu tài xế."
                    ]
                ];
            }
        }

        // Trả lời danh sách khuyến mãi
        if (
            str_contains($query, 'khuyến mãi') || str_contains($query, 'ưu đãi') || str_contains($query, 'promotion')
        ) {
            return [
                'success' => true,
                'data' => [
                    'message' => "Các chương trình khuyến mãi hiện có của Phương Thanh Express:<br>🎁 Giảm giá 10% cho khách hàng đặt vé online lần đầu<br>👥 Ưu đãi nhóm từ 5 người trở lên<br>🎂 Tặng quà sinh nhật cho khách hàng thân thiết<br>⭐ Chương trình tích điểm đổi vé miễn phí<br>🎫 Nhiều mã giảm giá hấp dẫn vào các dịp lễ, Tết<br>Liên hệ hotline 0905999555 để biết thêm chi tiết!"
                ]
            ];
        }

        // Trả lời tiện ích trên xe
        if (
            str_contains($query, 'tiện ích') || str_contains($query, 'tiện nghi') || str_contains($query, 'dịch vụ trên xe')
        ) {
            return [
                'success' => true,
                'data' => [
                    'message' => "Các tiện ích trên xe Phương Thanh Express:<br>📶 Wifi miễn phí tốc độ cao<br>🥤 Nước uống, khăn lạnh miễn phí<br>🛏️ Ghế giường nằm êm ái, có phòng riêng (xe VIP)<br>❄️ Điều hòa, máy lạnh hiện đại<br>🚻 Nhà vệ sinh trên xe<br>🎵 Hệ thống giải trí: tivi, nhạc, sạc điện thoại<br>🚐 Trung chuyển miễn phí nội thành Đà Nẵng<br>🚚 Hỗ trợ gửi hàng, vận chuyển xe máy<br>Bạn cần biết thêm về tiện ích nào không?"
                ]
            ];
        }

        // Trả lời thông tin ghế trống trên chuyến xe
        if (
            str_contains($query, 'ghế trống') || str_contains($query, 'còn ghế') || str_contains($query, 'ghế nào')
        ) {
            $trip = Trip::where('departure', 'Đà Nẵng')->where('destination', 'Quảng Bình')->first();
            if ($trip) {
                $seats = Seat::where('trip_id', $trip->id)->where('status', 'available')->get();
                if ($seats->count()) {
                    $seatNumbers = $seats->pluck('seat_number')->toArray();
                    return [
                        'success' => true,
                        'data' => [
                            'message' => "Các ghế trống trên chuyến Đà Nẵng - Quảng Bình: " . implode(', ', $seatNumbers)
                        ]
                    ];
                } else {
                    return [
                        'success' => true,
                        'data' => [
                            'message' => "Hiện tại không còn ghế trống trên chuyến này."
                        ]
                    ];
                }
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Không tìm thấy chuyến xe Đà Nẵng - Quảng Bình."
                    ]
                ];
            }
        }

        // Trả lời thông tin vé theo số vé
        if (preg_match('/vé số (\d+)/', $query, $matches)) {
            $ticketId = $matches[1];
            $ticket = Ticket::find($ticketId);
            if ($ticket) {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Thông tin vé số {$ticketId}: Khách hàng: {$ticket->customer_name}, Tuyến: {$ticket->route_id}, Ghế: {$ticket->seat_number}, Trạng thái: {$ticket->status}"
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Không tìm thấy vé số {$ticketId}."
                    ]
                ];
            }
        }

        // Nếu là các câu hỏi về đặt vé online, đặt vé, hướng dẫn đặt vé... thì trả về luôn câu trả lời chuẩn, không gọi AI
        $datVeKeywords = ['đặt vé', 'online', 'hướng dẫn đặt vé', 'website', 'app', 'ứng dụng', 'mua vé', 'book vé', 'đặt chỗ', 'mua chỗ', 'đặt vé xe khách', 'đặt vé xe khách phương thanh'];
        foreach ($datVeKeywords as $kw) {
            if (str_contains($query, $kw)) {
                return [
                    'success' => true,
                    'data' => [
                        'message' => 'Để đặt vé online xe khách Phương Thanh Express, bạn làm theo các bước sau:<br><br>1️⃣ <b>Truy cập website chính thức:</b> <a href="https://phuongthanhexpress.com/dat-ve" target="_blank">https://phuongthanhexpress.com/dat-ve</a><br>2️⃣ <b>Chọn tuyến đường, ngày đi, số lượng vé.</b><br>3️⃣ <b>Chọn ghế mong muốn.</b><br>4️⃣ <b>Nhập thông tin liên hệ (họ tên, số điện thoại).</b><br>5️⃣ <b>Chọn phương thức thanh toán (tiền mặt, chuyển khoản, ví điện tử, v.v.).</b><br>6️⃣ <b>Xác nhận đặt vé.</b><br>7️⃣ <b>Nhận mã vé qua SMS hoặc email.</b><br><br>Nếu cần hỗ trợ, gọi ngay hotline: <a href="tel:0905333333">0905.3333.33</a>'
                    ]
                ];
            }
        }

        // Nếu là các câu hỏi về giá vé, vé, bao nhiêu... mà không khớp tuyến xe khách trong database, không gọi AI, trả về câu mặc định
        $giaVeKeywords = ['giá', 'giá vé', 'bao nhiêu', 'vé'];
        foreach ($giaVeKeywords as $kw) {
            if (str_contains($query, $kw)) {
                // Kiểm tra nếu đã trả lời giá vé tuyến xe ở trên thì bỏ qua
                // Nếu chưa trả lời, nghĩa là không khớp tuyến xe khách trong database
                return [
                    'success' => true,
                    'data' => [
                        'message' => 'Xin lỗi, tôi chỉ hỗ trợ thông tin về xe khách Phương Thanh Express. Vui lòng truy cập website hoặc gọi hotline để biết thêm chi tiết.'
                    ]
                ];
            }
        }

        // Nếu là các câu hỏi về hotline, liên hệ, số điện thoại... thì trả về hotline Phương Thanh Express, không gọi AI
        $hotlineKeywords = ['hotline', 'liên hệ', 'số điện thoại', 'tổng đài', 'gọi điện', 'contact'];
        foreach ($hotlineKeywords as $kw) {
            if (str_contains($query, $kw)) {
                return [
                    'success' => true,
                    'data' => [
                        'message' => 'Hotline đặt vé: <a href="tel:0905333333">0905.3333.33</a> | Gửi hàng: <a href="tel:0905888888">0905.888.888</a> (Anh Mạnh) | Thuê xe: <a href="tel:0905111111">0905.1111.11</a> | Hợp đồng: <a href="tel:0905222222">0905.2222.22</a> (Anh Hùng)'
                    ]
                ];
            }
        }

        // Danh sách từ khóa và câu trả lời (FAQ)
        $faq = [
            [
                'keywords' => ['Xin Chào', 'Hello','Alo','Chào'],
                'answer' => 'Xin chào! Tôi là chatbot của Nhà xe Phương Thanh. Tôi có thể giúp bạn:\n1. Đặt vé xe\n2. Xem lịch trình\n3. Tìm hiểu về chúng tôi\n4. Hỗ trợ khác\nBạn cần tôi giúp gì ạ?'
            ],
            [
                'keywords' => ['thông tin liên hệ', 'liên hệ', 'hotline', 'số điện thoại', 'email'],
                'answer' => 'Thông tin liên hệ:<br>- Hotline: 0905.999999<br>- Email: phuongthanh@gmail.com<br>- Địa chỉ: 12 Bàu Cầu 12, Hòa Xuân, Hòa Vang, Đà Nẵng'
            ],
            [
                'keywords' => ['1', 'đặt vé xe', 'đặt vé'],
                'answer' => 'Bạn muốn đặt vé xe? Vui lòng cho biết:\n1. Tuyến xe bạn muốn đi\n2. Ngày khởi hành\n3. Số lượng vé\n4. Thông tin liên hệ'
            ],
            [
                'keywords' => ['2', 'xem lịch trình', 'lịch trình'],
                'answer' => 'Bạn muốn xem lịch trình tuyến nào? Vui lòng chọn:\n1. Đà Nẵng - Quảng Bình\n2. Đà Nẵng - Nghệ An\n3. Đà Nẵng - Hà Giang\n4. Đà Nẵng - HCM'
            ],
            [
                'keywords' => ['3', 'tìm hiểu về chúng tôi', 'thông tin'],
                'answer' => 'Bạn muốn tìm hiểu thông tin gì về chúng tôi?\n1. Giới thiệu về nhà xe\n2. Tiện nghi xe\n3. Chính sách khách hàng\n4. Liên hệ'
            ],
            [
                'keywords' => ['4', 'hỗ trợ khác', 'giúp đỡ'],
                'answer' => 'Bạn cần hỗ trợ gì thêm?\n1. Gửi hàng\n2. Đổi/hủy vé\n3. Khiếu nại\n4. Quay lại menu chính'
            ],
            // Thêm các câu trả lời chi tiết cho từng lựa chọn
            [
                'keywords' => ['đà nẵng - quảng bình', 'quảng bình'],
                'answer' => 'Tuyến Đà Nẵng - Quảng Bình:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 6-7 tiếng\n- Giá vé: 250.000đ - 350.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - nghệ an', 'nghệ an'],
                'answer' => 'Tuyến Đà Nẵng - Nghệ An:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 10-12 tiếng\n- Giá vé: 350.000đ - 450.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - hà giang', 'hà giang'],
                'answer' => 'Tuyến Đà Nẵng - Hà Giang:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 24-26 tiếng\n- Giá vé: 650.000đ - 750.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['đà nẵng - hcm', 'hcm', 'sài gòn'],
                'answer' => 'Tuyến Đà Nẵng - HCM:\n- Giờ khởi hành: 6h, 10h, 14h, 20h\n- Thời gian di chuyển: 18-20 tiếng\n- Giá vé: 550.000đ - 650.000đ\nBạn muốn đặt vé không?'
            ],
            [
                'keywords' => ['giới thiệu về nhà xe', 'nhà xe'],
                'answer' => 'Nhà xe Phương Thanh:\n- Thành lập năm 2010\n- Đội xe hiện đại, an toàn\n- Đội ngũ tài xế chuyên nghiệp\n- Dịch vụ chất lượng cao\nBạn muốn biết thêm thông tin gì?'
            ],
            [
                'keywords' => ['tiện nghi xe', 'tiện nghi'],
                'answer' => 'Tiện nghi trên xe:\n1. Wifi miễn phí\n2. Nước uống miễn phí\n3. Chăn đắp\n4. Nhà vệ sinh\n5. Điều hòa\nBạn cần biết thêm gì không?'
            ],
            [
                'keywords' => ['chính sách khách hàng', 'chính sách'],
                'answer' => 'Chính sách khách hàng:\n1. Hoàn/đổi vé\n2. Bảo hiểm hành khách\n3. Ưu đãi sinh viên\n4. Chính sách nhóm\nBạn muốn biết chi tiết về chính sách nào?'
            ],
            [
                'keywords' => ['liên hệ', 'contact'],
                'answer' => 'Thông tin liên hệ:\n- Hotline: 0905.3333.33\n- Email: phuongthanh@gmail.com\n- Địa chỉ: 12 Bàu Cầu 12, Hòa Xuân, Hòa Vang, Đà Nẵng\nBạn cần hỗ trợ gì thêm?'
            ],
            [
                'keywords' => ['gửi hàng', 'chuyển hàng'],
                'answer' => 'Dịch vụ gửi hàng:\n1. Gửi hàng thông thường\n2. Gửi xe máy\n3. Chuyển phát nhanh\n4. Liên hệ hotline\nBạn muốn gửi loại hàng nào?'
            ],
            [
                'keywords' => ['đổi/hủy vé', 'hủy vé'],
                'answer' => 'Chính sách đổi/hủy vé:\n1. Hủy vé trước 2h\n2. Đổi vé trước 2h\n3. Hoàn tiền\n4. Quay lại menu chính\nBạn cần thực hiện thao tác nào?'
            ],
            [
                'keywords' => ['khiếu nại', 'phản hồi'],
                'answer' => 'Khiếu nại/Phản hồi:\n1. Gửi email\n2. Gọi hotline\n3. Đến văn phòng\n4. Quay lại menu chính\nBạn muốn gửi khiếu nại qua kênh nào?'
            ],
            [
                'keywords' => ['quay lại menu chính', 'menu chính', 'quay lại'],
                'answer' => 'Xin chào! Tôi là chatbot của Nhà xe Phương Thanh. Tôi có thể giúp bạn:\n1. Đặt vé xe\n2. Xem lịch trình\n3. Tìm hiểu về chúng tôi\n4. Hỗ trợ khác\nBạn cần tôi giúp gì ạ?'
            ],
            [
                'keywords' => ['giá vé', 'bao nhiêu tiền', 'giá', 'vé', 'vé xe', 'vé xe khách'],
                'answer' => 'Giá vé tùy vào tuyến và thời điểm. Bạn vui lòng cung cấp tuyến đi và ngày đi để được báo giá chính xác.'
            ],
            [
                'keywords' => ['lịch trình', 'giờ chạy', 'thời gian', 'mấy giờ', 'khởi hành', 'lịch trình xe', 'lịch trình xe khách','lịch trình xe khách phương thanh'],
                'answer' => 'Bạn muốn xem lịch trình tuyến nào? Vui lòng cung cấp điểm đi và điểm đến hoặc xem mục Tuyến Hoạt Động trên website.'
            ],
            [
                'keywords' => ['wifi', 'tiện nghi', 'nước', 'dịch vụ', 'máy lạnh', 'điều hòa', 'tivi', 'chăn', 'nhà vệ sinh', 'tiện nghi', 'tiện nghi xe', 'tiện nghi xe khách', 'tiện nghi xe khách phương thanh','tiện nghi xe khách phương thanh phương thanh'],
                'answer' => 'Xe Phương Thanh có wifi, nước uống, điều hòa, chăn đắp, tivi, nhà vệ sinh và nhiều tiện nghi khác.'
            ],
            [
                'keywords' => ['khuyến mãi', 'giảm giá', 'ưu đãi', 'chương trình', 'tích điểm', 'thân thiết', 'blind box', 'khuyến mãi xe khách', 'khuyến mãi xe khách phương thanh', 'khuyến mãi xe khách phương thanh phương thanh','Nhà xe bạn đang có chương trình khuyến mãi gì'],
                'answer' => 'Chúng tôi có nhiều chương trình khuyến mãi: giảm giá cho khách hàng thân thiết, Blind Box trúng thưởng iPhone, mã giảm giá và nhiều quà tặng hấp dẫn.'
            ],
            [
                'keywords' => ['gửi hàng', 'chuyển hàng', 'vận chuyển', 'gửi đồ', 'gửi xe', 'gửi hàng xe khách', 'gửi hàng xe khách phương thanh'],
                'answer' => 'Bạn có thể gửi hàng qua hotline 0905.888.888 (Anh Mạnh) hoặc đến văn phòng tại Đà Nẵng. Chúng tôi nhận gửi hàng, gửi xe máy, chuyển phát nhanh.'
            ],
            [
                'keywords' => ['đặt vé', 'mua vé', 'book vé', 'đặt chỗ', 'mua chỗ', 'đặt vé xe khách', 'đặt vé xe khách phương thanh'],
                'answer' => 'Bạn có thể đặt vé trực tuyến trên website <a href="https://phuongthanhexpress.com/dat-ve" target="_blank">Phương Thanh Express</a> hoặc gọi hotline <a href="tel:0905333333">0905.3333.33</a> để được hỗ trợ đặt chỗ nhanh nhất. 🚍'
            ],
            [
                'keywords' => ['địa chỉ', 'văn phòng', 'trụ sở', 'đâu', 'ở đâu', 'địa chỉ xe khách', 'địa chỉ nhà xe phương thanh'],
                'answer' => 'Văn phòng nhà xe Phương Thanh: 12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.'
            ],
            [
                'keywords' => ['xe giường nằm', 'loại xe', 'ghế', 'giường', 'phòng', 'chất lượng', 'xe giường nằm xe khách', 'xe giường nằm xe khách phương thanh'],
                'answer' => 'Nhà xe Phương Thanh sử dụng xe giường nằm cao cấp, có phòng riêng, ghế massage, wifi, nước uống miễn phí và nhiều tiện nghi khác.'
            ],
            [
                'keywords' => ['chính sách', 'bảo hiểm', 'an toàn', 'hoàn vé', 'đổi vé', 'trả vé', 'chính sách xe giường nằm', 'chính sách xe giường nằm xe khách', 'chính sách xe giường nằm xe khách phương thanh'],
                'answer' => 'Chúng tôi có chính sách hoàn/đổi vé linh hoạt, bảo hiểm hành khách đầy đủ và cam kết an toàn tuyệt đối cho khách hàng.'
            ],
            [
                'keywords' => ['tài xế', 'lái xe', 'phục vụ', 'nhân viên', 'tài xế xe khách', 'tài xế xe khách phương thanh'],
                'answer' => 'Đội ngũ tài xế của Phương Thanh:<br>👨‍✈️ Nguyễn Văn A - 0905.111.111<br>👨‍✈️ Trần Văn B - 0905.222.222<br>👨‍✈️ Lê Văn C - 0905.333.333<br>Tất cả đều được đào tạo chuyên nghiệp, phục vụ tận tâm.'
            ],
            [
                'keywords' => ['thanh toán', 'trả tiền', 'momo', 'vnpay', 'chuyển khoản', 'tiền mặt', 'thanh toán xe khách', 'thanh toán xe khách phương thanh'],
                'answer' => 'Bạn có thể thanh toán bằng tiền mặt, chuyển khoản, ví MoMo, VNPAY hoặc các phương thức thanh toán điện tử khác.'
            ],
            [
                'keywords' => ['đón trả', 'điểm đón', 'điểm trả', 'bến xe', 'trung chuyển', 'đón trả xe khách', 'đón trả xe khách phương thanh'],
                'answer' => 'Nhà xe có nhiều điểm đón/trả linh hoạt tại Đà Nẵng, Quảng Bình, Nghệ An, Hà Giang, HCM... và hỗ trợ trung chuyển tận nơi trong nội thành.'
            ],
            [
                'keywords' => ['hành lý', 'vali', 'balo', 'gửi hành lý', 'quy định hành lý', 'hành lý xe khách', 'hành lý xe khách phương thanh','Hành lý của tôi'],
                'answer' => 'Mỗi khách được mang theo 1 vali và 1 balo miễn phí. Nếu có thêm hành lý cồng kềnh, vui lòng liên hệ trước để được hỗ trợ.'
            ],
            [
                'keywords' => ['thời gian chạy', 'bao lâu', 'mất bao lâu', 'di chuyển','thời gian chạy khoảng bao lâu'],
                'answer' => 'Thời gian di chuyển tùy tuyến, ví dụ Đà Nẵng - Quảng Bình khoảng 6-7 tiếng, Đà Nẵng - Nghệ An khoảng 10-12 tiếng. Bạn cần hỏi tuyến cụ thể để biết chi tiết.'
            ],
            [
                'keywords' => ['feedback', 'đánh giá', 'phản hồi', 'góp ý', 'khiếu nại', 'feedback xe khách', 'feedback xe khách phương thanh','ý kiến'],
                'answer' => 'Bạn có thể gửi góp ý, phản hồi hoặc khiếu nại qua hotline hoặc email phuongthanh@gmail.com. Chúng tôi luôn lắng nghe để phục vụ tốt hơn.'
            ],
            [
                'keywords' => ['trẻ em', 'vé trẻ em', 'em bé', 'bé', 'trẻ nhỏ', 'trẻ em xe khách', 'trẻ em xe khách phương thanh','người già', 'người già xe khách', 'người già xe khách phương thanh','cựu chiến binh'],
                'answer' => 'Trẻ em dưới 5 tuổi được miễn phí vé nếu ngồi cùng người lớn. Trẻ từ 5 tuổi trở lên cần mua vé riêng.'
            ],
            [
                'keywords' => ['vật nuôi', 'thú cưng', 'chó', 'mèo', 'pet','thú nuôi'],
                'answer' => 'Nhà xe hỗ trợ vận chuyển vật nuôi nhỏ, vui lòng liên hệ trước để được tư vấn chi tiết.'
            ],
            [
                'keywords' => ['xuất bến', 'giờ xuất bến', 'lịch xuất bến'],
                'answer' => 'Xe xuất bến nhiều khung giờ trong ngày: 6h, 10h, 14h, 20h. Bạn vui lòng chọn giờ phù hợp khi đặt vé.'
            ],
            [
                'keywords' => ['đổi lịch', 'đổi chuyến', 'chuyển chuyến', 'chuyển vé'],
                'answer' => 'Bạn có thể đổi lịch/chuyến trước giờ xuất bến tối thiểu 2 tiếng, vui lòng liên hệ hotline để được hỗ trợ.'
            ],
            [
                'keywords' => ['mất đồ', 'quên đồ', 'bỏ quên', 'đồ thất lạc'],
                'answer' => 'Nếu bạn bỏ quên đồ trên xe, hãy liên hệ ngay hotline để được hỗ trợ tìm lại.'
            ],
            [
                'keywords' => ['ưu đãi sinh viên', 'giảm giá sinh viên', 'vé sinh viên','sinh viên'],
                'answer' => 'Nhà xe có chính sách giảm giá cho sinh viên, vui lòng xuất trình thẻ sinh viên khi đặt vé.'
            ],
            [
                'keywords' => ['đặt vé nhóm', 'đoàn', 'nhiều người', 'đặt nhiều vé','đoàn', 'đoàn vé'],
                'answer' => 'Đặt vé nhóm từ 5 người trở lên sẽ được ưu đãi đặc biệt, vui lòng liên hệ hotline để nhận báo giá.'
            ],
            [
                'keywords' => ['xe trung chuyển', 'trung chuyển miễn phí', 'xe đưa đón'],
                'answer' => 'Nhà xe có xe trung chuyển miễn phí trong nội thành Đà Nẵng và các điểm lớn, vui lòng báo trước khi đặt vé.'
            ],
            [
                'keywords' => ['người già', 'hỗ trợ người già', 'người khuyết tật', 'phụ nữ mang thai'],
                'answer' => 'Nhà xe ưu tiên hỗ trợ người già, người khuyết tật, phụ nữ mang thai. Vui lòng báo trước để được phục vụ tốt nhất.'
            ],
            [
                'keywords' => ['trẻ em đi một mình', 'bé đi một mình', 'gửi trẻ em'],
                'answer' => 'Trẻ em dưới 12 tuổi không được đi xe một mình. Nếu cần gửi trẻ, phải có người lớn đi kèm.'
            ],
            [
                'keywords' => ['điều kiện thời tiết', 'mưa bão', 'hoãn chuyến', 'dời chuyến'],
                'answer' => 'Trong trường hợp thời tiết xấu, nhà xe sẽ chủ động liên hệ khách để đổi/hoãn chuyến và hỗ trợ hoàn tiền nếu cần.'
            ],
            [
                'keywords' => ['bảo mật', 'an ninh', 'thông tin cá nhân'],
                'answer' => 'Nhà xe cam kết bảo mật thông tin cá nhân và an ninh cho khách hàng.'
            ],
            [
                'keywords' => ['đặt vé online', 'website', 'app', 'ứng dụng'],
                'answer' => 'Bạn có thể đặt vé online trực tiếp trên website <a href="https://phuongthanhexpress.com/dat-ve" target="_blank">Phương Thanh Express</a> hoặc gọi hotline <a href="tel:0905333333">0905.3333.33</a> để được hỗ trợ đặt vé nhanh nhất!'
            ],
            [
                'keywords' => ['hủy vé', 'bỏ vé', 'không đi'],
                'answer' => 'Bạn có thể hủy vé trước giờ xuất bến tối thiểu 2 tiếng để được hoàn tiền theo chính sách.'
            ],
            [
                'keywords' => ['chuyển tuyến', 'đi tuyến khác', 'đổi tuyến'],
                'answer' => 'Bạn có thể chuyển tuyến nếu còn chỗ, vui lòng liên hệ hotline để được hỗ trợ.'
            ],
            [
                'keywords' => ['đặt vé tết', 'vé tết', 'lịch tết', 'giá vé tết'],
                'answer' => 'Nhà xe mở bán vé Tết sớm, giá vé có thể thay đổi theo từng thời điểm. Vui lòng liên hệ hotline để đặt vé Tết.'
            ],
            [
                'keywords' => ['đặt vé khứ hồi', 'vé khứ hồi', 'giảm giá khứ hồi'],
                'answer' => 'Đặt vé khứ hồi sẽ được giảm giá, vui lòng báo trước khi đặt để nhận ưu đãi.'
            ],
            [
                'keywords' => ['đặt vé qua điện thoại', 'gọi đặt vé', 'đặt vé qua hotline'],
                'answer' => 'Bạn có thể đặt vé qua hotline 0905.3333.33, nhân viên sẽ hỗ trợ bạn nhanh chóng.'
            ],
            [
                'keywords' => ['đặt vé cho người khác', 'mua vé hộ', 'đặt hộ vé'],
                'answer' => 'Bạn có thể đặt vé cho người thân, chỉ cần cung cấp thông tin người đi khi đặt vé.'
            ],
            [
                'keywords' => ['chính sách đổi trả', 'đổi trả vé', 'đổi vé', 'trả vé'],
                'answer' => 'Chính sách đổi trả vé linh hoạt, hoàn tiền theo quy định. Vui lòng liên hệ để biết chi tiết.'
            ],
            [
                'keywords' => ['giờ làm việc', 'thời gian làm việc', 'mấy giờ mở cửa'],
                'answer' => 'Văn phòng làm việc từ 7h00 đến 21h00 hàng ngày, hotline hoạt động 24/7.'
            ],
            [
                'keywords' => ['đặt vé quốc tế', 'đi nước ngoài', 'tuyến quốc tế'],
                'answer' => 'Hiện tại nhà xe chỉ phục vụ các tuyến nội địa Việt Nam.'
            ],
            [
                'keywords' => ['đặt vé xe máy', 'gửi xe máy', 'vận chuyển xe máy'],
                'answer' => 'Bạn có thể gửi xe máy cùng chuyến, vui lòng báo trước để được sắp xếp chỗ.'
            ],
            [
                'keywords' => ['đặt vé xe khách', 'xe khách', 'xe bus'],
                'answer' => 'Nhà xe Phương Thanh chuyên xe khách giường nằm chất lượng cao, đặt vé dễ dàng qua website hoặc hotline.'
            ],
            [
                'keywords' => ['lịch sử nhà xe', 'lịch sử hình thành', 'thành lập từ khi nào', 'lịch sử phát triển', 'quá trình phát triển'],
                'answer' => 'Nhà xe Phương Thanh được thành lập từ năm 2010, với hơn 10 năm phát triển và phục vụ hàng triệu lượt khách mỗi năm. Chúng tôi không ngừng đổi mới để mang lại trải nghiệm tốt nhất cho khách hàng.'
            ],
            [
                'keywords' => ['sứ mệnh', 'tầm nhìn', 'giá trị cốt lõi', 'mục tiêu', 'cam kết'],
                'answer' => 'Sứ mệnh của Phương Thanh Express là mang đến dịch vụ vận tải an toàn, tiện nghi, đúng giờ và tận tâm. Giá trị cốt lõi: An toàn - Chất lượng - Khách hàng là trung tâm.'
            ],
            [
                'keywords' => ['đội ngũ', 'nhân sự', 'tài xế', 'nhân viên', 'đội ngũ phục vụ'],
                'answer' => 'Đội ngũ tài xế và nhân viên của Phương Thanh đều được đào tạo bài bản, chuyên nghiệp, tận tâm phục vụ khách hàng với thái độ thân thiện và trách nhiệm.'
            ],
            [
                'keywords' => ['tuyến nổi bật', 'tuyến chính', 'tuyến xe nổi bật', 'tuyến xe chính'],
                'answer' => 'Các tuyến nổi bật của Phương Thanh:<br>🛣️ Đà Nẵng - Quảng Bình<br>🛣️ Đà Nẵng - Nghệ An<br>🛣️ Đà Nẵng - Hà Giang<br>🛣️ Đà Nẵng - TP.HCM<br><a href="https://phuongthanhexpress.com/tuyen-xe" target="_blank">Xem chi tiết các tuyến</a>'
            ],
            [
                'keywords' => ['cam kết chất lượng', 'chất lượng dịch vụ', 'cam kết'],
                'answer' => 'Phương Thanh cam kết chất lượng dịch vụ: xe đời mới, vệ sinh sạch sẽ, tài xế an toàn, hỗ trợ khách hàng 24/7, hoàn tiền nếu không hài lòng.'
            ],
            [
                'keywords' => ['phản hồi khách hàng', 'đánh giá khách hàng', 'feedback khách hàng'],
                'answer' => 'Chúng tôi luôn lắng nghe và trân trọng mọi ý kiến đóng góp của khách hàng để ngày càng hoàn thiện dịch vụ. Bạn có thể gửi phản hồi qua hotline hoặc email.'
            ],
            [
                'keywords' => ['dịch vụ đặc biệt', 'dịch vụ vip', 'dịch vụ cao cấp', 'dịch vụ riêng'],
                'answer' => 'Phương Thanh có các dịch vụ VIP, xe phòng riêng, xe hợp đồng, trung chuyển tận nơi, gửi hàng nhanh, hỗ trợ khách đoàn, khách doanh nghiệp.'
            ],
            [
                'keywords' => ['lý do nên chọn', 'tại sao chọn', 'ưu điểm', 'điểm mạnh', 'vì sao nên đi'],
                'answer' => 'Lý do nên chọn Phương Thanh: xe mới, giá hợp lý, nhiều khung giờ, tài xế thân thiện, hỗ trợ 24/7, nhiều khuyến mãi, trung chuyển miễn phí, đặt vé online dễ dàng.'
            ],
            [
                'keywords' => ['giải thưởng', 'thành tích', 'vinh danh', 'top nhà xe'],
                'answer' => 'Phương Thanh nhiều năm liền đạt danh hiệu "Nhà xe được yêu thích nhất miền Trung" và nhiều giải thưởng về chất lượng dịch vụ.'
            ],
            [
                'keywords' => ['đối tác', 'hợp tác', 'liên kết', 'đối tác chiến lược'],
                'answer' => 'Chúng tôi hợp tác với nhiều đối tác lớn: các bến xe, khách sạn, công ty du lịch, trường đại học, doanh nghiệp vận tải... để phục vụ khách hàng tốt nhất.'
            ],
            [
                'keywords' => ['chính sách khách hàng thân thiết', 'khách hàng thân thiết', 'tích điểm', 'ưu đãi thành viên'],
                'answer' => 'Khách hàng thân thiết của Phương Thanh được tích điểm, nhận mã giảm giá, ưu đãi sinh nhật, ưu đãi nhóm, và nhiều quà tặng hấp dẫn.'
            ],
            [
                'keywords' => ['giờ xuất bến', 'thời gian xuất bến', 'giờ chạy tuyến', 'giờ chạy xe', 'giờ xe chạy', 'giờ xuất phát', 'giờ khởi hành'],
                'answer' => 'Các tuyến xe Phương Thanh xuất bến nhiều khung giờ trong ngày: 6h00, 10h00, 14h00, 20h00. Bạn vui lòng chọn tuyến và ngày đi để biết giờ xuất bến cụ thể hoặc liên hệ hotline để được tư vấn.'
            ],
            [
                'keywords' => ['giá vé vip', 'giá vé thường', 'giá vé loại xe', 'giá vé từng loại', 'giá vé từng tuyến'],
                'answer' => 'Giá vé xe giường nằm thường: 350.000đ - 450.000đ/tuyến. Giá vé xe VIP/phòng riêng: 500.000đ - 650.000đ/tuyến. Giá có thể thay đổi theo thời điểm, bạn vui lòng cung cấp tuyến và ngày đi để được báo giá chính xác.'
            ],
            [
                'keywords' => ['chính sách đổi vé', 'chính sách trả vé', 'đổi vé', 'trả vé'],
                'answer' => 'Chính sách đổi trả vé: Đổi vé miễn phí trước giờ xuất bến 2 tiếng. Trả vé trước 2 tiếng sẽ hoàn lại 80% giá vé. Sau thời gian này, vé không được hoàn/trả. Vui lòng liên hệ hotline để được hỗ trợ nhanh nhất.'
            ],
            [
                'keywords' => ['điểm đón', 'điểm trả', 'đón khách', 'trả khách', 'đón ở đâu', 'trả ở đâu', 'điểm đón trả'],
                'answer' => 'Các điểm đón/trả tại Đà Nẵng: Bến xe Trung tâm, BigC, cầu vượt Hòa Cầm, trung chuyển tận nơi nội thành. Tại Quảng Bình: Bến xe Đồng Hới, các điểm dọc QL1A, trung chuyển tận nơi TP Đồng Hới. Vui lòng cung cấp địa điểm cụ thể để được tư vấn.'
            ],
            [
                'keywords' => ['hướng dẫn đặt vé', 'cách đặt vé', 'đặt vé online', 'đặt vé qua web', 'hướng dẫn mua vé'],
                'answer' => 'Để đặt vé online xe khách Phương Thanh Express, bạn làm theo các bước sau:<br><br>1️⃣ <b>Truy cập website chính thức:</b> <a href="https://phuongthanhexpress.com/dat-ve" target="_blank">https://phuongthanhexpress.com/dat-ve</a><br>2️⃣ <b>Chọn tuyến đường, ngày đi, số lượng vé.</b><br>3️⃣ <b>Chọn ghế mong muốn.</b><br>4️⃣ <b>Nhập thông tin liên hệ (họ tên, số điện thoại).</b><br>5️⃣ <b>Chọn phương thức thanh toán (tiền mặt, chuyển khoản, ví điện tử, v.v.).</b><br>6️⃣ <b>Xác nhận đặt vé.</b><br>7️⃣ <b>Nhận mã vé qua SMS hoặc email.</b><br><br>Nếu cần hỗ trợ, gọi ngay hotline: <a href="tel:0905333333">0905.3333.33</a>'
            ],
            [
                'keywords' => ['dịch vụ hợp đồng', 'thuê xe', 'xe hợp đồng', 'xe du lịch', 'thuê xe riêng'],
                'answer' => 'Phương Thanh cung cấp dịch vụ xe hợp đồng, thuê xe du lịch, xe đưa đón sân bay, xe đi tour, xe cưới hỏi... Liên hệ hotline 0905.1111.11 để được báo giá và tư vấn chi tiết.'
            ],
            [
                'keywords' => ['hỗ trợ khách đoàn', 'khách đoàn', 'doanh nghiệp', 'trường học', 'ưu đãi đoàn', 'đặt vé đoàn'],
                'answer' => 'Nhà xe có chính sách ưu đãi đặc biệt cho khách đoàn, doanh nghiệp, trường học: giảm giá, trung chuyển tận nơi, xuất hóa đơn VAT, hợp đồng linh hoạt. Vui lòng liên hệ hotline để nhận báo giá tốt nhất.'
            ],
            [
                'keywords' => ['ưu đãi tết', 'khuyến mãi tết', 'giá vé tết', 'ưu đãi hè', 'khuyến mãi hè', 'ưu đãi lễ', 'khuyến mãi lễ', 'ưu đãi sinh viên', 'giảm giá sinh viên'],
                'answer' => 'Phương Thanh thường xuyên có các chương trình ưu đãi theo mùa: giảm giá vé Tết, hè, lễ hội, ưu đãi sinh viên, tặng quà, mã giảm giá... Theo dõi website hoặc fanpage để cập nhật thông tin mới nhất.'
            ],
        ];

        foreach ($faq as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (trim($query) === mb_strtolower(trim($keyword))) {
                    Log::info('Chatbot FAQ matched (exact)', ['query' => $query, 'keyword' => $keyword, 'answer' => $item['answer']]);
                    return [
                        'success' => true,
                        'data' => [
                            'message' => str_replace(["\\r\\n", "\\r", "\\n", "\r\n", "\r", "\n"], "<br>", $item['answer'])
                        ]
                    ];
                }
            }
        }

        // Nếu không khớp, gọi AI
        try {
            $response = Http::withHeaders([
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
                'x-api-key' => $this->apiKey,
            ])->post($this->baseUrl . '/messages', [
                'model' => $this->model,
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $query
                    ]
                ]
            ]);

            if (!$response->successful()) {
                throw new \Exception('Anthropic API error: ' . $response->body());
            }

            $data = $response->json();
            $result = $data['content'][0]['text'] ?? '';

            // Kiểm tra nếu AI trả lời không liên quan đến nhà xe
            if ($this->isNotRelatedToBusCompany($result)) {
                return [
                    'success' => true,
                    'data' => [
                        'message' => "Xin lỗi, tôi chỉ có thể hỗ trợ các thông tin liên quan đến dịch vụ của Phương Thanh Express. Vui lòng truy cập website hoặc liên hệ hotline 0905.999999 để được hỗ trợ thêm!"
                    ]
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'message' => nl2br($result)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => true,
                'data' => [
                    'message' => "Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi về giá vé, lịch trình, tiện nghi, khuyến mãi, gửi hàng... hoặc gọi hotline 0905.999999 để được hỗ trợ!"
                ]
            ];
        }
    }

    private function isNotRelatedToBusCompany($text)
    {
        $keywords = ['xe khách', 'phương thanh', 'đặt vé', 'lịch trình', 'giá vé', 'chuyến xe', 'vận tải', 'gửi hàng', 'hotline', 'nhà xe'];
        foreach ($keywords as $kw) {
            if (stripos($text, $kw) !== false) {
                return false; // Có liên quan
            }
        }
        return true; // Không liên quan
    }

    /**
     * Lấy lịch sử chat của một phiên
     *
     * @param string $sessionId
     * @return array
     */
    public function getChatHistory(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withoutVerifying()
            ->get($this->baseUrl . '/history/' . $sessionId);

            if (!$response->successful()) {
                throw new \Exception('Failed to get chat history: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Failed to get chat history', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể lấy lịch sử chat',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa lịch sử chat của một phiên
     *
     * @param string $sessionId
     * @return array
     */
    public function clearChatHistory(string $sessionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withoutVerifying()
            ->delete($this->baseUrl . '/history/' . $sessionId);

            if (!$response->successful()) {
                throw new \Exception('Failed to clear chat history: ' . $response->body());
            }

            return [
                'success' => true,
                'message' => 'Đã xóa lịch sử chat'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to clear chat history', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Không thể xóa lịch sử chat',
                'error' => $e->getMessage()
            ];
        }
    }
}
