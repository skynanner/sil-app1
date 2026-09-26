<?php

namespace App\Filament\Widgets;

use App\Models\BudgetAllocation;
use Filament\Widgets\ChartWidget;

class BudgetRealizationChart extends ChartWidget
{
    protected ?string $heading = 'Realisasi vs Pagu Anggaran per Satker';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isPPKVerifier() || $user->isPPSPMVerifier());
    }

    protected function getData(): array
    {
        $currentYear = (int) date('Y');
        $allocations = BudgetAllocation::with('workUnit')
            ->where('fiscal_year', $currentYear)
            ->get();

        $labels = [];
        $paguData = [];
        $realizedData = [];

        foreach ($allocations as $alloc) {
            $satkerName = $alloc->workUnit?->code ?: substr($alloc->description, 0, 15);
            $labels[] = $satkerName;
            $paguData[] = (float) $alloc->total_amount;
            $realizedData[] = (float) $alloc->realized_amount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pagu Anggaran (Rp)',
                    'data' => $paguData,
                    'backgroundColor' => '#94a3b8',
                ],
                [
                    'label' => 'Realisasi SP2D (Rp)',
                    'data' => $realizedData,
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
