<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeBlock;
use App\Models\TravelReport;
use App\Models\TravelRequest;
use App\Models\TravelRequestPersonnel;
use Carbon\Carbon;

class TravelRequestService
{
    /**
     * Validasi apakah pegawai dapat ditugaskan untuk rentang tanggal tertentu.
     * Mengembalikan pesan kesalahan jika tidak valid, atau null jika valid.
     */
    public function checkEmployeeEligibility(int $employeeId, string $startDate, string $endDate, ?int $excludeRequestId = null): ?string
    {
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return 'Pegawai tidak ditemukan.';
        }

        if (! $employee->is_active) {
            return "Pegawai {$employee->full_name} berstatus tidak aktif.";
        }

        // Cek status blokir aktif
        $isBlocked = EmployeeBlock::where('employee_id', $employeeId)
            ->whereIn('status', [EmployeeBlock::STATUS_ACTIVE, EmployeeBlock::STATUS_TGR_PROCESS])
            ->exists();

        if ($isBlocked) {
            return "Pegawai {$employee->full_name} sedang DIBLOKIR karena laporan perjalanan dinas yang belum diselesaikan atau proses TGR.";
        }

        // Cek laporan perjalanan yang overdue
        $hasOverdue = TravelReport::where('status', TravelReport::STATUS_OVERDUE)
            ->whereHas('travelRequest.requestPersonnel', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->exists();

        if ($hasOverdue) {
            return "Pegawai {$employee->full_name} memiliki laporan perjalanan dinas yang sudah MELEWATI BATAS WAKTU (OVERDUE).";
        }

        // Cek tumpang tindih tanggal penugasan (overlap) dengan SPRINT lain yang aktif
        $hasOverlap = TravelRequestPersonnel::where('employee_id', $employeeId)
            ->whereHas('travelRequest', function ($query) use ($excludeRequestId) {
                $query->where('status', '!=', TravelRequest::STATUS_PROBLEM);
                if ($excludeRequestId) {
                    $query->where('id', '!=', $excludeRequestId);
                }
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('activity_start_date', [$startDate, $endDate])
                    ->orWhereBetween('activity_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('activity_start_date', '<=', $startDate)
                            ->where('activity_end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($hasOverlap) {
            return "Pegawai {$employee->full_name} sudah memiliki penugasan SPRINT lain yang aktif pada tanggal {$startDate} s/d {$endDate}.";
        }

        return null;
    }
}
