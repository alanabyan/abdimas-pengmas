<template>
    <div class="barang-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div>
                <h1 class="pg-title">Daftar Barang</h1>
                <p class="pg-sub">Kelola inventaris barang yang dapat dipinjam oleh warga masjid.</p>
            </div>
            <RouterLink to="/barang/tambah" class="btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Barang
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
                        <input v-model="search" type="text" placeholder="Cari nama barang..." class="search-input"
                            @input="onSearch" />
                        <button v-if="search" class="search-clear" @click="search = ''; onSearch()">✕</button>
                    </div>
                    <div class="filter-group">
                        <select v-model="filterKondisi" class="filter-select" @change="fetchData(1)">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                        <button class="filter-toggle" :class="{ 'filter-toggle--active': filterTersedia }"
                            @click="filterTersedia = !filterTersedia; fetchData(1)"
                            title="Hanya tampilkan barang tersedia">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="13" height="13">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Tersedia
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-card">
                    <!-- Loading skeleton -->
                    <div v-if="loading" class="skeleton-wrap">
                        <div v-for="i in 5" :key="i" class="skeleton-row">
                            <div class="skeleton-img"></div>
                            <div class="skeleton-lines">
                                <div class="skeleton-line" style="width:55%"></div>
                                <div class="skeleton-line" style="width:35%"></div>
                            </div>
                            <div class="skeleton-line" style="width:70px"></div>
                            <div class="skeleton-line" style="width:90px"></div>
                            <div class="skeleton-line" style="width:60px"></div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="!barangs.length" class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40"
                            height="40" style="color:#cbd5e1">
                            <rect x="2" y="7" width="20" height="14" rx="2" />
                            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                            <line x1="12" y1="12" x2="12" y2="16" />
                            <line x1="10" y1="14" x2="14" y2="14" />
                        </svg>
                        <p>Tidak ada data barang</p>
                        <RouterLink to="/barang/tambah" class="btn-add" style="margin-top:8px">Tambah Barang Pertama
                        </RouterLink>
                    </div>

                    <!-- Table content -->
                    <table v-else class="barang-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Kondisi</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in barangs" :key="b.id" class="barang-row"
                                :class="{ 'barang-row--active': selectedBarang?.id === b.id }" @click="selectBarang(b)">
                                <td>
                                    <div class="barang-cell">
                                        <div class="barang-img-wrap">
                                            <img v-if="b.foto_url" :src="b.foto_url" :alt="b.nama_barang" class="barang-img" />
                                            <div v-else class="barang-img-placeholder"
                                                :style="{ background: itemColor(b.nama_barang) }">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.8" stroke-linecap="round" width="16" height="16"
                                                    style="color:white; opacity:0.8">
                                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="barang-name">{{ b.nama_barang }}</p>
                                            <p class="barang-kategori">{{ b.kategori?.nama ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="stok-wrap">
                                        <span class="stok-tersedia">{{ b.stok_tersedia }}</span>
                                        <span class="stok-sep">/</span>
                                        <span class="stok-total">{{ b.stok_total }}</span>
                                    </div>
                                    <div class="stok-bar">
                                        <div class="stok-bar__fill" :style="{ width: stokPct(b) + '%' }"
                                            :class="stokBarClass(b)">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-kondisi" :class="kondisiClass(b.kondisi)">{{ b.kondisi }}</span>
                                </td>
                                <td class="td-lokasi">{{ b.lokasi || '—' }}</td>
                                <td>
                                    <div class="action-btns">
                                        <button class="act-btn act-btn--view" title="Lihat detail"
                                            @click.stop="selectBarang(b)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" width="14" height="14">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                        <RouterLink :to="`/barang/${b.id}/edit`" class="act-btn act-btn--edit"
                                            title="Edit" @click.stop>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" width="14" height="14">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
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
                    <div v-else-if="barangs.length" class="pagination">
                        <span class="pag-info">Menampilkan {{ barangs.length }} dari {{ meta.total }} barang</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Detail panel ───────────────────────────────────────── -->
            <div class="right-col">

                <!-- Stat cards -->
                <div class="stat-grid">
                    <div class="stat-card stat-card--green">
                        <p class="stat-val">{{ meta.total }}</p>
                        <p class="stat-label">Total Jenis Barang</p>
                    </div>
                    <div class="stat-row">
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ statsTersedia }}</p>
                            <p class="stat-label">Tersedia</p>
                        </div>
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ statsDipinjam }}</p>
                            <p class="stat-label">Dipinjam</p>
                        </div>
                    </div>
                </div>

                <!-- Detail barang -->
                <div class="detail-card" v-if="selectedBarang">
                    <!-- Foto -->
                    <div class="detail-foto-wrap">
                        <img v-if="selectedBarang.foto_url" :src="selectedBarang.foto_url" :alt="selectedBarang.nama_barang"
                            class="detail-foto" />
                        <div v-else class="detail-foto-placeholder"
                            :style="{ background: itemColor(selectedBarang.nama_barang) }">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" width="28" height="28" style="color:white; opacity:0.7">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                            </svg>
                        </div>
                        <!-- Foto upload overlay -->
                        <label class="foto-upload-btn" title="Ganti foto">
                            <input type="file" accept="image/*" class="foto-input" @change="onFotoChange" />
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="12" height="12">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="17 8 12 3 7 8" />
                                <line x1="12" y1="3" x2="12" y2="15" />
                            </svg>
                        </label>
                        <button v-if="selectedBarang.foto_url" class="foto-delete-btn" title="Hapus foto"
                            @click="doHapusFoto">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="11" height="11">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <!-- Header -->
                    <div class="detail-header">
                        <div class="detail-meta">
                            <p class="detail-name">{{ selectedBarang.nama_barang }}</p>
                            <span class="detail-kategori">{{ selectedBarang.kategori?.nama ?? '—' }}</span>
                        </div>
                        <RouterLink :to="`/barang/${selectedBarang.id}/edit`" class="btn-edit-sm">Edit</RouterLink>
                    </div>

                    <!-- Stok visual -->
                    <div class="stok-detail-box">
                        <div class="stok-detail-item">
                            <p class="stok-detail-val stok-detail-val--green">{{ selectedBarang.stok_tersedia ?? '—' }}
                            </p>
                            <p class="stok-detail-label">Tersedia</p>
                        </div>
                        <div class="stok-detail-divider"></div>
                        <div class="stok-detail-item">
                            <p class="stok-detail-val stok-detail-val--orange">{{ stokDipinjam }}</p>
                            <p class="stok-detail-label">Dipinjam</p>
                        </div>
                        <div class="stok-detail-divider"></div>
                        <div class="stok-detail-item">
                            <p class="stok-detail-val">{{ selectedBarang.stok_total ?? '—' }}</p>
                            <p class="stok-detail-label">Total</p>
                        </div>
                    </div>

                    <!-- Info fields -->
                    <div class="detail-fields">
                        <div class="detail-field">
                            <span class="field-label">Kondisi</span>
                            <span class="badge-kondisi" :class="kondisiClass(selectedBarang.kondisi)">
                                {{ selectedBarang.kondisi }}
                            </span>
                        </div>
                        <div class="detail-field">
                            <span class="field-label">Lokasi</span>
                            <span class="field-val">{{ selectedBarang.lokasi || '—' }}</span>
                        </div>
                        <div class="detail-field" v-if="selectedBarang.deskripsi">
                            <span class="field-label">Deskripsi</span>
                            <span class="field-val field-val--desc">{{ selectedBarang.deskripsi }}</span>
                        </div>
                    </div>

                    <!-- Riwayat peminjaman terbaru -->
                    <div v-if="selectedBarang.peminjamans?.length" class="riwayat-box">
                        <p class="riwayat-title">Peminjaman Terbaru</p>
                        <div v-for="p in selectedBarang.peminjamans.slice(0, 3)" :key="p.id" class="riwayat-item">
                            <div class="riwayat-dot" :class="statusDotClass(p.status)"></div>
                            <div class="riwayat-info">
                                <p class="riwayat-name">{{ p.warga?.nama ?? '—' }}</p>
                                <p class="riwayat-date">{{ p.tgl_rencana_kembali ?? '' }} · {{ p.status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hapus -->
                    <button class="btn-delete" @click="confirmDelete(selectedBarang)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="14" height="14">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14H6L5 6" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                            <path d="M9 6V4h6v2" />
                        </svg>
                        Hapus Barang
                    </button>
                </div>

                <!-- Placeholder saat belum pilih -->
                <div v-else class="detail-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"
                        style="color:#cbd5e1">
                        <rect x="2" y="7" width="20" height="14" rx="2" />
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                    </svg>
                    <p>Pilih barang untuk melihat detail</p>
                </div>

                <!-- Kondisi summary -->
                <div class="kondisi-card" v-if="barangs.length">
                    <p class="activity-title">Ringkasan Kondisi</p>
                    <div class="kondisi-list">
                        <div class="kondisi-item">
                            <span class="kondisi-dot kondisi-dot--baik"></span>
                            <span class="kondisi-label">Baik</span>
                            <span class="kondisi-count">{{ kondisiCount('Baik') }}</span>
                        </div>
                        <div class="kondisi-item">
                            <span class="kondisi-dot kondisi-dot--ringan"></span>
                            <span class="kondisi-label">Rusak Ringan</span>
                            <span class="kondisi-count">{{ kondisiCount('Rusak Ringan') }}</span>
                        </div>
                        <div class="kondisi-item">
                            <span class="kondisi-dot kondisi-dot--berat"></span>
                            <span class="kondisi-label">Rusak Berat</span>
                            <span class="kondisi-count">{{ kondisiCount('Rusak Berat') }}</span>
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
                <h3 class="modal-title">Hapus Barang?</h3>
                <p class="modal-body">
                    Barang <strong>{{ deleteTarget?.nama }}</strong> akan dihapus permanen.
                    Barang yang sedang dipinjam tidak dapat dihapus.
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
import { useBarangStore } from '@/stores/barang'
import barangService from '@/services/barangService'
import { useToast } from 'vue-toastification'

const store = useBarangStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const search = ref('')
const filterKondisi = ref('')
const filterTersedia = ref(false)
const selectedBarang = ref(null)
const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)
let searchTimer = null

// ── Computed dari store ─────────────────────────────────────────────
const barangs = computed(() => store.barangs)
const meta = computed(() => store.meta)
const loading = computed(() => store.loading)

const pageNumbers = computed(() => {
    const total = meta.value.last_page
    const current = meta.value.current_page
    const start = Math.max(1, current - 2)
    const end = Math.min(total, start + 4)
    const pages = []
    for (let i = start; i <= end; i++) pages.push(i)
    return pages
})

const paginationInfo = computed(() => {
    const { current_page, per_page, total } = meta.value
    const from = (current_page - 1) * (per_page || 10) + 1
    const to = Math.min(current_page * (per_page || 10), total)
    return `${from}–${to} dari ${total} barang`
})

const statsTersedia = computed(() => barangs.value.filter(b => (b.stok_tersedia ?? 0) > 0).length)
const statsDipinjam = computed(() => barangs.value.reduce((acc, b) => acc + (b.stok_dipinjam ?? 0), 0))

const stokDipinjam = computed(() => {
    if (!selectedBarang.value) return 0
    const b = selectedBarang.value
    return (b.stok_total ?? 0) - (b.stok_tersedia ?? 0)
})

// ── Methods ─────────────────────────────────────────────────────────
async function fetchData(page = 1) {
    await store.fetchBarangs({
        page,
        search: search.value || undefined,
        kondisi: filterKondisi.value || undefined,
        tersedia: filterTersedia.value || undefined,
        per_page: 10,
    })
}

function onSearch() {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => fetchData(1), 400)
}

