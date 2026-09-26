<?php

namespace App\Console\Commands;

use App\Models\EmployeeBlock;
use App\Models\TravelMonitoring;
use App\Models\TravelReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOverdueReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perjadin:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek laporan perjalanan dinas yang melewati batas waktu (overdue) dan blokir otomatis pegawai terkait';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa laporan perjalanan dinas yang melewati batas waktu...');

        $overdueReports = TravelReport::where('status', TravelReport::STATUS_PENDING)
            ->where('due_date', '<', now()->toDateString())
            ->with(['travelRequest.requestPersonnel.employee'])
            ->get();

        if ($overdueReports->isEmpty()) {
            $this->info('Tidak ada laporan yang overdue hari ini.');
            return self::SUCCESS;
        }

        $blockedCount = 0;
        $reportCount = 0;

        foreach ($overdueReports as $report) {
            DB::transaction(function () use ($report, &$blockedCount, &$reportCount) {
                // Update status laporan menjadi OVERDUE
                $report->update(['status' => TravelReport::STATUS_OVERDUE]);
                $reportCount++;

                // Update monitoring
                TravelMonitoring::where('travel_request_id', $report->travel_request_id)
                    ->update(['report_status' => TravelMonitoring::REPORT_STATUS_OVERDUE]);

                // Blokir semua personel yang ditugaskan pada SPRINT ini
                $travelRequest = $report->travelRequest;
                if ($travelRequest && $travelRequest->requestPersonnel) {
                    foreach ($travelRequest->requestPersonnel as $personnel) {
                        $alreadyBlocked = EmployeeBlock::where('employee_id', $personnel->employee_id)
                            ->where('travel_request_id', $travelRequest->id)
                            ->whereIn('status', [EmployeeBlock::STATUS_ACTIVE, EmployeeBlock::STATUS_TGR_PROCESS])
                            ->exists();

                        if (! $alreadyBlocked) {
                            $dueDateFormatted = $report->due_date ? $report->due_date->format('d/m/Y') : '-';
                            EmployeeBlock::create([
                                'employee_id' => $personnel->employee_id,
                                'travel_request_id' => $travelRequest->id,
                                'reason' => "Keterlambatan penyampaian LPJ SPRINT No. {$travelRequest->sprint_number} (Batas Waktu: {$dueDateFormatted})",
                                'status' => EmployeeBlock::STATUS_ACTIVE,
                                'blocked_at' => now(),
                            ]);
                            $blockedCount++;
                        }
                    }
                }
            });
        }

        $this->info("Pemeriksaan selesai. {$reportCount} laporan ditandai OVERDUE, {$blockedCount} pegawai diblokir.");
        return self::SUCCESS;
    }
}
