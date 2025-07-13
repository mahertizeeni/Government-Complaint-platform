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
            return $history ? json_decode($history, true) : [];
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
        'description' => '',
    ];

    $cities = City::all();
    $entities = GovernmentEntity::all();

    $descriptionCaptured = false;

    foreach ($conversation as $msg) {
        if (!$msg['is_bot']) {
            $text = trim($msg['content']);

            // التحقق إذا كانت الرسالة وصف
            if (!$descriptionCaptured && mb_strlen($text) > 40) {
                $data['description'] = $text;
                $descriptionCaptured = true;
            }

            $words = explode(' ', $text);

            // مطابقة المدينة
            if (!$data['city_id']) {
                foreach ($words as $word) {
                    foreach ($cities as $city) {
                        similar_text(trim($city->name), trim($word), $percent);
                        if ($percent >= 70) {
                            $data['city_id'] = $city->id;
                            break 2;
                        }
                    }
                }
            }

            // مطابقة الجهة الحكومية
            if (!$data['government_entity_id']) {
                foreach ($words as $word) {
                    foreach ($entities as $entity) {
                        similar_text(trim($entity->name), trim($word), $percent);
                        if ($percent >= 70) {
                            $data['government_entity_id'] = $entity->id;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    return $data;
}

}
