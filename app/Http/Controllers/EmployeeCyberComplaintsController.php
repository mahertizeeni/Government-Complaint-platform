<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CyberComplaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\CyberComplaintResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmployeeCyberComplaintsController extends Controller
{  
  use AuthorizesRequests;
public function getComplaints(Request $request)
{
    $this->authorize('viewAny', CyberComplaint::class);

    $employee = Auth::user();
    if (!($employee instanceof Employee)) {
        return ApiResponse::sendResponse(403, 'Unauthorized', []);
    }

    if ($employee->government_entity_id == 12 && $employee->city_id == 15) {
        $Cybercomplaints = CyberComplaint::all();
        return ApiResponse::sendResponse(200, 'Get CyberComplaints', $Cybercomplaints);
    }

    // غيره ما بيشوف شي
    return ApiResponse::sendResponse(200, 'Get CyberComplaints', []);
}


 public function show($id)
{
    $employee = Auth::user();

    $cybercomplaint = CyberComplaint::where('id', $id)
        ->first();

    if (!$cybercomplaint) {
        return ApiResponse::sendResponse(404, 'cybercomplaint not found or unauthorized', []);
    }

    return ApiResponse::sendResponse(200, 'cybercomplaint retrieved successfully', new CyberComplaintResource($cybercomplaint));
}
public function updateStatus(Request $request , $id)
{
 $request->validate(([
  'status'=>'required|in:pending,accepted,rejected',
    ]));
    $CyberComplaint=CyberComplaint::find($id);

    if(!$CyberComplaint)
 {
     return ApiResponse::sendResponse(404,'Not Found',[]);
 }
     $this->authorize('update', $CyberComplaint);

  $CyberComplaint->status=$request->status ;
  $CyberComplaint->save();
// ارسال الايميل
    Mail::to($CyberComplaint->user->email)->send(new \App\Mail\CyberComplaintStatusUpdated($CyberComplaint));

    return ApiResponse::sendResponse(200,'Status Updated Successfully');
  }
}