async function selectBarang(b) {
    selectedBarang.value = b
    if (!b.peminjamans) {
        try {
            const detail = await barangService.getOne(b.id)
            selectedBarang.value = detail
        } catch (err) {
            console.error(err)
        }
    }
}

function confirmDelete(b) {
    deleteTarget.value = b
    showDeleteModal.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await store.deleteBarang(deleteTarget.value.id)
        toast.success(`Barang ${deleteTarget.value.nama_barang} berhasil dihapus.`)
        showDeleteModal.value = false
        if (selectedBarang.value?.id === deleteTarget.value.id) {
            selectedBarang.value = null
        }
    } catch (err) {
        toast.error(err?.response?.data?.message || 'Gagal menghapus barang.')
    } finally {
        deleting.value = false
    }
}

async function onFotoChange(e) {
    const file = e.target.files?.[0]
    if (!file || !selectedBarang.value) return
    const fd = new FormData()
    fd.append('foto', file)
    try {
        const res = await barangService.uploadFoto(selectedBarang.value.id, fd)
        selectedBarang.value = { ...selectedBarang.value, foto_url: res.foto_url }
        // update list juga
        const idx = store.barangs.findIndex(b => b.id === selectedBarang.value.id)
        if (idx !== -1) store.barangs[idx] = { ...store.barangs[idx], foto_url: res.foto_url }
        toast.success('Foto berhasil diperbarui.')
    } catch {
        toast.error('Gagal mengupload foto.')
    }
    e.target.value = ''
}

