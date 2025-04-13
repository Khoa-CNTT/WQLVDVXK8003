<?php

namespace App\Http\Controllers;

use App\Models\ChatbotLog;
use App\Models\Route;
use App\Models\Trip;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Process a chatbot query.
     */
    public function processQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'session_id' => 'required|string',
        ]);

        // Try to generate response locally first
        $localResponse = $this->generateLocalResponse($request->query);

        if ($localResponse) {
            // Log the conversation
            $this->logConversation($request->query, $localResponse, $request->session_id);

            return response()->json([
                'response' => $localResponse,
            ]);
        }

        // If local response doesn't work, use external AI API
        try {
            $response = $this->queryClaudeAI($request->query);

            // Log the conversation
            $this->logConversation($request->query, $response, $request->session_id);

            return response()->json([
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('AI API Error: ' . $e->getMessage());

            // Fallback response
            $fallbackResponse = "Xin lỗi, tôi không thể trả lời câu hỏi này ngay bây giờ. Vui lòng liên hệ với chúng tôi qua hotline 0905.999999 để được hỗ trợ.";

            // Log the conversation with error
            $this->logConversation($request->query, $fallbackResponse, $request->session_id, true);

            return response()->json([
                'response' => $fallbackResponse,
            ]);
        }
    }

    /**
     * Generate a response locally based on predefined patterns.
     */
    private function generateLocalResponse($query)
    {
        $query = mb_strtolower($query, 'UTF-8');

        // Simple pattern matching for common questions
        $patterns = [
            '/xin\s+chào|chào\s+bạn|hello|hi/i' => 'Chào bạn! Phương Thanh Express rất vui được hỗ trợ bạn hôm nay.',

            '/giá\s+vé|vé\s+giá|giá\s+bao\s+nhiêu|bao\s+nhiêu\s+tiền/i' => function() {
                $routes = Route::all();
                $response = "Dưới đây là giá vé cơ bản cho các tuyến của chúng tôi:\n";

                foreach ($routes as $route) {
                    $response .= "- {$route->departure_location} - {$route->arrival_location}: " . number_format($route->base_price) . " VND\n";
                }

                $response .= "\nLưu ý: Giá vé có thể thay đổi tùy theo ngày và loại xe. Vui lòng kiểm tra trang đặt vé để biết giá chính xác.";
                return $response;
            },

            '/tuyến|tuyến\s+đường|tuyến\s+xe|chạy\s+đâu/i' => function() {
                $routes = Route::all();
                $response = "Phương Thanh Express hiện đang khai thác các tuyến đường sau:\n";

                foreach ($routes as $route) {
                    $response .= "- {$route->departure_location} - {$route->arrival_location}\n";
                }

                return $response;
            },

            '/thời\s+gian|mất\s+bao\s+lâu|bao\s+nhiêu\s+giờ/i' => function() {
                $routes = Route::all();
                $response = "Thời gian di chuyển trung bình của các tuyến:\n";

                foreach ($routes as $route) {
                    $hours = floor($route->estimated_time / 60);
                    $minutes = $route->estimated_time % 60;
                    $response .= "- {$route->departure_location} - {$route->arrival_location}: {$hours} giờ";
                    if ($minutes > 0) {
                        $response .= " {$minutes} phút";
                    }
                    $response .= "\n";
                }

                return $response;
            },

            '/đặt\s+vé|mua\s+vé|book/i' => "Để đặt vé, bạn có thể thực hiện theo các cách sau:\n1. Đặt vé trực tuyến trên website của chúng tôi\n2. Gọi hotline 0905.3333.33\n3. Đến trực tiếp văn phòng tại Đà Nẵng",

            '/thanh\s+toán|payment|trả\s+tiền/i' => "Chúng tôi hỗ trợ các phương thức thanh toán sau:\n1. Thanh toán khi lên xe (COD)\n2. Thanh toán qua VNPAY\n3. Thanh toán qua MoMo",

            '/xe\s+đón|điểm\s+đón|đón\s+ở\s+đâu/i' => "Điểm đón khách tại Đà Nẵng:\n- Văn phòng chính: 12 Bàu Cầu 12, Hòa Xuân, Hòa Vang, Đà Nẵng\n- Bến xe trung tâm Đà Nẵng\n\nChúng tôi cũng có dịch vụ đón tận nơi trong nội thành với phụ phí 50.000 VND.",

            '/hủy\s+vé|hoàn\s+vé|refund/i' => "Chính sách hủy vé:\n- Hủy trước 24 giờ: hoàn 100% tiền vé\n- Hủy từ 12-24 giờ: hoàn 70% tiền vé\n- Hủy từ 6-12 giờ: hoàn 50% tiền vé\n- Hủy dưới 6 giờ: không hoàn tiền\n\nĐể hủy vé, vui lòng liên hệ hotline 0905.3333.33 hoặc thực hiện trên website.",

            '/tiện\s+nghi|tiện\s+ích|dịch\s+vụ/i' => "Các tiện ích trên xe:\n- Wi-Fi miễn phí\n- Nước uống miễn phí\n- Chăn, gối (đối với xe giường nằm)\n- Điều hòa\n- Hệ thống giải trí\n- Dịch vụ trung chuyển tại các điểm đến",

            '/hotline|số\s+điện\s+thoại|liên\s+hệ/i' => "Thông tin liên hệ:\n- Đặt vé: 0905.3333.33\n- Gửi hàng: 0905.888.888 (Anh Mạnh)\n- Thuê xe chở hàng: 0905.1111.11\n- Hợp đồng thuê xe: 0905.2222.22 (Anh Hùng)\n- Email: phuongthanh@gmail.com",

            '/khuyến\s+mãi|ưu\s+đãi|giảm\s+giá/i' => "Chương trình khuyến mãi hiện tại:\n1. Giảm 10% cho khách hàng tích lũy từ 10 chuyến\n2. Giảm 20% cho khách hàng tích lũy từ 15 chuyến\n3. Giảm 40% cho khách hàng tích lũy từ 20 chuyến\n\nChúng tôi cũng có chương trình quay thưởng BLIND BOX với nhiều phần quà hấp dẫn.",
        ];

        foreach ($patterns as $pattern => $response) {
            if (preg_match($pattern, $query)) {
                return is_callable($response) ? $response() : $response;
            }
        }

        // Check for route specific queries
        preg_match('/(?:từ|đi|chạy)\s+(.*?)\s+(?:đến|tới|về|tại)\s+(.*?)(?:\s|$|\?|\.)/i', $query, $matches);
        if (count($matches) >= 3) {
            $from = trim($matches[1]);
            $to = trim($matches[2]);

            $route = Route::where('departure_location', 'like', "%{$from}%")
                ->where('arrival_location', 'like', "%{$to}%")
                ->first();

            if ($route) {
                $hours = floor($route->estimated_time / 60);
                $minutes = $route->estimated_time % 60;
                $timeStr = "{$hours} giờ";
                if ($minutes > 0) {
                    $timeStr .= " {$minutes} phút";
                }

                $trips = Trip::where('route_id', $route->id)
                    ->where('departure_date', '>=', now()->format('Y-m-d'))
                    ->where('status', 'scheduled')
                    ->orderBy('departure_date')
                    ->orderBy('departure_time')
                    ->take(3)
                    ->get();

                $response = "Tuyến {$route->departure_location} - {$route->arrival_location}:\n";
                $response .= "- Giá vé: " . number_format($route->base_price) . " VND\n";
                $response .= "- Thời gian di chuyển: {$timeStr}\n";
                $response .= "- Khoảng cách: {$route->distance} km\n\n";

                if ($trips->count() > 0) {
                    $response .= "Các chuyến sắp tới:\n";
                    foreach ($trips as $trip) {
                        $response .= "- Ngày {$trip->departure_date}, khởi hành {$trip->departure_time}, giá " . number_format($trip->price) . " VND\n";
                    }
                }

                return $response;
            }
        }

        // No match found, return null so external AI can handle it
        return null;
    }

    /**
     * Query Claude AI API for response.
     */
    private function queryClaudeAI($query)
    {
        // Configure API credentials
        $apiKey = env('CLAUDE_API_KEY');
        $apiUrl = 'https://api.anthropic.com/v1/messages';

        // Prepare conversation context
        $systemPrompt = "Bạn là trợ lý ảo của Phương Thanh Express, công ty vận tải xe khách chuyên tuyến Đà Nẵng đi các tỉnh phía Bắc như Quảng Bình, Nghệ An, Hà Giang và đi Hồ Chí Minh. Hãy trả lời câu hỏi một cách lịch sự, thân thiện và hữu ích. Thông tin liên hệ: Đặt vé: 0905.3333.33, Gửi hàng: 0905.888.888, Văn phòng: 12 Bàu Cầu 12, Hòa Xuân, Hòa Vang, Đà Nẵng.";

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01'
                ],
                'json' => [
                    'model' => 'claude-3-opus-20240229',
                    'max_tokens' => 1000,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $query
                        ]
                    ]
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            return $result['content'][0]['text'];

        } catch (\Exception $e) {
            Log::error('Claude AI API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log conversation to database.
     */
    private function logConversation($query, $response, $sessionId, $isError = false)
    {
        try {
            $chatbotLog = new ChatbotLog();
            $chatbotLog->user_id = Auth::check() ? Auth::id() : null;
            $chatbotLog->user_query = $query;
            $chatbotLog->bot_response = $response;
            $chatbotLog->session_id = $sessionId;
            $chatbotLog->save();
        } catch (\Exception $e) {
            Log::error('Failed to log chatbot conversation: ' . $e->getMessage());
        }
    }
}
