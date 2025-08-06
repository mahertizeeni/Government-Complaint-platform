<?php

namespace App\Services;

use App\Models\City;
use App\Models\GovernmentEntity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class ComplaintChatService
{
    protected $ttl = 3600;

    public function getConversation(string $sessionToken): array
    {
        try {
            $history = Redis::get("complaint_chat:{$sessionToken}");
            $conversation = $history ? json_decode($history, true) : [];

            // إذا المحادثة جديدة، أضف رسالة system كمقدمة
            if (empty($conversation)) {
                $conversation[] = [
                    'content' => <<<EOT
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
                    'role' => 'system'
                ];
            }

            return $conversation;
        } catch (\Exception $e) {
            Log::error("Redis getConversation error: " . $e->getMessage());
            return [];
        }
    }

    public function saveConversation(string $sessionToken, array $messages): void
    {
        try {
            Redis::setex("complaint_chat:{$sessionToken}", $this->ttl, json_encode($messages));
        } catch (\Exception $e) {
            Log::error("Redis saveConversation error: " . $e->getMessage());
        }
    }

    public function clearConversation(string $sessionToken): void
    {
        try {
            Redis::del("complaint_chat:{$sessionToken}");
        } catch (\Exception $e) {
            Log::error("Redis clearConversation error: " . $e->getMessage());
        }
    }

    /**
     * استخراج بيانات الشكوى من المحادثة
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

        $descriptionCaptured = false;

        foreach ($conversation as $msg) {
            if (!$msg['is_bot']) {
                $text = trim($msg['content']);

                // خزن أول رسالة وصف فقط
                if (!$descriptionCaptured) {
                    $data['description'] = $text;
                    $descriptionCaptured = true;
                }

                // مطابقة المدينة
                if (!$data['city_id']) {
                    foreach ($cities as $city) {
                        if (mb_stripos($text, $city->name) !== false) {
                            $data['city_id'] = $city->id;
                            break;
                        }
                    }
                }

                // مطابقة الجهة الحكومية
                if (!$data['government_entity_id']) {
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
