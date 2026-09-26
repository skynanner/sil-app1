<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-xl text-sm text-sky-900 dark:text-sky-200">
            <p class="font-semibold text-base mb-1">Daftar Antrean Verifikasi PPK (Pejabat Pembuat Komitmen)</p>
            <p>Halaman ini menampilkan seluruh pengajuan SPRINT yang menunggu verifikasi dan persetujuan komitmen anggaran dari PPK.</p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
