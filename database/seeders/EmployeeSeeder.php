<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\WorkUnit;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $biroKeu = WorkUnit::where('code', 'BIRO_KEU')->first();
        $ditOps = WorkUnit::where('code', 'DIT_OPS')->first();
        $ditHukum = WorkUnit::where('code', 'DIT_HUKUM')->first();
        $biroUmum = WorkUnit::where('code', 'BIRO_UMUM')->first();
        $zonaBarat = WorkUnit::where('code', 'ZONA_BARAT')->first();
        $zonaTengah = WorkUnit::where('code', 'ZONA_TENGAH')->first();

        $employees = [
            [
                'nip' => '198501152010121001',
                'nik' => '3171011501850001',
                'full_name' => 'Ahmad Fauzi, S.E.',
                'rank_grade' => 'Penata Tk. I / III/d',
                'position' => 'Staf Pengelola Keuangan',
                'work_unit_id' => $biroKeu?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '197805202002121002',
                'nik' => '3172022005780002',
                'full_name' => 'Kolonel Bakamla Hendra Wijaya, S.T., M.Tr.Hanla',
                'rank_grade' => 'Kolonel / IV/b',
                'position' => 'Kasubdit Patroli Laut / PPK Satker',
                'work_unit_id' => $ditOps?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '198003102005012003',
                'nik' => '3173031003800003',
                'full_name' => 'Pembina Tk. I Siti Aminah, S.E., M.M.',
                'rank_grade' => 'Pembina Tk. I / IV/b',
                'position' => 'Kepala Bagian Keuangan / PPSPM',
                'work_unit_id' => $biroKeu?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '198808122012122004',
                'nik' => '3174041208880004',
                'full_name' => 'Dian Purnama, A.Md.',
                'rank_grade' => 'Penata Muda Tk. I / III/b',
                'position' => 'Bendahara Pengeluaran',
                'work_unit_id' => $biroKeu?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '198204182006041005',
                'nik' => '3175051804820005',
                'full_name' => 'Letkol Bakamla Budi Santoso, S.H.',
                'rank_grade' => 'Letkol / IV/a',
                'position' => 'Kasi Advokasi Hukum Laut',
                'work_unit_id' => $ditHukum?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '198609252009121006',
                'nik' => '3171062509860006',
                'full_name' => 'Mayor Bakamla Rizky Pratama, S.T.',
                'rank_grade' => 'Mayor / III/d',
                'position' => 'Kasi Perencanaan Operasi Laut',
                'work_unit_id' => $ditOps?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '199102142015031007',
                'nik' => '2171071402910007',
                'full_name' => 'Kapten Bakamla Aditya Nugraha, S.Tr.Pel.',
                'rank_grade' => 'Kapten / III/b',
                'position' => 'Komandan KN Belut Laut 406',
                'work_unit_id' => $zonaBarat?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '199307222018012008',
                'nik' => '3172082207930008',
                'full_name' => 'Rina Wulandari, S.Kom.',
                'rank_grade' => 'Penata Muda / III/a',
                'position' => 'Pranata Komputer Ahli Pertama',
                'work_unit_id' => $biroUmum?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
            [
                'nip' => '199511052020121009',
                'nik' => '7171090511950009',
                'full_name' => 'Serka Kamla Eko Prasetyo',
                'rank_grade' => 'Serka / II/b',
                'position' => 'Bintara Operasi Pangkalan Manado',
                'work_unit_id' => $zonaTengah?->id,
                'is_active' => true,
                'imported_at' => now(),
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                ['nip' => $employee['nip']],
                $employee
            );
        }
    }
}
