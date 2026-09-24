<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\BudgetAllocation;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeBlock;
use App\Models\Payment;
use App\Models\ReportAttachment;
use App\Models\RequestStatusHistory;
use App\Models\StandardCost;
use App\Models\TravelCostCalculation;
use App\Models\TravelCostItem;
use App\Models\TravelMonitoring;
use App\Models\TravelReport;
use App\Models\TravelRequest;
use App\Models\TravelRequestPersonnel;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Seeder;

class TravelRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budgetOps = BudgetAllocation::where('account_code', '524111')->first();
        $userBudi = User::where('username', 'user_budi')->first();
        $userRizky = User::where('username', 'user_rizky')->first();
        $userPpk = User::where('username', 'ppk_verifier')->first();
        $userPpspm = User::where('username', 'ppspm_verifier')->first();
        $userAdmin = User::where('username', 'admin')->first();

        $empBudi = Employee::where('nip', '198204182006041005')->first();
        $empRizky = Employee::where('nip', '198609252009121006')->first();
        $empRina = Employee::where('nip', '199307222018012008')->first();
        $empAditya = Employee::where('nip', '199102142015031007')->first();
        $empEko = Employee::where('nip', '199511052020121009')->first();

        $templateReport = DocumentTemplate::where('document_type', DocumentTemplate::TYPE_TRAVEL_REPORT)->first();

        $sbmUangHarianBatam = StandardCost::where('name', 'like', '%Uang Harian%Batam%')->first();
        $sbmHotelBatam = StandardCost::where('name', 'like', '%Biaya Hotel Kepri%Eselon III%')->first();
        $sbmTiketBatam = StandardCost::where('name', 'like', '%Tiket Pesawat Jakarta - Batam%')->first();
        $sbmTransportBatam = StandardCost::where('name', 'like', '%Transport Bandara Hang Nadim%')->first();

        // -------------------------------------------------------------
        // KASUS 1: WAITING_VERIFICATION (Baru diajukan, menunggu PPK)
        // -------------------------------------------------------------
        $req1 = TravelRequest::updateOrCreate(
            ['sprint_number' => 'SPRINT/001/BAKAMLA/IX/2026'],
            [
                'sprint_date' => '2026-09-20',
                'sprint_file_path' => 'sprint_files/sprint_001_batam.pdf',
                'activity_name' => 'Koordinasi Operasi Pengamanan Maritim di Perairan Batam',
                'activity_location' => 'Batam, Kepulauan Riau',
                'activity_start_date' => '2026-10-01',
                'activity_end_date' => '2026-10-03',
                'budget_allocation_id' => $budgetOps?->id,
                'submitted_by' => $userBudi?->id,
                'status' => TravelRequest::STATUS_WAITING_VERIFICATION,
                'submitted_at' => now()->subDays(2),
            ]
        );

        if ($empBudi && $empRina) {
            TravelRequestPersonnel::updateOrCreate(
                ['travel_request_id' => $req1->id, 'employee_id' => $empBudi->id],
                [
                    'activity_start_date' => $req1->activity_start_date,
                    'activity_end_date' => $req1->activity_end_date,
                ]
            );
            TravelRequestPersonnel::updateOrCreate(
                ['travel_request_id' => $req1->id, 'employee_id' => $empRina->id],
                [
                    'activity_start_date' => $req1->activity_start_date,
                    'activity_end_date' => $req1->activity_end_date,
                ]
            );
        }

        RequestStatusHistory::create([
            'travel_request_id' => $req1->id,
            'old_status' => null,
            'new_status' => TravelRequest::STATUS_WAITING_VERIFICATION,
            'notes' => 'Pengajuan perjalanan dinas baru disubmit oleh pegawai.',
            'changed_by' => $userBudi?->id,
            'changed_at' => now()->subDays(2),
        ]);

        // -------------------------------------------------------------
        // KASUS 2: IN_PROCESS (Disetujui PPK, sedang proses di PPSPM)
        // -------------------------------------------------------------
        $req2 = TravelRequest::updateOrCreate(
            ['sprint_number' => 'SPRINT/002/BAKAMLA/IX/2026'],
            [
                'sprint_date' => '2026-09-18',
                'sprint_file_path' => 'sprint_files/sprint_002_pemeriksaan.pdf',
                'activity_name' => 'Pemeriksaan Kesiapan Sarana Patroli Pangkalan Batam',
                'activity_location' => 'Batam, Kepulauan Riau',
                'activity_start_date' => '2026-09-28',
                'activity_end_date' => '2026-09-30',
                'budget_allocation_id' => $budgetOps?->id,
                'submitted_by' => $userRizky?->id,
                'status' => TravelRequest::STATUS_IN_PROCESS,
                'submitted_at' => now()->subDays(5),
            ]
        );

        $personnelRizky = TravelRequestPersonnel::updateOrCreate(
            ['travel_request_id' => $req2->id, 'employee_id' => $empRizky->id],
            [
                'activity_start_date' => $req2->activity_start_date,
                'activity_end_date' => $req2->activity_end_date,
            ]
        );

        // Perhitungan nominatif disetujui PPK
        $calc2 = TravelCostCalculation::updateOrCreate(
            ['travel_request_id' => $req2->id],
            [
                'total_amount' => 6110000.00,
                'approved_by' => $userPpk?->id,
                'approved_at' => now()->subDays(3),
                'notes' => 'Nominatif biaya disetujui sesuai pagu SBM Kepulauan Riau.',
            ]
        );

        if ($personnelRizky && $calc2) {
            TravelCostItem::create([
                'calculation_id' => $calc2->id,
                'personnel_id' => $personnelRizky->id,
                'standard_cost_id' => $sbmTiketBatam?->id,
                'description' => 'Tiket Pesawat Jakarta - Batam PP',
                'quantity' => 1,
                'unit_amount' => 3200000.00,
                'subtotal' => 3200000.00,
            ]);
            TravelCostItem::create([
                'calculation_id' => $calc2->id,
                'personnel_id' => $personnelRizky->id,
                'standard_cost_id' => $sbmUangHarianBatam?->id,
                'description' => 'Uang Harian Perjalanan Dinas (3 Hari)',
                'quantity' => 3,
                'unit_amount' => 450000.00,
                'subtotal' => 1350000.00,
            ]);
            TravelCostItem::create([
                'calculation_id' => $calc2->id,
                'personnel_id' => $personnelRizky->id,
                'standard_cost_id' => $sbmHotelBatam?->id,
                'description' => 'Akomodasi Hotel Batam (2 Malam)',
                'quantity' => 2,
                'unit_amount' => 990000.00,
                'subtotal' => 1980000.00,
            ]);
        }

        // Verifikasi PPK
        Verification::create([
            'travel_request_id' => $req2->id,
            'verifier_id' => $userPpk?->id,
            'stage' => Verification::STAGE_PPK,
            'decision' => Verification::DECISION_ACCEPTED,
            'notes' => 'Kelengkapan dokumen dan pagu anggaran telah diverifikasi memenuhi syarat.',
            'verified_at' => now()->subDays(3),
        ]);

        RequestStatusHistory::create([
            'travel_request_id' => $req2->id,
            'old_status' => null,
            'new_status' => TravelRequest::STATUS_WAITING_VERIFICATION,
            'notes' => 'Pengajuan disubmit.',
            'changed_by' => $userRizky?->id,
            'changed_at' => now()->subDays(5),
        ]);

        RequestStatusHistory::create([
            'travel_request_id' => $req2->id,
            'old_status' => TravelRequest::STATUS_WAITING_VERIFICATION,
            'new_status' => TravelRequest::STATUS_IN_PROCESS,
            'notes' => 'Pengajuan diterima oleh Verifikator PPK, diteruskan ke PPSPM.',
            'changed_by' => $userPpk?->id,
            'changed_at' => now()->subDays(3),
        ]);

        // -------------------------------------------------------------
        // KASUS 3: SP2D (Selesai cair, SP2D terbit, LPD diupload)
        // -------------------------------------------------------------
        $req3 = TravelRequest::updateOrCreate(
            ['sprint_number' => 'SPRINT/003/BAKAMLA/VIII/2026'],
            [
                'sprint_date' => '2026-08-01',
                'sprint_file_path' => 'sprint_files/sprint_003_workshop.pdf',
                'activity_name' => 'Workshop Harmonisasi Regulasi Keamanan Laut Nasional',
                'activity_location' => 'DKI Jakarta',
                'activity_start_date' => '2026-08-10',
                'activity_end_date' => '2026-08-12',
                'budget_allocation_id' => $budgetOps?->id,
                'submitted_by' => $userBudi?->id,
                'status' => TravelRequest::STATUS_SP2D,
                'submitted_at' => '2026-08-02 09:00:00',
            ]
        );

        $personnelBudi3 = TravelRequestPersonnel::updateOrCreate(
            ['travel_request_id' => $req3->id, 'employee_id' => $empBudi->id],
            [
                'activity_start_date' => $req3->activity_start_date,
                'activity_end_date' => $req3->activity_end_date,
            ]
        );

        $calc3 = TravelCostCalculation::updateOrCreate(
            ['travel_request_id' => $req3->id],
            [
                'total_amount' => 1590000.00,
                'approved_by' => $userPpk?->id,
                'approved_at' => '2026-08-04 11:00:00',
                'notes' => 'Disetujui uang harian 3 hari DKI Jakarta.',
            ]
        );

        // Verifikasi PPK & PPSPM
        Verification::create([
            'travel_request_id' => $req3->id,
            'verifier_id' => $userPpk?->id,
            'stage' => Verification::STAGE_PPK,
            'decision' => Verification::DECISION_ACCEPTED,
            'notes' => 'Verifikasi materiil disetujui.',
            'verified_at' => '2026-08-04 11:00:00',
        ]);

        Verification::create([
            'travel_request_id' => $req3->id,
            'verifier_id' => $userPpspm?->id,
            'stage' => Verification::STAGE_PPSPM,
            'decision' => Verification::DECISION_ACCEPTED,
            'notes' => 'Verifikasi formal disetujui, SPM diterbitkan.',
            'verified_at' => '2026-08-06 14:00:00',
        ]);

        Payment::updateOrCreate(
            ['travel_request_id' => $req3->id],
            [
                'sp2d_number' => 'SP2D-2026-08-12001',
                'sakti_reference' => 'SAKTI-2026-VIII-00918',
                'amount' => 1590000.00,
                'payment_date' => '2026-08-08',
                'processed_by' => $userPpspm?->id,
            ]
        );

        $report3 = TravelReport::updateOrCreate(
            ['travel_request_id' => $req3->id],
            [
                'template_id' => $templateReport?->id,
                'submitted_by' => $userBudi?->id,
                'report_file_path' => 'reports/lpd_sprint_003_budi.pdf',
                'due_date' => '2026-08-19',
                'status' => TravelReport::STATUS_SUBMITTED,
                'submitted_at' => '2026-08-15 16:30:00',
            ]
        );

        ReportAttachment::create([
            'travel_report_id' => $report3->id,
            'attachment_type' => ReportAttachment::TYPE_TRANSPORT_PROOF,
            'file_name' => 'struk_taksi_bandara.pdf',
            'file_path' => 'attachments/struk_taksi_bandara.pdf',
            'uploaded_at' => '2026-08-15 16:35:00',
        ]);

        TravelMonitoring::create([
            'employee_id' => $empBudi->id,
            'travel_request_id' => $req3->id,
            'period_month' => 8,
            'period_year' => 2026,
            'activity_start_date' => '2026-08-10',
            'activity_end_date' => '2026-08-12',
            'approved_amount' => 1590000.00,
            'report_status' => TravelMonitoring::REPORT_STATUS_SUBMITTED,
            'recorded_at' => '2026-08-08 10:00:00',
        ]);

        RequestStatusHistory::create([
            'travel_request_id' => $req3->id,
            'old_status' => TravelRequest::STATUS_IN_PROCESS,
            'new_status' => TravelRequest::STATUS_SP2D,
            'notes' => 'Pembayaran telah diselesaikan via SP2D-2026-08-12001.',
            'changed_by' => $userPpspm?->id,
            'changed_at' => '2026-08-08 10:00:00',
        ]);

        // -------------------------------------------------------------
        // KASUS 4: PROBLEM (Ditolak oleh Verifikator PPK dengan catatan)
        // -------------------------------------------------------------
        $req4 = TravelRequest::updateOrCreate(
            ['sprint_number' => 'SPRINT/004/BAKAMLA/IX/2026'],
            [
                'sprint_date' => '2026-09-15',
                'sprint_file_path' => 'sprint_files/sprint_004_sosialisasi.pdf',
                'activity_name' => 'Sosialisasi Penegakan Hukum Laut di Pelabuhan Bitung',
                'activity_location' => 'Manado, Sulawesi Utara',
                'activity_start_date' => '2026-10-15',
                'activity_end_date' => '2026-10-17',
                'budget_allocation_id' => $budgetOps?->id,
                'submitted_by' => $userRizky?->id,
                'status' => TravelRequest::STATUS_PROBLEM,
                'submitted_at' => now()->subDays(6),
            ]
        );

        if ($empRizky) {
            TravelRequestPersonnel::updateOrCreate(
                ['travel_request_id' => $req4->id, 'employee_id' => $empRizky->id],
                [
                    'activity_start_date' => $req4->activity_start_date,
                    'activity_end_date' => $req4->activity_end_date,
                ]
            );
        }

        Verification::create([
            'travel_request_id' => $req4->id,
            'verifier_id' => $userPpk?->id,
            'stage' => Verification::STAGE_PPK,
            'decision' => Verification::DECISION_REJECTED,
            'notes' => 'Mata anggaran akun tidak sesuai dengan DIPA kegiatan, dan tanggal SPRINT perlu disesuaikan dengan jadwal sosialisasi.',
            'verified_at' => now()->subDays(4),
        ]);

        RequestStatusHistory::create([
            'travel_request_id' => $req4->id,
            'old_status' => TravelRequest::STATUS_WAITING_VERIFICATION,
            'new_status' => TravelRequest::STATUS_PROBLEM,
            'notes' => 'Ditolak: Mata anggaran akun tidak sesuai dengan DIPA kegiatan.',
            'changed_by' => $userPpk?->id,
            'changed_at' => now()->subDays(4),
        ]);

        // -------------------------------------------------------------
        // KASUS 5: OVERDUE & BLOCKED (Pegawai belum upload LPD -> Terblokir)
        // -------------------------------------------------------------
        if ($empAditya) {
            $req5 = TravelRequest::updateOrCreate(
                ['sprint_number' => 'SPRINT/005/BAKAMLA/VII/2026'],
                [
                    'sprint_date' => '2026-06-25',
                    'sprint_file_path' => 'sprint_files/sprint_005_aditya.pdf',
                    'activity_name' => 'Patroli Khusus Selat Malaka KN Belut Laut 406',
                    'activity_location' => 'Batam, Kepulauan Riau',
                    'activity_start_date' => '2026-07-01',
                    'activity_end_date' => '2026-07-05',
                    'budget_allocation_id' => $budgetOps?->id,
                    'submitted_by' => $userBudi?->id,
                    'status' => TravelRequest::STATUS_SP2D,
                    'submitted_at' => '2026-06-26 10:00:00',
                ]
            );

            TravelRequestPersonnel::updateOrCreate(
                ['travel_request_id' => $req5->id, 'employee_id' => $empAditya->id],
                [
                    'activity_start_date' => $req5->activity_start_date,
                    'activity_end_date' => $req5->activity_end_date,
                ]
            );

            Payment::updateOrCreate(
                ['travel_request_id' => $req5->id],
                [
                    'sp2d_number' => 'SP2D-2026-07-05009',
                    'sakti_reference' => 'SAKTI-2026-VII-00211',
                    'amount' => 4500000.00,
                    'payment_date' => '2026-06-29',
                    'processed_by' => $userPpspm?->id,
                ]
            );

            // Laporan overdue (lewat batas waktu)
            TravelReport::updateOrCreate(
                ['travel_request_id' => $req5->id],
                [
                    'template_id' => $templateReport?->id,
                    'submitted_by' => null,
                    'report_file_path' => null,
                    'due_date' => '2026-07-12', // Melewati batas
                    'status' => TravelReport::STATUS_OVERDUE,
                    'submitted_at' => null,
                ]
            );

            // Record di travel monitoring
            TravelMonitoring::create([
                'employee_id' => $empAditya->id,
                'travel_request_id' => $req5->id,
                'period_month' => 7,
                'period_year' => 2026,
                'activity_start_date' => '2026-07-01',
                'activity_end_date' => '2026-07-05',
                'approved_amount' => 4500000.00,
                'report_status' => TravelMonitoring::REPORT_STATUS_OVERDUE,
                'recorded_at' => '2026-06-29 11:00:00',
            ]);

            // Blokir aktif bagi pegawai Aditya
            EmployeeBlock::updateOrCreate(
                [
                    'employee_id' => $empAditya->id,
                    'travel_request_id' => $req5->id,
                ],
                [
                    'reason' => 'Pegawai belum mengunggah laporan perjalanan dinas SPRINT/005/BAKAMLA/VII/2026 melewati jatuh tempo (12 Juli 2026).',
                    'status' => EmployeeBlock::STATUS_ACTIVE,
                    'tgr_reference' => 'TGR/2026/07/003',
                    'blocked_at' => '2026-07-13 00:01:00',
                    'resolved_at' => null,
                    'resolved_by' => null,
                ]
            );
        }

        // -------------------------------------------------------------
        // AUDIT LOGS SAMPEL
        // -------------------------------------------------------------
        AuditLog::create([
            'user_id' => $userBudi?->id,
            'action' => 'CREATE',
            'entity_name' => 'travel_requests',
            'entity_id' => $req1->id,
            'old_value' => null,
            'new_value' => ['sprint_number' => $req1->sprint_number, 'status' => $req1->status],
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(2),
        ]);

        AuditLog::create([
            'user_id' => $userPpk?->id,
            'action' => 'APPROVE',
            'entity_name' => 'travel_requests',
            'entity_id' => $req2->id,
            'old_value' => ['status' => TravelRequest::STATUS_WAITING_VERIFICATION],
            'new_value' => ['status' => TravelRequest::STATUS_IN_PROCESS],
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(3),
        ]);
    }
}
