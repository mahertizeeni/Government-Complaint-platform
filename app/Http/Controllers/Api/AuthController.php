<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Models\Password_Reset_Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\NewRegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\PasswordResetToken;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required',
         'confirmed',
        Password::min(8)       // الطول الأدنى 8
            ->letters()       // لازم يحتوي أحرف
            ->mixedCase()     // لازم يحتوي حرف كبير وصغير
            ->numbers()       // لازم يحتوي أرقام        
            ],
            'national_id' => ['required', 'string', 'max:20','regex:/^06010/', 'unique:' . User::class],
        ],[],
         [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'national_id' => 'National ID',
        ]);

        if ($validator->fails())
        {
            return ApiResponse::sendResponse(422, 'Register Validation Errors',$validator->messages()->all());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => Crypt::encryptString($request->national_id), 
            'national_id_hash' => Hash::make($request->national_id),
            'password' => Hash::make($request->password),
        ]);
        $data['token'] = $user->createToken('ComplaintGoverment')->plainTextToken;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['national_id'] = $user->national_id;
        /* $token = $request->session()->token();
        $token = csrf_token(); */

        return ApiResponse::sendResponse(201, 'User Account Created Successfully', $data);
    }



    public function login(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => ['nullable', 'string', 'email','required_without:national_id'],
        'national_id' => ['nullable', 'string','required_without:email'],
        'password' => ['required'],
    ], [], [
        'email' => __('lang.email'),
        'national_id' => __('lang.national_id'),
        'password' => __('lang.password'),
    ]);

    if ($validator->fails()) {
        return ApiResponse::sendResponse(422, 'Login Validation Errors', $validator->errors());
    }

    $user = null;

    if ($request->email) {
        $user = User::where('email', $request->email)->first();
    } elseif ($request->national_id) {
        $user = User::all()->first(function($u) use ($request) {
            return Hash::check($request->national_id, $u->national_id_hash);
        });
    }

    if ($user && Hash::check($request->password, $user->password)) {
        $data['token'] = $user->createToken('MyAuthApp')->plainTextToken;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['national_id'] = Crypt::decryptString($user->national_id);
        return ApiResponse::sendResponse(200, 'Login Successfully', $data);
    }

    return ApiResponse::sendResponse(401, 'These credentials don\'t exist', null);
    }


    public function logout(Request $request)
    {
    $request->user()->currentAccessToken()->delete();
    return ApiResponse::sendResponse(200,'LOgout succsesfully', null);
    }

    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'], // email أو national_id
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation error', $validator->errors());
        }

        // البحث عن المستخدم بالإيميل أو national_id
        $user = User::where('email', $request->identifier)
                    ->orWhere('national_id', $request->identifier)
                    ->first();

        if (!$user) {
            return ApiResponse::sendResponse(401, "These credentials don't exist", null);
        }

        $code = strtoupper(Str::random(6));

        // إنشاء أو تحديث رمز الاستعادة
        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            ['token' => $code, 'created_at' => now()]
        );

        // إرسال الكود عبر البريد
        Mail::to($user->email)->send(new ResetPasswordMail($code));

        return ApiResponse::sendResponse(200, 'Reset code sent to your email');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation error', $validator->errors());
        }

        // التحقق من الكود
        $reset = PasswordResetToken::where('email', $request->email)
                    ->where('token', $request->token)
                    ->first();

        if (!$reset) {
            return ApiResponse::sendResponse(401, "Invalid or expired token", null);
        }

        // التحقق من مدة صلاحية الكود (15 دقيقة)
        if (now()->diffInMinutes($reset->created_at) > 15) {
            return ApiResponse::sendResponse(401, "Token expired", null);
        }

        // البحث عن المستخدم
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponse::sendResponse(404, "User not found", null);
        }

        // تحديث كلمة المرور
        $user->password = Hash::make($request->password);
        $user->save();

        // حذف الكود بعد الاستخدام
        $reset->delete();

        return ApiResponse::sendResponse(200, "Password has been reset successfully");
    }
}