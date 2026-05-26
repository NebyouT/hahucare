<?php

namespace Database\Seeders;

use App\Models\User;
use Modules\Clinic\Models\Doctor;
use Modules\Clinic\Models\DoctorClinicMapping;
use Modules\Clinic\Models\Clinics;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateDemoDoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find the vendor user
        $vendor = User::where('email', 'vendor@hahucare.com')->first();

        if (!$vendor) {
            $this->command->error('Vendor user (vendor@hahucare.com) not found!');
            return;
        }

        // Create or update the doctor user
        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@hahucare.com'],
            [
                'first_name' => 'Dr. Sarah',
                'last_name' => 'Johnson',
                'password' => Hash::make('P0o9i8u7!'),
                'mobile' => '+91 9876543210',
                'date_of_birth' => '1985-06-15',
                'country' => 230,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'gender' => 'female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'doctor',
                'status' => 1,
            ]
        );

        // Assign doctor role
        $doctorUser->assignRole('doctor');

        // Create or update the doctor record
        $doctor = Doctor::updateOrCreate(
            ['doctor_id' => $doctorUser->id],
            [
                'vendor_id' => $vendor->id,
                'experience' => 10,
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Get a clinic for this vendor
        $clinic = Clinics::where('vendor_id', $vendor->id)->first();

        if ($clinic) {
            // Link doctor to clinic
            DoctorClinicMapping::updateOrCreate(
                [
                    'doctor_id' => $doctorUser->id,
                    'clinic_id' => $clinic->id,
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );

            $this->command->info('Demo doctor created successfully!');
            $this->command->info('Email: doctor@hahucare.com');
            $this->command->info('Password: P0o9i8u7!');
            $this->command->info('Linked to clinic: ' . $clinic->name);
        } else {
            $this->command->warn('No clinic found for vendor. Doctor created but not linked to any clinic.');
            $this->command->info('Demo doctor created successfully!');
            $this->command->info('Email: doctor@hahucare.com');
            $this->command->info('Password: P0o9i8u7!');
        }
    }
}
