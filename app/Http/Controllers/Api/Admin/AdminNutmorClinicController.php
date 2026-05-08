<?php

namespace App\Http\Controllers\Api\Admin;

use App\Domain\Nutmor\Models\Clinic;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminNutmorClinicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);
        $q = trim((string) $request->query('q', ''));

        $query = Clinic::query()
            ->withCount('branches')
            ->orderBy('name');

        if ($q !== '') {
            $query->where(function ($sub) use ($q): void {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$q.'%');
            });
        }

        if (trim((string) $request->query('include', '')) === 'branches') {
            $query->with(['branches:id,clinic_id,name']);
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['nullable', 'string', 'max:191', Rule::unique(Clinic::class, 'slug')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string', 'max:2048'],
            'location_text' => ['nullable', 'string', 'max:500'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'branches' => ['nullable', 'array'],
            'branches.*.name' => ['required_with:branches', 'string', 'max:255'],
            'branches.*.address' => ['nullable', 'string', 'max:500'],
        ]);

        $slug = $this->resolveSlug($validated['slug'] ?? null, $validated['name']);

        $clinic = DB::connection('nutmor')->transaction(function () use ($validated, $slug) {
            $clinic = Clinic::query()->create([
                'slug' => $slug,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'location_text' => $validated['location_text'] ?? null,
                'rating' => $validated['rating'] ?? null,
                'review_count' => $validated['review_count'] ?? 0,
                'published_at' => isset($validated['published_at'])
                    ? \Carbon\Carbon::parse($validated['published_at'])
                    : null,
            ]);

            foreach ($validated['branches'] ?? [] as $row) {
                $clinic->branches()->create([
                    'name' => $row['name'],
                    'address' => $row['address'] ?? null,
                ]);
            }

            return $clinic;
        });

        return response()->json([
            'data' => $clinic->load(['branches']),
        ], 201);
    }

    public function show(Clinic $clinic): JsonResponse
    {
        $clinic->load(['branches.doctors']);

        return response()->json(['data' => $clinic]);
    }

    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['sometimes', 'string', 'max:191', Rule::unique(Clinic::class, 'slug')->ignore($clinic->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string', 'max:2048'],
            'location_text' => ['nullable', 'string', 'max:500'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
        ]);

        if (array_key_exists('slug', $validated) && $validated['slug'] === '') {
            unset($validated['slug']);
        }

        if (array_key_exists('published_at', $validated)) {
            $v = $validated['published_at'];
            $clinic->published_at = $v !== null && $v !== ''
                ? \Carbon\Carbon::parse($v)
                : null;
            unset($validated['published_at']);
        }

        $clinic->fill($validated);
        $clinic->save();

        return response()->json(['data' => $clinic->fresh(['branches.doctors'])]);
    }

    public function destroy(Clinic $clinic): JsonResponse
    {
        $clinic->delete();

        return response()->json(null, 204);
    }

    private function resolveSlug(?string $slug, string $name): string
    {
        $base = $slug !== null && $slug !== ''
            ? Str::slug($slug)
            : Str::slug($name);

        if ($base === '' || $base === '-') {
            $base = 'clinic-'.Str::lower(Str::random(8));
        }

        $candidate = $base;
        $i = 1;
        while (Clinic::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
