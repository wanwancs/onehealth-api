<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Nutmor\Models\ClinicBranch;
use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminNutmorDoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);
        $q = trim((string) $request->query('q', ''));
        $clinicId = $request->query('clinic_id');
        $branchId = $request->query('branch_id');

        $query = Doctor::query()
            ->with(['branch.clinic'])
            ->orderBy('display_name');

        if ($q !== '') {
            $query->where('display_name', 'like', '%'.$q.'%');
        }

        if ($branchId !== null && $branchId !== '') {
            $query->where('clinic_branch_id', (int) $branchId);
        } elseif ($clinicId !== null && $clinicId !== '') {
            $query->whereHas('branch', function ($sub) use ($clinicId): void {
                $sub->where('clinic_id', (int) $clinicId);
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clinic_branch_id' => ['required', 'integer', Rule::exists(ClinicBranch::class, 'id')],
            'display_name' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'is_verified' => ['sometimes', 'boolean'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'response_time_label' => ['nullable', 'string', 'max:100'],
            'appointment_slot_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
            'onehealth_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $doctor = Doctor::query()->create([
            'clinic_branch_id' => $validated['clinic_branch_id'],
            'display_name' => $validated['display_name'],
            'specialty' => $validated['specialty'] ?? null,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'review_count' => $validated['review_count'] ?? 0,
            'is_verified' => $validated['is_verified'] ?? false,
            'license_number' => $validated['license_number'] ?? null,
            'years_experience' => $validated['years_experience'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'followers_count' => $validated['followers_count'] ?? 0,
            'response_time_label' => $validated['response_time_label'] ?? null,
            'appointment_slot_minutes' => $validated['appointment_slot_minutes'] ?? 15,
            'onehealth_user_id' => $validated['onehealth_user_id'] ?? null,
        ]);

        return response()->json([
            'data' => $doctor->load(['branch.clinic']),
        ], 201);
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json(['data' => $doctor->load(['branch.clinic'])]);
    }

    public function update(Request $request, Doctor $doctor): JsonResponse
    {
        $validated = $request->validate([
            'clinic_branch_id' => ['sometimes', 'integer', Rule::exists(ClinicBranch::class, 'id')],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'is_verified' => ['sometimes', 'boolean'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'response_time_label' => ['nullable', 'string', 'max:100'],
            'appointment_slot_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
            'onehealth_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $doctor->fill($validated);
        $doctor->save();

        return response()->json(['data' => $doctor->fresh(['branch.clinic'])]);
    }

    public function destroy(Doctor $doctor): JsonResponse
    {
        $doctor->delete();

        return response()->json(null, 204);
    }
}
