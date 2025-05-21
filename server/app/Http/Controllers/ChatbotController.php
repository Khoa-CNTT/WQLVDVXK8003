<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer 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',
            ])->post('https://integrate.api.nvidia.com/v1/chat/completions', [
                'model' => 'deepseek-ai/deepseek-r1',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Bạn là 1 chuyên gia về lập trình, là 1 giảng viên về code, bạn giúp học viên tiến bộ trong lập trình blockchain database. Nếu câu hỏi không liên quan, hãy từ chối một cách nhẹ nhàng.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => 0.6,
                'top_p' => 0.7,
                'max_tokens' => 4096,
                'stream' => false
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'API không trả về phản hồi hợp lệ'], 500);
            }

            $data = $response->json();
            return response()->json(['response' => $data['choices'][0]['message']['content']]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi xảy ra khi xử lý yêu cầu'], 500);
        }
    }
}
