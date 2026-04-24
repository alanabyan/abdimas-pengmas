<template>
    <div class="kategori-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div>
                <h1 class="pg-title">Daftar Kategori</h1>
                <p class="pg-sub">Kelola kategori barang inventaris masjid.</p>
            </div>
            <RouterLink to="/kategori/tambah" class="btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Kategori
            </RouterLink>
        </div>

        <!-- ── Main grid ───────────────────────────────────────────────── -->
        <div class="main-grid">

            <!-- LEFT ─────────────────────────────────────────────────── -->
            <div class="left-col">

                <!-- Search bar -->
                <div class="toolbar">
                    <div class="search-wrap">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="16" height="16">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input v-model="search" type="text" placeholder="Cari nama kategori..." class="search-input" />
                        <button v-if="search" class="search-clear" @click="search = ''">✕</button>
                    </div>
                    <div class="view-toggle">
                        <button class="view-btn" :class="{ 'view-btn--active': viewMode === 'grid' }"
                            @click="viewMode = 'grid'" title="Tampilan grid">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" />
                            </svg>
                        </button>
                        <button class="view-btn" :class="{ 'view-btn--active': viewMode === 'list' }"
                            @click="viewMode = 'list'" title="Tampilan list">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <line x1="8" y1="6" x2="21" y2="6" />
                                <line x1="8" y1="12" x2="21" y2="12" />
                                <line x1="8" y1="18" x2="21" y2="18" />
                                <line x1="3" y1="6" x2="3.01" y2="6" />
                                <line x1="3" y1="12" x2="3.01" y2="12" />
                                <line x1="3" y1="18" x2="3.01" y2="18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Loading skeleton -->
                <div v-if="loading" class="skeleton-grid" :class="{ 'skeleton-grid--list': viewMode === 'list' }">
                    <div v-for="i in 6" :key="i" class="skeleton-card">
                        <div class="sk-ikon"></div>
                        <div class="sk-lines">
                            <div class="sk-line" style="width:60%"></div>
                            <div class="sk-line" style="width:40%"></div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-else-if="!filteredKategoris.length" class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"
                        style="color:#cbd5e1">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                    </svg>
                    <p>{{ search ? 'Tidak ada kategori yang cocok' : 'Belum ada kategori' }}</p>
                    <RouterLink v-if="!search" to="/kategori/tambah" class="btn-add" style="margin-top:8px">
                        Tambah Kategori Pertama
                    </RouterLink>
                </div>

                <!-- Grid view -->
                <div v-else-if="viewMode === 'grid'" class="kategori-grid">
                    <div v-for="k in filteredKategoris" :key="k.id" class="kategori-card"
                        :class="{ 'kategori-card--active': selectedKategori?.id === k.id }" @click="selectKategori(k)">
                        <div class="kcard-top">
                            <div class="kcard-ikon"
                                :style="{ background: itemColor(k.nama) + '18', color: itemColor(k.nama) }">
                                <component v-if="k.ikon" :is="getIcon(k.ikon)" width="20" height="20" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" width="20" height="20">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </div>
                            <div class="kcard-actions">
                                <RouterLink :to="`/kategori/${k.id}/edit`" class="kcard-btn kcard-btn--edit"
                                    title="Edit" @click.stop>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" width="12" height="12">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </RouterLink>
                            </div>
                        </div>
                        <p class="kcard-nama">{{ k.nama }}</p>
                        <p class="kcard-desc" v-if="k.deskripsi">{{ k.deskripsi }}</p>
                        <p class="kcard-desc kcard-desc--empty" v-else>Tidak ada deskripsi</p>
                        <div class="kcard-footer">
                            <span class="kcard-count">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="11" height="11">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                                {{ k.barangs_count ?? 0 }} barang
                            </span>
                        </div>
                    </div>
                </div>

                <!-- List view -->
                <div v-else class="table-card">
                    <table class="kategori-table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Barang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="k in filteredKategoris" :key="k.id" class="kategori-row"
                                :class="{ 'kategori-row--active': selectedKategori?.id === k.id }"
                                @click="selectKategori(k)">
                                <td>
                                    <div class="list-cell">
                                        <div class="list-ikon"
                                            :style="{ background: itemColor(k.nama) + '18', color: itemColor(k.nama) }">
                                            <component v-if="k.ikon" :is="getIcon(k.ikon)" width="20" height="20" />
                                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" width="13" height="13">
                                                <rect x="3" y="3" width="7" height="7" />
                                                <rect x="14" y="3" width="7" height="7" />
                                                <rect x="3" y="14" width="7" height="7" />
                                                <rect x="14" y="14" width="7" height="7" />
                                            </svg>
                                        </div>
                                        <span class="list-nama">{{ k.nama }}</span>
                                    </div>
                                </td>
                                <td class="td-desc">{{ k.deskripsi || '—' }}</td>
                                <td>
                                    <span class="count-badge">{{ k.barangs_count ?? 0 }} barang</span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="act-btn act-btn--view" title="Detail"
                                            @click.stop="selectKategori(k)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" width="14" height="14">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                        <RouterLink :to="`/kategori/${k.id}/edit`" class="act-btn act-btn--edit"
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
                </div>
            </div>

            <!-- RIGHT: Detail panel ───────────────────────────────────────── -->
            <div class="right-col">

                <!-- Stat cards -->
                <div class="stat-grid">
                    <div class="stat-card stat-card--green">
                        <p class="stat-val">{{ kategoris.length }}</p>
                        <p class="stat-label">Total Kategori</p>
                    </div>
                    <div class="stat-row">
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ totalBarang }}</p>
                            <p class="stat-label">Total Barang</p>
                        </div>
                        <div class="stat-card stat-card--sm">
                            <p class="stat-val stat-val--sm">{{ kategoriKosong }}</p>
                            <p class="stat-label">Kosong</p>
                        </div>
                    </div>
                </div>

                <!-- Detail panel -->
                <div class="detail-card" v-if="selectedKategori">
                    <!-- Ikon besar -->
                    <div class="detail-hero" :style="{ background: itemColor(selectedKategori.nama) + '15' }">
                        <div class="detail-ikon-lg"
                            :style="{ background: itemColor(selectedKategori.nama) + '25', color: itemColor(selectedKategori.nama) }">
                            <component v-if="selectedKategori.ikon" :is="getIcon(selectedKategori.ikon)" size="28" />
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" width="28" height="28">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" />
                            </svg>
                        </div>
                        <RouterLink :to="`/kategori/${selectedKategori.id}/edit`" class="btn-edit-sm">
                            Edit
                        </RouterLink>
                    </div>

                    <!-- Info -->
                    <div class="detail-body">
                        <p class="detail-nama">{{ selectedKategori.nama }}</p>
                        <p class="detail-desc" v-if="selectedKategori.deskripsi">{{ selectedKategori.deskripsi }}</p>
                        <p class="detail-desc detail-desc--empty" v-else>Tidak ada deskripsi</p>

                        <!-- Count -->
                        <div class="detail-count-box">
                            <div class="detail-count-icon" :style="{ color: itemColor(selectedKategori.nama) }">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="16" height="16">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                            </div>
                            <div>
                                <p class="detail-count-val">{{ selectedKategori.barangs_count ?? 0 }}</p>
                                <p class="detail-count-label">Barang dalam kategori ini</p>
                            </div>
                        </div>

                        <!-- Barang list -->
                        <div v-if="selectedKategori.barangs?.length" class="barang-list-mini">
                            <p class="barang-list-title">Barang Terdaftar</p>
                            <div v-for="b in selectedKategori.barangs.slice(0, 5)" :key="b.id" class="barang-list-item">
                                <div class="barang-list-dot" :class="kondisiDotClass(b.kondisi)"></div>
                                <span class="barang-list-nama">{{ b.nama }}</span>
                                <span class="barang-list-stok">{{ b.stok_tersedia }} tersedia</span>
                            </div>
                            <RouterLink v-if="selectedKategori.barangs.length > 5" to="/barang"
                                class="barang-list-more">
                                +{{ selectedKategori.barangs.length - 5 }} barang lainnya →
                            </RouterLink>
                        </div>

                        <!-- Hapus -->
                        <button class="btn-delete" @click="confirmDelete(selectedKategori)"
                            :disabled="(selectedKategori.barangs_count ?? 0) > 0"
                            :title="(selectedKategori.barangs_count ?? 0) > 0 ? 'Tidak dapat dihapus, masih ada barang' : ''">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M9 6V4h6v2" />
                            </svg>
                            {{ (selectedKategori.barangs_count ?? 0) > 0 ? 'Tidak bisa dihapus (ada barang)' : 'Hapus Kategori' }}
                        </button>
                    </div>
                </div>

                <!-- Placeholder -->
                <div v-else class="detail-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"
                        style="color:#cbd5e1">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                    </svg>
                    <p>Pilih kategori untuk melihat detail</p>
                </div>

                <!-- Distribusi barang -->
                <div class="dist-card" v-if="kategoris.length">
                    <p class="dist-title">Distribusi Barang</p>
                    <div class="dist-list">
                        <div v-for="k in topKategoris" :key="k.id" class="dist-item">
                            <div class="dist-label-wrap">

                                <!-- SESUDAH -->
                                <div class="dist-ikon-wrap" :style="{ color: itemColor(k.nama) }">
                                    <component v-if="k.ikon && getIcon(k.ikon)" :is="getIcon(k.ikon)" size="14" />
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" width="14" height="14">
                                        <path
                                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                        <path d="m3.3 7 8.7 5 8.7-5" />
                                        <path d="M12 22V12" />
                                    </svg>
                                </div>
                                <span class="dist-nama">{{ k.nama }}</span>
                                <span class="dist-count">{{ k.barangs_count ?? 0 }}</span>
                            </div>
                            <div class="dist-bar">
                                <div class="dist-bar__fill"
                                    :style="{ width: distPct(k) + '%', background: itemColor(k.nama) }">
                                </div>
                            </div>
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
                <h3 class="modal-title">Hapus Kategori?</h3>
                <p class="modal-body">
                    Kategori <strong>{{ deleteTarget?.nama }}</strong> akan dihapus permanen.
                    Kategori yang masih memiliki barang tidak dapat dihapus.
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
import { useKategoriStore } from '@/stores/kategori'
import kategoriService from '@/services/kategoriService'
import { useToast } from 'vue-toastification'
import * as icons from 'lucide-vue-next'

