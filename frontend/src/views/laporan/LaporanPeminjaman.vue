<template>
    <div class="laporan-page">

        <div class="page-top">
            <div class="breadcrumb">
                <RouterLink to="/laporan" class="bc-link">Laporan</RouterLink>
                <span class="bc-sep">›</span>
                <span class="bc-current">Laporan Peminjaman</span>
            </div>
            <div class="page-title-row">
                <div>
                    <h1 class="pg-title">Laporan Peminjaman</h1>
                    <p class="pg-sub">Riwayat peminjaman barang inventaris masjid.</p>
                </div>
                <button class="btn-pdf" @click="cetakPdf" :disabled="!filteredData.length || loadingPdf">
                    <span v-if="loadingPdf" class="spinner-sm"></span>
                    <template v-else>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            width="15" height="15">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </template>
                    {{ loadingPdf ? 'Mengunduh...' : 'Cetak PDF' }}
                </button>
            </div>
        </div>

        <div class="filter-card">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Kategori</label>
                    <select v-model="filter.kategori_id" class="filter-input" @change="applyFilter">
                        <option value="">Semua Kategori</option>
                        <option v-for="k in daftarKategori" :key="k.id" :value="k.id">
                            {{ k.nama_kategori ?? k.nama }}
                        </option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select v-model="filter.status" class="filter-input" @change="applyFilter">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Terlambat">Terlambat</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Dari Tanggal</label>
                    <input v-model="filter.tgl_mulai" type="date" class="filter-input" @change="applyFilter" />
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input v-model="filter.tgl_selesai" type="date" class="filter-input" @change="applyFilter" />
                </div>
                <div class="filter-actions">
                    <button class="btn-reset" @click="resetFilter">Reset</button>
                </div>
            </div>

            <div v-if="!loading && listData.length" class="summary-chips">
                <div class="chip chip--total">
                    <span class="chip-val">{{ filteredData.length }}</span>
                    <span class="chip-lbl">Total</span>
                </div>
                <div class="chip chip--blue">
                    <span class="chip-val">{{ countByStatus('Aktif') }}</span>
                    <span class="chip-lbl">Aktif</span>
                </div>
                <div class="chip chip--green">
                    <span class="chip-val">{{ countByStatus('Selesai') }}</span>
                    <span class="chip-lbl">Selesai</span>
                </div>
                <div class="chip chip--red">
                    <span class="chip-val">{{ countByStatus('Terlambat') }}</span>
                    <span class="chip-lbl">Terlambat</span>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div v-if="loading" class="state-center">
                <span class="spinner-lg"></span>
                <p>Memuat data laporan...</p>
            </div>

            <div v-else-if="!filteredData.length" class="state-center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                    style="color:#d1d5db">
                    <rect x="2" y="7" width="20" height="14" rx="2" />
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                </svg>
                <p>Tidak ada data peminjaman.</p>
            </div>

            <template v-else>
                <div class="table-scroll">
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Peminjam</th>
                                <th>Barang & Kategori</th>
                                <th class="th-center">Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in filteredData" :key="row.id">
                                <td class="td-no">{{ i + 1 }}</td>
                                <td>
                                    <p class="td-name">{{ row.warga?.nama_warga || '—' }}</p>
                                    <p class="td-sub">{{ row.warga?.no_hp || '' }}</p>
                                </td>
                                <td>
                                    <p class="td-name">{{ row.barang?.nama_barang || '—' }}</p>
                                    <span class="badge-kat">
                                        {{ row.barang?.kategori?.nama_kategori ?? row.barang?.kategori?.nama ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="td-center td-name">{{ row.jumlah }}</td>
                                <td class="td-date">{{ fmtDate(row.tgl_pinjam) }}</td>
                                <td class="td-date">{{ fmtDate(row.tgl_kembali_aktual) || '—' }}</td>
                                <td>
                                    <span class="badge-status" :class="statusClass(row.status)">
                                        {{ row.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span class="footer-info">Menampilkan {{ filteredData.length }} data</span>
                    <span class="footer-info">{{ now }}</span>
                </div>
            </template>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import laporanService from '@/services/laporanService' // ← import service
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const loadingPdf = ref(false)          // ← state loading PDF
const listData = ref([])
const daftarKategori = ref([])
const now = new Date().toLocaleDateString('id-ID', {
    day: '2-digit', month: 'long', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
})

const filter = reactive({ kategori_id: '', status: '', tgl_mulai: '', tgl_selesai: '' })

const filteredData = computed(() => {
    return listData.value.filter(item => {
        const matchKat = !filter.kategori_id || item.barang?.kategori_id == filter.kategori_id
        const matchStatus = !filter.status || item.status === filter.status
        const matchMulai = !filter.tgl_mulai || item.tgl_pinjam >= filter.tgl_mulai
        const matchSelesai = !filter.tgl_selesai || item.tgl_pinjam <= filter.tgl_selesai
        return matchKat && matchStatus && matchMulai && matchSelesai
    })
})

function countByStatus(s) { return filteredData.value.filter(d => d.status === s).length }

async function fetchData() {
    loading.value = true
    try {
        const [resP, resK] = await Promise.all([
            api.get('/peminjaman'),
            api.get('/kategori')
        ])
        listData.value = resP.data?.data ?? resP.data ?? []
        daftarKategori.value = resK.data?.data ?? resK.data ?? []
    } catch {
        toast.error('Gagal memuat data.')
    } finally {
        loading.value = false
    }
}

function applyFilter() { /* reaktif via computed */ }
function resetFilter() {
    filter.kategori_id = ''
    filter.status = ''
    filter.tgl_mulai = ''
    filter.tgl_selesai = ''
}

function fmtDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function statusClass(s) {
    if (s === 'Aktif' || s === 'Pinjam') return 'status--aktif'
    if (s === 'Selesai' || s === 'Kembali') return 'status--selesai'
    return 'status--terlambat'
}

// ── Download PDF ─────────────────────────────────────────────────────────
async function cetakPdf() {
    if (!filteredData.value.length) return

    loadingPdf.value = true
    try {
        // Kirim filter aktif ke backend (sesuai param LaporanController)
        const params = {}
        if (filter.tgl_mulai) params.dari = filter.tgl_mulai
        if (filter.tgl_selesai) params.sampai = filter.tgl_selesai
        if (filter.status) params.status = filter.status
        // kategori_id tidak ada di backend filter, tapi bisa ditambah jika perlu

        const blob = await laporanService.downloadPeminjamanPdf(params)

        // Trigger download di browser
        const url = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `laporan-peminjaman-${new Date().toISOString().slice(0, 10)}.pdf`
        document.body.appendChild(link)
        link.click()
        link.remove()
        URL.revokeObjectURL(url)

        toast.success('PDF berhasil diunduh.')
    } catch {
        toast.error('Gagal mengunduh PDF.')
    } finally {
        loadingPdf.value = false
    }
}

onMounted(fetchData)
</script>

<style scoped>
.laporan-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    /* Tambahan padding biar rapi di ujung layar */
    padding: 24px;
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

.page-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-pdf {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #16a34a;
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
    background: #15803d;
}

.btn-pdf:disabled {
    opacity: 0.5;
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

.chip--blue {
    background: #eff6ff;
    color: #1e40af;
}

.chip--green {
    background: #f0fdf4;
    color: #166534;
}

.chip--red {
    background: #fef2f2;
    color: #991b1b;
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
    min-width: 750px;
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

.th-center {
    text-align: center;
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
}

.td-date {
    white-space: nowrap;
    font-size: 12px;
}

.badge-kat {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    background: #f0fdf4;
    color: #166534;
    margin-top: 3px;
}

.badge-status {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status--aktif {
    background: #eff6ff;
    color: #1d4ed8;
}

.status--selesai {
    background: #f0fdf4;
    color: #15803d;
}

.status--terlambat {
    background: #fef2f2;
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

/* ── RESPONSIVE MODE (PAKAI PSEUDO CLASS AGAR TIDAK GANTI TEMPLATE) ──────── */

@media (max-width: 768px) {
    .laporan-page { padding: 16px; }

    .page-title-row {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .btn-pdf {
        width: 100%;
        justify-content: center;
    }

    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        width: 100%;
    }

    .filter-actions {
        width: 100%;
    }

    .btn-reset {
        width: 100%;
        justify-content: center;
    }

    .summary-chips {
        justify-content: space-between;
    }
    
    .chip {
        flex: 1;
        justify-content: center;
    }

    /* Sembunyikan kolom # (ke-1) dan Tgl Kembali (ke-6) di HP biar ringkas */
    .rep-table th:nth-child(1),
    .rep-table td:nth-child(1),
    .rep-table th:nth-child(6),
    .rep-table td:nth-child(6) {
        display: none;
    }
    
    .rep-table {
        min-width: 100%; /* Biar gak maksain scroll kalau kolom udah dikurangin */
    }
}

@media (max-width: 480px) {
    /* Sembunyikan Jumlah (ke-4) di layar sangat kecil */
    .rep-table th:nth-child(4),
    .rep-table td:nth-child(4) {
        display: none;
    }
    
    .table-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>