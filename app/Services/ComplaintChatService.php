<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\City;
use App\Models\GovernmentEntity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComplaintChatService
{
    public function getConversation(string $sessionToken): array
    {
        try {
            $chat = ChatSession::firstOrCreate(
                ['session_token' => $sessionToken],
                ['conversation' => null]
            );

            $conversation = $chat->conversation ? json_decode($chat->conversation, true) : [];

            // إذا المحادثة فارغة أضف رسالة system تمهيدية
            if (empty($conversation)) {
                $conversation[] = [
                    'content' => <<<'EOT'
أنت مساعد ذكي تتحدث فقط باللغة العربية الفصحى.

مهمتك هي مساعدة المواطن على تقديم شكوى عبر جمع المعلومات التالية:
1. وصف تفصيلي للمشكلة  
2. اسم الجهة الحكومية المسؤولة  
3. المدينة التي حدثت فيها المشكلة

✅ بعد أن يرسل المستخدم وصف المشكلة، أظهر تعليقًا تعاطفيًا صغيرًا يعكس فهمك للوضع، ثم تابع بالسؤال التالي.

❌ لا تطلب بريدًا إلكترونيًا أو رقم هاتف  
✅ لا تكرر الأسئلة التي تمّت الإجابة عليها  
✅ تابع من حيث توقفت فقط  
✅ لا تخرج عن السياق الرسمي والمفيد
EOT,
                    'is_bot' => true,
                    'timestamp' => now()->toIso8601String(),
                    'role' => 'system',
                ];

                $chat->conversation = json_encode($conversation);
                $chat->save();
            }

            return $conversation;
        } catch (\Exception $e) {
            Log::error("DB getConversation error: " . $e->getMessage());
            return [];
        }
    }

    public function saveConversation(string $sessionToken, array $messages): void
    {
        try {
            ChatSession::updateOrCreate(
                ['session_token' => $sessionToken],
                ['conversation' => json_encode($messages)]
            );
        } catch (\Exception $e) {
            Log::error("DB saveConversation error: " . $e->getMessage());
        }
    }

    public function clearConversation(string $sessionToken): void
    {
        try {
            ChatSession::where('session_token', $sessionToken)->delete();
        } catch (\Exception $e) {
            Log::error("DB clearConversation error: " . $e->getMessage());
        }
    }

    /**
     * استخراج بيانات الشكوى من المحادثة.
     */
    public function extractComplaintData(array $conversation): array
    {
        $data = [
            'user_id' => Auth::id(),
            'city_id' => null,
            'government_entity_id' => null,
            'description' => null,
        ];

        $cities = City::all();
        $entities = GovernmentEntity::all();
        $firstDesc = false;

        foreach ($conversation as $msg) {
            if (!$msg['is_bot']) {
                $text = trim($msg['content']);

                if (!$firstDesc) {
                    $data['description'] = $text;
                    $firstDesc = true;
                }

                if (is_null($data['city_id'])) {
                    foreach ($cities as $city) {
                        if (mb_stripos($text, $city->name) !== false) {
                            $data['city_id'] = $city->id;
                            break;
                        }
                    }
                }

                if (is_null($data['government_entity_id'])) {
                    foreach ($entities as $entity) {
                        if (mb_stripos($text, $entity->name) !== false) {
                            $data['government_entity_id'] = $entity->id;
                            break;
                        }
                    }
                }
            }
        }

        return $data;
    }
}
