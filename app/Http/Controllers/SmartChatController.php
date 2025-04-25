<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmartChatController extends Controller
{
    function chat(Request $request){

        $userMessage = $request->input('message');
        $systemMessage = [
            'role' => 'system',
            'content' => "أنت مساعد ذكي لمنصة شكاوى حكومية. هدفك هو جمع تفاصيل الشكوى من المستخدم خطوة بخطوة. استخدم اللغة العربية الفصحى، واسأل سؤالًا واحدًا فقط في كل مرة.
    
            ابدأ بسؤاله: ما هو موضوع الشكوى؟
    
            بعدها اسأل بالترتيب:
            1. ماذا حدث بالضبط؟
            2. متى وأين وقع الحدث؟
            3. من هم الأطراف أو الجهات المعنية؟
            4. ما العنوان أو الموقع المرتبط بالشكوى؟
            5. هل تملك مستندات أو صور داعمة؟
            6. هل ترغب في تقديم الشكوى الآن؟
    
            لا تقم بإرسال الشكوى إلا بعد جمع جميع التفاصيل السابقة."
        ];
    
        $response = Http::withHeader([
            'Authorziation'=>'Bearer'.env('GROQ_API_KEY'),
            ])->post('https://api.groq.com/openai/v1/chat/completions',[
                'model'=>'allam-2-7b',
                'messages'=>[
                    $systemMessage,
                    ['role'=>'user','content'=>$userMessage]
                ],

            ]);
            $replay=$response['choises'][0]['message']['content'];
            
            
    }
}
