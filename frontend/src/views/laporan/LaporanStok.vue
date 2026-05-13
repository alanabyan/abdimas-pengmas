<template>
    <div class="laporan-page">

        <!-- ── Header ──────────────────────────────────────────────────────── -->
        <div class="page-top">
            <div>
                <h1 class="pg-title">Laporan Stok Barang</h1>
                <p class="pg-sub">Rekap ketersediaan stok seluruh barang inventaris per kategori.</p>
            </div>
            <div style="">
                <button class="btn-pdf" @click="downloadPdf" :disabled="loadingPdf || !dataStok.length">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="14" height="14">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="12" y1="18" x2="12" y2="12" />
                        <line x1="9" y1="15" x2="15" y2="15" />
                    </svg>
                    {{ loadingPdf ? 'Memuat...' : 'Unduh PDF' }}
                </button>
            </div>
        </div>

        <!-- ── Loading ─────────────────────────────────────────────────────── -->
        <div v-if="loading" class="state-center">
            <span class="spinner-lg"></span>
            <p>Memuat data stok...</p>
        </div>

        <template v-else-if="dataStok.length">

            <!-- ── Grand total (sama persis pola gt-card di LaporanStok lama) ── -->
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

            <!-- ── Distribusi mini chart ────────────────────────────────────── -->
            <div class="dist-card">
                <p class="dist-title">Distribusi Stok per Kategori</p>
                <div class="dist-list">
                    <div v-for="kat in dataStok" :key="kat.kategori" class="dist-item">
                        <div class="dist-label-wrap">
                            <div class="dist-dot" :style="{ background: itemColor(kat.kategori) }"></div>
                            <span class="dist-nama">{{ kat.kategori }}</span>
                            <span class="dist-count">{{ kat.stok_tersedia }} / {{ kat.stok_total }}</span>
                        </div>
                        <div class="dist-bar">
                            <div class="dist-bar__fill"
                                :style="{ width: pctBar(kat.stok_tersedia, kat.stok_total) + '%', background: itemColor(kat.kategori) }">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Kategori blocks ──────────────────────────────────────────── -->
            <div v-for="kategori in dataStok" :key="kategori.kategori" class="kategori-block">

                <!-- Header accordion -->
                <div class="kat-header" @click="toggleKat(kategori.kategori)">
                    <div class="kat-header__left">
                        <div class="kat-ikon"
                            :style="{ background: itemColor(kategori.kategori) + '18', color: itemColor(kategori.kategori) }">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" width="18" height="18">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                            </svg>
                        </div>
                        <div>
                            <p class="kat-name">{{ kategori.kategori }}</p>
                            <p class="kat-sub">{{ kategori.barangs?.length || 0 }} Jenis Barang</p>
                        </div>
                    </div>

                    <div class="kat-header__stats">
                        <span class="kat-stat kat-stat--total">
                            Total: {{ kategori.stok_total }}
                        </span>
                        <span class="kat-stat kat-stat--green">
                            Ada: {{ kategori.stok_tersedia }}
                        </span>
                        <span class="kat-stat kat-stat--orange" v-if="kategori.stok_dipinjam > 0">
                            Pinjam: {{ kategori.stok_dipinjam }}
                        </span>
                    </div>

                    <!-- Progress bar mini di header -->
                    <div class="kat-progress-mini">
                        <div class="kat-progress-mini__fill"
                            :style="{ width: pctBar(kategori.stok_tersedia, kategori.stok_total) + '%', background: itemColor(kategori.kategori) }">
                        </div>
                    </div>

                    <svg :class="{ 'rotate-180': collapsed[kategori.kategori] }" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16"
                        style="color:#9ca3af; transition:transform 0.2s; flex-shrink:0;">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>

                <!-- Table barang -->
                <div v-if="!collapsed[kategori.kategori]" class="kat-table-wrap">
                    <table class="stok-table">
                        <thead>
                            <tr>
                                <th style="width:32px">#</th>
                                <th>Nama Barang</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th class="th-center">Stok Total</th>
                                <th class="th-center">Tersedia</th>
                                <th class="th-center">Dipinjam</th>
                                <th class="th-center" style="width:140px">Ketersediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(barang, idx) in kategori.barangs" :key="barang.id">
                                <td class="td-no">{{ idx + 1 }}</td>
                                <td>
                                    <p class="td-name">{{ barang.nama_barang }}</p>
                                </td>
                                <td class="td-loc">{{ barang.lokasi || '—' }}</td>
                                <td>
                                    <span class="badge-kondisi" :class="kondisiClass(barang.kondisi)">
                                        {{ barang.kondisi || '—' }}
                                    </span>
                                </td>
                                <td class="td-center td-bold">{{ barang.stok_total }}</td>
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
                                        <span class="progress-pct"
                                            :class="progressClass(barang.stok_tersedia, barang.stok_total)">
                                            {{ progressPct(barang.stok_tersedia, barang.stok_total) }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- ── Footer ──────────────────────────────────────────────────── -->
            <div class="laporan-footer">
                <div class="footer-left">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="13" height="13" style="color:#16a34a">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    Laporan dibuat pada: <strong>{{ now }}</strong>
                </div>
                <p class="footer-right">Data bersumber dari sistem inventaris Masjid</p>
            </div>

        </template>

        <!-- ── Empty ───────────────────────────────────────────────────────── -->
        <div v-else class="state-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                style="color:#d1d5db">
                <rect x="2" y="7" width="20" height="14" rx="2" />
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
            </svg>
            <p>Tidak ada data stok.</p>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import laporanService from '@/services/laporanService'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const dataStok = ref([])
const collapsed = reactive({})
const now = new Date().toLocaleDateString('id-ID', {
    day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
})

// ── Grand total ─────────────────────────────────────────────────────────────
const grandTotal = computed(() => {
    let total = 0, tersedia = 0, dipinjam = 0
    dataStok.value.forEach(kat => {
        total += Number(kat.stok_total || 0)
        tersedia += Number(kat.stok_tersedia || 0)
        dipinjam += (Number(kat.stok_total || 0) - Number(kat.stok_tersedia || 0))
    })
    return { stok_total: total, stok_tersedia: tersedia, stok_dipinjam: dipinjam }
})

const loadingPdf = ref(false)

async function downloadPdf() {
    loadingPdf.value = true
    try {
        const res = await laporanService.getStokPdf()   // lihat poin 5
        const url = URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }))
        const a = document.createElement('a')
        a.href = url
        a.download = `laporan-stok-${new Date().toISOString().slice(0, 10)}.pdf`
        a.click()
        URL.revokeObjectURL(url)
    } catch {
        toast.error('Gagal mengunduh PDF.')
    } finally {
        loadingPdf.value = false
    }
}

