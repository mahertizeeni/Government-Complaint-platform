<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class EmployeeComplaintsController extends Controller
{
 public function getComplaints(Request $request)
 {
    $employee = Auth::user();
    $complaints = Complaint::where('government_entity_id',$employee->government_entity_id)
    ->where('city_id',$employee->city_id)->select('id','description','status','attachments','map_iframe','is_emergency','created_at')
    ->get();
  
    return ApiResponse::sendResponse(200,'Get Complaints',$complaints);

 }
}