const iconMap = {
    bucket: 'Droplet',        // pengganti ember
    'tools-kitchen': 'Utensils',
    prayer: 'BookOpen',       // atau Heart / Book
    plug: 'Plug',
    armchair: 'Armchair',
    box: 'Box'
}

function getIcon(name) {
    const fixed = iconMap[name] || name
    return icons[fixed] || icons.Box
}

const store = useKategoriStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const search = ref('')
const viewMode = ref('grid')
const selectedKategori = ref(null)
const showDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

// ── Computed ────────────────────────────────────────────────────────
const kategoris = computed(() => store.kategoris)
const loading = computed(() => store.loading)

const filteredKategoris = computed(() => {
    if (!search.value) return kategoris.value
    const q = search.value.toLowerCase()
    return kategoris.value.filter(k =>
        k.nama.toLowerCase().includes(q) ||
        (k.deskripsi ?? '').toLowerCase().includes(q)
    )
})

const totalBarang = computed(() => kategoris.value.reduce((s, k) => s + (k.barangs_count ?? 0), 0))
const kategoriKosong = computed(() => kategoris.value.filter(k => (k.barangs_count ?? 0) === 0).length)

const topKategoris = computed(() =>
    [...kategoris.value].sort((a, b) => (b.barangs_count ?? 0) - (a.barangs_count ?? 0)).slice(0, 5)
)

