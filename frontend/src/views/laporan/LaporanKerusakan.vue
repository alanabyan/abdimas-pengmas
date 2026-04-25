<template>
    <div class="laporan-page">

        <!-- Header -->
        <div class="page-top">
            <div class="breadcrumb">
                <RouterLink to="/laporan" class="bc-link">Laporan</RouterLink>
                <span class="bc-sep">›</span>
                <span class="bc-current">Laporan Kerusakan & Kehilangan</span>
            </div>
            <div class="page-title-row">
                <div>
                    <h1 class="pg-title">Laporan Kerusakan & Kehilangan</h1>
                    <p class="pg-sub">Daftar barang yang dikembalikan rusak atau dilaporkan hilang.</p>
                </div>
                <button class="btn-pdf" :disabled="pdfLoading || !data.length" @click="downloadPdf">
                    <span v-if="pdfLoading" class="spinner"></span>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" width="15" height="15">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    {{ pdfLoading ? 'Mengunduh...' : 'Unduh PDF' }}
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-card">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Dari Tanggal</label>
                    <input v-model="filter.dari" type="date" class="filter-input" @change="fetchData" />
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input v-model="filter.sampai" type="date" class="filter-input" @change="fetchData" />
                </div>
                <div class="filter-actions">
                    <button class="btn-filter" @click="fetchData" :disabled="loading">Terapkan</button>
                    <button class="btn-reset" @click="resetFilter">Reset</button>
                </div>
            </div>

            <!-- Summary -->
            <div v-if="!loading && data.length" class="summary-chips">
                <div class="chip chip--total">
                    <span class="chip-val">{{ data.length }}</span>
                    <span class="chip-lbl">Total Kasus</span>
                </div>
                <div class="chip chip--orange">
                    <span class="chip-val">{{ countByKondisi('Rusak') }}</span>
                    <span class="chip-lbl">Rusak</span>
                </div>
                <div class="chip chip--red">
                    <span class="chip-val">{{ countByKondisi('Hilang') }}</span>
                    <span class="chip-lbl">Hilang</span>
                </div>
            </div>
        </div>

        <!-- Alert info -->
        <div class="alert-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                width="15" height="15">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            Laporan ini hanya menampilkan barang dengan status akhir <strong>Rusak</strong> atau <strong>Hilang</strong>
            saat pengembalian.
        </div>

        <!-- Table -->
        <div class="table-card">
            <div v-if="loading" class="state-center">
                <span class="spinner-lg"></span>
                <p>Memuat data laporan...</p>
            </div>

            <div v-else-if="!data.length" class="state-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                    style="color:#d1d5db">
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                </svg>
                <p>Tidak ada data kerusakan atau kehilangan.</p>
            </div>

            <template v-else>
                <div class="table-scroll">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in data" :key="row.id">
                                <td class="td-no">{{ i + 1 }}</td>
                                <td>
                                    <p class="td-name">{{ row.barang?.nama_barang || '—' }}</p>
                                    <p class="td-sub">Jumlah: {{ row.jumlah }} unit</p>
                                </td>
                                <td>
                                    <p class="td-name">{{ row.warga?.nama_warga || '—' }}</p>
                                    <p class="td-sub">{{ row.warga?.no_hp || '' }}</p>
                                </td>
                                <td class="td-date">{{ fmtDate(row.tgl_pinjam) }}</td>
                                <td class="td-date">{{ fmtDate(row.tgl_kembali_aktual) }}</td>
                                <td>
                                    <span class="badge-kondisi" :class="kondisiClass(row.kondisi_kembali)">
                                        {{ row.kondisi_kembali }}
                                    </span>
                                </td>
                                <td class="td-catatan">{{ row.catatan || '—' }}</td>
                                <td class="td-sub">{{ row.marbot?.nama_marbot || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span class="footer-info">Menampilkan {{ data.length }} kasus</span>
                    <span class="footer-info">Dicetak: {{ now }}</span>
                </div>
            </template>
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
const pdfLoading = ref(false)
const data = ref([])
const now = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
const filter = reactive({ dari: '', sampai: '' })

async function fetchData() {
    loading.value = true
    try {
        const params = {}
        if (filter.dari) params.dari = filter.dari
        if (filter.sampai) params.sampai = filter.sampai
        const res = await laporanService.getKerusakan(params)
        data.value = res.data ?? res
    } catch { useToast().error('Gagal memuat data.') }
    finally { loading.value = false }
}

function resetFilter() { filter.dari = filter.sampai = ''; fetchData() }

async function downloadPdf() {
    pdfLoading.value = true
    try {
        const params = {}
        if (filter.dari) params.dari = filter.dari
        if (filter.sampai) params.sampai = filter.sampai
        const blob = await laporanService.downloadKerusakanPdf(params)
        const url = `http://localhost:8000/api/v1/laporan/kerusakan/pdf?dari=${filter.dari}&sampai=${filter.sampai}`;
        window.open(url, '_blank');
        const a = document.createElement('a')
        a.href = url; a.download = `laporan-kerusakan-${new Date().toISOString().slice(0, 10)}.pdf`
        document.body.appendChild(a); a.click()
        document.body.removeChild(a); URL.revokeObjectURL(url)
        toast.success('PDF berhasil diunduh.')
    } catch { toast.error('Gagal mengunduh PDF.') }
    finally { pdfLoading.value = false }
}

function countByKondisi(k) { return data.value.filter(d => d.kondisi_kembali === k).length }
function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
function kondisiClass(k) {
    if (k === 'Rusak') return 'kondisi--rusak'
    if (k === 'Hilang') return 'kondisi--hilang'
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

.btn-pdf {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #dc2626;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 18px;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-pdf:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-pdf:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.filter-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 16px 18px;
    margin-bottom: 14px;
}

.filter-row {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 140px;
}

.filter-label {
    font-size: 11.5px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-input {
    padding: 8px 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
    color: #111827;
    outline: none;
    font-family: inherit;
    background: white;
    transition: border-color 0.2s;
}

.filter-input:focus {
    border-color: #16a34a;
}

.filter-actions {
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.btn-filter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}

.btn-filter:hover:not(:disabled) {
    background: #15803d;
}

.btn-reset {
    padding: 9px 14px;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-reset:hover {
    background: #f9fafb;
}

.summary-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f5f5f5;
}

.chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.chip-val {
    font-weight: 800;
    font-size: 14px;
}

.chip--total {
    background: #f8fafc;
    color: #374151;
}

.chip--orange {
    background: #fff7ed;
    color: #9a3412;
}

.chip--red {
    background: #fef2f2;
    color: #991b1b;
}

.alert-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 9px;
    padding: 10px 14px;
    font-size: 12.5px;
    color: #1e40af;
    margin-bottom: 14px;
}

.table-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
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

.table-scroll {
    overflow-x: auto;
}

.rep-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
    min-width: 800px;
}

.rep-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #f0f0f0;
}

.rep-table th {
    text-align: left;
    padding: 10px 14px;
    font-size: 10.5px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.rep-table tbody tr {
    border-bottom: 1px solid #f9fafb;
    transition: background 0.1s;
}

.rep-table tbody tr:last-child {
    border-bottom: none;
}

.rep-table tbody tr:hover {
    background: #fafafa;
}

.rep-table td {
    padding: 10px 14px;
    vertical-align: middle;
    color: #374151;
}

.td-no {
    color: #9ca3af;
    font-size: 12px;
    text-align: center;
    font-weight: 500;
}

.td-name {
    font-weight: 600;
    color: #111827;
    margin: 0;
    font-size: 12.5px;
}

.td-sub {
    font-size: 11px;
    color: #9ca3af;
    margin: 2px 0 0;
}

.td-date {
    white-space: nowrap;
    font-size: 12px;
}

.td-catatan {
    max-width: 160px;
    font-size: 12px;
    color: #6b7280;
}

.badge-kondisi {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.kondisi--rusak {
    background: #ffedd5;
    color: #9a3412;
}

.kondisi--hilang {
    background: #fee2e2;
    color: #991b1b;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid #f5f5f5;
    font-size: 11.5px;
    color: #9ca3af;
    flex-wrap: wrap;
    gap: 6px;
}

.spinner {
    width: 13px;
    height: 13px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
</style>