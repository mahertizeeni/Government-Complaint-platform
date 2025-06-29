<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CyberComplaint;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCyberComplaintRequest;
use App\Http\Resources\CyberComplaintResource;

class UserCyberComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
           $cybercomplaints =CyberComplaint::where('user_id',Auth::id())->get(); 
        return ApiResponse::sendResponse(200,'The CyberComplaints For User',new CyberComplaintResource($cybercomplaints));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(StoreCyberComplaintRequest $request)
    { $data = $request->validated();
        if($request->hasFile('evidence_file'))
        {
            $file = $request->file('evidence_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads',$fileName,'public');
            $data['evidence_file']=$filePath;
        }
        $data['user_id']= Auth::id() ;
        CyberComplaint::create($data);

        return ApiResponse::sendResponse(201,'Complaint Sent Successfully ',new CyberComplaintResource($data));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
         $cybercomplaint = CyberComplaint::where('user_id', Auth::id())->firstOrFail($id);
        return $cybercomplaint;
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
        $cybercomplaint = CyberComplaint::where('user_id', Auth::id())->findorfail($id);
        $cybercomplaint->delete();
        return ApiResponse::sendResponse(200,'Complaint Deleted Successfully',[]);
    }
}
