<?php

namespace App\Filament\Resources\EmployeeBlocks;

use App\Filament\Resources\EmployeeBlocks\Pages\CreateEmployeeBlock;
use App\Filament\Resources\EmployeeBlocks\Pages\EditEmployeeBlock;
use App\Filament\Resources\EmployeeBlocks\Pages\ListEmployeeBlocks;
use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\Employee;
use App\Models\EmployeeBlock;
use App\Models\TravelRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeBlockResource extends Resource
{
    protected static ?string $model = EmployeeBlock::class;

    protected static ?string $modelLabel = 'Pegawai Terblokir';
    protected static ?string $pluralModelLabel = 'Daftar Pegawai Terblokir';
    protected static string|UnitEnum|null $navigationGroup = '📊 Dashboard & Laporan';
    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'full_name')
                    ->getOptionLabelFromRecordUsing(fn (Employee $e) => "{$e->full_name} ({$e->nip})")
                    ->searchable()
                    ->required(),
                Select::make('travel_request_id')
                    ->label('Terkait SPRINT')
                    ->relationship('travelRequest', 'sprint_number')
                    ->searchable()
                    ->nullable(),
                Select::make('status')
                    ->label('Status Blokir')
                    ->options([
                        EmployeeBlock::STATUS_ACTIVE => 'Aktif (Diblokir)',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'Dalam Proses TGR',
                        EmployeeBlock::STATUS_RESOLVED => 'Selesai / Dibuka',
                    ])
                    ->default(EmployeeBlock::STATUS_ACTIVE)
                    ->required(),
                Textarea::make('reason')
                    ->label('Alasan Pemblokiran')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('tgr_reference')
                    ->label('Referensi SK TGR / Kasus'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.full_name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('employee.workUnit.name')
                    ->label('Satker')
                    ->wrap(),
                TextColumn::make('travelRequest.sprint_number')
                    ->label('No. SPRINT')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EmployeeBlock::STATUS_ACTIVE => 'danger',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'warning',
                        EmployeeBlock::STATUS_RESOLVED => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        EmployeeBlock::STATUS_ACTIVE => 'DIBLOKIR',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'PROSES TGR',
                        EmployeeBlock::STATUS_RESOLVED => 'SELESAI (DIBUKA)',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->wrap()
                    ->limit(60),
                TextColumn::make('tgr_reference')
                    ->label('Ref TGR')
                    ->placeholder('-'),
                TextColumn::make('blocked_at')
                    ->label('Tgl Blokir')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('resolved_at')
                    ->label('Tgl Buka')
                    ->dateTime('d M Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resolver.name')
                    ->label('Dibuka Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        EmployeeBlock::STATUS_ACTIVE => 'Aktif (Diblokir)',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'Dalam Proses TGR',
                        EmployeeBlock::STATUS_RESOLVED => 'Selesai / Dibuka',
                    ]),
            ])
            ->recordActions([
                // Proses TGR
                Action::make('process_tgr')
                    ->label('Proses TGR')
                    ->icon('heroicon-o-scale')
                    ->color('warning')
                    ->visible(fn (EmployeeBlock $record) => $record->status === EmployeeBlock::STATUS_ACTIVE && auth()->user()?->isAdmin())
                    ->form([
                        TextInput::make('tgr_reference')
                            ->label('Nomor SK TGR / Dokumen Tuntutan Ganti Rugi')
                            ->placeholder('Contoh: TGR/BKML/2026/012')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan Tambahan'),
                    ])
                    ->action(function (EmployeeBlock $record, array $data) {
                        $reason = $record->reason . (! empty($data['notes']) ? " | Catatan TGR: {$data['notes']}" : '');
                        $record->update([
                            'status' => EmployeeBlock::STATUS_TGR_PROCESS,
                            'tgr_reference' => $data['tgr_reference'],
                            'reason' => $reason,
                        ]);

                        Notification::make()
                            ->title('Status Diperbarui ke Proses TGR')
                            ->success()
                            ->send();
                    }),

                // Buka Blokir (Resolve)
                Action::make('resolve_block')
                    ->label('Buka Blokir')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (EmployeeBlock $record) => $record->status !== EmployeeBlock::STATUS_RESOLVED && auth()->user()?->isAdmin())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembukaan Blokir Pegawai')
                    ->modalDescription('Apakah Anda yakin ingin membuka blokir pegawai ini? Pegawai akan dapat ditugaskan kembali dalam SPRINT baru.')
                    ->action(function (EmployeeBlock $record) {
                        $record->update([
                            'status' => EmployeeBlock::STATUS_RESOLVED,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Blokir Berhasil Dibuka')
                            ->body("Pegawai {$record->employee?->full_name} kini berstatus normal.")
                            ->success()
                            ->send();
                    }),

                Action::make('view_sprint')
                    ->label('Lihat SPRINT')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(fn (EmployeeBlock $record) => ! empty($record->travel_request_id))
                    ->url(fn (EmployeeBlock $record) => TravelRequestResource::getUrl('view', ['record' => $record->travel_request_id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeBlocks::route('/'),
            'create' => CreateEmployeeBlock::route('/create'),
            'edit' => EditEmployeeBlock::route('/{record}/edit'),
        ];
    }
}
