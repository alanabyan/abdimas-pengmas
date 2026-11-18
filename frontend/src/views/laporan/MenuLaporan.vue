<template>
    <div class="laporan-menu">

        <div class="page-top">
            <div>
                <h1 class="pg-title">Laporan</h1>
                <p class="pg-sub">Pilih jenis laporan yang ingin Anda lihat atau ekspor.</p>
            </div>
        </div>

        <div v-if="route.path.startsWith('/bendahara')" class="menu-grid">
            <RouterLink :to="`${basePath}/keuangan`" class="menu-card menu-card--amber">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path d="M4 19h16" />
                        <path d="M7 16V8" />
                        <path d="M12 16V5" />
                        <path d="M17 16v-6" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Laporan Keuangan</h3>
                    <p class="menu-card__desc">Rekap infaq, qurban, dan transaksi bendahara per bulan.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>

            <RouterLink :to="`${basePath}/keuangan`" class="menu-card menu-card--green">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path d="M19 11H5" />
                        <path d="M19 7H5" />
                        <path d="M19 15H5" />
                        <path d="M19 19H5" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Rekap Qurban</h3>
                    <p class="menu-card__desc">Cek daftar pendaftaran qurban, status pembayaran, dan nominal per sapi.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>

            <RouterLink to="/bendahara/kas" class="menu-card menu-card--blue">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path d="M3 6h18v12H3z" />
                        <path d="M16 12h3" />
                        <path d="M8 12h3" />
                        <path d="M12 8v8" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Kas Masuk &amp; Keluar</h3>
                    <p class="menu-card__desc">Pantau mutasi kas, pengeluaran, dan saldo kas bendahara harian.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>
        </div>

        <div v-else class="menu-grid">
            <RouterLink :to="`${basePath}/keuangan`" class="menu-card menu-card--amber">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path d="M4 19h16" />
                        <path d="M7 16V8" />
                        <path d="M12 16V5" />
                        <path d="M17 16v-6" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Laporan Keuangan</h3>
                    <p class="menu-card__desc">Rekap infaq, qurban, dan transaksi terbaru untuk monitoring bendahara.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>

            <RouterLink :to="`${basePath}/peminjaman`" class="menu-card menu-card--green">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Laporan Peminjaman</h3>
                    <p class="menu-card__desc">Rekap seluruh transaksi peminjaman barang. Filter berdasarkan tanggal,
                        warga, atau status.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>

            <RouterLink :to="`${basePath}/kerusakan`" class="menu-card menu-card--red">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Laporan Kerusakan &amp; Kehilangan</h3>
                    <p class="menu-card__desc">Daftar barang yang dikembalikan dalam kondisi rusak atau dilaporkan
                        hilang.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>

            <RouterLink :to="`${basePath}/stok`" class="menu-card menu-card--blue">
                <div class="menu-card__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" width="28" height="28">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                    </svg>
                </div>
                <div class="menu-card__body">
                    <h3 class="menu-card__title">Laporan Stok Barang</h3>
                    <p class="menu-card__desc">Rekap ketersediaan stok barang per kategori. Ketahui jumlah yang tersedia
                        dan sedang dipinjam.</p>
                </div>
                <div class="menu-card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        width="18" height="18">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </div>
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const basePath = computed(() => route.path.startsWith('/bendahara') ? '/bendahara/laporan' : '/laporan')
</script>

<style scoped>
.laporan-menu {
    font-family: 'DM Sans', 'Inter', sans-serif;
}

.page-top {
    margin-bottom: 24px;
}

.pg-title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.pg-sub {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0;
}

.menu-grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-width: 640px;
}

.menu-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: white;
    border-radius: 14px;
    padding: 20px;
    border: 1.5px solid #f0f0f0;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}

.menu-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
}

.menu-card__icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.menu-card--green .menu-card__icon {
    background: #f0fdf4;
    color: #16a34a;
}

.menu-card--green:hover {
    border-color: #86efac;
}

.menu-card--red .menu-card__icon {
    background: #fef2f2;
    color: #dc2626;
}

.menu-card--red:hover {
    border-color: #fca5a5;
}

.menu-card--blue .menu-card__icon {
    background: #eff6ff;
    color: #2563eb;
}

.menu-card--blue:hover {
    border-color: #93c5fd;
}

.menu-card--amber .menu-card__icon {
    background: #fff7ed;
    color: #d97706;
}

.menu-card--amber:hover {
    border-color: #fbbf24;
}

.menu-card__body {
    flex: 1;
    min-width: 0;
}

.menu-card__title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.menu-card__desc {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    line-height: 1.5;
}

.menu-card__arrow {
    color: #d1d5db;
    flex-shrink: 0;
    transition: color 0.15s;
}

.menu-card:hover .menu-card__arrow {
    color: #9ca3af;
}
</style>