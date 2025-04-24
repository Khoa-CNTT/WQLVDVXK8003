<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatbotLog;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    /**
     * Xử lý truy vấn từ người dùng gửi đến chatbot
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleQuery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->query;
        $userId = $request->user()->id ?? null;

        // Lưu lại log truy vấn
        $log = ChatbotLog::create([
            'user_id' => $userId,
            'query' => $query,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Xử lý truy vấn
        $response = $this->processQuery($query);

        // Cập nhật response vào log
        $log->response = $response;
        $log->save();

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'response' => $response
            ]
        ]);
    }

    /**
     * Xử lý câu hỏi bằng ClaudeAI
     *
     * @param  string  $query
     * @return string
     */
    private function processQuery($query)
    {
        // Thử xử lý các câu hỏi phổ biến trước
        $commonResponse = $this->handleCommonQueries($query);
        if ($commonResponse) {
            return $commonResponse;
        }

        // Nếu cần xử lý phức tạp hơn, gọi đến Claude AI API
        try {
            $claudeApiKey = env('CLAUDE_AI_API_KEY');
            $claudeApiUrl = 'https://api.anthropic.com/v1/messages';

            // Chuẩn bị context thông tin về công ty xe khách
            $context = $this->prepareContext();

            $response = Http::withHeaders([
                'x-api-key' => $claudeApiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($claudeApiUrl, [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "Bạn là trợ lý ảo của công ty xe khách Phương Thanh Express, nhiệm vụ của bạn là trả lời các câu hỏi về lịch trình, giá vé, dịch vụ, và các thông tin khác liên quan đến công ty. Hãy trả lời ngắn gọn, thân thiện và hữu ích. Sử dụng giọng điệu chuyên nghiệp, thân thiện, với người Việt Nam.\n\nThông tin về công ty:\n" . $context
                    ],
                    [
                        'role' => 'user',
                        'content' => $query
                    ]
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? 'Xin lỗi, tôi không thể trả lời câu hỏi này vào lúc này.';
            } else {
                return 'Xin lỗi, hệ thống hỗ trợ đang gặp sự cố. Vui lòng thử lại sau hoặc liên hệ trực tiếp qua hotline 0905.999999.';
            }
        } catch (\Exception $e) {
            // Fallback khi API gặp lỗi
            return 'Xin lỗi, tôi không thể kết nối đến hệ thống hỗ trợ vào lúc này. Vui lòng thử lại sau hoặc liên hệ trực tiếp qua hotline 0905.999999.';
        }
    }

    /**
     * Xử lý các câu hỏi phổ biến mà không cần gọi API
     *
     * @param  string  $query
     * @return string|null
     */
    private function handleCommonQueries($query)
    {
        // Chuẩn hóa truy vấn, loại bỏ dấu câu và chuyển thành chữ thường
        $normalizedQuery = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $query), 'UTF-8');

        // Các mẫu câu hỏi phổ biến
        $patterns = [
            'gia ve' => $this->getTicketPriceInfo(),
            'lich trinh' => $this->getScheduleInfo(),
            'tuyen duong' => $this->getRouteInfo(),
            'dia chi' => 'Văn phòng chính: 12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng. Quý khách có thể liên hệ hotline 0905.999999 để biết thêm chi tiết về các văn phòng khác.',
            'so dien thoai' => 'Quý khách có thể liên hệ các số điện thoại sau:\n- Đặt vé: 0905.3333.33\n- Gửi hàng: 0905.888.888 (Anh Mạnh)\n- Thuê xe chở hàng: 0905.1111.11\n- Hợp đồng thuê xe: 0905.2222.22 (Anh Hùng)',
            'dat ve' => 'Quý khách có thể đặt vé trực tuyến trên website hoặc app của Phương Thanh Express, hoặc gọi hotline 0905.3333.33. Khi đặt vé, quý khách cần cung cấp thông tin cá nhân, chọn tuyến đường, ngày giờ và số lượng vé.',
            'huy ve' => 'Để hủy vé, quý khách vui lòng truy cập tài khoản trên website hoặc app của Phương Thanh Express, chọn vé cần hủy và làm theo hướng dẫn. Lưu ý rằng việc hủy vé cần được thực hiện ít nhất 24 giờ trước giờ khởi hành để được hoàn tiền 100%. Hủy vé trong vòng 24 giờ sẽ bị phí 10% giá vé.',
            'gui hang' => 'Phương Thanh Express nhận gửi hàng đi các tỉnh thành trên các tuyến xe đang khai thác. Để gửi hàng, quý khách vui lòng liên hệ 0905.888.888 (Anh Mạnh) hoặc đến trực tiếp văn phòng của chúng tôi.',
            'thanh toan' => 'Phương Thanh Express hỗ trợ các hình thức thanh toán sau: tiền mặt khi lên xe, chuyển khoản ngân hàng, và thanh toán trực tuyến qua VNPAY hoặc MoMo.',
            'tien ich' => 'Các tiện ích trên xe của Phương Thanh Express bao gồm: WiFi miễn phí, giường nằm cao cấp, nước uống miễn phí, điều hòa mát lạnh, xe trung chuyển, và hệ thống giải trí với màn hình LED và tai nghe cá nhân.',
            'chuong trinh khuyen mai' => 'Phương Thanh Express có các chương trình khuyến mãi như: Khách hàng thân thiết (giảm 10-40% khi tích lũy từ 10-20 chuyến) và chương trình Blind Box với nhiều phần quà hấp dẫn.',
            'thoi gian di' => $this->getTravelTimeInfo(),
            'xin chao' => 'Xin chào! Tôi là trợ lý ảo của Phương Thanh Express. Tôi có thể giúp gì cho bạn?',
            'cam on' => 'Không có gì, rất vui được hỗ trợ bạn! Nếu có thắc mắc gì thêm, đừng ngần ngại hỏi tôi nhé.'
        ];

        // Kiểm tra truy vấn có chứa từ khóa nào trong các mẫu không
        foreach ($patterns as $keyword => $response) {
            if (mb_strpos($normalizedQuery, $keyword) !== false) {
                return $response;
            }
        }

        // Không tìm thấy mẫu phù hợp
        return null;
    }

    /**
     * Chuẩn bị ngữ cảnh về công ty xe khách
     *
     * @return string
     */
    private function prepareContext()
    {
        // Lấy thông tin tuyến đường
        $routes = Route::where('status', 'active')->get();
        $routeInfo = "Các tuyến đường:\n";
        foreach ($routes as $route) {
            $routeInfo .= "- {$route->departure} - {$route->destination}: Khoảng cách {$route->distance}km, thời gian di chuyển khoảng " . round($route->duration/60) . " giờ, giá vé từ " . number_format($route->base_price) . " VND.\n";
        }

        // Lấy thông tin lịch trình
        $trips = Trip::with(['route'])
            ->where('status', 'active')
            ->whereDate('departure_time', '>=', now())
            ->orderBy('departure_time')
            ->limit(10)
            ->get();

        $scheduleInfo = "Lịch trình sắp tới:\n";
        foreach ($trips as $trip) {
            $departureTime = Carbon::parse($trip->departure_time)->format('d/m/Y H:i');
            $scheduleInfo .= "- {$trip->route->departure} - {$trip->route->destination}: Khởi hành {$departureTime}, giá vé " . number_format($trip->price) . " VND.\n";
        }

        // Thông tin về công ty
        $companyInfo = "
Phương Thanh Express là công ty vận tải hành khách với hơn 10 năm kinh nghiệm, chuyên cung cấp dịch vụ vận chuyển hành khách giữa TP. Đà Nẵng và các tỉnh phía Bắc.

Thông tin liên hệ:
- Đặt vé: 0905.3333.33
- Gửi hàng: 0905.888.888 (Anh Mạnh)
- Thuê xe chở hàng: 0905.1111.11
- Hợp đồng thuê xe: 0905.2222.22 (Anh Hùng)
- Hotline: 0905.999999
- Email: phuongthanh@gmail.com
- Địa chỉ: 12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.

Tiện ích trên xe:
- WiFi miễn phí
- Giường nằm cao cấp
- Nước uống miễn phí
- Điều hòa mát lạnh
- Xe trung chuyển
- Hệ thống giải trí với màn hình LED và tai nghe cá nhân

Phương thức thanh toán:
- Tiền mặt khi lên xe
- Chuyển khoản ngân hàng
- Thanh toán trực tuyến qua VNPAY
- Thanh toán trực tuyến qua MoMo

Chương trình khuyến mãi:
- Khách hàng thân thiết: Giảm 10% khi tích lũy từ 10 chuyến, 20% khi tích lũy từ 15 chuyến, 40% khi tích lũy từ 20 chuyến.
- Chương trình Blind Box với nhiều phần quà hấp dẫn.
";

        return $companyInfo . "\n" . $routeInfo . "\n" . $scheduleInfo;
    }

    /**
     * Lấy thông tin về giá vé
     *
     * @return string
     */
    private function getTicketPriceInfo()
    {
        // Lấy thông tin giá vé các tuyến
        $routes = Route::where('status', 'active')->get();
        $priceInfo = "Thông tin giá vé các tuyến:\n";

        foreach ($routes as $route) {
            $priceInfo .= "- Tuyến {$route->departure} - {$route->destination}: Từ " . number_format($route->base_price) . " VND.\n";
        }

        $priceInfo .= "\nGiá vé có thể thay đổi theo thời điểm và loại xe. Quý khách vui lòng liên hệ 0905.3333.33 để biết giá chính xác cho chuyến đi cụ thể.";

        return $priceInfo;
    }

    /**
     * Lấy thông tin về lịch trình
     *
     * @return string
     */
    private function getScheduleInfo()
    {
        // Lấy lịch trình 5 ngày tới
        $startDate = now();
        $endDate = now()->addDays(5);

        $trips = Trip::with(['route'])
            ->where('status', 'active')
            ->whereBetween('departure_time', [$startDate, $endDate])
            ->orderBy('departure_time')
            ->get();

        $scheduleInfo = "Lịch trình các chuyến xe trong 5 ngày tới:\n";

        $tripsByDate = $trips->groupBy(function($trip) {
            return Carbon::parse($trip->departure_time)->format('d/m/Y');
        });

        foreach ($tripsByDate as $date => $dateTrips) {
            $scheduleInfo .= "\nNgày {$date}:\n";

            foreach ($dateTrips as $trip) {
                $departureTime = Carbon::parse($trip->departure_time)->format('H:i');
                $scheduleInfo .= "- {$trip->route->departure} - {$trip->route->destination}: Khởi hành {$departureTime}, giá vé " . number_format($trip->price) . " VND.\n";
            }
        }

        $scheduleInfo .= "\nĐể đặt vé, vui lòng truy cập website hoặc gọi số 0905.3333.33.";

        return $scheduleInfo;
    }

    /**
     * Lấy thông tin về tuyến đường
     *
     * @return string
     */
    private function getRouteInfo()
    {
        // Lấy thông tin tuyến đường
        $routes = Route::where('status', 'active')->get();
        $routeInfo = "Phương Thanh Express hiện đang khai thác các tuyến đường sau:\n";

        foreach ($routes as $route) {
            $routeInfo .= "- Tuyến {$route->departure} - {$route->destination}: Khoảng cách {$route->distance}km, thời gian di chuyển khoảng " . round($route->duration/60) . " giờ.\n";
        }

        $routeInfo .= "\nMỗi tuyến đều có nhiều chuyến trong ngày. Để biết thêm chi tiết, vui lòng liên hệ 0905.3333.33.";

        return $routeInfo;
    }

    /**
     * Lấy thông tin về thời gian di chuyển
     *
     * @return string
     */
    private function getTravelTimeInfo()
    {
        // Lấy thông tin thời gian di chuyển
        $routes = Route::where('status', 'active')->get();
        $timeInfo = "Thời gian di chuyển các tuyến:\n";

        foreach ($routes as $route) {
            $hours = floor($route->duration / 60);
            $minutes = $route->duration % 60;
            $timeInfo .= "- Tuyến {$route->departure} - {$route->destination}: Khoảng {$hours} giờ";
            if ($minutes > 0) {
                $timeInfo .= " {$minutes} phút";
            }
            $timeInfo .= ".\n";
        }

        $timeInfo .= "\nThời gian di chuyển có thể thay đổi tùy thuộc vào điều kiện giao thông và thời tiết.";

        return $timeInfo;
    }

    /**
     * Xem logs chatbot (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request)
    {
        $query = ChatbotLog::with('user');

        // Lọc theo người dùng
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Lọc theo ngày
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Tìm kiếm theo nội dung
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('query', 'like', "%{$search}%")
                  ->orWhere('response', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
