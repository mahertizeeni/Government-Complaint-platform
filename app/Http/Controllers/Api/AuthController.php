<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\NewRegisterRequest;
use App\Models\Password_Reset_Token;
use App\Models\Password_reset_token as ModelsPassword_reset_token;
use App\Models\PasswordReset as ModelsPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
            'national_id' => $request->national_id,
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

/*     public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'password' => ['required'],
        ], [], [
            'email' => __('lang.email'),
            'password' => __('lang.password'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Login Validation Errors', $validator->errors());
        }


    $user = User::where('email', $request->identifier)// محاولة تسجيل الدخول باستخدام البريد الإلكتروني أو الرقم الوطني
    ->orWhere('national_id', $request->identifier)
    ->first();

    if ($user && Hash::check($request->password, $user->password)) {
    $data['token'] = $user->createToken('MyAuthApp')->plainTextToken;
    $data['name'] = $user->name;
    $data['email'] = $user->email;
    $data['national_id'] = $user->national_id;
    return ApiResponse::sendResponse(200, 'Login Successfully', $data);
    }
           return ApiResponse::sendResponse(401, 'These credentials doesn\'t exist', null);

    } */


    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => ['nullable', 'string', 'email','required_without:national_id'], // حقل البريد الإلكتروني
        'national_id' => ['nullable', 'string','required_without:email'], // حقل الرقم الوطني
        'password' => ['required'],
    ], [], [
        'email' => __('lang.email'),
        'national_id' => __('lang.national_id'),
        'password' => __('lang.password'),
    ]);

    if ($validator->fails()) {
        return ApiResponse::sendResponse(422, 'Login Validation Errors', $validator->errors());
    }

    // البحث عن المستخدم باستخدام البريد الإلكتروني أو الرقم الوطني
    $user = null;

    if ($request->email) {
        $user = User::where('email', $request->email)->first();
    } elseif ($request->national_id) {
        $user = User::where('national_id', $request->national_id)->first();
    }

    if ($user && Hash::check($request->password, $user->password)) {
        $data['token'] = $user->createToken('MyAuthApp')->plainTextToken;
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['national_id'] = $user->national_id;
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
        'identifier' => ['required', 'string'], // حقل واحد للبريد الإلكتروني أو الرقم الوطني
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }


    $user = User::where('email', $request->identifier)  // تحقق من وجود المستخدم باستخدام البريد الإلكتروني أو الرقم الوطني
                ->orWhere('national_id', $request->identifier)
                ->first();

    if (!$user) {
        return response()->json(['message' => 'User  not found'], 404);
    }

    // إنشاء كود إعادة تعيين مكون من ستة أحرف
    $code = strtoupper(Str::random(6));

    // تخزين الكود في قاعدة البيانات
    Password_Reset_Token::create([
        'email' => $user->email,
        'token' => $code,
        'created_at' => now(),
    ]);

    // إرسال البريد الإلكتروني
    Mail::to($user->email)->send(new ResetPasswordMail($code));

    return response()->json(['message' => 'Reset code sent to your email'], 200);
}/*  */
}
