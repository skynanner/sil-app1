<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\RequestStatusHistory;
use App\Models\TravelCostCalculation;
use App\Models\TravelRequest;
use App\Models\Verification;
use App\Services\BudgetService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class PpkVerification extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Verifikasi PPK';
    protected static ?string $navigationLabel = 'Antrean Verifikasi PPK';
    protected static string|UnitEnum|null $navigationGroup = '✅ Verifikasi & Pembayaran';
    protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected string $view = 'filament.pages.ppk-verification';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isPPKVerifier());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TravelRequest::query()
                    ->where('status', TravelRequest::STATUS_WAITING_VERIFICATION)
                    ->with(['budgetAllocation.workUnit', 'submitter', 'costCalculation', 'requestPersonnel.employee'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('sprint_number')
                    ->label('Nomor SPRINT')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('activity_name')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('activity_location')
                    ->label('Tujuan / Lokasi'),
                TextColumn::make('period')
                    ->label('Jadwal Kegiatan')
                    ->state(fn (TravelRequest $record) => $record->activity_start_date?->format('d M') . ' s/d ' . $record->activity_end_date?->format('d M Y')),
                TextColumn::make('budgetAllocation.account_code')
                    ->label('Pembebanan Anggaran')
                    ->state(fn (TravelRequest $record) => $record->budgetAllocation ? "{$record->budgetAllocation->account_code} ({$record->budgetAllocation->workUnit?->name})" : '-'),
                TextColumn::make('request_personnel_count')
                    ->counts('requestPersonnel')
                    ->label('Jml Personel')
                    ->badge()
                    ->color('info'),
                TextColumn::make('submitted_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('verify')
                    ->label('Proses Verifikasi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->button()
                    ->form([
                        Select::make('decision')
                            ->label('Keputusan')
                            ->options([
                                Verification::DECISION_ACCEPTED => 'Setujui (Approve)',
                                Verification::DECISION_REJECTED => 'Tolak (Bermasalah)',
                            ])
                            ->default(Verification::DECISION_ACCEPTED)
                            ->required(),
                        TextInput::make('approved_amount')
                            ->label('Komitmen Anggaran (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(fn (TravelRequest $record) => $record->costCalculation?->total_amount ?? 0)
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan Verifikasi')
                            ->required(),
                    ])
                    ->action(function (TravelRequest $record, array $data) {
                        $user = auth()->user();
                        $decision = $data['decision'];
                        $notes = $data['notes'];
                        $amount = (float) $data['approved_amount'];

                        DB::transaction(function () use ($record, $user, $decision, $notes, $amount) {
                            $oldStatus = $record->status;
                            $newStatus = ($decision === Verification::DECISION_ACCEPTED)
                                ? TravelRequest::STATUS_IN_PROCESS
                                : TravelRequest::STATUS_PROBLEM;

                            Verification::create([
                                'travel_request_id' => $record->id,
                                'verifier_id' => $user->id,
                                'stage' => Verification::STAGE_PPK,
                                'decision' => $decision,
                                'notes' => $notes,
                                'verified_at' => now(),
                            ]);

                            RequestStatusHistory::create([
                                'travel_request_id' => $record->id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'changed_by' => $user->id,
                                'changed_at' => now(),
                                'notes' => "Verifikasi PPK: {$decision} - {$notes}",
                            ]);

                            if ($decision === Verification::DECISION_ACCEPTED) {
                                $calc = $record->costCalculation ?: new TravelCostCalculation();
                                $calc->travel_request_id = $record->id;
                                $calc->total_amount = $amount;
                                $calc->approved_by = $user->id;
                                $calc->approved_at = now();
                                $calc->notes = $notes;
                                $calc->save();

                                if ($amount > 0) {
                                    app(BudgetService::class)->commitBudget($record, $amount);
                                }
                            }

                            $record->update(['status' => $newStatus]);
                        });

                        Notification::make()
                            ->title('Verifikasi Berhasil')
                            ->body("Pengajuan SPRINT {$record->sprint_number} berhasil diproses.")
                            ->success()
                            ->send();
                    }),

                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (TravelRequest $record) => TravelRequestResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
