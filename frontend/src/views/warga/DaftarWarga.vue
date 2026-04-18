<template>
    <div class="warga-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div>
                <h1 class="pg-title">Daftar Warga</h1>
                <p class="pg-sub">Kelola data penduduk yang berhak mengakses inventaris masjid.</p>
            </div>
            <RouterLink to="/warga/tambah" class="btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Warga
            </RouterLink>
        </div>

        <!-- ── Main grid ───────────────────────────────────────────────── -->
        <div class="main-grid">

            <!-- LEFT: Tabel ──────────────────────────────────────────────── -->
            <div class="left-col">

                <!-- Search + Filter bar -->
                <div class="toolbar">
                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="16" height="16">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Cari berdasarkan nama, alamat..."
                            class="search-input" @input="onSearch" />
                        <button v-if="search" class="search-clear" @click="search = ''; onSearch()">✕</button>
                    </div>
                    <div class="filter-group">
                        <select v-model="filterRtRw" class="filter-select" @change="fetchData(1)">
                            <option value="">Semua RT/RW</option>
                            <option v-for="rt in rtRwList" :key="rt" :value="rt">{{ rt }}</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-card">
                    <!-- Loading skeleton -->
                    <div v-if="loading" class="skeleton-wrap">
                        <div v-for="i in 5" :key="i" class="skeleton-row">
                            <div class="skeleton-avatar"></div>
                            <div class="skeleton-lines">
                                <div class="skeleton-line" style="width:60%"></div>
                                <div class="skeleton-line" style="width:40%"></div>
                            </div>
                            <div class="skeleton-line" style="width:80px"></div>
                            <div class="skeleton-line" style="width:100px"></div>
                            <div class="skeleton-line" style="width:60px"></div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="!wargas.length" class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40"
                            height="40" style="color:#cbd5e1">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <p>Tidak ada data warga</p>
                        <RouterLink to="/warga/tambah" class="btn-add" style="margin-top:8px">Tambah Warga Pertama
                        </RouterLink>
                    </div>

                    <!-- Table content -->
                    <table v-else class="warga-table">
                        <thead>
                            <tr>
                                <th>Nama Warga</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>RT/RW</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="w in wargas" :key="w.id" class="warga-row"
                                :class="{ 'warga-row--active': selectedWarga?.id === w.id }" @click="selectWarga(w)">
                                <td>
                                    <div class="warga-cell">
                                        <div class="warga-avatar" :style="{ background: avatarColor(w.nama_warga) }">
                                            {{ initials(w.nama_warga) }}
                                        </div>
                                        <div>
                                            <p class="warga-name">{{ w.nama_warga }}</p>
                                            <p class="warga-status" :class="statusClass(w)">{{ statusLabel(w) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-hp">{{ w.no_hp }}</td>
                                <td class="td-alamat">{{ w.alamat }}</td>
                                <td>
                                    <span class="badge-rtrw" :class="{ 'badge-rtrw--warn': hasAktif(w) }">
                                        {{ w.rt_rw || '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="act-btn act-btn--view" title="Lihat detail"
                                            @click.stop="selectWarga(w)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" width="14" height="14">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                        <RouterLink :to="`/warga/${w.id}/edit`" class="act-btn act-btn--edit"
                                            title="Edit" @click.stop>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" width="14" height="14">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 1 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </RouterLink>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="meta.last_page > 1" class="pagination">
                        <span class="pag-info">Menampilkan {{ paginationInfo }}</span>
                        <div class="pag-btns">
                            <button class="pag-btn" :disabled="meta.current_page === 1"
                                @click="fetchData(meta.current_page - 1)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="14" height="14">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                            </button>
                            <button v-for="p in pageNumbers" :key="p" class="pag-btn"
                                :class="{ 'pag-btn--active': p === meta.current_page }" @click="fetchData(p)">{{ p
                                }}</button>
                            <button class="pag-btn" :disabled="meta.current_page === meta.last_page"
                                @click="fetchData(meta.current_page + 1)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="14" height="14">
                                    <polyline points="9 18 15 12 9 6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div v-else-if="wargas.length" class="pagination">
                        <span class="pag-info">Menampilkan {{ wargas.length }} dari {{ meta.total }} warga</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Detail panel ───────────────────────────────────────── -->
            <div class="right-col">

                <!-- Stat cards -->
                <div class="stat-grid">
                    <div class="stat-card stat-card--green">
                        <p class="stat-val">{{ meta.total }}</p>
                        <p class="stat-label">Total Warga Terdaftar</p>
                    </div>
                    <div class="stat-row">
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ statsAktif }}</p>
                            <p class="stat-label">Aktif Pinjam</p>
                        </div>
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ statsTerlambat }}</p>
                            <p class="stat-label">Terlambat</p>
                        </div>
                    </div>
                </div>

                <!-- Detail warga -->
                <div class="detail-card" v-if="selectedWarga">
                    <!-- Header -->
                    <div class="detail-header">
                        <div class="detail-avatar" :style="{ background: avatarColor(selectedWarga.nama_warga) }">
                            {{ initials(selectedWarga.nama_warga) }}
                        </div>
                        <div class="detail-meta">
                            <p class="detail-name">{{ selectedWarga.nama_warga }}</p>
                            <span class="detail-badge " :class="statusClass(selectedWarga)">
                                {{ statusLabel(selectedWarga) }}
                            </span>
                        </div>
                        <RouterLink :to="`/warga/${selectedWarga.id}/edit`" class="btn-edit-sm">Edit Profil</RouterLink>
                    </div>

                    <!-- Info fields -->
                    <div class="detail-fields">
                        <div class="detail-field">
                            <span class="field-label">Nomor Telepon</span>
                            <span class="field-val">{{ selectedWarga.no_hp }}</span>
                        </div>
                        <div class="detail-field">
                            <span class="field-label">RT / RW</span>
                            <span class="field-val">{{ selectedWarga.rt_rw || '—' }}</span>
                        </div>
                        <div class="detail-field">
                            <span class="field-label">Alamat Lengkap</span>
                            <span class="field-val">{{ selectedWarga.alamat }}</span>
                        </div>
                    </div>

                    <!-- Peminjaman aktif info -->
                    <div v-if="peminjamanAktif" class="pinjam-box">
                        <div class="pinjam-box__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <div>
                            <p class="pinjam-box__text">Status Peminjaman Saat Ini</p>
                            <p class="pinjam-box__sub">{{ peminjamanAktif }}</p>
                        </div>
                        <RouterLink :to="`/warga/${selectedWarga.id}`" class="pinjam-link">
                            Lihat Inventaris →
                        </RouterLink>
                    </div>

                    <div v-if="(selectedWarga?.peminjaman_aktif_count || 0) === 0">
                        <RouterLink :to="`/warga/${selectedWarga.id}`" class="btn-edit-sm">
                            Lihat Inventaris
                        </RouterLink>
                    </div>

                    <!-- Hapus -->
                    <button class="btn-delete" @click="confirmDelete(selectedWarga)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14H6L5 6" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                            <path d="M9 6V4h6v2" />
                        </svg>
                        Hapus Warga
                    </button>
                </div>

                <!-- Placeholder saat belum pilih -->
                <div v-else class="detail-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"
                        style="color:#cbd5e1">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                    <p>Pilih warga untuk melihat detail</p>
                </div>

                <!-- Aktivitas terbaru -->
                <div class="activity-card" v-if="recentActivity.length">
                    <p class="activity-title">Aktivitas Terbaru</p>
                    <div v-for="act in recentActivity" :key="act.id" class="activity-item">
                        <div class="act-avatar" :style="{ background: avatarColor(act.nama) }">{{ initials(act.nama) }}
                        </div>
                        <div class="act-info">
                            <p class="act-name">{{ act.nama }}</p>
                            <p class="act-desc">{{ act.keterangan }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Modal Konfirmasi Hapus ───────────────────────────────────── -->
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
            <div class="modal">
                <div class="modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="24" height="24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <h3 class="modal-title">Hapus Data Warga?</h3>
                <p class="modal-body">
                    Data <strong>{{ deleteTarget?.nama_warga }}</strong> akan dihapus permanen.
                    Warga dengan peminjaman aktif tidak dapat dihapus.
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" @click="showDeleteModal = false">Batal</button>
                    <button class="btn-confirm-delete" :disabled="deleting" @click="doDelete">
                        {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useWargaStore } from '@/stores/warga'
import { useToast } from 'vue-toastification'

const store = useWargaStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const search = ref('')
const filterRtRw = ref('')
const selectedWarga = ref(null)
const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)
let searchTimer = null

