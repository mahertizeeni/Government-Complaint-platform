<?php

namespace App\Http\Controllers;

use id;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SmartChatController extends Controller
{
    
     
    public function chat(Request $request)
    {
        $userMessage = $request->input('message');

        if (!$userMessage) {
            return response()->json(['error' => 'الرسالة مطلوبة.'], 400);
        }

        $systemMessage = [
            'role' => 'system',
            'content' => "أنت مساعد ذكي لمنصة تقديم الشكاوى الإلكترونية.
دورك هو مساعدة المستخدمين في تعبئة تفاصيل شكواهم بطريقة مبسطة ومنظمة.
استخدم اللغة العربية الفصحى المبسطة، وتواصل بلطف واحترافية.
ابدأ دائماً برسالة ترحيبية قصيرة عندما تكون أول رسالة.
ثم اسأل المستخدم عن موضوع شكواه بشكل عام.

بعد أن يحدد المستخدم موضوع الشكوى:
- حاول أن تستنتج بنفسك الجهة المرتبطة بالشكوى بناءً على الموضوع (الخيارات: الكهرباء، المياه، البلدية، المالية، العقارية).
- إذا لم يكن واضحاً، اطلب من المستخدم تحديد الجهة بشكل مباشر.

بعد تحديد الجهة، تابع الأسئلة بالتسلسل التالي، سؤالاً واحداً في كل مرة، وانتظر إجابة المستخدم قبل المتابعة:
1. ما هو موضوع الشكوى؟ (إذا لم يكن واضحًا من البداية)
2. ماذا حدث بالتفصيل؟
3. متى وأين وقع الحادث أو المشكلة؟
4. من هم الأطراف أو الجهات المشاركة أو المسؤولة؟
5. ما هو العنوان أو الموقع المرتبط بالشكوى؟
6. هل لديك مستندات أو صور داعمة للشكوى؟

ملاحظات مهمة:
- لا ترسل أكثر من سؤال في نفس الوقت.
- لا تُنهي المحادثة إلا بعد جمع جميع المعلومات المذكورة.
- كن صبوراً وشجع المستخدم إذا كانت إجاباته قصيرة أو غير واضحة.
- إذا كانت إجابة المستخدم غامضة، اطلب منه التوضيح بطريقة مهذبة.

بعد جمع جميع التفاصيل:
- قم بتلخيص الشكوى بشكل مرتب وواضح.
- ثم اسأل المستخدم: (هل أنت متأكد أنك تريد تقديم الشكوى بهذه التفاصيل؟)

لا تقدم الشكوى قبل التأكد من موافقة المستخدم النهائية.
تصرف بلطف واحترام كامل طوال المحادثة."
        ];

        $payload = [
            'model' => 'allam-2-7b',
            'messages' => [
                $systemMessage,
                ['role' => 'user', 'content' => $userMessage],
            ]
        ];
        $apikey = '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';
        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(3, 2000)
                ->withHeaders([
                    'Authorization' => 'Bearer ' .$apikey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

            if ($response->successful()) {
                $aiReply = $response->json()['choices'][0]['message']['content'];
                if ($userMessage == 'المدينة؟') {
                    Session::put('city', $aiReply);  // مثال على تخزين المدينة
                }
                if ($userMessage == 'الجهة') {
                    Session::put('intity', $aiReply);  // مثال على تخزين المدينة
                }

                return response()->json([
                    'ai_reply' => $aiReply
                ]);
            } else {
                return response()->json(['error' => 'حدث خطأ أثناء الاتصال بخدمة Groq'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
    public function submitComplaint(Request $request)
    {
        // استرجاع البيانات من الجلسة
        $city = Session::get('city');
        $intity = Session::get('intity');
        $description = $request->input('description');
        $attachments = ''; 

        // تخزين الشكوى في قاعدة البيانات
        $complaint = new Complaint();
        // $complaint->user_id = auth()->id();  
        $complaint->city = $city;
        $complaint->intity = $intity;
        $complaint->description = $description;
        $complaint->attachments = $attachments;
        $complaint->is_emergency = false;  // إذا كان هناك طارئ
        $complaint->status = 'pending';
        $complaint->save();

        // رد على المستخدم
        return response()->json(['message' => 'تم تقديم الشكوى بنجاح']);
    }
}
