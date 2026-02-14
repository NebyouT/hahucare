<?php

namespace Modules\Pharma\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get supplier type IDs by name
        $supplierTypes = DB::table('supplier_types')->pluck('id', 'name')->toArray();

        // Get pharma user IDs by email
        $pharmaUsers = User::where('user_type', 'pharma')
            ->whereIn('email', ['peter@gmail.com', 'emily@gmail.com', 'michael@gmail.com', 'sarah@gmail.com'])
            ->pluck('id', 'email')
            ->toArray();

        $suppliers = [
            [
                'first_name' => 'Michael',
                'last_name' => 'Scott',
                'email' => 'michael@gmail.com',
                'contact_number' => '212 555-0150',
                'supplier_type_name' => 'Pharmaceutical Distributor',
                'payment_terms' => '30',
                'pharma_email' => 'peter@gmail.com', // Peter Jones
                'status' => 'Active',
            ],
            [
                'first_name' => 'Olivia',
                'last_name' => 'Chen',
                'email' => 'olivia@gmail.com',
                'contact_number' => '404 555-0165',
                'supplier_type_name' => 'Medical Equipment Vendor',
                'payment_terms' => '45',
                'pharma_email' => 'emily@gmail.com', // Emily White
                'status' => 'Active',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Rodriguez',
                'email' => 'david@gmail.com',
                'contact_number' => '800 555-0172',
                'supplier_type_name' => 'Pharmaceutical Distributor',
                'payment_terms' => '60',
                'pharma_email' => 'michael@gmail.com', // Michael Brown
                'status' => 'Active',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Hayes',
                'email' => 'emily@gmail.com',
                'contact_number' => '650 555-0188',
                'supplier_type_name' => 'Compounding Pharmacy',
                'payment_terms' => '40',
                'pharma_email' => 'sarah@gmail.com', // Sarah Lee
                'status' => 'Active',
            ],
            [
                'first_name' => 'William',
                'last_name' => 'Kim',
                'email' => 'william@gmail.com',
                'contact_number' => '310 555-0125',
                'supplier_type_name' => 'Compounding Pharmacy',
                'payment_terms' => '50',
                'pharma_email' => 'peter@gmail.com', // Peter Jones
                'status' => 'Active',
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Patel',
                'email' => 'jessica@gmail.com',
                'contact_number' => '718 555-0191',
                'supplier_type_name' => 'Pharmaceutical Distributor',
                'payment_terms' => '45',
                'pharma_email' => 'emily@gmail.com', // Emily White
                'status' => 'Active',
            ],
        ];

        $data = [];
        foreach ($suppliers as $supplier) {
            $supplierTypeId = $supplierTypes[$supplier['supplier_type_name']] ?? null;
            $pharmaId = $pharmaUsers[$supplier['pharma_email']] ?? null;

            if ($supplierTypeId && $pharmaId) {
                $data[] = [
                    'first_name' => $supplier['first_name'],
                    'last_name' => $supplier['last_name'],
                    'email' => $supplier['email'],
                    'contact_number' => $supplier['contact_number'],
                    'pharma_id' => $pharmaId,
                    'supplier_type_id' => $supplierTypeId,
                    'payment_terms' => $supplier['payment_terms'],
                    'status' => $supplier['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($data)) {
            DB::table('suppliers')->insert($data);
        }
    }
}
