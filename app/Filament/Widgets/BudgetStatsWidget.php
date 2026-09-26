<?php

namespace App\Filament\Widgets;

use App\Models\BudgetAllocation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isPPKVerifier() || $user->isPPSPMVerifier());
    }

    protected function getStats(): array
    {
        $currentYear = (int) date('Y');
        $allocations = BudgetAllocation::where('fiscal_year', $currentYear)->get();

        $totalPagu = $allocations->sum('total_amount');
        $totalCommitted = $allocations->sum('committed_amount');
        $totalRealized = $allocations->sum('realized_amount');
        $remaining = $totalPagu - $totalCommitted - $totalRealized;
        $remainingPercent = $totalPagu > 0 ? round(($remaining / $totalPagu) * 100, 1) : 0;
        $realizedPercent = $totalPagu > 0 ? round(($totalRealized / $totalPagu) * 100, 1) : 0;

        return [
            Stat::make('Pagu Anggaran ' . $currentYear, 'Rp ' . number_format($totalPagu, 0, ',', '.'))
                ->description('Total pagu alokasi perjadin')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Komitmen PPK', 'Rp ' . number_format($totalCommitted, 0, ',', '.'))
                ->description('Dana terikat proses verifikasi')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),

            Stat::make('Realisasi SP2D', 'Rp ' . number_format($totalRealized, 0, ',', '.'))
                ->description("{$realizedPercent}% dari total pagu")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Sisa Pagu', 'Rp ' . number_format($remaining, 0, ',', '.'))
                ->description("{$remainingPercent}% anggaran tersedia")
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($remainingPercent < 20 ? 'danger' : 'info'),
        ];
    }
}
