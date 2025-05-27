<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\CyberComplaint;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmployeeCyberComplaintsController extends Controller
{
 use AuthorizesRequests;
 public function getComplaints(Request $request)
 {
   $this->authorize('viewAny',CyberComplaint::class);
   $employee = Auth::user();
   if(!($employee instanceof Employee))
   {
    return ApiResponse::sendResponse(403,'Unauthorized',[]);
   }
   $complaints = CyberComplaint::all();
    return ApiResponse::sendResponse(200,'Get Complaints',$complaints);

 }
}
