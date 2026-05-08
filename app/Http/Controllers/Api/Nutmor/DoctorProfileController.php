<?php

namespace App\Http\Controllers\Api\Nutmor;

use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DoctorProfileController extends Controller
{
    public function show(Doctor $doctor): JsonResponse
    {
        $doctor->load(['branch.clinic']);

        return response()->json(['data' => $doctor]);
    }
}
