<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\GroqService;
use Illuminate\Support\Facades\DB;
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
    // لو ما أرسل الفرونت session_token نولّد واحد جديد فوراً
    $sessionToken = $request->input('session_token') ?? (string) Str::uuid();
    $userMessage = $request->input('message');

    //سجل للتشخيص
    Log::info('Chat request payload (handled): ', array_merge($request->all(), ['resolved_session_token' => $sessionToken]));

    // ما نرجع 400 بسبب عدم وجود session_token — نحتاج فقط الرسالة
    if (!$userMessage) {
        return response()->json([
            'error' => 'الرسالة مفقودة',
            'session_token' => $sessionToken
        ], 400);
    }

    // استرجاع المحادثة (ستُنشأ لو الـ session جديد)
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
            DB::beginTransaction();
            Complaint::create($data);
            $this->chatService->clearConversation($sessionToken);
            DB::commit();

            return response()->json([
                'response' => "تم استلام شكواك بنجاح، وشكرًا لتواصلك معنا.",
                'session_token' => $sessionToken
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل في حفظ الشكوى: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'حدث خطأ أثناء حفظ الشكوى.',
                'session_token' => $sessionToken
            ], 500);
        }
    }

    // توليد رد تلقائي من الذكاء الاصطناعي
    try {
        $botResponse = $this->groqService->generateResponse($conversation);
    } catch (\Exception $e) {
        Log::error("فشل في توليد الرد: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'error' => 'فشل في توليد الرد من الخدمة الخارجية.',
            'session_token' => $sessionToken
        ], 500);
    }

    // أضف رد الذكاء الاصطناعي للمحادثة
    $conversation[] = [
        'content' => $botResponse,
        'is_bot' => true,
        'timestamp' => now()->toIso8601String(),
    ];

    // حفظ المحادثة
    $this->chatService->saveConversation($sessionToken, $conversation);

    return response()->json([
        'response' => $botResponse,
        'session_token' => $sessionToken
 
    ]);
}
}
