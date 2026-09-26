<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BlockedEmployeesTable;
use App\Filament\Widgets\BudgetRealizationChart;
use App\Filament\Widgets\BudgetStatsWidget;
use App\Filament\Widgets\LatestRequestsTable;
use App\Filament\Widgets\RequestStatsWidget;
use App\Filament\Widgets\RequestTrendChart;
use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard E-PERJADIN';
    protected static ?string $navigationLabel = 'Dashboard Utama';
    protected static string|UnitEnum|null $navigationGroup = '📊 Dashboard & Laporan';
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            RequestStatsWidget::class,
            BudgetStatsWidget::class,
            RequestTrendChart::class,
            BudgetRealizationChart::class,
            LatestRequestsTable::class,
            BlockedEmployeesTable::class,
        ];
    }
}
