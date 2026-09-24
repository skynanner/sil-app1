<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'code' => Role::ADMIN,
                'name' => 'Administrator',
                'description' => 'Staff Keuangan yang mengelola master data dan hak akses pengguna.',
            ],
            [
                'code' => Role::PPK_VERIFIER,
                'name' => 'Verifikator PPK',
                'description' => 'Pejabat Pembuat Komitmen yang memverifikasi pengajuan secara materiil dan menyetujui perhitungan nominatif biaya.',
            ],
            [
                'code' => Role::PPSPM_VERIFIER,
                'name' => 'Verifikator PPSPM',
                'description' => 'Pejabat Penandatangan Surat Perintah Membayar yang memverifikasi kelayakan formal dan memproses pembayaran.',
            ],
            [
                'code' => Role::USER,
                'name' => 'Pegawai / User',
                'description' => 'Pegawai Bakamla RI yang mengajukan permohonan perjalanan dinas berdasarkan SPRINT.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}
