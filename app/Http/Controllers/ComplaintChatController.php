<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Session;

// class SmartChatController extends Controller
// {
//     // system prompt يحضّر النموذج لتصرف كصديق:
//     private array $systemPrompt = [
//         'role'    => 'system',
//         'content' => <<<EOT
// أنت صديق مخلص للمستخدم، تستمع لشكواه وتساعده كأنها دردشة بين أصدقاء:
// - أولاً، أرحب وصديقي يحكيلي وصف مشكلته دون أسئلة مُسبقة.
// - بعد الوصف، أطلب اسم المدينة فقط.
// - ثم استنتج الجهة الحكومية المسؤولة عن الشكوى بناءً على الوصف والمدينة.
// - في النهاية، لخص الشكوى (الوصف، المدينة، الجهة) بأسلوب ودّي، واسأل: "هل ترغب أن أقدّم الشكوى رسميًّا بهذه التفاصيل؟"
// - استخدم اللغة العربية الفصحى المبسطة، وبأسلوب دردشة صديق.
// EOT
//     ];

//     public function chat(Request $request)
//     {
//         Log::info('🔁 STEP: ' . Session::get('current_step'));

//         $userMessage = trim($request->input('message', ''));
//         if ($userMessage === '') {
//             return response()->json(['error' => 'الرسالة مطلوبة.'], 400);
//         }

//         // خطوتان فقط: 1=وصف، 2=مدينة، 3=التلخيص+التأكيد
//         $currentStep = Session::get('current_step', 1);

//         // جلب تاريخ المحادثة السابق
//         $history = Session::get('chat_history', []);

//         // نضيف رسالة المستخدم
//         $history[] = ['role' => 'user', 'content' => $userMessage];

//         // نبني الـ messages للـ API
//         $messages = [ $this->systemPrompt ];
//         foreach ($history as $m) {
//             $messages[] = $m;
//         }

//         // حسب الخطوة نوجه المساعد أو نتركه يستنتج:
//         if ($currentStep === 1) {
//             // بعد الوصف، نطلب المدينة
//             $messages[] = [
//                 'role'    => 'assistant',
//                 'content' => "شكرًا للمشاركة، صديقي. بأي مدينة حدثت هذه المشكلة؟"
//             ];
//         } // بعد التعديل:
// elseif ($currentStep === 2) {
//     // هنا نرسل للموديل فقط هذا الـ prompt البسيط
//     $messages[] = [
//         'role'    => 'assistant',
//         'content' => "حسنًا، بناءً على الوصف والمدينة (دمشق)، استنتج الجهة الحكومية المسؤولة ولخص الشكوى كاملة."
//     ];
// }


//         // استدعاء الـ API
//         $response = Http::timeout(30)
//             ->retry(2, 1000)
//             ->withHeaders([
//                 'Authorization' => 'Bearer '.env('GROQ_API_KEY'),
//                 'Content-Type'  => 'application/json',
//             ])
//             ->post('https://api.groq.com/openai/v1/chat/completions', [
//                 'model'       => 'allam-2-7b',
//                 'messages'    => $messages,
//                 'temperature' => 0.3,
//             ]);

//         if (! $response->successful()) {
//             return response()->json(['error' => 'فشل الاتصال بخدمة AI.'], 500);
//         }

//         $aiReply = $response->json('choices.0.message.content');

//         // خزّن رد المساعد
//         $history[] = ['role' => 'assistant', 'content' => $aiReply];
//         Session::put('chat_history', $history);

//         // حدّث الخطوة
//         if ($currentStep < 3) {
//             Session::put('current_step', $currentStep + 1);
//         } else {
//             // أنهينا المحادثة التأكيدية
//             Session::forget('current_step');
//             // Session::forget('chat_history'); // إذا رغبت بإعادة البدء من جديد لاحقًا
//         }

//         return response()->json([
//             'ai_reply'  => $aiReply,
//             'step'      => Session::get('current_step'),
//         ]);
//     }
// }

namespace App\Http\Controllers;

use App\Services\ComplaintChatService;
use App\Services\GroqService;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComplaintChatController extends Controller
{
    protected $chatService;
    protected $groqService;

    public function __construct(ComplaintChatService $chatService, GroqService $groqService)
    {
        $this->chatService = $chatService;
        $this->groqService = $groqService;
    }

    public function handleChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_token' => 'required|string',
        ]);

        $sessionToken = $request->session_token;
        $userMessage = $request->message;

        try {
            // جلب المحادثة القديمة من Redis
            $conversation = $this->chatService->getConversation($sessionToken);
        } catch (\Exception $e) {
            Log::error("Error getting conversation: " . $e->getMessage());
            return response()->json(['error' => 'خطأ في جلب المحادثة'], 500);
        }

        try {
            // إذا المحادثة مكتملة، خزّن الشكوى واحذف المحادثة المؤقتة
            if ($this->conversationIsComplete($conversation)) {
                // استخراج البيانات من المحادثة
                $data = $this->chatService->extractComplaintData($conversation);

                // تخزين الشكوى في قاعدة البيانات
                Complaint::create($data);

                // حذف المحادثة من Redis
                $this->chatService->clearConversation($sessionToken);

                // إعادة تعيين المحادثة لمحادثة جديدة
                $conversation = [];
            }
        } catch (\Exception $e) {
            Log::error("Error processing completed conversation: " . $e->getMessage());
        }

        try {
            // إضافة رسالة المستخدم للمحادثة
            $conversation[] = [
                'content' => $userMessage,
                'is_bot' => false,
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error("Error adding user message: " . $e->getMessage());
        }

        try {
            // توليد رد البوت من خدمة Groq
            $botResponse = $this->groqService->generateResponse($conversation);
        } catch (\Exception $e) {
            Log::error("Error generating bot response: " . $e->getMessage());
            return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية'], 500);
        }

        try {
            // إضافة رد البوت للمحادثة
            $conversation[] = [
                'content' => $botResponse,
                'is_bot' => true,
                'timestamp' => now()->toIso8601String(),
            ];

            // حفظ المحادثة في Redis
            $this->chatService->saveConversation($sessionToken, $conversation);
        } catch (\Exception $e) {
            Log::error("Error saving conversation: " . $e->getMessage());
        }

        return response()->json([
            'response' => $botResponse,
        ]);
    }

    /**
     * التحقق من اكتمال المحادثة
     * @param array $conversation
     * @return bool
     */
    protected function conversationIsComplete(array $conversation): bool
    {
        // قاعدة: إذا عدد الرسائل 6 أو أكثر اعتبرها مكتملة
        if (count($conversation) >= 6) {
            return true;
        }

        // أو التحقق من وجود رسالة بوت تحتوي جملة تأكيدية
        $lastBotMessage = collect($conversation)
            ->reverse()
            ->firstWhere('is_bot', true);

        if ($lastBotMessage && str_contains($lastBotMessage['content'], 'تم استلام شكواك')) {
            return true;
        }

        return false;
    }
}
