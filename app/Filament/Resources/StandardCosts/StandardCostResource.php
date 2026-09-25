<?php

namespace App\Filament\Resources\StandardCosts;

use App\Filament\Resources\StandardCosts\Pages\CreateStandardCost;
use App\Filament\Resources\StandardCosts\Pages\EditStandardCost;
use App\Filament\Resources\StandardCosts\Pages\ListStandardCosts;
use App\Models\StandardCost;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
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

class StandardCostResource extends Resource
{
    protected static ?string $model = StandardCost::class;

    protected static ?string $modelLabel = 'Standar Biaya (SBM)';
    protected static ?string $pluralModelLabel = 'Standar Biaya (SBM)';
    protected static string|UnitEnum|null $navigationGroup = '🔧 Master Data';
    protected static ?int $navigationSort = 4;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('cost_type')
                    ->label('Kategori Biaya')
                    ->options([
                        StandardCost::TYPE_DAILY_ALLOWANCE => 'Uang Harian',
                        StandardCost::TYPE_HOTEL => 'Biaya Hotel / Penginapan',
                        StandardCost::TYPE_AIRFARE => 'Tiket Pesawat',
                        StandardCost::TYPE_TRANSPORT => 'Transportasi Lokal / Darat',
                        StandardCost::TYPE_OTHER => 'Lain-lain',
                    ])
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Komponen SBM')
                    ->required()
                    ->maxLength(255),
                Grid::make(2)->schema([
                    TextInput::make('region_origin')
                        ->label('Wilayah Asal')
                        ->maxLength(100),
                    TextInput::make('region_destination')
                        ->label('Wilayah Tujuan')
                        ->maxLength(100),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('rank_group')
                        ->label('Tingkat / Golongan')
                        ->maxLength(100),
                    TextInput::make('unit')
                        ->label('Satuan')
                        ->placeholder('Contoh: OH, Hari, Tiket')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('unit_amount')
                        ->label('Tarif Satuan (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->required(),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('fiscal_year')
                        ->label('Tahun Anggaran')
                        ->numeric()
                        ->default((int) date('Y'))
                        ->required(),
                    DatePicker::make('valid_from')
                        ->label('Berlaku Mulai'),
                    DatePicker::make('valid_until')
                        ->label('Berlaku Sampai'),
                ]),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('cost_type')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StandardCost::TYPE_DAILY_ALLOWANCE => 'success',
                        StandardCost::TYPE_HOTEL => 'warning',
                        StandardCost::TYPE_AIRFARE => 'info',
                        StandardCost::TYPE_TRANSPORT => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('route')
                    ->label('Rute')
                    ->state(fn (StandardCost $record) => $record->region_origin || $record->region_destination
                        ? "{$record->region_origin} → {$record->region_destination}"
                        : '-'),
                TextColumn::make('rank_group')
                    ->label('Gol/Tingkat')
                    ->placeholder('-'),
                TextColumn::make('unit_amount')
                    ->label('Tarif Satuan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan'),
                TextColumn::make('fiscal_year')
                    ->label('Tahun')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('cost_type')
                    ->label('Kategori')
                    ->options([
                        StandardCost::TYPE_DAILY_ALLOWANCE => 'Uang Harian',
                        StandardCost::TYPE_HOTEL => 'Hotel',
                        StandardCost::TYPE_AIRFARE => 'Tiket Pesawat',
                        StandardCost::TYPE_TRANSPORT => 'Transportasi',
                        StandardCost::TYPE_OTHER => 'Lain-lain',
                    ]),
                SelectFilter::make('fiscal_year')
                    ->label('Tahun Anggaran')
                    ->options(fn () => StandardCost::distinct()->pluck('fiscal_year', 'fiscal_year')->toArray()),
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
            'index' => ListStandardCosts::route('/'),
            'create' => CreateStandardCost::route('/create'),
            'edit' => EditStandardCost::route('/{record}/edit'),
        ];
    }
}
