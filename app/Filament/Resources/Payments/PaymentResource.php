<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\Payment;
use App\Models\TravelRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $modelLabel = 'Riwayat Pembayaran & SP2D';
    protected static ?string $pluralModelLabel = 'Riwayat Pembayaran & SP2D';
    protected static string|UnitEnum|null $navigationGroup = '✅ Verifikasi & Pembayaran';
    protected static ?int $navigationSort = 3;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    public static function canCreate(): bool
    {
        return false; // Created via PPSPM action
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('travel_request_id')
                    ->label('Pengajuan SPRINT')
                    ->relationship('travelRequest', 'sprint_number')
                    ->disabled(),
                TextInput::make('sp2d_number')
                    ->label('Nomor SP2D')
                    ->required(),
                TextInput::make('sakti_reference')
                    ->label('Referensi SAKTI'),
                TextInput::make('amount')
                    ->label('Nominal Pembayaran')
                    ->prefix('Rp')
                    ->numeric()
                    ->required(),
                DatePicker::make('payment_date')
                    ->label('Tanggal SP2D')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sp2d_number')
                    ->label('Nomor SP2D')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('sakti_reference')
                    ->label('Ref SAKTI')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('travelRequest.sprint_number')
                    ->label('Nomor SPRINT')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('travelRequest.activity_name')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('travelRequest.budgetAllocation.workUnit.name')
                    ->label('Satker')
                    ->wrap(),
                TextColumn::make('amount')
                    ->label('Jumlah Terbayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('Tanggal SP2D')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('processor.name')
                    ->label('Diproses Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('payment_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('payment_date', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view_request')
                    ->label('Lihat SPRINT')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Payment $record) => TravelRequestResource::getUrl('view', ['record' => $record->travel_request_id])),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
        ];
    }
}
