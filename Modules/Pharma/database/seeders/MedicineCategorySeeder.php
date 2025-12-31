<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicineCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Analgesics',
            'Antibiotics',
            'Antihypertensives',
            'Antihistamines',
            'Gastrointestinal Agents',
            'Cardiovascular',
            'Respiratory',
            'Antidiabetics',
            'Anticoagulants',
            'Hormones',
            'Diuretics',
        ];

        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'name' => $category,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('medicine_categories')->insert($data);
    }
}
