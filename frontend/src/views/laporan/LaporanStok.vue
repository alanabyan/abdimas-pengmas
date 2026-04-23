<template>
    <div class="laporan-page">

        <!-- Header -->
        <div class="page-top">
            <div class="breadcrumb">
                <RouterLink to="/laporan" class="bc-link">Laporan</RouterLink>
                <span class="bc-sep">›</span>
                <span class="bc-current">Laporan Stok Barang</span>
            </div>
            <div class="page-title-row">
                <div>
                    <h1 class="pg-title">Laporan Stok Barang</h1>
                    <p class="pg-sub">Rekap ketersediaan stok seluruh barang inventaris per kategori.</p>
                </div>
                <button class="btn-refresh" @click="fetchData" :disabled="loading">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="14" height="14" :class="{ 'rotating': loading }">
                        <polyline points="23 4 23 10 17 10" />
                        <path d="M20.49 15a9 9 0 1 1-.49-3.34" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="state-center card-pad">
            <span class="spinner-lg"></span>
            <p>Memuat data stok...</p>
        </div>

        <template v-else-if="dataStok.length">

            <!-- Grand total cards -->
            <div class="grand-total-row">
                <div class="gt-card gt-card--total">
                    <p class="gt-val">{{ grandTotal.stok_total }}</p>
                    <p class="gt-label">Total Unit Barang</p>
                </div>
                <div class="gt-card gt-card--green">
                    <p class="gt-val">{{ grandTotal.stok_tersedia }}</p>
                    <p class="gt-label">Unit Tersedia</p>
                </div>
                <div class="gt-card gt-card--orange">
                    <p class="gt-val">{{ grandTotal.stok_dipinjam }}</p>
                    <p class="gt-label">Unit Dipinjam</p>
                </div>
                <div class="gt-card gt-card--blue">
                    <p class="gt-val">{{ dataStok.length }}</p>
                    <p class="gt-label">Kategori</p>
                </div>
            </div>

            <!-- Per kategori -->
            <div v-for="kategori in dataStok" :key="kategori.kategori" class="kategori-block">

                <!-- Kategori header -->
                <div class="kat-header" @click="toggleKat(kategori.kategori)">
                    <div class="kat-header__left">
                        <div class="kat-icon">📦</div>
                        <div>
                            <p class="kat-name">{{ kategori.kategori }}</p>
                            <p class="kat-sub">{{ kategori.jumlah_barang }} barang</p>
                        </div>
                    </div>
                    <div class="kat-header__stats">
                        <span class="kat-stat kat-stat--total">Total: {{ kategori.stok_total }}</span>
                        <span class="kat-stat kat-stat--green">Tersedia: {{ kategori.stok_tersedia }}</span>
                        <span class="kat-stat kat-stat--orange" v-if="kategori.stok_dipinjam > 0">Dipinjam: {{
                            kategori.stok_dipinjam }}</span>
                    </div>
                    <svg :class="{ 'rotate-180': !collapsed[kategori.kategori] }" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16"
                        style="color:#9ca3af;transition:transform 0.2s;flex-shrink:0;">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>

                <!-- Barang table -->
                <div v-if="!collapsed[kategori.kategori]" class="kat-table-wrap">
                    <table class="stok-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th class="th-center">Stok Total</th>
                                <th class="th-center">Tersedia</th>
                                <th class="th-center">Dipinjam</th>
                                <th class="th-center">Ketersediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="barang in kategori.barangs" :key="barang.id">
                                <td>
                                    <p class="td-name">{{ barang.nama_barang }}</p>
                                </td>
                                <td class="td-loc">{{ barang.lokasi || '—' }}</td>
                                <td>
                                    <span class="badge-kondisi" :class="kondisiClass(barang.kondisi)">{{ barang.kondisi
                                        }}</span>
                                </td>
                                <td class="td-center">{{ barang.stok_total }}</td>
                                <td class="td-center td-green">{{ barang.stok_tersedia }}</td>
                                <td class="td-center td-orange">{{ barang.stok_total - barang.stok_tersedia }}</td>
                                <td class="td-center">
                                    <div class="progress-wrap">
                                        <div class="progress-bar">
                                            <div class="progress-fill"
                                                :class="progressClass(barang.stok_tersedia, barang.stok_total)"
                                                :style="{ width: progressPct(barang.stok_tersedia, barang.stok_total) + '%' }">
                                            </div>
                                        </div>
                                        <span class="progress-pct">{{ progressPct(barang.stok_tersedia,
                                            barang.stok_total) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Footer -->
            <div class="laporan-footer">
                <p>Laporan dibuat pada: <strong>{{ now }}</strong></p>
                <p>Data bersumber dari sistem inventaris SIMBA v2.0</p>
            </div>

        </template>

        <!-- Empty -->
        <div v-else class="state-center card-pad">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                style="color:#d1d5db">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
            </svg>
            <p>Tidak ada data stok barang.</p>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import laporanService from '@/services/laporanService'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const dataStok = ref([])
const grandTotal = ref({ stok_total: 0, stok_tersedia: 0, stok_dipinjam: 0 })
const collapsed = reactive({})
const now = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })

async function fetchData() {
    loading.value = true
    try {
        const res = await laporanService.getStok()
        dataStok.value = res.data ?? res
        grandTotal.value = res.grand_total ?? { stok_total: 0, stok_tersedia: 0, stok_dipinjam: 0 }
    } catch { toast.error('Gagal memuat data stok.') }
    finally { loading.value = false }
}

