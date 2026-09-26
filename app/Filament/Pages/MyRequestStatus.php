<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\TravelRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MyRequestStatus extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Status Pengajuan Saya';
    protected static ?string $navigationLabel = 'Status Pengajuan Saya';
    protected static string|UnitEnum|null $navigationGroup = '📋 Perjalanan Dinas';
    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected string $view = 'filament.pages.my-request-status';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                TravelRequest::query()
                    ->where(function (Builder $q) use ($user) {
                        $q->where('submitted_by', $user->id);
                        if ($user->employee_id) {
                            $q->orWhereHas('requestPersonnel', function (Builder $pq) use ($user) {
                                $pq->where('employee_id', $user->employee_id);
                            });
                        }
                    })
                    ->with(['budgetAllocation.workUnit', 'costCalculation', 'verifications', 'statusHistories'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('sprint_number')
                    ->label('Nomor SPRINT')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('activity_name')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('activity_location')
                    ->label('Tujuan / Lokasi'),
                TextColumn::make('period')
                    ->label('Jadwal Kegiatan')
                    ->state(fn (TravelRequest $record) => $record->activity_start_date?->format('d M') . ' s/d ' . $record->activity_end_date?->format('d M Y')),
                TextColumn::make('status')
                    ->label('Status Pengajuan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'warning',
                        TravelRequest::STATUS_IN_PROCESS => 'info',
                        TravelRequest::STATUS_PROBLEM => 'danger',
                        TravelRequest::STATUS_SP2D => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'Menunggu Verifikasi',
                        TravelRequest::STATUS_IN_PROCESS => 'Dalam Proses',
                        TravelRequest::STATUS_PROBLEM => 'Bermasalah (Perlu Revisi)',
                        TravelRequest::STATUS_SP2D => 'SP2D Terbit (Selesai)',
                        default => $state,
                    }),
                TextColumn::make('latest_note')
                    ->label('Catatan Terakhir')
                    ->state(function (TravelRequest $record) {
                        $lastHistory = $record->statusHistories->first();
                        return $lastHistory?->notes ?: '-';
                    })
                    ->wrap()
                    ->color(fn (TravelRequest $record) => $record->status === TravelRequest::STATUS_PROBLEM ? 'danger' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'Menunggu Verifikasi',
                        TravelRequest::STATUS_IN_PROCESS => 'Dalam Proses',
                        TravelRequest::STATUS_PROBLEM => 'Bermasalah',
                        TravelRequest::STATUS_SP2D => 'SP2D (Selesai)',
                    ]),
            ])
            ->recordActions([
                Action::make('view_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (TravelRequest $record) => TravelRequestResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
