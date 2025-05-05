<?php

namespace App\Http\Controllers\Api;

use App\Models\Complaint;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;

class ComplaintController extends Controller
{
    // عرض جميع الشكاوى
    public function index()
    {
        $complaints = Complaint::all();
        if (count ($complaints)>0) {
            return ApiResponse::sendResponse(200, 'The complaints', ComplaintResource::collection($complaints));
        }
        return ApiResponse::sendResponse(200, 'No complaints', []);
    }

    // تخزين شكوى جديدة
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $complaint = Complaint::create(
            [
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),  // إذا كنت تستخدم المصادقة
        ]);

        return ApiResponse::sendResponse(201, 'Complaint created successfully', new ComplaintResource($complaint));
    }

    // عرض شكوى معينة
    public function show(Complaint $complaint)
    {
        return ApiResponse::sendResponse(200, 'Complaint details', new ComplaintResource($complaint));
    }

    // تحديث شكوى معينة
    public function update(Request $request, Complaint $complaint)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $complaint->update($request->only(['title', 'description']));

        return ApiResponse::sendResponse(200, 'Complaint updated successfully', new ComplaintResource($complaint));
    }

    // حذف شكوى معينة
    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
        return ApiResponse::sendResponse(204, 'Complaint deleted successfully', []);
    }
    public function category($category_id)
    {
        $complaint=Complaint::where('category_id',$category_id)->latest()->get();
        if(count($complaint) > 0)
        {
            return ApiResponse::sendResponse(200,'Complaint of complaint retrieved successfully',ComplaintResource::collection($complaint));
        }
        return ApiResponse::sendResponse(200,'empty',[]);
    }
}