// ── Computed dari store ─────────────────────────────────────────────
const wargas = computed(() => store.wargas)
const meta = computed(() => store.meta)
const loading = computed(() => store.loading)

// Daftar RT/RW unik untuk filter dropdown
const rtRwList = computed(() => {
    const set = new Set(wargas.value.map(w => w.rt_rw).filter(Boolean))
    return [...set].sort()
})

// Pagination page numbers (max 5 tampil)
const pageNumbers = computed(() => {
    const total = meta.value.last_page
    const current = meta.value.current_page
    const pages = []
    const start = Math.max(1, current - 2)
    const end = Math.min(total, start + 4)
    for (let i = start; i <= end; i++) pages.push(i)
    return pages
})

const paginationInfo = computed(() => {
    const { current_page, per_page, total } = meta.value
    const from = (current_page - 1) * (per_page || 10) + 1
    const to = Math.min(current_page * (per_page || 10), total)
    return `${from}–${to} dari ${total} warga`
})

// Stats (dummy — idealnya dari API dashboard)
const statsAktif = computed(() => wargas.value.filter(w => w.peminjaman_aktif_count > 0).length || 0)
const statsTerlambat = ref(0)

// Peminjaman aktif warga terpilih
const peminjamanAktif = computed(() => {
    if (!selectedWarga.value?.peminjamans?.length) return null
    const aktif = selectedWarga.value.peminjamans.find(p =>
        ['Aktif', 'Menunggu', 'Terlambat'].includes(p.status)
    )
    if (!aktif) return null
    return `Meminjam ${aktif.jumlah ?? 1} item · Kembali ${aktif.tgl_rencana_kembali}`
})

