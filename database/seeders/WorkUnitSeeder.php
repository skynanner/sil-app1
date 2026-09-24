<?php

namespace Database\Seeders;

use App\Models\WorkUnit;
use Illuminate\Database\Seeder;

class WorkUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'code' => 'SETAMA',
                'name' => 'Sekretariat Utama Bakamla RI',
                'is_active' => true,
            ],
            [
                'code' => 'BIRO_KEU',
                'name' => 'Biro Keuangan dan Perencanaan',
                'is_active' => true,
            ],
            [
                'code' => 'BIRO_UMUM',
                'name' => 'Biro Umum',
                'is_active' => true,
            ],
            [
                'code' => 'DIT_OPS',
                'name' => 'Direktorat Operasi Laut',
                'is_active' => true,
            ],
            [
                'code' => 'DIT_HUKUM',
                'name' => 'Direktorat Hukum',
                'is_active' => true,
            ],
            [
                'code' => 'ZONA_BARAT',
                'name' => 'Kantor Kamla Zona Maritim Barat (Batam)',
                'is_active' => true,
            ],
            [
                'code' => 'ZONA_TENGAH',
                'name' => 'Kantor Kamla Zona Maritim Tengah (Manado)',
                'is_active' => true,
            ],
            [
                'code' => 'ZONA_TIMUR',
                'name' => 'Kantor Kamla Zona Maritim Timur (Ambon)',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            WorkUnit::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
