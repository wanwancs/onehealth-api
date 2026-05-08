<?php

namespace App\Http\Controllers\Api\Nutmor;

use App\Domain\Medreco\Models\QueueEntry;
use App\Domain\Nutmor\Models\Appointment;
use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Appointment::query()
            ->where('patient_onehealth_user_id', $request->user()->id)
            ->with(['doctor.branch.clinic'])
            ->orderByDesc('starts_at')
            ->limit(100)
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:nutmor.doctors,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        $doctor = Doctor::query()->with('branch.clinic')->findOrFail($data['doctor_id']);
        $patient = $request->user();

        $starts = Carbon::parse($data['starts_at']);
        $slotMins = max(5, (int) ($doctor->appointment_slot_minutes ?? 15));
        $ends = isset($data['ends_at'])
            ? Carbon::parse($data['ends_at'])
            : $starts->copy()->addMinutes($slotMins);

        if ($ends->lessThanOrEqualTo($starts)) {
            throw ValidationException::withMessages(['ends_at' => ['เวลาสิ้นสุดต้องหลังเวลาเริ่ม']]);
        }

        $this->assertSlotAvailable($doctor, $starts, $ends);

        $appointment = Appointment::query()->create([
            'doctor_id' => $doctor->id,
            'patient_onehealth_user_id' => $patient->id,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'status' => 'confirmed',
        ]);

        $this->syncQueueEntry($appointment, $doctor, $patient);

        $appointment->load(['doctor.branch.clinic']);

        return response()->json(['data' => $appointment], 201);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        if ((int) $appointment->patient_onehealth_user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', Rule::in(['confirmed', 'cancelled', 'completed'])],
        ]);

        $appointment->fill($data);
        $appointment->save();

        if (isset($data['status']) && $data['status'] === 'cancelled') {
            QueueEntry::query()
                ->where('nutmor_appointment_id', $appointment->id)
                ->update(['status' => 'cancelled']);
        }

        $appointment->load(['doctor.branch.clinic']);

        return response()->json(['data' => $appointment]);
    }

    private function assertSlotAvailable(Doctor $doctor, Carbon $starts, Carbon $ends): void
    {
        $fallbackMins = max(5, (int) ($doctor->appointment_slot_minutes ?? 15));

        $overlap = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '<', $ends)
            ->whereRaw('COALESCE(ends_at, DATE_ADD(starts_at, INTERVAL ? MINUTE)) > ?', [$fallbackMins, $starts])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['starts_at' => ['ช่วงเวลานี้ถูกจองแล้ว']]);
        }
    }

    private function syncQueueEntry(Appointment $appointment, Doctor $doctor, User $patient): void
    {
        $branch = $doctor->branch;
        $clinic = $branch->clinic;
        $queueDate = $appointment->starts_at->toDateString();

        $next = (int) QueueEntry::query()
            ->whereDate('queue_date', $queueDate)
            ->where('nutmor_doctor_id', $doctor->id)
            ->max('position') + 1;

        QueueEntry::query()->updateOrCreate(
            ['nutmor_appointment_id' => $appointment->id],
            [
                'nutmor_doctor_id' => $doctor->id,
                'queue_date' => $queueDate,
                'position' => $next,
                'status' => 'waiting',
                'patient_display_name' => $patient->name,
                'doctor_display_name' => $doctor->display_name,
                'clinic_name' => $clinic->name,
                'branch_name' => $branch->name,
            ]
        );
    }
}