// Aktivitas terbaru (dummy dari data yang ada)
const recentActivity = computed(() => {
    return wargas.value.slice(0, 4).map(w => ({
        id: w.id,
        nama: w.nama_warga,
        keterangan: 'Data warga terdaftar',
    }))
})

// ── Methods ─────────────────────────────────────────────────────────
async function fetchData(page = 1) {
    await store.fetchWargas({
        page,
        search: search.value || undefined,
        rt_rw: filterRtRw.value || undefined,
        per_page: 10,
    })
}

function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => fetchData(1), 400)
}

async function selectWarga(w) {
    selectedWarga.value = w
    // Load detail lengkap jika belum ada peminjamans
    if (!w.peminjamans) {
        try {
            const detail = await import('@/services/wargaService').then(m => m.default.getOne(w.id))
            selectedWarga.value = detail
        } catch(err) {
            console.error(err)
        }
    }
}

function confirmDelete(w) {
    deleteTarget.value = w
    showDeleteModal.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await store.deleteWarga(deleteTarget.value.id)
        toast.success(`Data ${deleteTarget.value.nama_warga} berhasil dihapus.`)
        showDeleteModal.value = false
        if (selectedWarga.value?.id === deleteTarget.value.id) {
            selectedWarga.value = null
        }
    } catch (err) {
        toast.error(err?.response?.data?.message || 'Gagal menghapus data warga.')
    } finally {
        deleting.value = false
    }
}

// ── Helpers visual ──────────────────────────────────────────────────
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']

function avatarColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

