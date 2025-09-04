<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Unit IT',
                'description' => 'Unit Teknologi Informasi',
                'code' => 'IT'
            ],
            [
                'name' => 'Unit HR',
                'description' => 'Unit Human Resources',
                'code' => 'HR'
            ],
            [
                'name' => 'Unit Finance',
                'description' => 'Unit Keuangan',
                'code' => 'FIN'
            ],
            [
                'name' => 'Unit Marketing',
                'description' => 'Unit Pemasaran',
                'code' => 'MKT'
            ],
            [
                'name' => 'Unit Operations',
                'description' => 'Unit Operasional',
                'code' => 'OPS'
            ]
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
