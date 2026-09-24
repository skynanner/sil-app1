<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\TreasuryOfficial;
use Illuminate\Database\Seeder;

class TreasuryOfficialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppkEmployee = Employee::where('nip', '197805202002121002')->first();
        $ppspmEmployee = Employee::where('nip', '198003102005012003')->first();
        $treasurerEmployee = Employee::where('nip', '198808122012122004')->first();

        $officials = [
            [
                'employee_id' => $ppkEmployee?->id,
                'official_type' => TreasuryOfficial::TYPE_PPK,
                'decree_number' => 'KEP-01/KABAKAMLA/I/2026',
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'employee_id' => $ppspmEmployee?->id,
                'official_type' => TreasuryOfficial::TYPE_PPSPM,
                'decree_number' => 'KEP-02/KABAKAMLA/I/2026',
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
            [
                'employee_id' => $treasurerEmployee?->id,
                'official_type' => TreasuryOfficial::TYPE_TREASURER,
                'decree_number' => 'KEP-03/KABAKAMLA/I/2026',
                'valid_from' => '2026-01-01',
                'valid_until' => '2026-12-31',
                'is_active' => true,
            ],
        ];

        foreach ($officials as $official) {
            if ($official['employee_id']) {
                TreasuryOfficial::updateOrCreate(
                    [
                        'employee_id' => $official['employee_id'],
                        'official_type' => $official['official_type'],
                    ],
                    $official
                );
            }
        }
    }
}