const maxBarang = computed(() => Math.max(...kategoris.value.map(k => k.barangs_count ?? 0), 1))

// ── Methods ─────────────────────────────────────────────────────────
async function selectKategori(k) {
    selectedKategori.value = k
    // Load detail (barangs list) jika belum ada
    if (!k.barangs) {
        try {
            const detail = await kategoriService.getOne(k.id)
            // merge ke store list & selected
            const idx = store.kategoris.findIndex(s => s.id === k.id)
            if (idx !== -1) store.kategoris[idx] = { ...store.kategoris[idx], ...detail }
            selectedKategori.value = { ...k, ...detail }
        } catch (err) {
            console.error(err)
        }
    }
}

function confirmDelete(k) {
    deleteTarget.value = k
    showDeleteModal.value = true
}

async function doDelete() {
    deleting.value = true
    try {
        await store.deleteKategori(deleteTarget.value.id)
        toast.success(`Kategori "${deleteTarget.value.nama}" berhasil dihapus.`)
        showDeleteModal.value = false
        if (selectedKategori.value?.id === deleteTarget.value.id) selectedKategori.value = null
    } catch (err) {
        toast.error(err?.response?.data?.message || 'Gagal menghapus kategori.')
    } finally {
        deleting.value = false
    }
}

function distPct(k) {
    return Math.round(((k.barangs_count ?? 0) / maxBarang.value) * 100)
}

