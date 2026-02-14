<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forms = [
            'Tablet',
            'Capsule',
            'Syrup',
            'Ointment',
            'Injection',
            'Inhaler',
        ];

        $data = [];
        foreach ($forms as $form) {
            $data[] = [
                'name' => $form,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('medicine_forms')->insert($data);
    }
}
