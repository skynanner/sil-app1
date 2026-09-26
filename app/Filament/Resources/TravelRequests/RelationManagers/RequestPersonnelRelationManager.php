<?php

namespace App\Filament\Resources\TravelRequests\RelationManagers;

use App\Models\Employee;
use App\Models\TravelRequest;
use App\Models\TravelRequestPersonnel;
use App\Services\TravelRequestService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RequestPersonnelRelationManager extends RelationManager
{
    protected static string $relationship = 'requestPersonnel';

    protected static ?string $title = 'Daftar Personel / Pelaksana SPRINT';

    public function form(Schema $schema): Schema
    {
        /** @var TravelRequest $owner */
        $owner = $this->getOwnerRecord();

        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'full_name')
                    ->getOptionLabelFromRecordUsing(fn (Employee $record) => "{$record->full_name} - NIP: {$record->nip} ({$record->position})")
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('activity_start_date')
                    ->label('Tanggal Mulai')
                    ->default($owner?->activity_start_date)
                    ->required(),
                DatePicker::make('activity_end_date')
                    ->label('Tanggal Selesai')
                    ->default($owner?->activity_end_date)
                    ->afterOrEqual('activity_start_date')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('employee.full_name')
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.rank_grade')
                    ->label('Pangkat / Gol')
                    ->sortable(),
                TextColumn::make('employee.position')
                    ->label('Jabatan')
                    ->wrap(),
                TextColumn::make('employee.workUnit.name')
                    ->label('Satker')
                    ->wrap(),
                TextColumn::make('activity_start_date')
                    ->label('Tgl Mulai')
                    ->date('d M Y'),
                TextColumn::make('activity_end_date')
                    ->label('Tgl Selesai')
                    ->date('d M Y'),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Personel')
                    ->before(function (CreateAction $action, array $data) {
                        $service = app(TravelRequestService::class);
                        $error = $service->checkEmployeeEligibility(
                            (int) $data['employee_id'],
                            $data['activity_start_date'],
                            $data['activity_end_date'],
                            $this->getOwnerRecord()->id
                        );

                        if ($error) {
                            Notification::make()
                                ->title('Validasi Personel Gagal')
                                ->body($error)
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->before(function (EditAction $action, TravelRequestPersonnel $record, array $data) {
                        $service = app(TravelRequestService::class);
                        $error = $service->checkEmployeeEligibility(
                            (int) $data['employee_id'],
                            $data['activity_start_date'],
                            $data['activity_end_date'],
                            $this->getOwnerRecord()->id
                        );

                        if ($error) {
                            Notification::make()
                                ->title('Validasi Personel Gagal')
                                ->body($error)
                                ->danger()
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