// ── Fetch ────────────────────────────────────────────────────────────────────
async function fetchData() {
    loading.value = true
    try {
        const res = await laporanService.getStok()
        const rawData = res.data ?? res
        dataStok.value = rawData.map(kat => ({
            ...kat,
            barangs: kat.barangs || [],
            stok_total: Number(kat.stok_total || 0),
            stok_tersedia: Number(kat.stok_tersedia || 0),
            stok_dipinjam: Number(kat.stok_total || 0) - Number(kat.stok_tersedia || 0),
        }))
    } catch {
        toast.error('Gagal memuat data stok.')
    } finally {
        loading.value = false
    }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function toggleKat(nama) { collapsed[nama] = !collapsed[nama] }

function progressPct(tersedia, total) {
    return total ? Math.round((tersedia / total) * 100) : 0
}

function pctBar(tersedia, total) {
    return total ? Math.round((tersedia / total) * 100) : 0
}

function progressClass(tersedia, total) {
    const pct = progressPct(tersedia, total)
    if (pct < 30) return 'fill--low'
    if (pct < 70) return 'fill--mid'
    return 'fill--high'
}

function kondisiClass(k) {
    if (k === 'Baik') return 'k--baik'
    if (k === 'Rusak Ringan') return 'k--ringan'
    return 'k--berat'
}

// Warna per kategori (sama persis dengan Kategori.vue)
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function itemColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

onMounted(fetchData)
</script>

<style scoped>
.laporan-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
}

/* ── Header ───────────────────────────────────────────────────────────────── */
.page-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
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
    margin: 2px 0 0;
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
    transition: all 0.2s;
    font-family: inherit;
    flex-shrink: 0;
}

.btn-refresh:hover:not(:disabled) {
    background: #f9fafb;
    border-color: #d1d5db;
}

.btn-refresh:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Grand total ─────────────────────────────────────────────────────────── */
.grand-total-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 14px;
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
    color: #111827;
}

.gt-label {
    font-size: 11.5px;
    font-weight: 500;
    margin: 6px 0 0;
    color: #6b7280;
}

