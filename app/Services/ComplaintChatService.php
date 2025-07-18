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

            $words = explode(' ', $text);

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