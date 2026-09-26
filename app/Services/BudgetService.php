<?php

namespace App\Services;

use App\Models\BudgetAllocation;
use App\Models\TravelRequest;
use Exception;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Catat komitmen anggaran saat verifikasi PPK disetujui.
     */
    public function commitBudget(TravelRequest $request, float $amount): void
    {
        DB::transaction(function () use ($request, $amount) {
            $budget = BudgetAllocation::lockForUpdate()->find($request->budget_allocation_id);
            if (! $budget) {
                throw new Exception('Alokasi anggaran tidak ditemukan.');
            }

            $remaining = (float) $budget->remaining_balance;
            if ($amount > $remaining) {
                throw new Exception("Sisa anggaran ({$remaining}) tidak mencukupi untuk komitmen sebesar {$amount}.");
            }

            $budget->increment('committed_amount', $amount);
        });
    }

    /**
     * Realisasikan pembayaran SP2D (mengurangi committed_amount dan menambah realized_amount).
     */
    public function realizeBudget(TravelRequest $request, float $amount): void
    {
        DB::transaction(function () use ($request, $amount) {
            $budget = BudgetAllocation::lockForUpdate()->find($request->budget_allocation_id);
            if (! $budget) {
                throw new Exception('Alokasi anggaran tidak ditemukan.');
            }

            // Kurangi committed (jika ada) dan tambah realized
            $currentCommitted = (float) $budget->committed_amount;
            $deductCommitted = min($currentCommitted, $amount);

            $budget->decrement('committed_amount', $deductCommitted);
            $budget->increment('realized_amount', $amount);
        });
    }

    /**
     * Kembalikan dana komitmen jika pengajuan dibatalkan atau ditolak.
     */
    public function releaseCommitment(TravelRequest $request, float $amount): void
    {
        DB::transaction(function () use ($request, $amount) {
            $budget = BudgetAllocation::lockForUpdate()->find($request->budget_allocation_id);
            if (! $budget) {
                return;
            }

            $currentCommitted = (float) $budget->committed_amount;
            $deduct = min($currentCommitted, $amount);
            if ($deduct > 0) {
                $budget->decrement('committed_amount', $deduct);
            }
        });
    }
}