function initials(nama = '') {
    return nama.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function hasAktif(w) {
    return (w.peminjaman_aktif_count || 0) > 0
}

function statusLabel(w) {
    if ((w.peminjaman_aktif_count || 0) > 0) return 'Ada Peminjaman'
    return 'Tidak Ada Peminjaman'
}

function statusClass(w) {
    return (w.peminjaman_aktif_count || 0) > 0 ? 'status--aktif' : 'status--none'
}

// ── Init ────────────────────────────────────────────────────────────
onMounted(() => fetchData())
</script>

<style scoped>
.warga-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
}

/* ── Header ──────────────────────────────────────────────────────── */
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

.btn-add {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 16px;
    border-radius: 9px;
    text-decoration: none;
    transition: background 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-add:hover {
    background: #15803d;
}

/* ── Grid ────────────────────────────────────────────────────────── */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 16px;
    align-items: start;
}

/* ── Toolbar ─────────────────────────────────────────────────────── */
.toolbar {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.search-wrap {
    flex: 1;
    min-width: 200px;
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 11px;
    color: #9ca3af;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 9px 32px 9px 36px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #111827;
    background: white;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}

.search-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.search-input::placeholder {
    color: #c4c9d1;
}

.search-clear {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 12px;
    padding: 2px 4px;
}

.filter-select {
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #374151;
    background: white;
    outline: none;
    cursor: pointer;
    font-family: inherit;
}

.filter-select:focus {
    border-color: #16a34a;
}

/* ── Table card ──────────────────────────────────────────────────── */
.table-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.warga-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.warga-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #f0f0f0;
}

.warga-table th {
    text-align: left;
    padding: 11px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.warga-row {
    cursor: pointer;
    transition: background 0.12s;
    border-bottom: 1px solid #f9fafb;
}

.warga-row:last-child {
    border-bottom: none;
}

.warga-row:hover {
    background: #f9fdf9;
}

.warga-row--active {
    background: #f0fdf4 !important;
}

.warga-row td {
    padding: 11px 14px;
    color: #374151;
    vertical-align: middle;
}

.warga-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.warga-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    color: white;
    font-size: 11.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.warga-name {
    font-weight: 600;
    color: #111827;
    font-size: 13px;
    margin: 0;
}

.status--aktif {
    font-size: 11px;
    color: #16a34a;
    font-weight: 500;
    margin: 1px 0 0;
}

.status--none {
    font-size: 11px;
    color: #9ca3af;
    margin: 1px 0 0;
}

.td-hp {
    white-space: nowrap;
    color: #6b7280;
    font-size: 12.5px;
}

.td-alamat {
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #6b7280;
    font-size: 12.5px;
}

.badge-rtrw {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    background: #e8f5e9;
    color: #166534;
    white-space: nowrap;
}

.badge-rtrw--warn {
    background: #fef3c7;
    color: #92400e;
}

.action-btns {
    display: flex;
    gap: 4px;
}

.act-btn {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    background: white;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    color: #6b7280;
}

.act-btn--view:hover {
    background: #f0fdf4;
    border-color: #86efac;
    color: #16a34a;
}

.act-btn--edit:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #2563eb;
}

/* Skeleton */
.skeleton-wrap {
    padding: 8px 0;
}

.skeleton-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-bottom: 1px solid #f9fafb;
}

.skeleton-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f1f5f9;
    flex-shrink: 0;
    animation: shimmer 1.5s infinite;
}

