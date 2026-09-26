<?php

namespace App\Filament\Resources\TravelRequests\RelationManagers;

use App\Models\TravelReport;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TravelReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'travelReports';

    protected static ?string $title = 'Laporan Pertanggungjawaban (LPJ)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('template_id')
                    ->label('Template Dokumen')
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload(),
                FileUpload::make('report_file_path')
                    ->label('File Laporan (PDF)')
                    ->directory('travel-reports')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480)
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Batas Waktu Unggah')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('status')
                    ->label('Status Laporan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TravelReport::STATUS_PENDING => 'warning',
                        TravelReport::STATUS_SUBMITTED => 'success',
                        TravelReport::STATUS_OVERDUE => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TravelReport::STATUS_PENDING => 'Belum Diunggah',
                        TravelReport::STATUS_SUBMITTED => 'Sudah Diunggah',
                        TravelReport::STATUS_OVERDUE => 'Melewati Batas Waktu',
                        default => $state,
                    }),
                TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('submitter.name')
                    ->label('Pengunggah')
                    ->placeholder('Belum Ada'),
                TextColumn::make('submitted_at')
                    ->label('Tanggal Unggah')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                Action::make('download')
                    ->label('Unduh Laporan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (TravelReport $record) => ! empty($record->report_file_path))
                    ->action(fn (TravelReport $record) => Storage::download($record->report_file_path)),
            ])
            ->toolbarActions([]);
    }
}
