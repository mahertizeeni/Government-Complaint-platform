<?php
namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

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
}
