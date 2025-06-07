<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SuggestionResource;

class SuggestionController extends Controller
{
    public function index()
    {
        $suggestions = Suggestion::latest()->get();
        if ($suggestions->count() > 0) {
            return ApiResponse::sendResponse(200, 'List of suggestions', SuggestionResource::collection($suggestions));
        }
        return ApiResponse::sendResponse(200, 'No suggestions found', []);
    }

    // إنشاء مقترح جديد
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'government_entity_id' => 'required|exists:government_entities,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $suggestion = Suggestion::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'government_entity_id' => $request->government_entity_id,
            'city_id' => $request->city_id,
        ]);

        return ApiResponse::sendResponse(201, 'Suggestion created successfully', new SuggestionResource($suggestion));
    }

    // عرض مقترح معين
    public function show(Suggestion $suggestion)
    {
        return ApiResponse::sendResponse(200, 'Suggestion details', new SuggestionResource($suggestion));
    }

    // تحديث مقترح معين
    public function update(Request $request, Suggestion $suggestion)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'government_entity_id' => 'sometimes|exists:government_entities,id',
            'city_id' => 'sometimes|exists:cities,id',
        ]);

        $suggestion->update($request->only([
            'title', 'description', 'government_entity_id', 'city_id'
        ]));

        return ApiResponse::sendResponse(200, 'Suggestion updated successfully', new SuggestionResource($suggestion));
    }

    // حذف مقترح
    public function destroy(Suggestion $suggestion)
    {
        $suggestion->delete();
        return ApiResponse::sendResponse(204, 'Suggestion deleted successfully', []);
    }

    // مقترحات حسب الجهة الحكومية
    public function byEntity($entityId)
    {
        $suggestions = Suggestion::where('government_entity_id', $entityId)->latest()->get();
        if ($suggestions->count() > 0) {
            return ApiResponse::sendResponse(200, 'Suggestions for entity retrieved successfully', SuggestionResource::collection($suggestions));
        }
        return ApiResponse::sendResponse(200, 'No suggestions found for this entity', []);
    }

    // مقترحات حسب المدينة
    public function byCity($cityId)
    {
        $suggestions = Suggestion::where('city_id', $cityId)->latest()->get();
        if ($suggestions->count() > 0) {
            return ApiResponse::sendResponse(200, 'Suggestions for city retrieved successfully', SuggestionResource::collection($suggestions));
        }
        return ApiResponse::sendResponse(200, 'No suggestions found for this city', []);
    }
}
