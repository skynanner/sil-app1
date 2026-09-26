<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EmployeeBlocks\EmployeeBlockResource;
use App\Models\EmployeeBlock;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BlockedEmployeesTable extends TableWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && $user->isAdmin();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Pegawai Terblokir Aktif')
            ->query(
                EmployeeBlock::query()
                    ->whereIn('status', [EmployeeBlock::STATUS_ACTIVE, EmployeeBlock::STATUS_TGR_PROCESS])
                    ->with(['employee.workUnit', 'travelRequest'])
                    ->latest('blocked_at')
            )
            ->columns([
                TextColumn::make('employee.nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('employee.full_name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('employee.workUnit.name')
                    ->label('Satuan Kerja')
                    ->wrap(),
                TextColumn::make('travelRequest.sprint_number')
                    ->label('No. SPRINT Terkait')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EmployeeBlock::STATUS_ACTIVE => 'danger',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        EmployeeBlock::STATUS_ACTIVE => 'DIBLOKIR',
                        EmployeeBlock::STATUS_TGR_PROCESS => 'PROSES TGR',
                        default => $state,
                    }),
                TextColumn::make('reason')
                    ->label('Alasan Pemblokiran')
                    ->wrap()
                    ->limit(60),
                TextColumn::make('blocked_at')
                    ->label('Waktu Blokir')
                    ->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                Action::make('manage')
                    ->label('Kelola Blokir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (EmployeeBlock $record) => EmployeeBlockResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
