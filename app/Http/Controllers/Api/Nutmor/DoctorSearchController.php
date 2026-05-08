<?php

namespace App\Http\Controllers\Api\Nutmor;

use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class DoctorSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 50);
        $q = trim((string) $request->query('q', ''));
        $specialty = trim((string) $request->query('specialty', ''));
        $clinicId = $request->query('clinic_id');
        $branchId = $request->query('branch_id');
        $location = trim((string) $request->query('location', ''));
        $sort = trim((string) $request->query('sort', 'rating'));

        $query = Doctor::query()
            ->with(['branch.clinic'])
            ->when($q !== '', function (Builder $builder) use ($q): void {
                $builder->where(function (Builder $w) use ($q): void {
                    $w->where('display_name', 'like', '%'.$q.'%')
                        ->orWhere('specialty', 'like', '%'.$q.'%')
                        ->orWhere('bio', 'like', '%'.$q.'%')
                        ->orWhereHas('branch.clinic', function (Builder $c) use ($q): void {
                            $c->where('name', 'like', '%'.$q.'%');
                        });
                });
            })
            ->when($specialty !== '', fn (Builder $builder) => $builder->where('specialty', 'like', '%'.$specialty.'%'))
            ->when($branchId !== null && $branchId !== '', fn (Builder $builder) => $builder->where('clinic_branch_id', (int) $branchId))
            ->when(
                ($clinicId !== null && $clinicId !== '') && ($branchId === null || $branchId === ''),
                fn (Builder $builder) => $builder->whereHas('branch', fn (Builder $b) => $b->where('clinic_id', (int) $clinicId))
            )
            ->when($location !== '', function (Builder $builder) use ($location): void {
                $builder->whereHas('branch.clinic', function (Builder $c) use ($location): void {
                    $c->where('location_text', 'like', '%'.$location.'%');
                });
            });

        match ($sort) {
            'name' => $query->orderBy('display_name'),
            default => $query->orderByDesc('rating')->orderBy('display_name'),
        };

        return response()->json($query->paginate($perPage));
    }
}
