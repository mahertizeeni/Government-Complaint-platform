<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;

class EmployeeSuggestionController extends Controller
{
     public function getSuggestions(Request $request)
 {
        $employee = Auth::user();
    $suggestions = Suggestion::where('government_entity_id',$employee->government_entity_id)
    ->where('city_id',$employee->city_id)
    ->get() ;
  
    return ApiResponse::sendResponse(200,'Get suggestions',$suggestions);

 }

public function updateStatus(Request $request , $id)
{
 $request->validate(([
  'status'=>'required|in:pending,accepted,rejected',
 ]));
$suggestion=suggestion::find($id);

if(!$suggestion)
{
 return ApiResponse::sendResponse(404,'Not Found',[]);
}
$suggestion->status=$request->status ;
$suggestion->save();
return ApiResponse::sendResponse(200,'Status Updated Successfully');
}
}
