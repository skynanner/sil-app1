<?php

namespace App\Filament\Resources\TreasuryOfficials;

use App\Filament\Resources\TreasuryOfficials\Pages\CreateTreasuryOfficial;
use App\Filament\Resources\TreasuryOfficials\Pages\EditTreasuryOfficial;
use App\Filament\Resources\TreasuryOfficials\Pages\ListTreasuryOfficials;
use App\Models\TreasuryOfficial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TreasuryOfficialResource extends Resource
{
    protected static ?string $model = TreasuryOfficial::class;

    protected static ?string $modelLabel = 'Pejabat Perbendaharaan';
    protected static ?string $pluralModelLabel = 'Pejabat Perbendaharaan';
    protected static string|UnitEnum|null $navigationGroup = '🔧 Master Data';
    protected static ?int $navigationSort = 3;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'full_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->full_name} ({$record->nip})")
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('official_type')
                    ->label('Tipe Pejabat')
                    ->options([
                        TreasuryOfficial::TYPE_PPK => 'PPK (Pejabat Pembuat Komitmen)',
                        TreasuryOfficial::TYPE_PPSPM => 'PPSPM (Pejabat Penandatangan SPM)',
                        TreasuryOfficial::TYPE_TREASURER => 'Bendahara Pengeluaran',
                    ])
                    ->required(),
                TextInput::make('decree_number')
                    ->label('Nomor SK / Penetapan')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('valid_from')
                    ->label('Berlaku Mulai')
                    ->required(),
                DatePicker::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->afterOrEqual('valid_from'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Nama Pejabat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('official_type')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TreasuryOfficial::TYPE_PPK => 'info',
                        TreasuryOfficial::TYPE_PPSPM => 'primary',
                        TreasuryOfficial::TYPE_TREASURER => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TreasuryOfficial::TYPE_PPK => 'PPK',
                        TreasuryOfficial::TYPE_PPSPM => 'PPSPM',
                        TreasuryOfficial::TYPE_TREASURER => 'Bendahara',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('decree_number')
                    ->label('Nomor SK')
                    ->searchable(),
                TextColumn::make('valid_from')
                    ->label('Berlaku Dari')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label('Berlaku S.d.')
                    ->date('d M Y')
                    ->placeholder('Tidak Terbatas')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('official_type')
                    ->label('Tipe Pejabat')
                    ->options([
                        TreasuryOfficial::TYPE_PPK => 'PPK',
                        TreasuryOfficial::TYPE_PPSPM => 'PPSPM',
                        TreasuryOfficial::TYPE_TREASURER => 'Bendahara',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ListTreasuryOfficials::route('/'),
            'create' => CreateTreasuryOfficial::route('/create'),
            'edit' => EditTreasuryOfficial::route('/{record}/edit'),
        ];
    }
}
