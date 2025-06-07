<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;
use App\Models\Complaint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;



class EmployeeComplaintsController extends Controller
{
 use AuthorizesRequests;
 public function getComplaints(Request $request)
 {
   $this->authorize('viewAny',Complaint::class);
    $employee = Auth::user();
    $complaints = Complaint::where('government_entity_id',$employee->government_entity_id)
    ->where('city_id',$employee->city_id)
    ->select('id','description','status','attachments','map_iframe','is_emergency','created_at')
    ->get() ;
  
    return ApiResponse::sendResponse(200,'Get Complaints',$complaints);

 }
 ### another way for Get By Gate and policy
//  public function getComplaints(Request $request)
// {
//     $this->authorize('viewAny', Complaint::class);

//     $employee = Auth::user();

//     $complaints = Complaint::all()->filter(function ($complaint) use ($employee) {
//         return Gate::forUser($employee)->allows('view', $complaint);
//     })->values();

//     return ApiResponse::sendResponse(200, 'Get Complaints', $complaints);
// }
public function updateStatus(Request $request , $id)
{
 $request->validate(([
  'status'=>'required|in:pending,accepted,rejected',
 ]));
$complaint=Complaint::find($id);

if(!$complaint)
{
 return ApiResponse::sendResponse(404,'Not Found',[]);
}
$complaint->status=$request->status ;
$complaint->save();
return ApiResponse::sendResponse(200,'Status Updated Successfully');
}
}
