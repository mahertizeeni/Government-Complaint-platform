<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $complaints =Complaint::where('user_id',Auth::id())->get(); 
        return ApiResponse::sendResponse(200,'The Complaints For User',new ComplaintResource($complaints));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComplaintRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id']=Auth::id();
        if($request->hasFile('attachments'))
        {
            $file = $request->file('attachments');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads',$fileName,'public');
            $validated['attachments']=$filePath;
        }
        $complaint = Complaint::create($validated);
        return ApiResponse::sendResponse(201,'Complaint Added Successfully',new ComplaintResource($complaint));

        
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $complaint = Complaint::where('user_id', Auth::id())->firstOrFail($id);
        return $complaint;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $complaint = Complaint::where('user_id', Auth::id())->findorfail($id);
        $complaint->delete();
        return ApiResponse::sendResponse(200,'Complaint Deleted Successfully',[]);
    }
}
