<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SuggestionResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmployeeSuggestionController extends Controller
{
    use AuthorizesRequests;
     public function getSuggestions(Request $request)
 {
     $this->authorize('viewAny',Suggestion::class);
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
public function show($id)
{
    $employee = Auth::user();

    $suggestion = Suggestion::where('id', $id)
        ->where('government_entity_id', $employee->government_entity_id)
        ->where('city_id', $employee->city_id)
        ->first();

    if (!$suggestion) {
        return ApiResponse::sendResponse(404, 'suggestion not found or unauthorized', []);
    }

    return ApiResponse::sendResponse(200, 'suggestion retrieved successfully', new SuggestionResource($suggestion));
}

}
