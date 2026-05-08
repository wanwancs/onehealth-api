<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Nutmor\Models\Clinic;
use App\Domain\Nutmor\Models\ClinicBranch;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNutmorClinicBranchController extends Controller
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeBranchTimes(array $data): array
    {
        foreach (['opens_at', 'closes_at'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
            if (! array_key_exists($key, $data) || $data[$key] === null) {
                continue;
            }
            if (is_string($data[$key]) && preg_match('/^\d{2}:\d{2}$/', $data[$key])) {
                $data[$key] .= ':00';
            }
        }

        return $data;
    }

    public function store(Request $request, Clinic $clinic): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        $validated = $this->normalizeBranchTimes($validated);

        $branch = $clinic->branches()->create($validated);

        return response()->json(['data' => $branch->load('clinic')], 201);
    }

    public function update(Request $request, ClinicBranch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        $validated = $this->normalizeBranchTimes($validated);

        $branch->fill($validated);
        $branch->save();

        return response()->json(['data' => $branch->fresh()->load('clinic')]);
    }

    public function destroy(ClinicBranch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json(null, 204);
    }
}
