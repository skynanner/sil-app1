<?php

namespace App\Filament\Resources\BudgetAllocations;

use App\Filament\Resources\BudgetAllocations\Pages\CreateBudgetAllocation;
use App\Filament\Resources\BudgetAllocations\Pages\EditBudgetAllocation;
use App\Filament\Resources\BudgetAllocations\Pages\ListBudgetAllocations;
use App\Models\BudgetAllocation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class BudgetAllocationResource extends Resource
{
    protected static ?string $model = BudgetAllocation::class;

    protected static ?string $modelLabel = 'Alokasi Anggaran';
    protected static ?string $pluralModelLabel = 'Alokasi Anggaran';
    protected static string|UnitEnum|null $navigationGroup = '🔧 Master Data';
    protected static ?int $navigationSort = 6;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('work_unit_id')
                    ->label('Satuan Kerja')
                    ->relationship('workUnit', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('fiscal_year')
                    ->label('Tahun Anggaran')
                    ->numeric()
                    ->default((int) date('Y'))
                    ->required(),
                TextInput::make('account_code')
                    ->label('Kode Akun / MAK')
                    ->placeholder('Contoh: 524111')
                    ->required()
                    ->maxLength(100),
                TextInput::make('description')
                    ->label('Uraian Anggaran')
                    ->required()
                    ->maxLength(255),
                Grid::make(3)->schema([
                    TextInput::make('total_amount')
                        ->label('Pagu Anggaran (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->required(),
                    TextInput::make('committed_amount')
                        ->label('Komitmen / Terikat (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    TextInput::make('realized_amount')
                        ->label('Realisasi (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('workUnit.name')
                    ->label('Satuan Kerja')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('fiscal_year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('account_code')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Uraian')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('total_amount')
                    ->label('Pagu')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('committed_amount')
                    ->label('Komitmen (PPK)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('realized_amount')
                    ->label('Realisasi (SP2D)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('remaining_balance')
                    ->label('Sisa Anggaran')
                    ->badge()
                    ->state(function (BudgetAllocation $record): string {
                        $remaining = (float) $record->remaining_balance;
                        $total = (float) $record->total_amount;
                        $percentage = $total > 0 ? round(($remaining / $total) * 100, 1) : 0;
                        return 'Rp ' . number_format($remaining, 0, ',', '.') . " ({$percentage}%)";
                    })
                    ->color(function (BudgetAllocation $record): string {
                        $remaining = (float) $record->remaining_balance;
                        $total = (float) $record->total_amount;
                        $percentage = $total > 0 ? ($remaining / $total) * 100 : 0;

                        if ($percentage > 50) {
                            return 'success';
                        }
                        if ($percentage >= 20) {
                            return 'warning';
                        }
                        return 'danger';
                    }),
            ])
            ->filters([
                SelectFilter::make('work_unit_id')
                    ->label('Satuan Kerja')
                    ->relationship('workUnit', 'name'),
                SelectFilter::make('fiscal_year')
                    ->label('Tahun Anggaran')
                    ->options(fn () => BudgetAllocation::distinct()->pluck('fiscal_year', 'fiscal_year')->toArray()),
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
            'index' => ListBudgetAllocations::route('/'),
            'create' => CreateBudgetAllocation::route('/create'),
            'edit' => EditBudgetAllocation::route('/{record}/edit'),
        ];
    }
}
