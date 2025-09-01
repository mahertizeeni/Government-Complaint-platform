<?php

namespace App\Http\Controllers\employee\auth;


use App\Models\Employee;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\GovernmentEntity;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeLoginRequest;
use App\Http\Requests\EmployeeRegisterRequest;
use App\Models\City;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(EmployeeRegisterRequest $request)
    {
        $validated = $request->validated();
    // التحقق من اسم الجهة الحكومية المدخلة
       $governmentEntity = GovernmentEntity::where('name',$validated['government_entity'])->first();

    if (!$governmentEntity)
     {
        return ApiResponse::sendResponse(404, 'Government Entity Not Found', []);
     }
      $government_entity_id = $governmentEntity->id;

      $city=City::where('name',$validated['city'])->first();
      if(!$city)
      {
        return ApiResponse::sendResponse(404 , 'City Not Found',[]);
      }
      $city_id = $city->id ;


        $employee = Employee::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'government_entity_id' => $government_entity_id,
            'city_id' => $city_id,
            'password' => Hash::make($validated['password']),
        ]);
        // تجهيز توكن مصادقة
        $data['token'] = $employee->createToken('EmployeeUser')->plainTextToken;
        $data['name'] = $employee->name;
        $data['email'] = $employee->email;

        return ApiResponse::sendResponse(201, 'Employee Registered Successfully', $data);
    }
// LogIn Function
    public function login(EmployeeLoginRequest $request)
    {
        $validated = $request->validated();

        $employee = Employee::where('email', $validated['email'])->first();

        if (!$employee) {
        return ApiResponse::sendResponse(404, 'Email not found', []);
        }

         if (!Hash::check($validated['password'], $employee->password)) {
        return ApiResponse::sendResponse(401, 'Incorrect password', []);
         }
        // انشاء توكن لتحقق
        $data['token'] = $employee->createToken('EmployeeUser')->plainTextToken;
        $data['name'] = $employee->name;
        $data['email'] = $employee->email;

        return ApiResponse::sendResponse(200, 'Employee Logged In Successfully', $data);
    }
// Logout Function
   public function logout(Request $request)
{
    $token = $request->user('employee')?->currentAccessToken();
     if($token)
     {
        $token->delete();
        return ApiResponse::sendResponse(200, 'Logged Out Successfully', []);
     }

     return ApiResponse::sendResponse(400, 'No active session found', []);
}

}