.gt-card--green {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.gt-card--green .gt-val {
    color: #166534;
}

.gt-card--green .gt-label {
    color: #16a34a;
}

.gt-card--orange {
    background: #fff7ed;
    border-color: #fed7aa;
}

.gt-card--orange .gt-val {
    color: #9a3412;
}

.gt-card--orange .gt-label {
    color: #ea580c;
}

.gt-card--blue {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.gt-card--blue .gt-val {
    color: #1e40af;
}

.gt-card--blue .gt-label {
    color: #2563eb;
}

/* ── Distribusi card ─────────────────────────────────────────────────────── */
.dist-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 18px;
    margin-bottom: 14px;
}

.dist-title {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 12px;
}

.dist-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 10px 24px;
}

.btn-pdf {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.btn-pdf:hover:not(:disabled) {
    background: #15803d;
}

.btn-pdf:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.dist-label-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 4px;
}

.dist-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dist-nama {
    flex: 1;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.dist-count {
    font-size: 11.5px;
    font-weight: 700;
    color: #111827;
}

.dist-bar {
    height: 5px;
    background: #f1f5f9;
    border-radius: 99px;
    overflow: hidden;
}

.dist-bar__fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.5s ease;
}

/* ── Kategori block ──────────────────────────────────────────────────────── */
.kategori-block {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    margin-bottom: 12px;
    overflow: hidden;
    transition: box-shadow 0.18s;
}

.kategori-block:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.kat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    cursor: pointer;
    transition: background 0.12s;
}

.kat-header:hover {
    background: #f9fafb;
}

.kat-header__left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.kat-ikon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
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
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}

.kat-stat {
    font-size: 11.5px;
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

/* Progress mini di header */
.kat-progress-mini {
    width: 60px;
    height: 5px;
    background: #f1f5f9;
    border-radius: 99px;
    overflow: hidden;
    flex-shrink: 0;
}

.kat-progress-mini__fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.4s ease;
}

/* ── Stok table ──────────────────────────────────────────────────────────── */
.kat-table-wrap {
    overflow-x: auto;
    border-top: 1px solid #f5f5f5;
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
    padding: 10px 14px;
    font-size: 10.5px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    padding: 10px 14px;
    vertical-align: middle;
    color: #374151;
}

.td-no {
    color: #9ca3af;
    font-size: 11.5px;
    font-weight: 600;
    text-align: center;
}

.td-name {
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.td-loc {
    font-size: 12px;
    color: #6b7280;
}

.td-center {
    text-align: center;
}

.td-bold {
    font-weight: 700;
    color: #111827;
}

.td-green {
    font-weight: 700;
    color: #16a34a;
}

.td-orange {
    font-weight: 700;
    color: #ea580c;
}

/* Badge kondisi */
.badge-kondisi {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.k--baik {
    background: #dcfce7;
    color: #15803d;
}

.k--ringan {
    background: #fef3c7;
    color: #b45309;
}

.k--berat {
    background: #fee2e2;
    color: #b91c1c;
}

/* Progress bar di tabel */
.progress-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    justify-content: center;
}

.progress-bar {
    flex: 1;
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
    max-width: 70px;
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

.progress-pct {
    font-size: 11px;
    font-weight: 700;
    width: 32px;
    text-align: right;
}

.progress-pct.fill--high {
    color: #16a34a;
}

.progress-pct.fill--mid {
    color: #f59e0b;
}

.progress-pct.fill--low {
    color: #ef4444;
}

/* ── Footer ──────────────────────────────────────────────────────────────── */
.laporan-footer {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 12px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 4px;
}

.footer-left {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
}

.footer-left strong {
    color: #374151;
}

.footer-right {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}

/* ── State center ────────────────────────────────────────────────────────── */
.state-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 60px 24px;
    color: #9ca3af;
    font-size: 14px;
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
}

/* ── Spinner ─────────────────────────────────────────────────────────────── */
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

.rotating {
    animation: spin 0.8s linear infinite;
}

/* ── Accordion ───────────────────────────────────────────────────────────── */
.rotate-180 {
    transform: rotate(180deg);
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .grand-total-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .dist-list {
        grid-template-columns: 1fr;
    }

    .kat-header__stats {
        display: none;
    }
}

@media (max-width: 480px) {
    .grand-total-row {
        grid-template-columns: 1fr 1fr;
    }
}
</style>