<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\ComplaintModel;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    // عرض جميع الشكاوى
    public function index()
    {
        $complaints = ComplaintModel::all();
        if ($complaints->isNotEmpty()) {
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

        $complaint = ComplaintModel::create(
            [
            'title' => $request->title,
            'description' => $request->description,
            /* 'user_id' => auth()->id(), */ // إذا كنت تستخدم المصادقة
        ]);

        return ApiResponse::sendResponse(201, 'Complaint created successfully', new ComplaintResource($complaint));
    }

    // عرض شكوى معينة
    public function show(ComplaintModel $complaint)
    {
        return ApiResponse::sendResponse(200, 'Complaint details', new ComplaintResource($complaint));
    }

    // تحديث شكوى معينة
    public function update(Request $request, ComplaintModel $complaint)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $complaint->update($request->only(['title', 'description']));

        return ApiResponse::sendResponse(200, 'Complaint updated successfully', new ComplaintResource($complaint));
    }

    // حذف شكوى معينة
    public function destroy(ComplaintModel $complaint)
    {
        $complaint->delete();
        return ApiResponse::sendResponse(204, 'Complaint deleted successfully', []);
    }
}
