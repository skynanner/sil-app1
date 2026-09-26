<?php

namespace App\Filament\Resources\TravelRequests\RelationManagers;

use App\Models\RequestStatusHistory;
use App\Models\TravelRequest;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Riwayat Status Pengajuan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $statusColors = [
            TravelRequest::STATUS_WAITING_VERIFICATION => 'warning',
            TravelRequest::STATUS_IN_PROCESS => 'info',
            TravelRequest::STATUS_PROBLEM => 'danger',
            TravelRequest::STATUS_SP2D => 'success',
        ];

        $statusLabels = [
            TravelRequest::STATUS_WAITING_VERIFICATION => 'Menunggu Verifikasi',
            TravelRequest::STATUS_IN_PROCESS => 'Dalam Proses',
            TravelRequest::STATUS_PROBLEM => 'Bermasalah',
            TravelRequest::STATUS_SP2D => 'SP2D (Selesai)',
        ];

        return $table
            ->recordTitleAttribute('notes')
            ->columns([
                TextColumn::make('old_status')
                    ->label('Status Sebelumnya')
                    ->badge()
                    ->color(fn (?string $state) => $state ? ($statusColors[$state] ?? 'gray') : 'gray')
                    ->formatStateUsing(fn (?string $state) => $state ? ($statusLabels[$state] ?? $state) : '-'),
                TextColumn::make('new_status')
                    ->label('Status Baru')
                    ->badge()
                    ->color(fn (string $state) => $statusColors[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state) => $statusLabels[$state] ?? $state),
                TextColumn::make('changer.name')
                    ->label('Diubah Oleh')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan Perubahan')
                    ->wrap(),
                TextColumn::make('changed_at')
                    ->label('Waktu Perubahan')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
