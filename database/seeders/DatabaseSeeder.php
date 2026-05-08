<?php

namespace Database\Seeders;

use App\Domain\Medreco\Models\QueueEntry;
use App\Domain\Nutmor\Models\Appointment;
use App\Domain\Nutmor\Models\Clinic;
use App\Domain\Nutmor\Models\ClinicBranch;
use App\Domain\Nutmor\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctorImages = ['/images/doctor-1.jpg', '/images/doctor-2.jpg', '/images/doctor-3.jpg'];
        $clinicImages = ['/images/clinic-1.jpg', '/images/clinic-2.jpg', '/images/clinic-3.jpg'];

        User::query()->updateOrCreate([
            'email' => 'superadmin@onehealth.local',
        ], [
            'name' => 'Super Admin',
            'password' => 'password',
            'is_super_admin' => true,
        ]);

        $doctorUser = User::query()->updateOrCreate([
            'email' => 'doctor@onehealth.local',
        ], [
            'name' => 'Dr. Demo',
            'password' => 'password',
        ]);

        $patient = User::query()->updateOrCreate([
            'email' => 'patient@onehealth.local',
        ], [
            'name' => 'Patient Demo',
            'password' => 'password',
        ]);

        $clinic = Clinic::query()->updateOrCreate([
            'slug' => 'demo-clinic',
        ], [
            'name' => 'Demo Clinic',
            'description' => 'Sample clinic for local development and SEO pages.',
            'cover_image_url' => $clinicImages[0],
            'location_text' => 'บางนา, กรุงเทพฯ',
            'rating' => 4.70,
            'review_count' => 256,
            'published_at' => now(),
        ]);

        $branch = ClinicBranch::query()->updateOrCreate([
            'clinic_id' => $clinic->id,
            'name' => 'Main branch',
        ], [
            'address' => '123 Demo Road',
            'phone' => '02-111-2233',
            'opens_at' => '10:00:00',
            'closes_at' => '20:00:00',
        ]);

        $doctor = Doctor::query()->updateOrCreate([
            'clinic_branch_id' => $branch->id,
            'display_name' => 'Dr. Demo',
        ], [
            'specialty' => 'อายุรกรรม',
            'avatar_url' => $doctorImages[0],
            'rating' => 4.90,
            'review_count' => 125,
            'is_verified' => true,
            'license_number' => 'ว. 11111',
            'years_experience' => 12,
            'bio' => 'แพทย์ทดสอบระบบ Nutmor — อายุรกรรมทั่วไป',
            'tags' => ['อายุรกรรม', 'ตรวจสุขภาพ', 'โรคเรื้อรัง'],
            'followers_count' => 1200,
            'response_time_label' => 'ภายใน 1 ชม.',
            'appointment_slot_minutes' => 15,
            'onehealth_user_id' => $doctorUser->id,
        ]);

        $starts = now()->startOfHour()->addHour();
        $appointment = Appointment::query()->updateOrCreate([
            'doctor_id' => $doctor->id,
            'patient_onehealth_user_id' => $patient->id,
            'starts_at' => $starts,
        ], [
            'ends_at' => $starts->copy()->addMinutes(30),
            'status' => 'confirmed',
        ]);

        QueueEntry::query()->updateOrCreate([
            'nutmor_appointment_id' => $appointment->id,
        ], [
            'nutmor_doctor_id' => $doctor->id,
            'queue_date' => $starts->toDateString(),
            'position' => 1,
            'status' => 'waiting',
            'patient_display_name' => $patient->name,
            'doctor_display_name' => $doctor->display_name,
            'clinic_name' => $clinic->name,
            'branch_name' => $branch->name,
        ]);

        $dummyClinics = [
            [
                'slug' => 'sukjai-clinic',
                'name' => 'สุขใจ คลินิก',
                'description' => 'ตรวจโรคทั่วไปและเวชศาสตร์ครอบครัว',
                'location_text' => 'สุขุมวิท, กรุงเทพฯ',
                'rating' => 4.72,
                'review_count' => 188,
                'branch' => [
                    'name' => 'สุขุมวิท',
                    'address' => 'สุขุมวิท 71 กรุงเทพฯ',
                    'phone' => '02-222-1001',
                    'opens_at' => '10:00:00',
                    'closes_at' => '20:00:00',
                ],
                'doctors' => [
                    ['name' => 'พญ. กัญญา เวชกิจ', 'specialty' => 'เวชศาสตร์ครอบครัว', 'rating' => 4.91, 'review_count' => 96],
                    ['name' => 'นพ. กิตติพงษ์ สินธุ์ทอง', 'specialty' => 'อายุรกรรม', 'rating' => 4.88, 'review_count' => 142],
                ],
            ],
            [
                'slug' => 'sukhum-clinic',
                'name' => 'สุขุม คลินิก',
                'description' => 'คลินิกอายุรกรรม ดูแลโรคเรื้อรัง',
                'location_text' => 'ลาดพร้าว, กรุงเทพฯ',
                'rating' => 4.67,
                'review_count' => 203,
                'branch' => [
                    'name' => 'ลาดพร้าว',
                    'address' => 'ลาดพร้าว 101 กรุงเทพฯ',
                    'phone' => '02-222-1002',
                    'opens_at' => '09:00:00',
                    'closes_at' => '19:00:00',
                ],
                'doctors' => [
                    ['name' => 'นพ. ธนพงศ์ วิทยา', 'specialty' => 'โรคหัวใจ', 'rating' => 4.85, 'review_count' => 88],
                    ['name' => 'พญ. ปิยะนุช แสงทอง', 'specialty' => 'ต่อมไร้ท่อ', 'rating' => 4.79, 'review_count' => 71],
                ],
            ],
            [
                'slug' => 'medic-care-center',
                'name' => 'Medic Care Center',
                'description' => 'ดูแลสุขภาพครบวงจร พร้อมจองนัดออนไลน์',
                'location_text' => 'จตุจักร, กรุงเทพฯ',
                'rating' => 4.81,
                'review_count' => 312,
                'branch' => [
                    'name' => 'บางนา',
                    'address' => 'บางนา-ตราด กม.3',
                    'phone' => '02-222-1003',
                    'opens_at' => '10:00:00',
                    'closes_at' => '20:00:00',
                ],
                'doctors' => [
                    ['name' => 'Dr. Siriporn Chai', 'specialty' => 'เวชปฏิบัติทั่วไป', 'rating' => 4.89, 'review_count' => 201],
                    ['name' => 'Dr. Thanawat Li', 'specialty' => 'ออร์โธปิดิกส์', 'rating' => 4.76, 'review_count' => 54],
                ],
            ],
            [
                'slug' => 'well-life-clinic',
                'name' => 'Well Life Clinic',
                'description' => 'คลินิกใกล้บ้าน เน้นการดูแลผู้ป่วยนอก',
                'location_text' => 'พระโขนง, กรุงเทพฯ',
                'rating' => 4.74,
                'review_count' => 167,
                'branch' => [
                    'name' => 'รัชดา',
                    'address' => 'รัชดาภิเษก 32',
                    'phone' => '02-222-1004',
                    'opens_at' => '10:00:00',
                    'closes_at' => '20:00:00',
                ],
                'doctors' => [
                    ['name' => 'พญ. ณัฐกานต์ วงศ์ดี', 'specialty' => 'ผิวหนัง', 'rating' => 4.90, 'review_count' => 134],
                    ['name' => 'นพ. ศุภชัย ศรีสุข', 'specialty' => 'ศัลยกรรมทั่วไป', 'rating' => 4.84, 'review_count' => 92],
                ],
            ],
        ];

        foreach ($dummyClinics as $index => $data) {
            $dummyClinic = Clinic::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'cover_image_url' => $clinicImages[$index % count($clinicImages)],
                    'location_text' => $data['location_text'],
                    'rating' => $data['rating'],
                    'review_count' => $data['review_count'],
                    'published_at' => now(),
                ]
            );

            $dummyBranch = ClinicBranch::query()->updateOrCreate(
                [
                    'clinic_id' => $dummyClinic->id,
                    'name' => $data['branch']['name'],
                ],
                [
                    'address' => $data['branch']['address'],
                    'phone' => $data['branch']['phone'] ?? null,
                    'opens_at' => $data['branch']['opens_at'] ?? null,
                    'closes_at' => $data['branch']['closes_at'] ?? null,
                ]
            );

            foreach ($data['doctors'] as $doctorIndex => $doctorData) {
                $email = sprintf('doctor%02d%02d@onehealth.local', $index + 1, $doctorIndex + 1);
                $doctorAccount = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $doctorData['name'],
                        'password' => 'password',
                    ]
                );

                $dummyDoctor = Doctor::query()->updateOrCreate(
                    [
                        'clinic_branch_id' => $dummyBranch->id,
                        'display_name' => $doctorData['name'],
                    ],
                    [
                        'specialty' => $doctorData['specialty'],
                        'avatar_url' => $doctorImages[($index + $doctorIndex) % count($doctorImages)],
                        'rating' => $doctorData['rating'],
                        'review_count' => $doctorData['review_count'] ?? 0,
                        'is_verified' => $doctorData['is_verified'] ?? true,
                        'license_number' => $doctorData['license_number'] ?? ('ว. '.(42000 + $index * 10 + $doctorIndex)),
                        'years_experience' => $doctorData['years_experience'] ?? (8 + $doctorIndex * 2),
                        'bio' => $doctorData['bio'] ?? 'แพทย์ทดสอบระบบ Nutmor',
                        'tags' => $doctorData['tags'] ?? ['ทั่วไป'],
                        'followers_count' => $doctorData['followers_count'] ?? (800 + $index * 50 + $doctorIndex * 10),
                        'response_time_label' => $doctorData['response_time_label'] ?? 'ภายใน 1 ชม.',
                        'appointment_slot_minutes' => $doctorData['appointment_slot_minutes'] ?? 15,
                        'onehealth_user_id' => $doctorAccount->id,
                    ]
                );

                $dummyStarts = now()->startOfHour()->addHours(2 + ($index * 2) + $doctorIndex);
                $dummyAppointment = Appointment::query()->updateOrCreate(
                    [
                        'doctor_id' => $dummyDoctor->id,
                        'patient_onehealth_user_id' => $patient->id,
                        'starts_at' => $dummyStarts,
                    ],
                    [
                        'ends_at' => $dummyStarts->copy()->addMinutes(30),
                        'status' => 'confirmed',
                    ]
                );

                QueueEntry::query()->updateOrCreate(
                    ['nutmor_appointment_id' => $dummyAppointment->id],
                    [
                        'nutmor_doctor_id' => $dummyDoctor->id,
                        'queue_date' => $dummyStarts->toDateString(),
                        'position' => $doctorIndex + 1,
                        'status' => 'waiting',
                        'patient_display_name' => $patient->name,
                        'doctor_display_name' => $dummyDoctor->display_name,
                        'clinic_name' => $dummyClinic->name,
                        'branch_name' => $dummyBranch->name,
                    ]
                );
            }
        }
    }
}
