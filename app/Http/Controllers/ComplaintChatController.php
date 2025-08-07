<?php

// namespace App\Http\Controllers;

// use App\Services\ComplaintChatService;
// use App\Services\GroqService;
// use App\Services\AiComplaintAnalyzer;
// use App\Models\Complaint;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class ComplaintChatController extends Controller
// {
//     protected $chatService;
//     protected $groqService;
//     protected $analyzer;

//     public function __construct(
//         ComplaintChatService $chatService,
//         GroqService $groqService,
//         AiComplaintAnalyzer $analyzer
//     ) {
//         $this->chatService = $chatService;
//         $this->groqService = $groqService;
//         $this->analyzer = $analyzer;
//     }

//     public function handleChat(Request $request)
//     {
//         $request->validate([
//             'message' => 'required|string',
//             'session_token' => 'required|string',
//         ]);

//         $sessionToken = $request->session_token;
//         $userMessage = trim($request->message);

//         // جلب المحادثة السابقة أو تهيئة جديدة
//         $conversation = $this->chatService->getConversation($sessionToken);

//         // إضافة رسالة المستخدم الجديدة
//         $conversation[] = [
//             'content' => $userMessage,
//             'is_bot' => false,
//             'timestamp' => now()->toIso8601String(),
//         ];

//         // تحقق هل المحادثة كاملة (حسب شروطك)
//         if ($this->conversationIsComplete($conversation)) {
//             // استخرج البيانات
//             $data = $this->chatService->extractComplaintData($conversation);

//             // تقييم مستوى الطوارئ
//             $emergencyLevel = $this->analyzer->rateEmergencyLevel($data['description'] ?? '') ?? 1;
//             $data['is_emergency'] = $emergencyLevel;

//             // حفظ الشكوى
//             Complaint::create($data);

//             // مسح المحادثة من التخزين (لإنهاء الجلسة)
//             $this->chatService->clearConversation($sessionToken);

//             // رد ثابت يؤكد الاستلام
//             $botResponse = "تم استلام شكواك بنجاح، وشكرًا لتواصلك معنا.";

//             // لا نحتاج لحفظ المحادثة بعد الآن لأننا انتهينا
//         } else {
//             // التوليد عبر Groq API بناءً على المحادثة كاملة
//             try {
//                 $botResponse = $this->groqService->generateResponse($conversation);
//             } catch (\Exception $e) {
//                 Log::error("Error generating bot response: " . $e->getMessage());
//                 return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية'], 500);
//             }

//             // إضافة رد البوت للمحادثة
//             $conversation[] = [
//                 'content' => $botResponse,
//                 'is_bot' => true,
//                 'timestamp' => now()->toIso8601String(),
//             ];

//             // حفظ المحادثة مع الرد الجديد
//             $this->chatService->saveConversation($sessionToken, $conversation);
//         }

//         return response()->json([
//             'response' => $botResponse,
//         ]);
//     }

//     protected function conversationIsComplete(array $conversation): bool
//     {
//         // شرط بسيط: إذا جمعنا وصف، جهة، ومدينة في الرسائل
//         $hasDescription = false;
//         $hasEntity = false;
//         $hasCity = false;

//         foreach ($conversation as $msg) {
//             if (!$msg['is_bot']) {
//                 $text = $msg['content'];

//                 if (!$hasDescription && mb_strlen($text) > 20) {
//                     $hasDescription = true;
//                 }

//                 if (!$hasEntity && preg_match('/(وزارة|بلدية|التربية|الكهرباء|الصحة)/ui', $text)) {
//                     $hasEntity = true;
//                 }

//                 if (!$hasCity && preg_match('/(دمشق|حلب|حمص|اللاذقية|طرطوس|)/ui', $text)) {
//                     $hasCity = true;
//                 }
//             }
//         }

//         return $hasDescription && $hasEntity && $hasCity;
//     }
// }

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiComplaintAnalyzer;



use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
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

    public function chat(Request $request)
    {
        $sessionToken = $request->input('session_token');
        $userMessage = $request->input('message');

        if (!$sessionToken || !$userMessage) {
            return response()->json(['error' => 'بيانات مفقودة'], 400);
        }

        // استرجاع المحادثة من الداتابيز (أو من الميموري)
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

        // حفظ المحادثة
        $this->chatService->saveConversation($sessionToken, $conversation);

        // إرجاع الرد
        return response()->json([
            'response' => $botResponse
        ]);
    }
}
