<?php

namespace App\Filament\Widgets;

use App\Models\TravelRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RequestStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        $query = TravelRequest::query();
        if ($user && ! $user->isAdmin() && ! $user->isPPKVerifier() && ! $user->isPPSPMVerifier()) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('submitted_by', $user->id);
                if ($user->employee_id) {
                    $q->orWhereHas('requestPersonnel', fn ($pq) => $pq->where('employee_id', $user->employee_id));
                }
            });
        }

        $total = (clone $query)->count();
        $waiting = (clone $query)->where('status', TravelRequest::STATUS_WAITING_VERIFICATION)->count();
        $inProcess = (clone $query)->where('status', TravelRequest::STATUS_IN_PROCESS)->count();
        $sp2d = (clone $query)->where('status', TravelRequest::STATUS_SP2D)->count();
        $problem = (clone $query)->where('status', TravelRequest::STATUS_PROBLEM)->count();

        return [
            Stat::make('Total Pengajuan', $total)
                ->description('Total SPRINT tercatat')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Menunggu Verifikasi', $waiting)
                ->description('Menunggu persetujuan PPK')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Dalam Proses', $inProcess)
                ->description('Disetujui PPK / Menunggu SP2D')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),

            Stat::make('SP2D Terbit', $sp2d)
                ->description('Telah dibayarkan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Bermasalah', $problem)
                ->description('Perlu revisi / perbaikan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
