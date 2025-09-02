<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiComplaintAnalyzer;
use App\Models\Complaint;
use Illuminate\Support\Facades\Log;
use App\Services\GroqService;
use App\Services\ComplaintChatService;

class ComplaintChatController extends Controller
{
    protected $groqService;
    protected $chatService;
    protected $analyzer;

    public function __construct(GroqService $groqService, ComplaintChatService $chatService, AiComplaintAnalyzer $analyzer)
    {
        $this->groqService = $groqService;
        $this->chatService = $chatService;
        $this->analyzer = $analyzer;
    }

public function handleChat(Request $request)
{
    $sessionToken = $request->input('session_token');
    $userMessage = trim((string) $request->input('message'));

    if ($userMessage === '') {
        return response()->json(['error' => 'الرسالة مطلوبة'], 422);
    }

    // إذا ما في session_token -> أنشئ واحد جديد
    if (empty($sessionToken)) {
        $sessionToken = (string) \Illuminate\Support\Str::uuid();
        Log::info("Generated new chat session", ['session' => $sessionToken]);
    }

    // استرجاع/إنشاء المحادثة
    $conversation = $this->chatService->getConversation($sessionToken);

    // أضف رسالة المستخدم
    $conversation[] = [
        'content' => $userMessage,
        'is_bot' => false,
        'timestamp' => now()->toIso8601String(),
    ];

    // حفظ المحادثة فورًا حتى لا نضيّع السياق
    $this->chatService->saveConversation($sessionToken, $conversation);
    Log::info("Saved conversation after user message", ['session' => $sessionToken, 'count' => count($conversation)]);

    // تحقق من الاكتمال
    if ($this->chatService->isConversationComplete($conversation)) {
        $data = $this->chatService->extractComplaintData($conversation);
        $data['is_emergency'] = $this->analyzer->rateEmergencyLevel($data['description'] ?? '') ?? 1;

        try {
            Complaint::create($data);
            $this->chatService->clearConversation($sessionToken);

            return response()->json([
                'session_token' => $sessionToken,
                'response' => "تم استلام شكواك بنجاح، وشكرًا لتواصلك معنا."
            ]);
        } catch (\Exception $e) {
            Log::error("فشل في حفظ الشكوى: " . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء حفظ الشكوى.'], 500);
        }
    }

    // الآن مرّر المحادثة الكاملة للـ LLM
    try {
        $botResponse = $this->groqService->generateResponse($conversation);
    } catch (\Exception $e) {
        Log::error("فشل في توليد الرد: " . $e->getMessage());
        return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية.'], 500);
    }

    // أضف رد الذكاء الاصطناعي للمحادثة وحفظ نهائي
    $conversation[] = [
        'content' => $botResponse,
        'is_bot' => true,
        'timestamp' => now()->toIso8601String(),
    ];
    $this->chatService->saveConversation($sessionToken, $conversation);
    Log::info("Saved conversation after bot response", ['session' => $sessionToken, 'count' => count($conversation)]);

    // إرجاع الرد مع التوكن دائماً
    return response()->json([
        'session_token' => $sessionToken,
        'response' => $botResponse
    ]);
}


}
