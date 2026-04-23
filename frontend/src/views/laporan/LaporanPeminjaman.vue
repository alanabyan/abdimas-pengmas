<template>
    <div class="laporan-page">

        <!-- Header -->
        <div class="page-top">
            <div class="breadcrumb">
                <RouterLink to="/laporan" class="bc-link">Laporan</RouterLink>
                <span class="bc-sep">›</span>
                <span class="bc-current">Laporan Peminjaman</span>
            </div>
            <div class="page-title-row">
                <div>
                    <h1 class="pg-title">Laporan Peminjaman</h1>
                    <p class="pg-sub">Rekap seluruh transaksi peminjaman barang inventaris masjid.</p>
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

        <!-- Filter bar -->
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
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select v-model="filter.status" class="filter-input" @change="fetchData">
                        <option value="">Semua Status</option>
                        <option value="Menunggu">Menunggu</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="Batal">Dibatalkan</option>
                        <option value="Rusak/Hilang">Rusak/Hilang</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="btn-filter" @click="fetchData" :disabled="loading">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        Terapkan
                    </button>
                    <button class="btn-reset" @click="resetFilter">Reset</button>
                </div>
            </div>

            <!-- Summary chips -->
            <div v-if="!loading && data.length" class="summary-chips">
                <div class="chip chip--total">
                    <span class="chip-val">{{ data.length }}</span>
                    <span class="chip-lbl">Total Transaksi</span>
                </div>
                <div class="chip chip--green">
                    <span class="chip-val">{{ countByStatus('Selesai') }}</span>
                    <span class="chip-lbl">Selesai</span>
                </div>
                <div class="chip chip--blue">
                    <span class="chip-val">{{ countByStatus('Aktif') }}</span>
                    <span class="chip-lbl">Aktif</span>
                </div>
                <div class="chip chip--yellow">
                    <span class="chip-val">{{ countByStatus('Menunggu') }}</span>
                    <span class="chip-lbl">Menunggu</span>
                </div>
                <div class="chip chip--orange">
                    <span class="chip-val">{{ countByStatus('Terlambat') }}</span>
                    <span class="chip-lbl">Terlambat</span>
                </div>
                <div class="chip chip--red">
                    <span class="chip-val">{{ countByStatus('Rusak/Hilang') }}</span>
                    <span class="chip-lbl">Rusak/Hilang</span>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-card">
            <!-- Loading -->
            <div v-if="loading" class="state-center">
                <span class="spinner-lg"></span>
                <p>Memuat data laporan...</p>
            </div>

            <!-- Empty -->
            <div v-else-if="!data.length" class="state-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                    style="color:#d1d5db">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <p>Tidak ada data untuk filter yang dipilih.</p>
            </div>

            <!-- Table -->
            <template v-else>
                <div class="table-scroll">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Warga</th>
                                <th>Barang</th>
                                <th>Jml</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Kondisi</th>
                                <th>Status</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in data" :key="row.id">
                                <td class="td-no">{{ i + 1 }}</td>
                                <td>
                                    <p class="td-name">{{ row.warga?.nama_warga || '—' }}</p>
                                    <p class="td-sub">{{ row.warga?.no_hp || '' }}</p>
                                </td>
                                <td>
                                    <p class="td-name">{{ row.barang?.nama_barang || '—' }}</p>
                                    <p class="td-sub">{{ row.keperluan || '' }}</p>
                                </td>
                                <td class="td-center">{{ row.jumlah }}</td>
                                <td class="td-date">{{ fmtDate(row.tgl_pinjam) }}</td>
                                <td class="td-date">
                                    <span v-if="row.tgl_kembali_aktual" class="date-done">{{
                                        fmtDate(row.tgl_kembali_aktual) }}</span>
                                    <span v-else class="date-plan">{{ fmtDate(row.tgl_rencana_kembali) }}</span>
                                </td>
                                <td>
                                    <span v-if="row.kondisi_kembali" :class="kondisiClass(row.kondisi_kembali)"
                                        class="badge-kondisi">
                                        {{ row.kondisi_kembali }}
                                    </span>
                                    <span v-else class="td-sub">{{ row.kondisi_pinjam }}</span>
                                </td>
                                <td><span class="badge-status" :class="statusClass(row.status)">{{ row.status }}</span>
                                </td>
                                <td class="td-sub">{{ row.marbot?.nama_marbot || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span class="footer-info">Menampilkan {{ data.length }} transaksi</span>
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

const filter = reactive({ dari: '', sampai: '', status: '' })

async function fetchData() {
    loading.value = true
    try {
        const params = {}
        if (filter.dari) params.dari = filter.dari
        if (filter.sampai) params.sampai = filter.sampai
        if (filter.status) params.status = filter.status
        const res = await laporanService.getPeminjaman(params)
        data.value = res.data ?? res
    } catch {
        toast.error('Gagal memuat data laporan.')
    } finally {
        loading.value = false
    }
}

function resetFilter() {
    filter.dari = filter.sampai = filter.status = ''
    fetchData()
}

async function downloadPdf() {
    pdfLoading.value = true
    try {
        const params = {}
        if (filter.dari) params.dari = filter.dari
        if (filter.sampai) params.sampai = filter.sampai
        if (filter.status) params.status = filter.status
        const blob = await laporanService.downloadPeminjamanPdf(params)
        const url = URL.createObjectURL(new Blob([blob], { type: 'application/pdf' }))
        const a = document.createElement('a')
        a.href = url
        a.download = `laporan-peminjaman-${new Date().toISOString().slice(0, 10)}.pdf`
        document.body.appendChild(a); a.click()
        document.body.removeChild(a); URL.revokeObjectURL(url)
        toast.success('PDF berhasil diunduh.')
    } catch {
        toast.error('Gagal mengunduh PDF.')
    } finally {
        pdfLoading.value = false
    }
}

function countByStatus(s) { return data.value.filter(d => d.status === s).length }

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function statusClass(s) {
    const map = {
        'Menunggu': 'status--yellow',
        'Aktif': 'status--blue',
        'Selesai': 'status--green',
        'Terlambat': 'status--orange',
        'Batal': 'status--gray',
        'Rusak/Hilang': 'status--red',
    }
    return map[s] || 'status--gray'
}

function kondisiClass(k) {
    if (k === 'Baik') return 'kondisi--baik'
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

/* Filter card */
.filter-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 16px 18px;
    margin-bottom: 16px;
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
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
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

.btn-filter:disabled {
    opacity: 0.6;
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

/* Summary chips */
.summary-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 14px;
    padding-top: 14px;
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

.chip-lbl {
    font-weight: 500;
}

.chip--total {
    background: #f8fafc;
    color: #374151;
}

.chip--green {
    background: #f0fdf4;
    color: #166534;
}

.chip--blue {
    background: #eff6ff;
    color: #1e40af;
}

.chip--yellow {
    background: #fefce8;
    color: #713f12;
}

.chip--orange {
    background: #fff7ed;
    color: #9a3412;
}

.chip--red {
    background: #fef2f2;
    color: #991b1b;
}

/* Table */
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
    min-width: 860px;
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

.td-center {
    text-align: center;
    font-weight: 600;
}

.td-date {
    white-space: nowrap;
    font-size: 12px;
}

.date-done {
    color: #16a34a;
    font-weight: 500;
}

.date-plan {
    color: #6b7280;
}

.badge-status {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.status--green {
    background: #dcfce7;
    color: #166534;
}

.status--blue {
    background: #dbeafe;
    color: #1e40af;
}

.status--yellow {
    background: #fef9c3;
    color: #713f12;
}

.status--orange {
    background: #ffedd5;
    color: #9a3412;
}

.status--red {
    background: #fee2e2;
    color: #991b1b;
}

.status--gray {
    background: #f1f5f9;
    color: #64748b;
}

.badge-kondisi {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.kondisi--baik {
    background: #dcfce7;
    color: #166534;
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