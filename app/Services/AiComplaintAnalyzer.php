<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiComplaintAnalyzer
{
    public function rateEmergencyLevel(string $description): ?int
    {
        $apiKey = env('OPENROUTER_API_KEY');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'mistralai/mistral-7b-instruct',
                'temperature' => 0,
                'messages' => [
                    ['role' => 'system', 'content' => 'أنت مساعد ذكي لتحليل شكاوى المواطنين. مهمتك تحديد مدى طارئ الشكوى اعتمادًا على وصفها.

قيّم الطارئية باستخدام رقم واحد فقط من 1 إلى 3، بدون شرح، فقط الرقم:

1 = شكوى غير طارئة (مثال: مشكلة بسيطة، تأخير في خدمة، خطر منخفض)  
2 = شكوى متوسطة الطارئة (مثال: مشاكل تؤثر على جودة الخدمة وقد تتفاقم لاحقًا)  
3 = شكوى طارئة جداً (مثال: خطر مباشر على السلامة، الصحة، أو الأمن)

رجاءً أعد فقط الرقم المناسب، بدون أي كلمات أو رموز إضافية.'],
                    ['role' => 'user', 'content' => $description],
                ],
            ]);

            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                Log::warning('Unexpected AI response format.', $data);
                return null;
            }

            $content = $data['choices'][0]['message']['content'];
            preg_match('/\b[1-3]\b/', $content, $matches);
            return isset($matches[0]) ? intval($matches[0]) : null;

        } catch (\Exception $e) {
            Log::error('AI emergency level detection failed: ' . $e->getMessage());
            return null;
        }
    }
}