function toggleKat(nama) {
    collapsed[nama] = !collapsed[nama]
}

function progressPct(tersedia, total) {
    if (!total) return 0
    return Math.round((tersedia / total) * 100)
}
function progressClass(tersedia, total) {
    const pct = progressPct(tersedia, total)
    if (pct === 0) return 'fill--empty'
    if (pct < 30) return 'fill--low'
    if (pct < 70) return 'fill--mid'
    return 'fill--high'
}
function kondisiClass(k) {
    if (k === 'Baik') return 'k--baik'
    if (k === 'Rusak Ringan') return 'k--ringan'
    if (k === 'Rusak Berat') return 'k--berat'
    return ''
}

onMounted(() => fetchData())
</script>

<style scoped>
.laporan-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    margin-bottom: 10px;
    color: #9ca3af;
}

.bc-link {
    color: #16a34a;
    text-decoration: none;
    font-weight: 500;
}

.bc-sep {
    color: #d1d5db;
}

.bc-current {
    color: #374151;
    font-weight: 500;
}

.page-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.page-top {
    margin-bottom: 20px;
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
    margin: 3px 0 0;
}

.btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: white;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 9px;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}

.btn-refresh:hover:not(:disabled) {
    background: #f9fafb;
}

.btn-refresh:disabled {
    opacity: 0.6;
}

@keyframes rotate {
    to {
        transform: rotate(360deg);
    }
}

.rotating {
    animation: rotate 0.8s linear infinite;
}

/* Grand total */
.grand-total-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 18px;
}

.gt-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 16px;
    text-align: center;
}

.gt-val {
    font-size: 28px;
    font-weight: 800;
    margin: 0;
    line-height: 1;
}

.gt-label {
    font-size: 11.5px;
    font-weight: 500;
    margin: 6px 0 0;
}

.gt-card--total .gt-val {
    color: #111827;
}

.gt-card--total .gt-label {
    color: #9ca3af;
}

.gt-card--green {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.gt-card--green .gt-val {
    color: #16a34a;
}

.gt-card--green .gt-label {
    color: #166534;
}

.gt-card--orange {
    background: #fff7ed;
    border-color: #fed7aa;
}

.gt-card--orange .gt-val {
    color: #ea580c;
}

.gt-card--orange .gt-label {
    color: #9a3412;
}

.gt-card--blue {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.gt-card--blue .gt-val {
    color: #2563eb;
}

.gt-card--blue .gt-label {
    color: #1e40af;
}

/* Kategori block */
.kategori-block {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    margin-bottom: 12px;
    overflow: hidden;
}

.kat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    cursor: pointer;
    transition: background 0.15s;
}

.kat-header:hover {
    background: #f9fafb;
}

.kat-header__left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.kat-icon {
    font-size: 20px;
}

.kat-name {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.kat-sub {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 2px 0 0;
}

.kat-header__stats {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.kat-stat {
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 10px;
}

.kat-stat--total {
    background: #f1f5f9;
    color: #374151;
}

.kat-stat--green {
    background: #dcfce7;
    color: #166534;
}

.kat-stat--orange {
    background: #ffedd5;
    color: #9a3412;
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Stok table */
.kat-table-wrap {
    border-top: 1px solid #f5f5f5;
    overflow-x: auto;
}

.stok-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
    min-width: 700px;
}

.stok-table thead tr {
    background: #f9fafb;
}

.stok-table th {
    text-align: left;
    padding: 9px 14px;
    font-size: 10.5px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}

.th-center {
    text-align: center;
}

.stok-table tbody tr {
    border-top: 1px solid #f9fafb;
    transition: background 0.1s;
}

.stok-table tbody tr:hover {
    background: #fafafa;
}

.stok-table td {
    padding: 9px 14px;
    vertical-align: middle;
}

.td-name {
    font-weight: 600;
    color: #111827;
    margin: 0;
    font-size: 12.5px;
}

.td-loc {
    font-size: 12px;
    color: #9ca3af;
}

.td-center {
    text-align: center;
    font-weight: 600;
    color: #374151;
}

.td-green {
    color: #16a34a;
}

.td-orange {
    color: #ea580c;
}

.badge-kondisi {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.k--baik {
    background: #dcfce7;
    color: #166534;
}

.k--ringan {
    background: #fef9c3;
    color: #713f12;
}

.k--berat {
    background: #fee2e2;
    color: #991b1b;
}

/* Progress */
.progress-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    justify-content: center;
    min-width: 100px;
}

.progress-bar {
    flex: 1;
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease;
}

.fill--high {
    background: #16a34a;
}

.fill--mid {
    background: #f59e0b;
}

.fill--low {
    background: #ef4444;
}

.fill--empty {
    background: #e5e7eb;
}

.progress-pct {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    min-width: 30px;
    text-align: right;
}

/* Footer */
.laporan-footer {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #9ca3af;
    margin-top: 4px;
}

.state-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 24px;
    color: #9ca3af;
    font-size: 14px;
}

.card-pad {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
}

.spinner-lg {
    width: 28px;
    height: 28px;
    border: 3px solid #e5e7eb;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width:600px) {
    .grand-total-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width:400px) {
    .grand-total-row {
        grid-template-columns: 1fr 1fr;
    }
}
</style>