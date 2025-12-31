<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

use App\Events\Backend\UserCreated;

class PharmaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define pharma users for the Pharma module
        $pharmaUsers = [
            [
                'first_name'      => 'john',
                'last_name'       => 'doe',
                'email'           => 'john@gmail.com',
                'password'        => Hash::make('john@123'),
                'mobile'          => '212 555-0100',
                'date_of_birth'   => '1992-03-10',
                'clinic_id'       => 1, // HeartCare & OrthoCare Center
                'country'         => 230,
                'state'           => 3812,
                'city'            => 41432,
                'pincode'         => '12345',
                'avatar'          => null,
                'profile_image'   => public_path('/dummy-images/profile/user/john.png'),
                'gender'          => 'male',
                'status'          => 1, // Active
                'email_verified_at' => Carbon::now(),
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
                'user_type'       => 'pharma',
            ],

            [
                'first_name' => 'Peter',
                'last_name' => 'Jones',
                'email' => 'pharma@kivicare.com',
                'password' => Hash::make('12345678'),
                'mobile' => '+91 2381547861',
                'date_of_birth' => '1984-10-05',
                'clinic_id' => 1,
                'country' => 230,
                'state' => 3812,
                'city' => 41432,
                'pincode' => '12345',
                'avatar' => null,
                'profile_image' => public_path('/dummy-images/profile/vendor/susan.png'),
                'gender' => 'Female',
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_type' => 'pharma',
            ],

            [
                'first_name'      => 'Emily',
                'last_name'       => 'White',
                'email'           => 'emily@gmail.com',
                'password'        => Hash::make('Emily@123'),
                'mobile'          => '404 555-0199',
                'date_of_birth'   => '1995-09-03',
                'clinic_id'       => 2, // Harmony Medical Center
                'country'         => 230,
                'state'           => 3812,
                'city'            => 41432,
                'pincode'         => '12345',
                'avatar'          => null,
                'profile_image'   => public_path('/dummy-images/profile/user/pedra.png'),
                'gender'          => 'female',
                'status'          => 1, // Active
                'email_verified_at' => Carbon::now(),
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
                'user_type'       => 'pharma',
            ],
            [
                'first_name'      => 'Michael',
                'last_name'       => 'Brown',
                'email'           => 'michael@gmail.com',
                'password'        => Hash::make('Michael@123'),
                'mobile'          => '800 555-0123',
                'date_of_birth'   => '1988-02-18',
                'clinic_id'       => 3, // Wellness Dental Clinic
                'country'         => 230,
                'state'           => 3812,
                'city'            => 41432,
                'pincode'         => '12345',
                'avatar'          => null,
                'profile_image'   => public_path('/dummy-images/profile/user/robert.png'),
                'gender'          => 'male',
                'status'          => 1, // Active
                'email_verified_at' => Carbon::now(),
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
                'user_type'       => 'pharma',
            ],
            [
                'first_name'      => 'Sarah',
                'last_name'       => 'Lee',
                'email'           => 'sarah@gmail.com',
                'password'        => Hash::make('Sarah@123'),
                'mobile'          => '650 555-0145',
                'date_of_birth'   => '1998-07-25',
                'clinic_id'       => 1, // HeartCare & OrthoCare Center
                'country'         => 230,
                'state'           => 3812,
                'city'            => 41432,
                'pincode'         => '12345',
                'avatar'          => null,
                'profile_image'   => public_path('/dummy-images/profile/user/diana.png'),
                'gender'          => 'female',
                'status'          => 0, // Inactive
                'email_verified_at' => Carbon::now(),
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
                'user_type'       => 'pharma',
            ],
        ];

        foreach ($pharmaUsers as $userData) {

            $userData = Arr::except($userData, ['profile_image']);

            if (!User::where('email', $userData['email'])->exists()) {
                $user = User::create($userData);

                $user->assignRole('pharma');

                event(new UserCreated($user));

                if (isset($userData['profile_image'])) {
                    $this->attachFeatureImage($user, $userData['profile_image']);
                }
            } else {
                $user = User::where('email', $userData['email'])->first();
                $user->update($userData);
            }

            // Ensure the pharma role is assigned

        }
    }
    private function attachFeatureImage($model, $publicPath)
    {
        if (!env('IS_DUMMY_DATA_IMAGE')) return false;

        $file = new \Illuminate\Http\File($publicPath);

        $media = $model->addMedia($file)->preservingOriginal()->toMediaCollection('profile_image');

        return $media;
    }
}
