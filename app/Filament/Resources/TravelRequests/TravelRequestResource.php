<?php

namespace App\Filament\Resources\TravelRequests;

use App\Filament\Resources\TravelRequests\Pages\CreateTravelRequest;
use App\Filament\Resources\TravelRequests\Pages\EditTravelRequest;
use App\Filament\Resources\TravelRequests\Pages\ListTravelRequests;
use App\Filament\Resources\TravelRequests\Pages\ViewTravelRequest;
use App\Filament\Resources\TravelRequests\RelationManagers\CostCalculationRelationManager;
use App\Filament\Resources\TravelRequests\RelationManagers\RequestPersonnelRelationManager;
use App\Filament\Resources\TravelRequests\RelationManagers\StatusHistoriesRelationManager;
use App\Filament\Resources\TravelRequests\RelationManagers\TravelReportsRelationManager;
use App\Filament\Resources\TravelRequests\RelationManagers\VerificationsRelationManager;
use App\Models\BudgetAllocation;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\ReportAttachment;
use App\Models\RequestStatusHistory;
use App\Models\Role;
use App\Models\TravelMonitoring;
use App\Models\TravelReport;
use App\Models\TravelRequest;
use App\Models\Verification;
use App\Services\BudgetService;
use App\Services\TravelRequestService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class TravelRequestResource extends Resource
{
    protected static ?string $model = TravelRequest::class;

    protected static ?string $modelLabel = 'Pengajuan Perjadin';
    protected static ?string $pluralModelLabel = 'Pengajuan Perjadin';
    protected static string|UnitEnum|null $navigationGroup = '📋 Perjalanan Dinas';
    protected static ?int $navigationSort = 1;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        // Admin dan Verifikator dapat melihat semua pengajuan
        if ($user->isAdmin() || $user->isPPKVerifier() || $user->isPPSPMVerifier()) {
            return $query;
        }

        // Pegawai / User biasa hanya melihat pengajuan yang ia buat atau ia sebagai personel
        return $query->where(function (Builder $q) use ($user) {
            $q->where('submitted_by', $user->id);
            if ($user->employee_id) {
                $q->orWhereHas('requestPersonnel', function (Builder $pq) use ($user) {
                    $pq->where('employee_id', $user->employee_id);
                });
            }
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Data SPRINT')
                        ->description('Informasi Surat Perintah Tugas')
                        ->schema([
                            TextInput::make('sprint_number')
                                ->label('Nomor SPRINT')
                                ->placeholder('Contoh: SPRIN/120/IX/2026')
                                ->required()
                                ->maxLength(255)
                                ->unique(TravelRequest::class, 'sprint_number', ignoreRecord: true),
                            DatePicker::make('sprint_date')
                                ->label('Tanggal SPRINT')
                                ->default(now())
                                ->required(),
                            TextInput::make('activity_name')
                                ->label('Nama Kegiatan')
                                ->placeholder('Contoh: Operasi Pengawasan Wilayah Maritim Barat')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            FileUpload::make('sprint_file_path')
                                ->label('File Dokumen SPRINT (PDF)')
                                ->directory('sprints')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->columnSpanFull(),
                        ]),

                    Step::make('Detail Kegiatan & Anggaran')
                        ->description('Lokasi, Jadwal, dan Sumber Pembebanan Biaya')
                        ->schema([
                            TextInput::make('activity_location')
                                ->label('Lokasi Kegiatan')
                                ->placeholder('Contoh: Batam / Kepulauan Riau')
                                ->required()
                                ->maxLength(255),
                            DatePicker::make('activity_start_date')
                                ->label('Tanggal Mulai')
                                ->required()
                                ->reactive(),
                            DatePicker::make('activity_end_date')
                                ->label('Tanggal Selesai')
                                ->required()
                                ->afterOrEqual('activity_start_date')
                                ->reactive(),
                            Select::make('budget_allocation_id')
                                ->label('Pembebanan Anggaran')
                                ->options(function () {
                                    return BudgetAllocation::with('workUnit')
                                        ->get()
                                        ->mapWithKeys(function ($b) {
                                            $satker = $b->workUnit?->name ?? 'Semua';
                                            $remaining = number_format($b->remaining_balance, 0, ',', '.');
                                            return [$b->id => "[{$satker}] MAK {$b->account_code} - {$b->description} (Sisa: Rp {$remaining})"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),
                            Hidden::make('submitted_by')
                                ->default(fn () => auth()->id()),
                            Hidden::make('status')
                                ->default(TravelRequest::STATUS_WAITING_VERIFICATION),
                            Hidden::make('submitted_at')
                                ->default(fn () => now()),
                        ]),

                    Step::make('Personel Pelaksana')
                        ->description('Pegawai yang ditugaskan dalam SPRINT')
                        ->schema([
                            Repeater::make('requestPersonnel')
                                ->relationship('requestPersonnel')
                                ->label('Daftar Personel')
                                ->schema([
                                    Select::make('employee_id')
                                        ->label('Pilih Pegawai')
                                        ->relationship('employee', 'full_name')
                                        ->getOptionLabelFromRecordUsing(fn (Employee $record) => "{$record->full_name} - NIP: {$record->nip} ({$record->position})")
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    DatePicker::make('activity_start_date')
                                        ->label('Tgl Mulai Tugas')
                                        ->required(),
                                    DatePicker::make('activity_end_date')
                                        ->label('Tgl Selesai Tugas')
                                        ->afterOrEqual('activity_start_date')
                                        ->required(),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sprint_number')
                    ->label('No. SPRINT')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('activity_name')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                TextColumn::make('activity_location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Pelaksanaan')
                    ->state(fn (TravelRequest $record) => $record->activity_start_date?->format('d M') . ' - ' . $record->activity_end_date?->format('d M Y')),
                TextColumn::make('request_personnel_count')
                    ->counts('requestPersonnel')
                    ->label('Personel')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'warning',
                        TravelRequest::STATUS_IN_PROCESS => 'info',
                        TravelRequest::STATUS_PROBLEM => 'danger',
                        TravelRequest::STATUS_SP2D => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'Menunggu Verifikasi',
                        TravelRequest::STATUS_IN_PROCESS => 'Dalam Proses',
                        TravelRequest::STATUS_PROBLEM => 'Bermasalah',
                        TravelRequest::STATUS_SP2D => 'SP2D (Selesai)',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('costCalculation.total_amount')
                    ->label('Biaya Disetujui')
                    ->money('IDR')
                    ->placeholder('Rp 0')
                    ->sortable(),
                TextColumn::make('submitter.name')
                    ->label('Diajukan Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('submitted_at')
                    ->label('Tgl Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        TravelRequest::STATUS_WAITING_VERIFICATION => 'Menunggu Verifikasi',
                        TravelRequest::STATUS_IN_PROCESS => 'Dalam Proses',
                        TravelRequest::STATUS_PROBLEM => 'Bermasalah',
                        TravelRequest::STATUS_SP2D => 'SP2D (Selesai)',
                    ]),
                SelectFilter::make('budget_allocation_id')
                    ->label('Alokasi Anggaran')
                    ->relationship('budgetAllocation', 'account_code'),
            ])
            ->recordActions([
                ViewAction::make(),

                // Action Verifikasi PPK (Approve / Reject)
                Action::make('verify_ppk')
                    ->label('Verifikasi PPK')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn (TravelRequest $record) => ($record->status === TravelRequest::STATUS_WAITING_VERIFICATION) && (auth()->user()?->isPPKVerifier() || auth()->user()?->isAdmin()))
                    ->form([
                        Select::make('decision')
                            ->label('Keputusan Verifikasi')
                            ->options([
                                Verification::DECISION_ACCEPTED => 'Setujui (Approve)',
                                Verification::DECISION_REJECTED => 'Tolak (Problem)',
                            ])
                            ->required(),
                        TextInput::make('approved_amount')
                            ->label('Total Anggaran yang Disetujui (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(fn (TravelRequest $record) => $record->costCalculation?->total_amount ?? 0)
                            ->helperText('Jumlah komitmen anggaran yang akan dipotongkan dari pagu.'),
                        Textarea::make('notes')
                            ->label('Catatan Verifikasi')
                            ->required(),
                    ])
                    ->action(function (TravelRequest $record, array $data) {
                        $user = auth()->user();
                        $decision = $data['decision'];
                        $notes = $data['notes'];
                        $amount = (float) ($data['approved_amount'] ?? 0);

                        DB::transaction(function () use ($record, $user, $decision, $notes, $amount) {
                            $oldStatus = $record->status;
                            $newStatus = ($decision === Verification::DECISION_ACCEPTED)
                                ? TravelRequest::STATUS_IN_PROCESS
                                : TravelRequest::STATUS_PROBLEM;

                            // Simpan verifikasi
                            Verification::create([
                                'travel_request_id' => $record->id,
                                'verifier_id' => $user->id,
                                'stage' => Verification::STAGE_PPK,
                                'decision' => $decision,
                                'notes' => $notes,
                                'verified_at' => now(),
                            ]);

                            // Catat riwayat status
                            RequestStatusHistory::create([
                                'travel_request_id' => $record->id,
                                'old_status' => $oldStatus,
                                'new_status' => $newStatus,
                                'changed_by' => $user->id,
                                'changed_at' => now(),
                                'notes' => "Verifikasi PPK: {$decision} - {$notes}",
                            ]);

                            if ($decision === Verification::DECISION_ACCEPTED) {
                                // Update atau create kalkulasi biaya
                                $calc = $record->costCalculation ?: new \App\Models\TravelCostCalculation();
                                $calc->travel_request_id = $record->id;
                                $calc->total_amount = $amount;
                                $calc->approved_by = $user->id;
                                $calc->approved_at = now();
                                $calc->notes = $notes;
                                $calc->save();

                                // Komitmen pagu anggaran
                                if ($amount > 0) {
                                    app(BudgetService::class)->commitBudget($record, $amount);
                                }
                            }

                            $record->update(['status' => $newStatus]);
                        });

                        Notification::make()
                            ->title('Verifikasi PPK Berhasil')
                            ->body("Pengajuan SPRINT {$record->sprint_number} telah diproses.")
                            ->success()
                            ->send();
                    }),

                // Action Verifikasi PPSPM & Pembayaran SP2D
                Action::make('record_payment')
                    ->label('Input SP2D & Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (TravelRequest $record) => ($record->status === TravelRequest::STATUS_IN_PROCESS) && (auth()->user()?->isPPSPMVerifier() || auth()->user()?->isAdmin()))
                    ->form([
                        Select::make('decision')
                            ->label('Keputusan PPSPM')
                            ->options([
                                'PAY' => 'Terbitkan SP2D & Bayar',
                                'REJECT' => 'Tolak / Kembalikan ke Problem',
                            ])
                            ->default('PAY')
                            ->required(),
                        TextInput::make('sp2d_number')
                            ->label('Nomor SP2D')
                            ->placeholder('Contoh: SP2D-2026-00451')
                            ->required(fn ($get) => $get('decision') === 'PAY'),
                        TextInput::make('sakti_reference')
                            ->label('Nomor Referensi SAKTI')
                            ->placeholder('Contoh: SAKTI-REF-8891'),
                        TextInput::make('amount')
                            ->label('Nominal Pembayaran (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->default(fn (TravelRequest $record) => $record->costCalculation?->total_amount ?? 0)
                            ->required(fn ($get) => $get('decision') === 'PAY'),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran SP2D')
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

                                // Simpan Pembayaran
                                Payment::create([
                                    'travel_request_id' => $record->id,
                                    'sp2d_number' => $data['sp2d_number'],
                                    'sakti_reference' => $data['sakti_reference'] ?? null,
                                    'amount' => $amount,
                                    'payment_date' => $data['payment_date'],
                                    'processed_by' => $user->id,
                                ]);

                                // Simpan Verifikasi PPSPM
                                Verification::create([
                                    'travel_request_id' => $record->id,
                                    'verifier_id' => $user->id,
                                    'stage' => Verification::STAGE_PPSPM,
                                    'decision' => Verification::DECISION_ACCEPTED,
                                    'notes' => "SP2D Terbit: {$data['sp2d_number']}. {$notes}",
                                    'verified_at' => now(),
                                ]);

                                // Update realisasi anggaran
                                app(BudgetService::class)->realizeBudget($record, $amount);

                                // Buat Laporan Pertanggungjawaban (status PENDING, deadline H+14)
                                $endDate = $record->activity_end_date ? Carbon::parse($record->activity_end_date) : now();
                                TravelReport::firstOrCreate(
                                    ['travel_request_id' => $record->id],
                                    [
                                        'status' => TravelReport::STATUS_PENDING,
                                        'due_date' => $endDate->copy()->addDays(14),
                                    ]
                                );

                                // Buat travel_monitoring untuk setiap personel
                                $record->load('requestPersonnel');
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
                                            'approved_amount' => $amount / max(1, $record->requestPersonnel->count()),
                                            'report_status' => TravelMonitoring::REPORT_STATUS_PENDING,
                                            'recorded_at' => now(),
                                        ]
                                    );
                                }
                            } else {
                                $newStatus = TravelRequest::STATUS_PROBLEM;

                                // Simpan Verifikasi Penolakan PPSPM
                                Verification::create([
                                    'travel_request_id' => $record->id,
                                    'verifier_id' => $user->id,
                                    'stage' => Verification::STAGE_PPSPM,
                                    'decision' => Verification::DECISION_REJECTED,
                                    'notes' => $notes,
                                    'verified_at' => now(),
                                ]);

                                // Lepaskan komitmen anggaran
                                if ($calc = $record->costCalculation) {
                                    app(BudgetService::class)->releaseCommitment($record, (float) $calc->total_amount);
                                }
                            }

                            // Catat history
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
                            ->title('Proses PPSPM Selesai')
                            ->body("Pengajuan SPRINT {$record->sprint_number} berhasil diperbarui.")
                            ->success()
                            ->send();
                    }),

                // Action Upload Laporan LPJ
                Action::make('upload_lpj')
                    ->label('Upload Laporan LPJ')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(function (TravelRequest $record) {
                        if ($record->status !== TravelRequest::STATUS_SP2D) {
                            return false;
                        }
                        $report = $record->travelReports()->first();
                        return ! $report || $report->status !== TravelReport::STATUS_SUBMITTED;
                    })
                    ->form([
                        FileUpload::make('report_file_path')
                            ->label('File Laporan Kegiatan (PDF)')
                            ->directory('travel-reports')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->required(),
                        Repeater::make('attachments')
                            ->label('Lampiran Bukti (Tiket, Hotel, Kuitansi, dsb.)')
                            ->schema([
                                Select::make('attachment_type')
                                    ->label('Jenis Lampiran')
                                    ->options([
                                        'BOARDING_PASS' => 'Boarding Pass / Tiket Transport',
                                        'HOTEL_INVOICE' => 'Invoice / Kuitansi Hotel',
                                        'RECEIPT' => 'Kuitansi Pengeluaran Riil',
                                        'ACTIVITY_PHOTO' => 'Foto Dokumentasi Kegiatan',
                                        'OTHER' => 'Lain-lain',
                                    ])
                                    ->required(),
                                TextInput::make('file_name')
                                    ->label('Nama Berkas')
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label('Unggah File')
                                    ->directory('report-attachments')
                                    ->maxSize(10240)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(1),
                    ])
                    ->action(function (TravelRequest $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $user = auth()->user();

                            $report = TravelReport::firstOrNew(['travel_request_id' => $record->id]);
                            $report->submitted_by = $user->id;
                            $report->report_file_path = $data['report_file_path'];
                            $report->status = TravelReport::STATUS_SUBMITTED;
                            $report->submitted_at = now();
                            $report->save();

                            // Simpan lampiran
                            if (! empty($data['attachments'])) {
                                foreach ($data['attachments'] as $att) {
                                    ReportAttachment::create([
                                        'travel_report_id' => $report->id,
                                        'file_name' => $att['file_name'] ?? 'Lampiran',
                                        'file_path' => $att['file_path'],
                                        'attachment_type' => $att['attachment_type'] ?? 'OTHER',
                                    ]);
                                }
                            }

                            // Update monitoring records
                            TravelMonitoring::where('travel_request_id', $record->id)
                                ->update(['report_status' => TravelMonitoring::REPORT_STATUS_SUBMITTED]);

                            // Buka blokir otomatis jika ada
                            \App\Models\EmployeeBlock::where('travel_request_id', $record->id)
                                ->where('status', \App\Models\EmployeeBlock::STATUS_ACTIVE)
                                ->update([
                                    'status' => \App\Models\EmployeeBlock::STATUS_RESOLVED,
                                    'resolved_at' => now(),
                                    'resolved_by' => $user->id,
                                ]);
                        });

                        Notification::make()
                            ->title('Laporan Berhasil Diunggah')
                            ->body('Laporan pertanggungjawaban telah tercatat dan status monitoring diperbarui.')
                            ->success()
                            ->send();
                    }),

                // Unduh SPRINT
                Action::make('download_sprint')
                    ->label('Unduh SPRINT')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (TravelRequest $record) => ! empty($record->sprint_file_path))
                    ->action(fn (TravelRequest $record) => Storage::download($record->sprint_file_path)),

                EditAction::make()
                    ->visible(fn (TravelRequest $record) => auth()->user()?->isAdmin() || in_array($record->status, [TravelRequest::STATUS_WAITING_VERIFICATION, TravelRequest::STATUS_PROBLEM])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RequestPersonnelRelationManager::class,
            CostCalculationRelationManager::class,
            VerificationsRelationManager::class,
            TravelReportsRelationManager::class,
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTravelRequests::route('/'),
            'create' => CreateTravelRequest::route('/create'),
            'view' => ViewTravelRequest::route('/{record}'),
            'edit' => EditTravelRequest::route('/{record}/edit'),
        ];
    }
}
