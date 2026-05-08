<?php

namespace App\Http\Controllers\Api\Nutmor;

use App\Domain\Nutmor\Models\Clinic;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ClinicController extends Controller
{
    public function index(): JsonResponse
    {
        $clinics = Clinic::query()
            ->published()
            ->orderBy('name')
            ->get([
                'id',
                'slug',
                'name',
                'description',
                'cover_image_url',
                'location_text',
                'rating',
                'review_count',
                'published_at',
            ]);

        return response()->json(['data' => $clinics]);
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $clinic = Clinic::query()
            ->published()
            ->where('slug', $slug)
            ->with(['branches.doctors'])
            ->firstOrFail();

        return response()->json(['data' => $clinic]);
    }
}
