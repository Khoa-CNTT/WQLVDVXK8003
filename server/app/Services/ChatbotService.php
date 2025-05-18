<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = '2e0b70a2689f75af08e6586f9b6a9f6d';
        $this->baseUrl = 'https://chatbot-api.phuongthanh.com/v1';
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
        try {
            Log::info('Starting chatbot query', [
                'query' => $query,
                'session_id' => $sessionId,
                'api_url' => $this->baseUrl,
            ]);

            // Cache key để lưu các câu hỏi tương tự
            $cacheKey = 'chatbot_query_' . md5($query);

            // Kiểm tra cache trước
            if (Cache::has($cacheKey)) {
                Log::info('Chatbot response retrieved from cache');
                return Cache::get($cacheKey);
            }

            // Phân tích nội dung câu hỏi
            $response = $this->analyzeQuery($query);

            // Cache kết quả
            Cache::put($cacheKey, $response, now()->addHour());

            return $response;

        } catch (\Exception $e) {
            Log::error('Chatbot error', [
                'query' => $query,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Xin lỗi, hiện tại tôi không thể trả lời câu hỏi này. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Phân tích nội dung câu hỏi và trả về câu trả lời phù hợp
     */
    private function analyzeQuery(string $query): array
    {
        $query = mb_strtolower($query);

        // Kiểm tra về giá vé
        if (str_contains($query, 'giá vé') || str_contains($query, 'giá') || str_contains($query, 'bao nhiêu tiền')) {
            return [
                'success' => true,
                'data' => [
                    'message' => 'Xin chào! Để báo giá vé chính xác cho bạn, tôi cần biết thêm: ' .
                               '1. Bạn muốn đi vào ngày nào? ' .
                               '2. Bạn muốn đi giờ nào trong ngày? ' .
                               '3. Bạn cần vé cho mấy người?',
                    'intent' => 'ask_ticket_price',
                    'requires' => [
                        'travel_date',
                        'travel_time',
                        'passenger_count'
                    ]
                ]
            ];
        }

        // Kiểm tra về lịch trình
        if (str_contains($query, 'lịch') || str_contains($query, 'giờ chạy') || str_contains($query, 'thời gian')) {
            return [
                'success' => true,
                'data' => [
                    'message' => 'Dạ, để xem lịch trình xe chạy, bạn cho tôi biết: ' .
                               '1. Bạn muốn đi từ đâu đến đâu? ' .
                               '2. Bạn dự định đi vào ngày nào?',
                    'intent' => 'ask_schedule',
                    'requires' => [
                        'route',
                        'travel_date'
                    ]
                ]
            ];
        }

        // Kiểm tra về tiện nghi
        if (str_contains($query, 'wifi') || str_contains($query, 'nước') || str_contains($query, 'tiện nghi') || str_contains($query, 'dịch vụ')) {
            return [
                'success' => true,
                'data' => [
                    'message' => 'Xe của Phương Thanh Express được trang bị đầy đủ tiện nghi: ' .
                               '- Wifi miễn phí ' .
                               '- Nước uống miễn phí ' .
                               '- Điều hòa ' .
                               '- Chăn đắp ' .
                               '- Tivi ' .
                               '- Nhà vệ sinh ',
                    'intent' => 'ask_amenities',
                    'requires' => []
                ]
            ];
        }

        // Câu hỏi không xác định
        return [
            'success' => true,
            'data' => [
                'message' => 'Xin chào! Tôi có thể giúp bạn: ' .
                           '1. Tra cứu giá vé ' .
                           '2. Xem lịch trình xe chạy ' .
                           '3. Tìm hiểu về tiện nghi trên xe ' .
                           '4. Đặt vé ' .
                           'Bạn cần hỗ trợ vấn đề nào?',
                'intent' => 'unknown',
                'requires' => ['user_intent']
            ]
        ];
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
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . '/history/' . $sessionId);

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
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->delete($this->baseUrl . '/history/' . $sessionId);

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
