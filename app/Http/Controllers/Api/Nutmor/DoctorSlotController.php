<?php

namespace App\Http\Controllers\Api\Nutmor;

use App\Domain\Nutmor\Models\Appointment;
use App\Domain\Nutmor\Models\Doctor;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DoctorSlotController extends Controller
{
    private const TZ = 'Asia/Bangkok';

    public function index(Request $request, Doctor $doctor): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $doctor->loadMissing('branch');
        $branch = $doctor->branch;
        if (! $branch) {
            throw ValidationException::withMessages(['doctor' => ['Branch not found for this doctor.']]);
        }

        $slotMinutes = max(5, min(120, (int) ($doctor->appointment_slot_minutes ?? 15)));
        $date = $data['date'];

        $openTime = $this->timeOfDayToHms($branch->opens_at, '10:00:00');
        $closeTime = $this->timeOfDayToHms($branch->closes_at, '20:00:00');

        $open = Carbon::parse($date.' '.$openTime, self::TZ);
        $close = Carbon::parse($date.' '.$closeTime, self::TZ);
        if ($close->lessThanOrEqualTo($open)) {
            $close = $open->copy()->addHours(10);
        }

        $dayStartUtc = Carbon::parse($date.' 00:00:00', self::TZ)->utc();
        $dayEndUtc = Carbon::parse($date.' 23:59:59', self::TZ)->utc();

        $booked = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('starts_at', [$dayStartUtc, $dayEndUtc])
            ->get(['starts_at', 'ends_at']);

        $slots = [];
        $cursor = $open->copy();
        while ($cursor->copy()->addMinutes($slotMinutes)->lessThanOrEqualTo($close)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($slotMinutes);

            if ($this->slotIsFree($slotStart, $slotEnd, $booked, $doctor)) {
                $slots[] = [
                    'starts_at' => $slotStart->copy()->utc()->toIso8601String(),
                    'label' => $slotStart->format('H:i'),
                ];
            }
            $cursor->addMinutes($slotMinutes);
        }

        return response()->json([
            'data' => [
                'date' => $date,
                'slot_minutes' => $slotMinutes,
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Appointment>  $booked
     */
    private function slotIsFree(Carbon $slotStart, Carbon $slotEnd, $booked, Doctor $doctor): bool
    {
        $slotStartTz = $slotStart->copy()->timezone(self::TZ);
        $slotEndTz = $slotEnd->copy()->timezone(self::TZ);
        $fallbackMins = max(5, (int) ($doctor->appointment_slot_minutes ?? 15));

        foreach ($booked as $appt) {
            $bStart = $appt->starts_at->copy()->timezone(self::TZ);
            $bEnd = $appt->ends_at
                ? $appt->ends_at->copy()->timezone(self::TZ)
                : $bStart->copy()->addMinutes($fallbackMins);

            if ($slotStartTz->lt($bEnd) && $slotEndTz->gt($bStart)) {
                return false;
            }
        }

        if ($slotStartTz->isPast()) {
            return false;
        }

        return true;
    }

    private function timeOfDayToHms(mixed $value, string $default): string
    {
        if ($value === null) {
            return $default;
        }
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('H:i:s');
        }
        if (is_string($value)) {
            return strlen($value) === 5 ? $value.':00' : $value;
        }

        return $default;
    }
}
