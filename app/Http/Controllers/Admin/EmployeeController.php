<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller

{
    public function index()
    {
        $employees = Employee::all();
        return ApiResponse::sendResponse(200, 'Employee List Retrieved Successfully', $employees);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:employees',
            'password' => 'required|string|confirmed',
            'entity' => 'required|in:الكهرباء,المياه,البلدية,المالية,العقارية,الصحة,التربية,النقل,الاتصالات والتقانة,التعليم العالي,التجارة الداخلية وحماية المستهلك,الجرائم الرقمية',
            'city' => 'nullable|string',
        ]);

        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'entity' => $request->entity,
            'city' => $request->city,
        ]);

        return ApiResponse::sendResponse(201, 'Employee Created Successfully', $employee);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'entity' => 'nullable|in:الكهرباء,المياه,البلدية,المالية,العقارية,الصحة,التربية,النقل,الاتصالات والتقانة,التعليم العالي,التجارة الداخلية وحماية المستهلك,الجرائم الرقمية',
            'city' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->update($request->only('entity', 'city'));

        return ApiResponse::sendResponse(200, 'Employee Updated Successfully', $employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return ApiResponse::sendResponse(200, 'Employee Deleted Successfully', null);
    }
    public function show($id)
{
    $employee = Employee::with(['governmentEntity'])->findOrFail($id);

    return ApiResponse::sendResponse(
        200,
        'Employee fetched successfully',
        new EmployeeResource($employee)
    );
}
}