// ── Helpers ──────────────────────────────────────────────────────────
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function itemColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

function kondisiDotClass(kondisi) {
    if (kondisi === 'Baik') return 'dot--baik'
    if (kondisi === 'Rusak Ringan') return 'dot--ringan'
    return 'dot--berat'
}

onMounted(() => store.fetchKategoris())
</script>

<style scoped>
.kategori-page {
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

/* ── Grid layout ─────────────────────────────────────────────────── */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 16px;
    align-items: start;
}

.left-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.right-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Toolbar ─────────────────────────────────────────────────────── */
.toolbar {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-wrap {
    flex: 1;
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

.view-toggle {
    display: flex;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    overflow: hidden;
    background: white;
    flex-shrink: 0;
}

.view-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: white;
    color: #9ca3af;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.view-btn:first-child {
    border-right: 1px solid #e5e7eb;
}

.view-btn:hover {
    background: #f9fafb;
    color: #374151;
}

.view-btn--active {
    background: #f0fdf4 !important;
    color: #16a34a !important;
}

/* ── Kategori grid ───────────────────────────────────────────────── */
.kategori-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.kategori-card {
    background: white;
    border-radius: 12px;
    border: 1.5px solid #f0f0f0;
    padding: 16px;
    cursor: pointer;
    transition: all 0.18s;
    position: relative;
    overflow: hidden;
}

.kategori-card:hover {
    border-color: #86efac;
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.08);
    transform: translateY(-1px);
}

.kategori-card--active {
    border-color: #16a34a !important;
    background: #f0fdf4 !important;
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.12) !important;
}

.kcard-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 12px;
}

