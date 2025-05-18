<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Xử lý câu hỏi từ người dùng
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'session_id' => 'nullable|string'
        ]);

        // Nếu không có session_id, tạo mới
        $sessionId = $request->session_id ?? (Auth::user()?->id ?? Str::random(32));

        $response = $this->chatbotService->sendQuery(
            $request->input('query'),
            $sessionId
        );

        return response()->json([
            'success' => $response['success'] ?? true,
            'data' => $response,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Lấy lịch sử chat
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $history = $this->chatbotService->getChatHistory($request->session_id);

        return response()->json([
            'success' => $history['success'] ?? true,
            'data' => $history
        ]);
    }

    /**
     * Xóa lịch sử chat
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearHistory(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $result = $this->chatbotService->clearChatHistory($request->session_id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }
}
