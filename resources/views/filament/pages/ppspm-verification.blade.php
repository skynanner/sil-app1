<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-900 dark:text-emerald-200">
            <p class="font-semibold text-base mb-1">Daftar Antrean Verifikasi PPSPM & Penerbitan SP2D</p>
            <p>Halaman ini menampilkan pengajuan yang telah disetujui PPK dan siap untuk verifikasi akhir, penerbitan SP2D, serta realisasi pembayaran.</p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
