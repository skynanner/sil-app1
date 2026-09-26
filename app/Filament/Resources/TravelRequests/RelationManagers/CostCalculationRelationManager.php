<?php

namespace App\Filament\Resources\TravelRequests\RelationManagers;

use App\Models\StandardCost;
use App\Models\TravelCostCalculation;
use App\Models\TravelCostItem;
use App\Models\TravelRequest;
use App\Models\TravelRequestPersonnel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostCalculationRelationManager extends RelationManager
{
    protected static string $relationship = 'costItems';

    protected static ?string $title = 'Rincian Perhitungan Biaya (SBM)';

    public function form(Schema $schema): Schema
    {
        /** @var TravelRequest $owner */
        $owner = $this->getOwnerRecord();

        return $schema
            ->components([
                Select::make('personnel_id')
                    ->label('Personel Terkait')
                    ->options(function () use ($owner) {
                        return TravelRequestPersonnel::with('employee')
                            ->where('travel_request_id', $owner->id)
                            ->get()
                            ->mapWithKeys(fn ($p) => [$p->id => "{$p->employee->full_name} ({$p->employee->nip})"]);
                    })
                    ->searchable()
                    ->required(),
                Select::make('standard_cost_id')
                    ->label('Komponen Standar Biaya (SBM)')
                    ->options(function () {
                        return StandardCost::where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($s) => [$s->id => "{$s->name} [{$s->cost_type}] - Rp " . number_format($s->unit_amount, 0, ',', '.') . " / {$s->unit}"]);
                    })
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($cost = StandardCost::find($state)) {
                            $set('unit_amount', $cost->unit_amount);
                            $set('description', $cost->name);
                            $qty = (float) ($get('quantity') ?: 1);
                            $set('subtotal', $qty * (float) $cost->unit_amount);
                        }
                    }),
                TextInput::make('description')
                    ->label('Keterangan / Uraian')
                    ->maxLength(255),
                Grid::make(3)->schema([
                    TextInput::make('quantity')
                        ->label('Kuantitas')
                        ->numeric()
                        ->default(1)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $unit = (float) ($get('unit_amount') ?: 0);
                            $set('subtotal', (float) $state * $unit);
                        })
                        ->required(),
                    TextInput::make('unit_amount')
                        ->label('Tarif Satuan (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $qty = (float) ($get('quantity') ?: 1);
                            $set('subtotal', (float) $state * $qty);
                        })
                        ->required(),
                    TextInput::make('subtotal')
                        ->label('Subtotal (Rp)')
                        ->prefix('Rp')
                        ->numeric()
                        ->readOnly()
                        ->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var TravelRequest $owner */
        $owner = $this->getOwnerRecord();
        $calc = $owner->costCalculation;

        return $table
            ->recordTitleAttribute('description')
            ->description($calc ? 'Total Anggaran Disetujui: Rp ' . number_format($calc->total_amount, 0, ',', '.') : 'Belum ada rincian biaya yang disetujui.')
            ->columns([
                TextColumn::make('personnel.employee.full_name')
                    ->label('Personel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Uraian Komponen')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->sortable(),
                TextColumn::make('unit_amount')
                    ->label('Harga Satuan')
                    ->money('IDR'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Item Biaya')
                    ->using(function (array $data): TravelCostItem {
                        /** @var TravelRequest $owner */
                        $owner = $this->getOwnerRecord();

                        $calculation = TravelCostCalculation::firstOrCreate(
                            ['travel_request_id' => $owner->id],
                            ['total_amount' => 0]
                        );

                        $item = new TravelCostItem();
                        $item->calculation_id = $calculation->id;
                        $item->personnel_id = $data['personnel_id'];
                        $item->standard_cost_id = $data['standard_cost_id'] ?? null;
                        $item->description = $data['description'] ?? null;
                        $item->quantity = $data['quantity'];
                        $item->unit_amount = $data['unit_amount'];
                        $item->subtotal = (float) $data['quantity'] * (float) $data['unit_amount'];
                        $item->save();

                        // Recalculate calculation total
                        $newTotal = $calculation->items()->sum('subtotal');
                        $calculation->update([
                            'total_amount' => $newTotal,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        return $item;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (TravelCostItem $record) {
                        $calc = $record->calculation;
                        if ($calc) {
                            $newTotal = $calc->items()->sum('subtotal');
                            $calc->update(['total_amount' => $newTotal]);
                        }
                    }),
                DeleteAction::make()
                    ->after(function (TravelCostItem $record) {
                        $calc = $record->calculation;
                        if ($calc) {
                            $newTotal = $calc->items()->sum('subtotal');
                            $calc->update(['total_amount' => $newTotal]);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function () {
                            /** @var TravelRequest $owner */
                            $owner = $this->getOwnerRecord();
                            if ($calc = $owner->costCalculation) {
                                $newTotal = $calc->items()->sum('subtotal');
                                $calc->update(['total_amount' => $newTotal]);
                            }
                        }),
                ]),
            ]);
    }
}
