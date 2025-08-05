<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AiComplaintAnalyzer;
use App\Http\Resources\ComplaintResource;
use App\Http\Requests\StoreComplaintRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

    // معالجة المرفقات
if ($request->hasFile('attachments')) {
    $file = $request->file('attachments');
    $uploadedFileUrl = Cloudinary::upload($file->getRealPath())->getSecurePath();
    $validated['attachments'] = $uploadedFileUrl;
}
    // تعيين user_id حسب كونها شكوى مجهولة أو لا
    //$validated['user_id'] = ($validated['anonymous'] ?? false) ? null : Auth::id();

    $validated = $request->validated();
    $validated['anonymous'] = (int) $validated['anonymous']; // تأكيد أنها رقم 0 أو 1
    $validated['user_id'] = $validated['anonymous'] === 1 ? null : Auth::id();


    // إنشاء الشكوى
        $complaint = Complaint::create($validated);

    // تحليل الذكاء الاصطناعي
    $aiRating = $analyzer->rateEmergencyLevel($complaint->description);

    // إذا كان تقييم الذكاء الاصطناعي صحيح (1 أو 2 أو 3)، خزن القيمة
        if ($aiRating !== null && in_array($aiRating, [1, 2, 3])) {
        $complaint->is_emergency = $aiRating;
    } else {
        
        $complaint->is_emergency = 1;
    }

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
