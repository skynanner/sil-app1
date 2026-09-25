<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard E-PERJADIN';
    protected static ?string $navigationLabel = 'Dashboard Utama';
    protected static string|UnitEnum|null $navigationGroup = '📊 Dashboard & Laporan';
    protected static ?int $navigationSort = 1;
}
