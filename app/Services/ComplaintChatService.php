<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\City;
use App\Models\GovernmentEntity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComplaintChatService
{
    public function isConversationComplete(array $conversation): bool
{
    $data = $this->extractComplaintData($conversation);
    return !empty($data['description']) && !empty($data['government_entity_id']) && !empty($data['city_id']);
}

 public function extractComplaintData(array $conversation): array
    {
        $data = [
            'user_id' => Auth::id(),
            'description' => '',
            'government_entity_id' => null,
            'city_id' => null,
        ];

        // كلمات أو عبارات لازم نتجاهلها (تحيات، افتتاحية)
        $ignorePhrases = [
            'مرحبا',
            'السلام عليكم',
            'هاي',
            'اهلا',
            'بدي قدم شكوى',
            'ممكن اشتكي',
            'تحية طيبة',
        ];

        foreach ($conversation as $message) {
            if (!empty($message['is_bot'])) {
                continue;
            }

            $content = trim($message['content']);

            // ✅ التحقق من الوصف
            if (empty($data['description'])) {
                if (
                    mb_strlen($content) > 15 && // لازم النص يكون طويل كفاية
                    !in_array(mb_strtolower($content), array_map('mb_strtolower', $ignorePhrases)) // مو من التحيات
                ) {
                    $data['description'] = $content;
                    continue;
                }
            }

            // ✅ التحقق من الجهة الحكومية
            if (!$data['government_entity_id']) {
                $entity = \App\Models\GovernmentEntity::where('name', 'like', "%$content%")->first();
                if ($entity) {
                    $data['government_entity_id'] = $entity->id;
                    continue;
                }
            }

            // ✅ التحقق من المدينة
            if (!$data['city_id']) {
                $city = \App\Models\City::where('name', 'like', "%$content%")->first();
                if ($city) {
                    $data['city_id'] = $city->id;
                }
            }
        }

        return $data;
    }


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
    // public function extractComplaintData(array $conversation): array
    // {
    //     $data = [
    //         'user_id' => Auth::id(),
    //         'city_id' => null,
    //         'government_entity_id' => null,
    //         'description' => null,
    //     ];

    //     $cities = City::all();
    //     $entities = GovernmentEntity::all();
    //     $firstDesc = false;

    //     foreach ($conversation as $msg) {
    //         if (!$msg['is_bot']) {
    //             $text = trim($msg['content']);

    //             if (!$firstDesc) {
    //                 $data['description'] = $text;
    //                 $firstDesc = true;
    //             }

    //             if (is_null($data['city_id'])) {
    //                 foreach ($cities as $city) {
    //                     if (mb_stripos($text, $city->name) !== false) {
    //                         $data['city_id'] = $city->id;
    //                         break;
    //                     }
    //                 }
    //             }

    //             if (is_null($data['government_entity_id'])) {
    //                 foreach ($entities as $entity) {
    //                     if (mb_stripos($text, $entity->name) !== false) {
    //                         $data['government_entity_id'] = $entity->id;
    //                         break;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     return $data;
    // }
}
