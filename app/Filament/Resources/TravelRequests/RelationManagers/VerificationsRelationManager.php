<?php

namespace App\Filament\Resources\TravelRequests\RelationManagers;

use App\Models\Verification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifications';

    protected static ?string $title = 'Riwayat Verifikasi';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('stage')
                    ->label('Tahap Verifikasi')
                    ->options([
                        Verification::STAGE_PPK => 'PPK',
                        Verification::STAGE_PPSPM => 'PPSPM',
                    ])
                    ->required(),
                Select::make('decision')
                    ->label('Keputusan')
                    ->options([
                        Verification::DECISION_ACCEPTED => 'Diterima (Disetujui)',
                        Verification::DECISION_REJECTED => 'Ditolak (Bermasalah)',
                    ])
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan / Alasan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stage')
            ->columns([
                TextColumn::make('stage')
                    ->label('Tahap')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Verification::STAGE_PPK => 'info',
                        Verification::STAGE_PPSPM => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('decision')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Verification::DECISION_ACCEPTED => 'success',
                        Verification::DECISION_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Verification::DECISION_ACCEPTED => 'Disetujui',
                        Verification::DECISION_REJECTED => 'Ditolak',
                        default => $state,
                    }),
                TextColumn::make('verifier.name')
                    ->label('Verifikator')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->wrap(),
                TextColumn::make('verified_at')
                    ->label('Waktu Verifikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
