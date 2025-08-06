<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\AiComplaintAnalyzer;
use App\Services\GroqService;
use App\Services\ComplaintChatService;
use App\Services\OpenRouterDeepSeekService;

class ComplaintChatController extends Controller
{
    protected $chatService;
    protected $groqService;
    protected $analyzer;
    protected $openRouterService;

    public function __construct(
        ComplaintChatService $chatService,
        GroqService $groqService,
        AiComplaintAnalyzer $analyzer,
        OpenRouterDeepSeekService $openRouterService
    ) {
        $this->chatService = $chatService;
        $this->groqService = $groqService;
        $this->analyzer = $analyzer;
        $this->openRouterService = $openRouterService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_token' => 'required|string'
        ]);

        $sessionToken = $request->input('session_token');
        $message = $request->input('message');

        // استرجع المحادثة السابقة
        $conversation = $this->chatService->getConversation($sessionToken);

        // أضف رسالة المستخدم
        $conversation[] = [
            'role' => 'user',
            'content' => $message
        ];

        // احفظ المحادثة بعد إضافة رسالة المستخدم
        $this->chatService->saveConversation($sessionToken, $conversation);

        // حاول توليد الرد باستخدام Groq أولاً
        try {
            $botResponse = $this->groqService->generateResponse($conversation);

            if ($botResponse === 'فشل في الاتصال بالخدمة الخارجية') {
                throw new \Exception("فشل في Groq، جاري التحويل إلى OpenRouter...");
            }

            Log::info("تم توليد الرد باستخدام Groq");
        } catch (\Exception $e) {
            Log::warning("فشل Groq: " . $e->getMessage());

            try {
                $botResponse = $this->openRouterService->generateResponse($conversation);
                Log::info("تم توليد الرد باستخدام OpenRouter/DeepSeek");
            } catch (\Exception $ex) {
                Log::error("فشل OpenRouter أيضًا: " . $ex->getMessage());
                return response()->json(['error' => 'فشل في توليد الرد من الخوادم الخارجية'], 500);
            }
        }

        // أضف رد البوت للمحادثة
        $conversation[] = [
            'role' => 'assistant',
            'content' => $botResponse
        ];

        // احفظ المحادثة بعد رد البوت
        $this->chatService->saveConversation($sessionToken, $conversation);

        return response()->json(['response' => $botResponse]);
    }
}