.kcard-ikon {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kcard-emoji {
    font-size: 22px;
    line-height: 1;
}

.dist-ikon-wrap {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.kcard-actions {
    opacity: 0;
    transition: opacity 0.15s;
}

.kategori-card:hover .kcard-actions {
    opacity: 1;
}

.kcard-btn {
    width: 26px;
    height: 26px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
    background: white;
    text-decoration: none;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s;
}

.kcard-btn--edit:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #2563eb;
}

.kcard-nama {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
}

.kcard-desc {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 12px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.kcard-desc--empty {
    color: #c4c9d1;
    font-style: italic;
}

.kcard-footer {
    border-top: 1px solid #f0f0f0;
    padding-top: 10px;
    margin-top: auto;
}

.kcard-count {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: #6b7280;
}

/* ── Table (list view) ───────────────────────────────────────────── */
.table-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.kategori-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.kategori-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #f0f0f0;
}

.kategori-table th {
    text-align: left;
    padding: 11px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.kategori-row {
    cursor: pointer;
    transition: background 0.12s;
    border-bottom: 1px solid #f9fafb;
}

.kategori-row:last-child {
    border-bottom: none;
}

.kategori-row:hover {
    background: #f9fdf9;
}

.kategori-row--active {
    background: #f0fdf4 !important;
}

.kategori-row td {
    padding: 12px 14px;
    vertical-align: middle;
}

.list-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.list-ikon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.list-nama {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

.td-desc {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #6b7280;
    font-size: 12.5px;
}

.count-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    background: #f1f5f9;
    color: #374151;
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

/* ── Skeleton ─────────────────────────────────────────────────────── */
.skeleton-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.skeleton-grid--list {
    grid-template-columns: 1fr;
}

.skeleton-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.sk-ikon {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    background: #f1f5f9;
    flex-shrink: 0;
    animation: shimmer 1.5s infinite;
}

.sk-lines {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sk-line {
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
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
}

/* ── Right col ───────────────────────────────────────────────────── */
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
    overflow: hidden;
}

.detail-hero {
    padding: 20px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.detail-ikon-lg {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.btn-edit-sm {
    font-size: 11.5px;
    font-weight: 600;
    color: #16a34a;
    border: 1px solid #86efac;
    padding: 5px 10px;
    border-radius: 7px;
    text-decoration: none;
    transition: all 0.15s;
    flex-shrink: 0;
}

.btn-edit-sm:hover {
    background: #f0fdf4;
}

.detail-body {
    padding: 0 16px 16px;
}

.detail-nama {
    font-size: 16px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 6px;
}

.detail-desc {
    font-size: 12.5px;
    color: #6b7280;
    line-height: 1.6;
    margin: 0 0 14px;
}

.detail-desc--empty {
    color: #c4c9d1;
    font-style: italic;
}

.detail-count-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f9fafb;
    border-radius: 9px;
    padding: 10px 12px;
    margin-bottom: 12px;
}

.detail-count-icon {
    flex-shrink: 0;
}

.detail-count-val {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.detail-count-label {
    font-size: 11px;
    color: #9ca3af;
    margin: 2px 0 0;
}

/* Barang list mini */
.barang-list-mini {
    margin-bottom: 14px;
}

.barang-list-title {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0 0 8px;
}

.barang-list-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
}

.barang-list-item:last-of-type {
    border-bottom: none;
}

.barang-list-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dot--baik {
    background: #16a34a;
}

.dot--ringan {
    background: #f59e0b;
}

.dot--berat {
    background: #ef4444;
}

.barang-list-nama {
    flex: 1;
    color: #374151;
    font-weight: 500;
}

.barang-list-stok {
    font-size: 11px;
    font-weight: 600;
    color: #16a34a;
}

.barang-list-more {
    display: block;
    font-size: 11.5px;
    color: #16a34a;
    text-decoration: none;
    margin-top: 6px;
    font-weight: 600;
}

.barang-list-more:hover {
    text-decoration: underline;
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

.btn-delete:hover:not(:disabled) {
    background: #fef2f2;
}

.btn-delete:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    border-color: #e5e7eb;
    color: #9ca3af;
}

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

/* Distribusi */
.dist-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.dist-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0 0 12px;
}

.dist-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.dist-label-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.dist-ikon {
    font-size: 14px;
}

.dist-nama {
    flex: 1;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.dist-count {
    font-size: 12px;
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

    .kategori-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
}

@media (max-width: 600px) {
    .stat-grid {
        flex-direction: column;
    }

    .kategori-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>