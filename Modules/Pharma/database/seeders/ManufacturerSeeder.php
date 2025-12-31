<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Map pharma first names to user IDs (pharma users)
        $pharmaUsers = User::where('user_type', 'pharma')
            ->whereIn('first_name', ['Peter', 'Emily', 'Michael', 'Sarah'])
            ->pluck('id', 'first_name')
            ->toArray();

        // Manufacturer → Pharma first_name pairs (row by row as provided)
        $rows = [
            ['manufacturer' => 'PharmaCo',       'pharma' => 'Peter'],
            ['manufacturer' => 'MedCare',        'pharma' => 'Emily'],
            ['manufacturer' => 'PharmaCo',       'pharma' => 'Michael'],
            ['manufacturer' => 'BioGen',         'pharma' => 'Sarah'],
            ['manufacturer' => 'BioGen',         'pharma' => 'Emily'],
            ['manufacturer' => 'PharmaCo',       'pharma' => 'Emily'],
            ['manufacturer' => 'MedCare',        'pharma' => 'Sarah'],
            ['manufacturer' => 'PharmaCo',       'pharma' => 'Peter'],
            ['manufacturer' => 'BioGen',         'pharma' => 'Peter'],
            ['manufacturer' => 'MedCare',        'pharma' => 'Peter'],
            ['manufacturer' => 'BioGen',         'pharma' => 'Sarah'],
            ['manufacturer' => 'PharmaCo',       'pharma' => 'Sarah'],
            ['manufacturer' => 'Global Pharma',  'pharma' => 'Sarah'],
        ];

        $data = [];

        foreach ($rows as $row) {
            $pharmaId = $pharmaUsers[$row['pharma']] ?? null;

            // Only insert if we found a matching pharma user
            if ($pharmaId) {
                $data[] = [
                    'name'       => $row['manufacturer'],
                    'pharma_id'  => $pharmaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($data)) {
            DB::table('manufacturers')->insert($data);
        }
    }
}
