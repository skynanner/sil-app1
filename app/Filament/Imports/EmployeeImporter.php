<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nip')
                ->label('NIP')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:50']),
            ImportColumn::make('nik')
                ->label('NIK')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:50']),
            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('rank_grade')
                ->label('Pangkat/Golongan')
                ->rules(['nullable', 'string', 'max:100']),
            ImportColumn::make('position')
                ->label('Jabatan')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('workUnit')
                ->label('Kode Satker')
                ->relationship(resolveUsing: 'code'),
        ];
    }

    public function resolveRecord(): Employee
    {
        $employee = Employee::firstOrNew([
            'nip' => $this->data['nip'],
        ]);

        $employee->imported_at = now();

        return $employee;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor data pegawai selesai. Berhasil: ' . Number::format($import->successful_rows) . ' baris.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Gagal: ' . Number::format($failedRowsCount) . ' baris.';
        }

        return $body;
    }
}
