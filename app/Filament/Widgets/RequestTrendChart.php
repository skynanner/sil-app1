<?php

namespace App\Filament\Widgets;

use App\Models\TravelRequest;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RequestTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pengajuan SPRINT (6 Bulan Terakhir)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $months = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('M Y');
            $months[] = $monthName;

            $count = TravelRequest::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $counts[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah SPRINT Diajukan',
                    'data' => $counts,
                    'borderColor' => '#d97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
