<?php

namespace Database\Seeders;

use App\Models\BudgetAllocation;
use App\Models\WorkUnit;
use Illuminate\Database\Seeder;

class BudgetAllocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ditOps = WorkUnit::where('code', 'DIT_OPS')->first();
        $ditHukum = WorkUnit::where('code', 'DIT_HUKUM')->first();
        $biroUmum = WorkUnit::where('code', 'BIRO_UMUM')->first();
        $zonaBarat = WorkUnit::where('code', 'ZONA_BARAT')->first();

        $allocations = [
            [
                'work_unit_id' => $ditOps?->id,
                'fiscal_year' => 2026,
                'account_code' => '524111',
                'description' => 'Belanja Perjalanan Dinas Biasa Operasi Pengawasan Laut',
                'total_amount' => 500000000.00,
                'committed_amount' => 45000000.00,
                'realized_amount' => 125000000.00,
            ],
            [
                'work_unit_id' => $ditOps?->id,
                'fiscal_year' => 2026,
                'account_code' => '524113',
                'description' => 'Belanja Perjalanan Dinas Dalam Kota Kegiatan Operasi Laut',
                'total_amount' => 150000000.00,
                'committed_amount' => 12000000.00,
                'realized_amount' => 38000000.00,
            ],
            [
                'work_unit_id' => $ditHukum?->id,
                'fiscal_year' => 2026,
                'account_code' => '524111',
                'description' => 'Belanja Perjalanan Dinas Penegakan Hukum dan Advokasi Kemaritiman',
                'total_amount' => 350000000.00,
                'committed_amount' => 30000000.00,
                'realized_amount' => 80000000.00,
            ],
            [
                'work_unit_id' => $biroUmum?->id,
                'fiscal_year' => 2026,
                'account_code' => '524111',
                'description' => 'Belanja Perjalanan Dinas Pengelolaan Perlengkapan & Umum',
                'total_amount' => 250000000.00,
                'committed_amount' => 20000000.00,
                'realized_amount' => 65000000.00,
            ],
            [
                'work_unit_id' => $zonaBarat?->id,
                'fiscal_year' => 2026,
                'account_code' => '524111',
                'description' => 'Belanja Perjalanan Dinas Rutin Pangkalan Patroli Zona Barat',
                'total_amount' => 400000000.00,
                'committed_amount' => 35000000.00,
                'realized_amount' => 110000000.00,
            ],
        ];

        foreach ($allocations as $alloc) {
            if ($alloc['work_unit_id']) {
                BudgetAllocation::updateOrCreate(
                    [
                        'work_unit_id' => $alloc['work_unit_id'],
                        'fiscal_year' => $alloc['fiscal_year'],
                        'account_code' => $alloc['account_code'],
                    ],
                    $alloc
                );
            }
        }
    }
}
