<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\TravelRequest;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestRequestsTable extends TableWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->heading('Pengajuan SPRINT Terbaru')
            ->query(function () use ($user): Builder {
                $query = TravelRequest::query()->with(['budgetAllocation.workUnit', 'submitter'])->latest('created_at')->limit(10);

                if ($user && ! $user->isAdmin() && ! $user->isPPKVerifier() && ! $user->isPPSPMVerifier()) {
                    $query->where(function (Builder $q) use ($user) {
                        $q->where('submitted_by', $user->id);
                        if ($user->employee_id) {
                            $q->orWhereHas('requestPersonnel', fn ($pq) => $pq->where('employee_id', $user->employee_id));
                        }
                    });
                }

                return $query;
            })
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
                    ->label('Lokasi'),
                TextColumn::make('period')
                    ->label('Jadwal Kegiatan')
                    ->state(fn (TravelRequest $record) => $record->activity_start_date?->format('d M') . ' - ' . $record->activity_end_date?->format('d M Y')),
                TextColumn::make('status')
                    ->label('Status')
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
                        TravelRequest::STATUS_PROBLEM => 'Bermasalah',
                        TravelRequest::STATUS_SP2D => 'SP2D Terbit',
                        default => $state,
                    }),
                TextColumn::make('submitted_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (TravelRequest $record) => TravelRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
