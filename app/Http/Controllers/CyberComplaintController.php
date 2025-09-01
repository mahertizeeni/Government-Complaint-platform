<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CyberComplaint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\CyberComplaintResource;
use App\Http\Requests\StoreCyberComplaintRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class CyberComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      public function index()
    {
        $complaints =CyberComplaint::where('user_id',Auth::id())->get();
        return ApiResponse::sendResponse(200, 'The CyberComplaints For User', CyberComplaintResource::collection($complaints));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
  
public function store(StoreCyberComplaintRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('evidence_file')) {
        $file = $request->file('evidence_file');
        
        $response = Http::attach(
            'file', fopen($file->getPathname(), 'r'), $file->getClientOriginalName()
        )->post('https://upload.uploadcare.com/base/', [
            'UPLOADCARE_PUB_KEY' => env('UPLOADCARE_PUBLIC_KEY'),
            'UPLOADCARE_STORE' => '1',
        ]);

        $uuid = $response['file'];
        $data['evidence_file'] = "https://ucarecdn.com/{$uuid}/";
    }

    $data['user_id'] = Auth::id();
    $complaint = CyberComplaint::create($data);

    return ApiResponse::sendResponse(201, 'Complaint Added Successfully', new CyberComplaintResource($complaint));
}
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cyberComplaint = CyberComplaint::findOrFail($id);

        return ApiResponse::sendResponse
        (
            200,
            'Cyber Complaint fetched successfully',
            new CyberComplaintResource($cyberComplaint)
        );
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
        $Cybercomplaint = CyberComplaint::findOrFail($id);
        $Cybercomplaint->delete();
        return ApiResponse::sendResponse(200,'CyberComplaint Deleted Successfully',[]);
    }
}
