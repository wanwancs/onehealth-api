<?php

namespace App\Http\Controllers\Api\Medreco;

use App\Domain\Medreco\Models\QueueEntry;
use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function today(Request $request): JsonResponse
    {
        $doctorIds = Doctor::query()
            ->where('onehealth_user_id', $request->user()->id)
            ->pluck('id');

        if ($doctorIds->isEmpty()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'queue_date' => now()->toDateString(),
                    'message' => 'No doctor profile linked to this OneHealth account.',
                ],
            ]);
        }

        $date = now()->toDateString();

        $entries = QueueEntry::query()
            ->whereDate('queue_date', $date)
            ->whereIn('nutmor_doctor_id', $doctorIds)
            ->where('status', '!=', 'cancelled')
            ->orderBy('position')
            ->get();

        return response()->json([
            'data' => $entries,
            'meta' => [
                'queue_date' => $date,
            ],
        ]);
    }
}
