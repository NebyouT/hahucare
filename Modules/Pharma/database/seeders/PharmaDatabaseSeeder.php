<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;

class PharmaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PharmaPermissionSeeder::class,
            PharmaUserSeeder::class,
            MedicineCategorySeeder::class,
            MedicineFormSeeder::class,
            SupplierTypeSeeder::class,
            SupplierSeeder::class,
            ManufacturerSeeder::class,
            MedicineSeeder::class,
        ]);
    }
}