async function doHapusFoto() {
    if (!selectedBarang.value?.foto_url) return
    try {
        await barangService.hapusFoto(selectedBarang.value.id)
        selectedBarang.value = { ...selectedBarang.value, foto_url: null }
        const idx = store.barangs.findIndex(b => b.id === selectedBarang.value.id)
        if (idx !== -1) store.barangs[idx] = { ...store.barangs[idx], foto_url: null }
        toast.success('Foto berhasil dihapus.')
    } catch {
        toast.error('Gagal menghapus foto.')
    }
}

// ── Helpers visual ──────────────────────────────────────────────────
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']

function itemColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

function stokPct(b) {
    if (!b.stok_total) return 0
    return Math.round(((b.stok_tersedia ?? 0) / b.stok_total) * 100)
}

function stokBarClass(b) {
    const pct = stokPct(b)
    if (pct === 0) return 'stok-bar__fill--empty'
    if (pct < 40) return 'stok-bar__fill--low'
    return 'stok-bar__fill--ok'
}

function kondisiClass(kondisi) {
    if (kondisi === 'Baik') return 'badge-kondisi--baik'
    if (kondisi === 'Rusak Ringan') return 'badge-kondisi--ringan'
    return 'badge-kondisi--berat'
}

function statusDotClass(status) {
    if (status === 'Aktif') return 'riwayat-dot--aktif'
    if (status === 'Terlambat') return 'riwayat-dot--terlambat'
    if (status === 'Selesai') return 'riwayat-dot--selesai'
    return 'riwayat-dot--default'
}

