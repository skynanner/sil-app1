<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TravelRequests\TravelRequestResource;
use App\Models\Payment;
use App\Models\RequestStatusHistory;
use App\Models\TravelMonitoring;
use App\Models\TravelReport;
use App\Models\TravelRequest;
use App\Models\Verification;
use App\Services\BudgetService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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

class PpspmVerification extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Verifikasi PPSPM & SP2D';
    protected static ?string $navigationLabel = 'Antrean PPSPM & SP2D';
    protected static string|UnitEnum|null $navigationGroup = '✅ Verifikasi & Pembayaran';
    protected static ?int $navigationSort = 2;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected string $view = 'filament.pages.ppspm-verification';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isPPSPMVerifier());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TravelRequest::query()
                    ->where('status', TravelRequest::STATUS_IN_PROCESS)
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
                    ->label('Jadwal Pelaksanaan')
                    ->state(fn (TravelRequest $record) => $record->activity_start_date?->format('d M') . ' s/d ' . $record->activity_end_date?->format('d M Y')),
                TextColumn::make('costCalculation.total_amount')
                    ->label('Komitmen Biaya (PPK)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('budgetAllocation.account_code')
                    ->label('MAK / Satker')
                    ->state(fn (TravelRequest $record) => $record->budgetAllocation ? "{$record->budgetAllocation->account_code} - {$record->budgetAllocation->workUnit?->name}" : '-'),
                TextColumn::make('request_personnel_count')
                    ->counts('requestPersonnel')
                    ->label('Personel')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([])
            ->recordActions([
                Action::make('process_payment')
                    ->label('Input SP2D & Bayar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->form([
                        Select::make('decision')
                            ->label('Keputusan')
                            ->options([
                                'PAY' => 'Terbitkan SP2D & Realisasi Pembayaran',
                                'REJECT' => 'Tolak Pengajuan (Kembalikan Bermasalah)',
                            ])
                            ->default('PAY')
                            ->required(),
                        TextInput::make('sp2d_number')
                            ->label('Nomor SP2D')
                            ->placeholder('Contoh: SP2D-2026-00982')
                            ->required(fn ($get) => $get('decision') === 'PAY'),
                        TextInput::make('sakti_reference')
                            ->label('Referensi SAKTI')
                            ->placeholder('Contoh: SAKTI-2026-1123'),
                        TextInput::make('amount')
                            ->label('Nominal Realisasi Pembayaran (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(fn (TravelRequest $record) => $record->costCalculation?->total_amount ?? 0)
                            ->required(fn ($get) => $get('decision') === 'PAY'),
                        DatePicker::make('payment_date')
                            ->label('Tanggal SP2D')
                            ->default(now())
                            ->required(fn ($get) => $get('decision') === 'PAY'),
                        Textarea::make('notes')
                            ->label('Catatan PPSPM')
                            ->required(),
                    ])
                    ->action(function (TravelRequest $record, array $data) {
                        $user = auth()->user();
                        $decision = $data['decision'];
                        $notes = $data['notes'];

                        DB::transaction(function () use ($record, $user, $decision, $notes, $data) {
                            $oldStatus = $record->status;

                            if ($decision === 'PAY') {
                                $newStatus = TravelRequest::STATUS_SP2D;
                                $amount = (float) $data['amount'];

                                Payment::create([
                                    'travel_request_id' => $record->id,
                                    'sp2d_number' => $data['sp2d_number'],
                                    'sakti_reference' => $data['sakti_reference'] ?? null,
                                    'amount' => $amount,
                                    'payment_date' => $data['payment_date'],
                                    'processed_by' => $user->id,
                                ]);

                                Verification::create([
                                    'travel_request_id' => $record->id,
                                    'verifier_id' => $user->id,
                                    'stage' => Verification::STAGE_PPSPM,
                                    'decision' => Verification::DECISION_ACCEPTED,
                                    'notes' => "SP2D Terbit: {$data['sp2d_number']}. {$notes}",
                                    'verified_at' => now(),
                                ]);

                                app(BudgetService::class)->realizeBudget($record, $amount);

                                $endDate = $record->activity_end_date ? Carbon::parse($record->activity_end_date) : now();
                                TravelReport::firstOrCreate(
                                    ['travel_request_id' => $record->id],
                                    [
                                        'status' => TravelReport::STATUS_PENDING,
                                        'due_date' => $endDate->copy()->addDays(14),
                                    ]
                                );

                                $record->load('requestPersonnel');
                                $personnelCount = max(1, $record->requestPersonnel->count());
                                foreach ($record->requestPersonnel as $p) {
                                    TravelMonitoring::updateOrCreate(
                                        [
                                            'employee_id' => $p->employee_id,
                                            'travel_request_id' => $record->id,
                                        ],
                                        [
                                            'period_month' => (int) $endDate->format('m'),
                                            'period_year' => (int) $endDate->format('Y'),
                                            'activity_start_date' => $p->activity_start_date ?: $record->activity_start_date,
                                            'activity_end_date' => $p->activity_end_date ?: $record->activity_end_date,
                                            'approved_amount' => $amount / $personnelCount,
                                            'report_status' => TravelMonitoring::REPORT_STATUS_PENDING,
                                            'recorded_at' => now(),
                                        ]
                                    );
                                }
                            } else {
                                $newStatus = TravelRequest::STATUS_PROBLEM;

                                Verification::create([
                                    'travel_request_id' => $record->id,
                                    'verifier_id' => $user->id,
                                    'stage' => Verification::STAGE_PPSPM,
                                    'decision' => Verification::DECISION_REJECTED,
                                    'notes' => $notes,
                                    'verified_at' => now(),
                                ]);

                                if ($calc = $record->costCalculation) {
                                    app(BudgetService::class)->releaseCommitment($record, (float) $calc->total_amount);
                                }
                            }

                            RequestStatusHistory::create([
                                'travel_request_id' => $record->id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'changed_by' => $user->id,
                                'changed_at' => now(),
                                'notes' => "Verifikasi PPSPM: {$decision} - {$notes}",
                            ]);

                            $record->update(['status' => $newStatus]);
                        });

                        Notification::make()
                            ->title('Pembayaran Berhasil')
                            ->body("SP2D untuk SPRINT {$record->sprint_number} berhasil dicatat.")
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
