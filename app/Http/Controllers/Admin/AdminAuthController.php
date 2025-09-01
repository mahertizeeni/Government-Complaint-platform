<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation error', $validator->errors()->all());
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return ApiResponse::sendResponse(401, 'Invalid credentials', []);
        }

        $token = $admin->createToken('AdminPanel')->plainTextToken;

        return ApiResponse::sendResponse(200, 'Admin logged in successfully', [
            'token' => $token,
            'name'  => $admin->name,
            'email' => $admin->email,
        ]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|confirmed|min:8',
             Password::min(8)       // الطول الأدنى 8
            ->letters()       // لازم يحتوي أحرف
            ->mixedCase()     // لازم يحتوي حرف كبير وصغير
            ->numbers()       // لازم يحتوي أرقام        
            
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Validation error', $validator->errors()->all());
        }

        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('AdminPanel')->plainTextToken;

        return ApiResponse::sendResponse(201, 'Admin registered successfully', [
            'token' => $token,
            'name'  => $admin->name,
            'email' => $admin->email,
        ]);
    }

}