.skeleton-lines {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.skeleton-line {
    height: 12px;
    border-radius: 6px;
    background: #f1f5f9;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

/* Empty */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
    gap: 10px;
    color: #9ca3af;
    font-size: 14px;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-top: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 8px;
}

.pag-info {
    font-size: 12px;
    color: #9ca3af;
}

.pag-btns {
    display: flex;
    gap: 4px;
}

.pag-btn {
    min-width: 30px;
    height: 30px;
    padding: 0 8px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pag-btn:hover:not(:disabled) {
    border-color: #16a34a;
    color: #16a34a;
}

.pag-btn--active {
    background: #16a34a;
    border-color: #16a34a;
    color: white;
}

.pag-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Right col ───────────────────────────────────────────────────── */
.right-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Stats */
.stat-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-card {
    background: white;
    border-radius: 11px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.stat-card--green {
    background: #16a34a;
    border-color: #16a34a;
}

.stat-val {
    font-size: 28px;
    font-weight: 800;
    color: white;
    margin: 0;
    line-height: 1;
}

.stat-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    margin: 4px 0 0;
    font-weight: 500;
}

.stat-row {
    display: flex;
    gap: 8px;
}

.stat-card--sm {
    flex: 1;
}

.stat-val--sm {
    font-size: 22px;
    color: #111827;
}

.stat-card--sm .stat-label {
    color: #9ca3af;
}

/* Detail card */
.detail-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 16px;
    overflow: hidden;
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.detail-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    color: white;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.detail-meta {
    flex: 1;
    min-width: 0;
}

.detail-name {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.detail-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    margin-top: 3px;
}

.status--aktif {
    background: #dcfce7;
    color: #166534;
}

.status--none {
    background: #f1f5f9;
    color: #64748b;
}

.btn-edit-sm {
    font-size: 11.5px;
    font-weight: 600;
    color: #16a34a;
    border: 1px solid #86efac;
    padding: 5px 10px;
    border-radius: 7px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s;
    flex-shrink: 0;
}

.btn-edit-sm:hover {
    background: #f0fdf4;
}

.detail-fields {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.detail-field {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
}

.detail-field:last-child {
    border-bottom: none;
}

.field-label {
    color: #9ca3af;
    font-weight: 500;
    flex-shrink: 0;
}

.field-val {
    color: #111827;
    font-weight: 500;
    text-align: right;
}

.pinjam-box {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    background: #fefce8;
    border: 1px solid #fef08a;
    border-radius: 9px;
    padding: 10px 12px;
    margin-top: 12px;
}

.pinjam-box__icon {
    color: #ca8a04;
    margin-top: 1px;
    flex-shrink: 0;
}

.pinjam-box__text {
    font-size: 11.5px;
    font-weight: 600;
    color: #713f12;
    margin: 0;
}

.pinjam-box__sub {
    font-size: 11px;
    color: #92400e;
    margin: 2px 0 0;
}

.pinjam-link {
    font-size: 11px;
    font-weight: 600;
    color: #16a34a;
    text-decoration: none;
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
    padding-top: 1px;
}

.btn-delete {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    background: none;
    border: 1px solid #fecaca;
    color: #dc2626;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
    width: 100%;
    justify-content: center;
    font-family: inherit;
}

.btn-delete:hover {
    background: #fef2f2;
}

/* Placeholder */
.detail-placeholder {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 32px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: #9ca3af;
    font-size: 13px;
    text-align: center;
}

/* Activity */
.activity-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.activity-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin: 0 0 10px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f9fafb;
}

.activity-item:last-child {
    border-bottom: none;
}

.act-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    color: white;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.act-name {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.act-desc {
    font-size: 11px;
    color: #9ca3af;
    margin: 1px 0 0;
}

/* ── Modal ───────────────────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 380px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    color: #dc2626;
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px;
}

.modal-body {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0 0 20px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 9px 18px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    font-family: inherit;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-confirm-delete {
    padding: 9px 18px;
    border: none;
    border-radius: 9px;
    background: #dc2626;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-confirm-delete:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-confirm-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .main-grid {
        grid-template-columns: 1fr;
    }

    .right-col {
        order: -1;
    }

    .stat-grid {
        flex-direction: row;
    }

    .stat-card--green {
        flex: 1;
    }

    .stat-row {
        flex: 1;
    }

    .td-alamat {
        display: none;
    }
}

@media (max-width: 600px) {
    .stat-grid {
        flex-direction: column;
    }

    .td-hp {
        display: none;
    }
}
</style>