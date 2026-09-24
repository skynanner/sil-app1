<?php

namespace Database\Seeders;

use App\Models\StandardCost;
use Illuminate\Database\Seeder;

class StandardCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $costs = [
            // Uang Harian (DAILY_ALLOWANCE)
            [
                'cost_type' => StandardCost::TYPE_DAILY_ALLOWANCE,
                'name' => 'Uang Harian Perjalanan Dinas - DKI Jakarta',
                'region_origin' => null,
                'region_destination' => 'DKI Jakarta',
                'rank_group' => 'Semua Golongan',
                'unit' => 'hari',
                'unit_amount' => 530000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_DAILY_ALLOWANCE,
                'name' => 'Uang Harian Perjalanan Dinas - Kepulauan Riau (Batam)',
                'region_origin' => null,
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Semua Golongan',
                'unit' => 'hari',
                'unit_amount' => 450000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_DAILY_ALLOWANCE,
                'name' => 'Uang Harian Perjalanan Dinas - Sulawesi Utara (Manado)',
                'region_origin' => null,
                'region_destination' => 'Sulawesi Utara',
                'rank_group' => 'Semua Golongan',
                'unit' => 'hari',
                'unit_amount' => 420000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_DAILY_ALLOWANCE,
                'name' => 'Uang Harian Diklat / Dalam Kota > 8 Jam',
                'region_origin' => null,
                'region_destination' => 'Dalam Kota',
                'rank_group' => 'Semua Golongan',
                'unit' => 'hari',
                'unit_amount' => 210000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],

            // Biaya Hotel / Penginapan (HOTEL)
            [
                'cost_type' => StandardCost::TYPE_HOTEL,
                'name' => 'Biaya Hotel Kepri - Pejabat Eselon II / Pamen Kolonel',
                'region_origin' => null,
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Eselon II / Gol IV',
                'unit' => 'malam',
                'unit_amount' => 1490000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_HOTEL,
                'name' => 'Biaya Hotel Kepri - Pejabat Eselon III / Pama / Gol III',
                'region_origin' => null,
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Eselon III / Gol III',
                'unit' => 'malam',
                'unit_amount' => 990000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_HOTEL,
                'name' => 'Biaya Hotel Kepri - Golongan I / II',
                'region_origin' => null,
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Golongan I / II',
                'unit' => 'malam',
                'unit_amount' => 610000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],

            // Tiket Pesawat (AIRFARE)
            [
                'cost_type' => StandardCost::TYPE_AIRFARE,
                'name' => 'Tiket Pesawat Jakarta - Batam (Ekonomi PP)',
                'region_origin' => 'DKI Jakarta',
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Semua Golongan',
                'unit' => 'tiket PP',
                'unit_amount' => 3200000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_AIRFARE,
                'name' => 'Tiket Pesawat Jakarta - Manado (Ekonomi PP)',
                'region_origin' => 'DKI Jakarta',
                'region_destination' => 'Sulawesi Utara',
                'rank_group' => 'Semua Golongan',
                'unit' => 'tiket PP',
                'unit_amount' => 5400000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],

            // Biaya Transportasi Lokal / Taksi (TRANSPORT)
            [
                'cost_type' => StandardCost::TYPE_TRANSPORT,
                'name' => 'Transport Bandara Soekarno Hatta PP (DKI Jakarta)',
                'region_origin' => 'DKI Jakarta',
                'region_destination' => 'DKI Jakarta',
                'rank_group' => 'Semua Golongan',
                'unit' => 'per trip',
                'unit_amount' => 350000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'cost_type' => StandardCost::TYPE_TRANSPORT,
                'name' => 'Transport Bandara Hang Nadim ke Pangkalan (Batam)',
                'region_origin' => 'Kepulauan Riau',
                'region_destination' => 'Kepulauan Riau',
                'rank_group' => 'Semua Golongan',
                'unit' => 'per trip',
                'unit_amount' => 280000.00,
                'fiscal_year' => 2026,
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
        ];

        foreach ($costs as $cost) {
            StandardCost::updateOrCreate(
                [
                    'name' => $cost['name'],
                    'fiscal_year' => $cost['fiscal_year'],
                ],
                $cost
            );
        }
    }
}
