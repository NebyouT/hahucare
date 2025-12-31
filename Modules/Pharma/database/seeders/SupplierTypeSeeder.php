<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplierTypes = [
            'Pharmaceutical Distributor',
            'Medical Equipment Vendor',
            'Compounding Pharmacy',
        ];

        $data = [];
        foreach ($supplierTypes as $type) {
            $data[] = [
                'name' => $type,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('supplier_types')->insert($data);
    }
}
