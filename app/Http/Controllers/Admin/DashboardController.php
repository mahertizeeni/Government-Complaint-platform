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
    public function index()
{
    return ApiResponse::sendResponse(200, 'Admin Dashboard entry point.', [
        'routes' => [
            'stats' => route('admin.dashboard.stats'),
            'complaints' => route('admin.dashboard.complaints'),
            'suggestions' => route('admin.dashboard.suggestions'),
        ],
    ]);
}
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
    $query = Complaint::with(['governmentEntity', 'city']);

    if ($request->filled('city_id')) {
        $query->where('city_id', $request->city_id);
    }

    if ($request->filled('government_entity_id')) {
        $query->where('government_entity_id', $request->government_entity_id);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    $perPage = $request->get('per_page', 20);
    $complaints = $query->latest()->paginate($perPage);

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
