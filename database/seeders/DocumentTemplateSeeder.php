<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();

        $templates = [
            [
                'name' => 'Format Laporan Pelaksanaan Tugas Perjalanan Dinas (LPD)',
                'document_type' => DocumentTemplate::TYPE_TRAVEL_REPORT,
                'file_path' => 'templates/format_lpd_bakamla_v1.docx',
                'version' => 1,
                'is_active' => true,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Format Surat Pernyataan Tanggung Jawab Belanja (SPTJB)',
                'document_type' => DocumentTemplate::TYPE_ACCOUNTABILITY,
                'file_path' => 'templates/format_sptjb_bakamla_v1.docx',
                'version' => 1,
                'is_active' => true,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Format Daftar Pengeluaran Riil (DPR)',
                'document_type' => DocumentTemplate::TYPE_ACCOUNTABILITY,
                'file_path' => 'templates/format_dpr_bakamla_v1.docx',
                'version' => 1,
                'is_active' => true,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
