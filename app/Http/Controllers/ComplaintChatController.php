<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GroqService;
use Illuminate\Support\Facades\Log;
use App\Services\AiComplaintAnalyzer;
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
    $userMessage = trim((string) $request->input('message'));
    if ($userMessage === '') {
        return response()->json(['error' => 'الرسالة مطلوبة'], 422);
    }

    // إذا ما وصل توكن من الفرونت → أنشئ واحد
    $sessionToken = $request->input('session_token');
    if (empty($sessionToken)) {
        // ممكن تستخدم UUID أو random_bytes، الاثنين مناسبين
        $sessionToken = (string) Str::uuid(); // مثال: "8c4d...-..."
        // أو: $sessionToken = bin2hex(random_bytes(16)); // "f3ab9c..."
    }

    // استرجاع/إنشاء المحادثة
    $conversation = $this->chatService->getConversation($sessionToken);

    // أضف رسالة المستخدم
    $conversation[] = [
        'content' => $userMessage,
        'is_bot' => false,
        'timestamp' => now()->toIso8601String(),
    ];

    // تحقق من الاكتمال
    if ($this->chatService->isConversationComplete($conversation)) {
        $data = $this->chatService->extractComplaintData($conversation);
        $data['is_emergency'] = $this->analyzer->rateEmergencyLevel($data['description'] ?? '') ?? 1;

        try {
            Complaint::create($data);
            $this->chatService->clearConversation($sessionToken);

            return response()->json([
                'session_token' => $sessionToken, // رجّع التوكن دائمًا
                'response' => "تم استلام شكواك بنجاح، وشكرًا لتواصلك معنا."
            ]);
        } catch (\Exception $e) {
            Log::error("فشل في حفظ الشكوى: " . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء حفظ الشكوى.'], 500);
        }
    }

    // توليد رد الذكاء الاصطناعي
    try {
        $botResponse = $this->groqService->generateResponse($conversation);
    } catch (\Exception $e) {
        Log::error("فشل في توليد الرد: " . $e->getMessage());
        return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية.'], 500);
    }

    // أضف رد البوت واحفظ
    $conversation[] = [
        'content' => $botResponse,
        'is_bot' => true,
        'timestamp' => now()->toIso8601String(),
    ];
    $this->chatService->saveConversation($sessionToken, $conversation);

    return response()->json([
        'session_token' => $sessionToken, // مهم: دايمًا رجّعه
        'response' => $botResponse
    ]);}
}
