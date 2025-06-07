<?php

namespace App\Http\Controllers\employee\auth;

use id;
use App\Models\Employee;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\GovernmentEntity;
use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'password' => ['required', 'confirmed'],
            'government_entity' => 'required',
            'city'=>['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Register Validation Error', $validator->errors());
        }

       $governmentEntity = GovernmentEntity::where('name', $request->government_entity)->first();

    if (!$governmentEntity)
     {
        return ApiResponse::sendResponse(404, 'Government Entity Not Found', []);
     }  
      $government_entity_id = $governmentEntity->id;

      $city=City::where('name',$request->city)->first();
      if(!$city)
      {
        return ApiResponse::sendResponse(404 , 'City Not Found',[]);
      }
      $city_id = $city->id ;


        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'government_entity_id' => $government_entity_id,
            'city_id' => $city_id,
            'password' => Hash::make($request->password),
        ]);

        $data['token'] = $employee->createToken('EmployeeUser')->plainTextToken;
        $data['name'] = $employee->name;
        $data['email'] = $employee->email;

        return ApiResponse::sendResponse(201, 'Employee Registered Successfully', $data);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(422, 'Login Validation Error', $validator->errors());
        }

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return ApiResponse::sendResponse(401, 'Invalid credentials', null);
        }

        $data['token'] = $employee->createToken('EmployeeUser')->plainTextToken;
        $data['name'] = $employee->name;
        $data['email'] = $employee->email;

        return ApiResponse::sendResponse(200, 'Employee Logged In Successfully', $data);
    }

    // public function logout(Request $request){

    //     $request->user('employee')->currentAccessToken()->delete();
    //     return ApiResponse::sendResponse(200,'Louged Out Successfully',[]);
    // }
    public function logout(Request $request)
{
    $request->user('employee')->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged Out Successfully',
        'data' => []
    ], 200);
}

}
