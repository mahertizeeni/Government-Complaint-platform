<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCyberComplaintRequest;
use App\Models\CyberComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CyberComplaintController extends Controller
{
    public function store(StoreCyberComplaintRequest $request)
    { $data = $request->validated();
        if($request->hasFile('evidence_file'))
        {
            $file = $request->file('evidence_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads',$fileName,'public');
            $data['evidence_file']=$filePath;
        }
        // $data['user_id']= Auth::id() ;
        $data['user_id']= 66 ;
        CyberComplaint::create($data);

        return ApiResponse::sendResponse(201,'Complaint Sent Successfully ',$data);
    }
}