function kondisiCount(kondisi) {
    return barangs.value.filter(b => b.kondisi === kondisi).length
}

// ── Init ────────────────────────────────────────────────────────────
onMounted(() => fetchData())
</script>

<style scoped>
.barang-page {
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

.filter-group {
    display: flex;
    gap: 8px;
    align-items: center;
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

.filter-toggle {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 500;
    color: #6b7280;
    background: white;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
}

.filter-toggle:hover {
    border-color: #86efac;
    color: #16a34a;
}

.filter-toggle--active {
    background: #f0fdf4;
    border-color: #16a34a;
    color: #16a34a;
    font-weight: 600;
}

/* ── Table card ──────────────────────────────────────────────────── */
.table-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.barang-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.barang-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #f0f0f0;
}

.barang-table th {
    text-align: left;
    padding: 11px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.barang-row {
    cursor: pointer;
    transition: background 0.12s;
    border-bottom: 1px solid #f9fafb;
}

.barang-row:last-child {
    border-bottom: none;
}

.barang-row:hover {
    background: #f9fdf9;
}

.barang-row--active {
    background: #f0fdf4 !important;
}

.barang-row td {
    padding: 11px 14px;
    color: #374151;
    vertical-align: middle;
}

.barang-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.barang-img-wrap {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid #f0f0f0;
}

.barang-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.barang-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.barang-name {
    font-weight: 600;
    color: #111827;
    font-size: 13px;
    margin: 0;
}

.barang-kategori {
    font-size: 11px;
    color: #9ca3af;
    margin: 1px 0 0;
}

/* Stok bar */
.stok-wrap {
    display: flex;
    align-items: baseline;
    gap: 2px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 4px;
}

.stok-tersedia {
    color: #16a34a;
}

.stok-sep {
    color: #d1d5db;
    font-weight: 400;
    font-size: 12px;
}

.stok-total {
    color: #9ca3af;
    font-size: 12px;
    font-weight: 500;
}

.stok-bar {
    width: 64px;
    height: 4px;
    background: #f1f5f9;
    border-radius: 99px;
    overflow: hidden;
}

.stok-bar__fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.3s;
}

.stok-bar__fill--ok {
    background: #16a34a;
}

.stok-bar__fill--low {
    background: #f59e0b;
}

.stok-bar__fill--empty {
    background: #ef4444;
    width: 100% !important;
}

/* Badges kondisi */
.badge-kondisi {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-kondisi--baik {
    background: #dcfce7;
    color: #166534;
}

.badge-kondisi--ringan {
    background: #fef3c7;
    color: #92400e;
}

.badge-kondisi--berat {
    background: #fee2e2;
    color: #991b1b;
}

.td-lokasi {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #6b7280;
    font-size: 12.5px;
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

.skeleton-img {
    width: 36px;
    height: 36px;
    border-radius: 8px;
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
    padding: 0;
    overflow: hidden;
}

/* Foto */
.detail-foto-wrap {
    position: relative;
    width: 100%;
    height: 130px;
    background: #f9fafb;
}

.detail-foto {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.detail-foto-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.foto-upload-btn {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    background: rgba(0, 0, 0, 0.55);
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    transition: background 0.15s;
}

.foto-upload-btn:hover {
    background: rgba(0, 0, 0, 0.75);
}

.foto-input {
    display: none;
}

.foto-delete-btn {
    position: absolute;
    bottom: 8px;
    right: 42px;
    width: 28px;
    height: 28px;
    background: rgba(220, 38, 38, 0.75);
    border: none;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    transition: background 0.15s;
}

.foto-delete-btn:hover {
    background: rgba(220, 38, 38, 0.95);
}

.detail-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    padding: 14px 16px 10px;
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

.detail-kategori {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
    display: block;
    margin-top: 2px;
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

/* Stok detail box */
.stok-detail-box {
    display: flex;
    align-items: center;
    background: #f9fafb;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    padding: 12px 0;
    margin-bottom: 0;
}

.stok-detail-item {
    flex: 1;
    text-align: center;
}

.stok-detail-val {
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.stok-detail-val--green {
    color: #16a34a;
}

.stok-detail-val--orange {
    color: #f59e0b;
}

.stok-detail-label {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    margin: 3px 0 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.stok-detail-divider {
    width: 1px;
    height: 32px;
    background: #e5e7eb;
    flex-shrink: 0;
}

/* Detail fields */
.detail-fields {
    display: flex;
    flex-direction: column;
    padding: 0 16px;
}

.detail-field {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
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

.field-val--desc {
    font-size: 12px;
    color: #6b7280;
    font-weight: 400;
    line-height: 1.5;
    text-align: right;
    max-width: 160px;
}

/* Riwayat */
.riwayat-box {
    border-top: 1px solid #f0f0f0;
    padding: 12px 16px 8px;
}

.riwayat-title {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    margin: 0 0 8px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.riwayat-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 5px 0;
}

.riwayat-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 4px;
    flex-shrink: 0;
}

.riwayat-dot--aktif {
    background: #16a34a;
}

.riwayat-dot--terlambat {
    background: #ef4444;
}

.riwayat-dot--selesai {
    background: #94a3b8;
}

.riwayat-dot--default {
    background: #d1d5db;
}

.riwayat-name {
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.riwayat-date {
    font-size: 11px;
    color: #9ca3af;
    margin: 1px 0 0;
}

/* Hapus */
.btn-delete {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 12px 16px 16px;
    background: none;
    border: 1px solid #fecaca;
    color: #dc2626;
    font-size: 12px;
    font-weight: 600;
    padding: 7px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
    width: calc(100% - 32px);
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

/* Kondisi card */
.kondisi-card {
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

.kondisi-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.kondisi-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
}

.kondisi-item:last-child {
    border-bottom: none;
}

.kondisi-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.kondisi-dot--baik {
    background: #16a34a;
}

.kondisi-dot--ringan {
    background: #f59e0b;
}

.kondisi-dot--berat {
    background: #ef4444;
}

.kondisi-label {
    flex: 1;
    color: #374151;
    font-weight: 500;
}

.kondisi-count {
    font-weight: 700;
    color: #111827;
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

    .td-lokasi {
        display: none;
    }
}

@media (max-width: 600px) {
    .stat-grid {
        flex-direction: column;
    }

    .stok-bar {
        display: none;
    }
}
</style>