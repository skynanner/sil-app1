<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-900 dark:text-amber-200">
            <p class="font-semibold text-base mb-1">Status Pengajuan & Pelaksanaan Perjadin Anda</p>
            <p>Halaman ini menampilkan seluruh riwayat perjalanan dinas yang Anda ajukan atau di mana Anda ditugaskan sebagai personel SPRINT.</p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
