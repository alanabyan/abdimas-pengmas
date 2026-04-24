<template>
    <div class="laporan-menu">

        <div class="page-top">
            <div>
                <h1 class="pg-title">Laporan</h1>
                <p class="pg-sub">Pilih jenis laporan yang ingin Anda lihat atau ekspor.</p>
            </div>
        </div>

        <!-- Cards -->
        <div class="menu-grid">

            <RouterLink to="/laporan/peminjaman" class="menu-card menu-card--green">
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

            <RouterLink to="/laporan/kerusakan" class="menu-card menu-card--red">
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
                    <h3 class="menu-card__title">Laporan Kerusakan & Kehilangan</h3>
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

            <RouterLink to="/laporan/stok" class="menu-card menu-card--blue">
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
import { RouterLink } from 'vue-router'
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