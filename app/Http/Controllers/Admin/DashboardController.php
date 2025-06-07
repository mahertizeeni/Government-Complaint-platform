<?php

namespace App\Http\Controllers\Admin;

use App\Models\Complaint;
use App\Models\Suggestion;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Http\Resources\SuggestionResource;

class DashboardController extends Controller
{
    // إحصائيات عامة للوحة التحكم
    public function stats()
    {
        $stats = [
            'total_complaints'      => Complaint::count(),
            'urgent_complaints'     => Complaint::where('priority', 'urgent')->count(),
            'resolved_complaints'   => Complaint::where('status', 'resolved')->count(),
            'pending_complaints'    => Complaint::where('status', 'pending')->count(),
            'total_suggestions'     => Suggestion::count(),
        ];

        return ApiResponse::sendResponse(200, 'Dashboard statistics retrieved successfully.', $stats);
    }

    // جميع الشكاوى مع فلترة اختيارية
    public function complaints(Request $request)
    {
        $query = Complaint::with(['entity', 'city', 'handled_by']);

        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->has('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $complaints = $query->latest()->paginate(20);

        return ApiResponse::sendResponse(
            200,
            'Complaints retrieved successfully.',
            ComplaintResource::collection($complaints)
        );
    }

    // جميع المقترحات
    public function suggestions()
    {
        $suggestions = Suggestion::latest()->paginate(20);

        return ApiResponse::sendResponse(
            200,
            'Suggestions retrieved successfully.',
            SuggestionResource::collection($suggestions)
        );
    }
}
