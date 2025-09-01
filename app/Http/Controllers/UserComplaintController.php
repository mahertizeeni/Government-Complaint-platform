<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\AiComplaintAnalyzer;
use App\Http\Resources\ComplaintResource;
use App\Http\Requests\StoreComplaintRequest;

class UserComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $complaints =Complaint::where('user_id',Auth::id())->get(); 
    return ApiResponse::sendResponse(200, 'The Complaints For User', ComplaintResource::collection($complaints));
    }

    /**
     * Store a newly created resource in storage.
     */
  


public function store(StoreComplaintRequest $request, AiComplaintAnalyzer $analyzer)
{
    $validated = $request->validated();

    //  رفع المرفقات إلى UploadCare
    if ($request->hasFile('attachments')) {
        $file = $request->file('attachments');

        $response = Http::attach(
            'file', fopen($file->getPathname(), 'r'), $file->getClientOriginalName()
        )->post('https://upload.uploadcare.com/base/', [
            'UPLOADCARE_PUB_KEY' => env('UPLOADCARE_PUBLIC_KEY'),
            'UPLOADCARE_STORE' => '1',
        ]);

        $uuid = $response['file'];
        $validated['attachments'] = "https://ucarecdn.com/{$uuid}/";
    }

    $validated['anonymous'] = (int) $validated['anonymous'];
    $validated['user_id'] = Auth::id();


    //  إنشاء الشكوى
    $complaint = Complaint::create($validated);

    //  تقييم الذكاء الاصطناعي
    $aiRating = $analyzer->rateEmergencyLevel($complaint->description);
    $complaint->is_emergency = in_array($aiRating, [1, 2, 3]) ? $aiRating : 1;
    $complaint->save();

    return ApiResponse::sendResponse(
        201,
        'Complaint Added Successfully',
        new ComplaintResource($complaint)
    );
}



    /**
     * Display the specified resource.
     */
public function show($id)
{
    $complaint = Complaint::with(['user', 'governmentEntity', 'city'])->findOrFail($id);

    return ApiResponse::sendResponse(
        200,
        'Complaint fetched successfully',
        new ComplaintResource($complaint)
    );
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
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();
        return ApiResponse::sendResponse(200,'Complaint Deleted Successfully',[]);
    }
}
