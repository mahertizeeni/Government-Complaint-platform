<?php

namespace App\Http\Controllers;

use App\Services\ComplaintChatService;
use App\Services\GroqService;
use App\Services\AiComplaintAnalyzer;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComplaintChatController extends Controller
{
    protected $chatService;
    protected $groqService;
    protected $analyzer;

    public function __construct(
        ComplaintChatService $chatService,
        GroqService $groqService,
        AiComplaintAnalyzer $analyzer 
    ) {
        $this->chatService = $chatService;
        $this->groqService = $groqService;
        $this->analyzer = $analyzer; 
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
            $conversation = $this->chatService->getConversation($sessionToken);
        } catch (\Exception $e) {
            Log::error("Error getting conversation: " . $e->getMessage());
            return response()->json(['error' => 'خطأ في جلب المحادثة'], 500);
        }

        try {
            if ($this->conversationIsComplete($conversation)) {
                $data = $this->chatService->extractComplaintData($conversation);

                // تحليل مستوى الطارئية من الوصف
                $emergencyLevel = $this->analyzer->rateEmergencyLevel($data['description']);
                $data['is_emergency'] = $emergencyLevel ?? 1;

                Complaint::create($data);

                $this->chatService->clearConversation($sessionToken);
                $conversation = [];
            }
        } catch (\Exception $e) {
            Log::error("Error processing completed conversation: " . $e->getMessage());
        }

        try {
            $conversation[] = [
                'content' => $userMessage,
                'is_bot' => false,
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error("Error adding user message: " . $e->getMessage());
        }

        try {
            $botResponse = $this->groqService->generateResponse($conversation);
        } catch (\Exception $e) {
            Log::error("Error generating bot response: " . $e->getMessage());
            return response()->json(['error' => 'فشل في توليد الرد من الخدمة الخارجية'], 500);
        }

        try {
            $conversation[] = [
                'content' => $botResponse,
                'is_bot' => true,
                'timestamp' => now()->toIso8601String(),
            ];

            $this->chatService->saveConversation($sessionToken, $conversation);
        } catch (\Exception $e) {
            Log::error("Error saving conversation: " . $e->getMessage());
        }

        return response()->json([
            'response' => $botResponse,
        ]);
    }

    protected function conversationIsComplete(array $conversation): bool
    {
        if (count($conversation) >= 6) {
            return true;
        }

        $lastBotMessage = collect($conversation)
            ->reverse()
            ->firstWhere('is_bot', true);

        if ($lastBotMessage && str_contains($lastBotMessage['content'], 'تم استلام شكواك')) {
            return true;
        }

        return false;
    }
}