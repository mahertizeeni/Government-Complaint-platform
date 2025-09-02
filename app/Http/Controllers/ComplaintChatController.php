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

    // الرسالة مطلوبة دائمًا
    if ($userMessage === '') {
        return response()->json(['error' => 'الرسالة مطلوبة'], 422);
    }

    // إذا ما وصل session_token من الفرونت -> أنشئ واحد جديد وأستمر بنفس المنطق
    if (empty($sessionToken)) {
        $sessionToken = (string) \Illuminate\Support\Str::uuid();
    }

    // استرجاع/إنشاء المحادثة من الداتابيز
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
                'session_token' => $sessionToken, // رجّع التوكن للفرونت
                'response' => "تم استلام شكواك بنجاح، وشكرًا لتواصلك معنا."
            ]);
        } catch (\Exception $e) {
            Log::error("فشل في حفظ الشكوى: " . $e->getMessage());
            return response()->json(['error' => 'حدث خطأ أثناء حفظ الشكوى.'], 500);
        }
    }

    // توليد رد تلقائي من الذكاء الاصطناعي
    try {
        $botResponse = $this->groqService->generateResponse($conversation);
    } catch (\Exception $e) {
        Log::error("فشل في توليد الرد: " . $e->getMessage());
        return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية.'], 500);
    }

    // أضف رد الذكاء الاصطناعي للمحادثة
    $conversation[] = [
        'content' => $botResponse,
        'is_bot' => true,
        'timestamp' => now()->toIso8601String(),
    ];

    // حفظ المحادثة (مهم علشان ما ينسى السياق)
    $this->chatService->saveConversation($sessionToken, $conversation);

    // إرجاع الرد مع session_token دائماً
    return response()->json([
        'session_token' => $sessionToken,
        'response' => $botResponse
    ]);
}

}
