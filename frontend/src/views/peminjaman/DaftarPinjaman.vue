<template>
  <div class="page">

    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
            <rect x="9" y="3" width="6" height="4" rx="1" />
            <path d="M9 12h6M9 16h4" />
          </svg>
        </div>
        <div>
          <h1 class="page-title">Peminjaman</h1>
          <p class="page-sub">Kelola semua transaksi peminjaman barang</p>
        </div>
      </div>
      <router-link to="/peminjaman/tambah" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Tambah Peminjaman
      </router-link>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card" v-for="s in stats" :key="s.label" :style="{ '--accent': s.color }">
        <span class="stat-num">{{ s.value }}</span>
        <span class="stat-label">{{ s.label }}</span>
        <div class="stat-bar"></div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input v-model="filters.search" placeholder="Cari warga atau barang..." />
      </div>
      <div class="filter-chips">
        <button v-for="s in statusOptions" :key="s.value" :class="['chip', { active: filters.status === s.value }]"
          @click="setStatus(s.value)">
          <span class="chip-dot" :style="{ background: s.color }"></span>
          {{ s.label }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <!-- Loading skeleton -->
      <div v-if="store.loading" class="skeleton-wrap">
        <div v-for="i in 6" :key="i" class="skel-row">
          <div class="skel-avatar"></div>
          <div class="skel-lines">
            <div class="skel-line w55"></div>
            <div class="skel-line w35"></div>
          </div>
          <div class="skel-pill"></div>
          <div class="skel-pill w70"></div>
          <div class="skel-btns"></div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="!filteredPeminjamans.length" class="empty">
        <div class="empty-ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
            <rect x="9" y="3" width="6" height="4" rx="1" />
          </svg>
        </div>
        <p class="empty-title">Tidak ada data peminjaman</p>
        <p class="empty-sub">Coba ubah filter atau tambah peminjaman baru</p>
      </div>

      <!-- Table content -->
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Peminjam</th>
            <th>Barang</th>
            <th>Jml</th>
            <th>Tgl Pinjam</th>
            <th>Rencana Kembali</th>
            <th>Status</th>
            <th class="th-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, idx) in filteredPeminjamans" :key="p.id" class="data-row"
            :style="{ animationDelay: `${idx * 35}ms` }">
            <td>
              <div class="person-cell">
                <div class="avatar" :style="{ background: avatarColor(p.warga?.nama_warga ?? p.warga?.nama) }">
                  {{ initials(p.warga?.nama_warga ?? p.warga?.nama_warga) }}
                </div>
                <div>
                  <div class="person-name">{{ p.warga?.nama_warga ?? p.warga?.nama_warga ?? '-' }}</div>
                  <div class="person-sub">{{ p.warga?.no_hp ?? '' }}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="barang-name">{{ p.barang?.nama_barang ?? p.barang?.nama_barang ?? '-' }}</div>
            </td>
            <td>
              <div class="qty-cell">
                <span class="qty-badge">{{ p.jumlah }}</span>
                <span v-if="p.status === 'Sebagian Kembali' && p.jumlah_kembali" class="qty-partial">
                  {{ p.jumlah_kembali }}/{{ p.jumlah }} kembali
                </span>
              </div>
            </td>
            <td><span class="date-text">{{ fmtDate(p.tgl_pinjam) }}</span></td>
            <td>
              <span class="date-text" :class="{ overdue: isOverdue(p) }">
                {{ fmtDate(p.tgl_rencana_kembali) }}
              </span>
            </td>
            <td>
              <StatusBadge :status="isOverdue(p) ? 'Telat' : p.status" />
            </td>
            <td>
              <div class="action-group">
                <router-link :to="`/peminjaman/${p.id}`" class="act-btn act-view" title="Detail">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </router-link>
                <button v-if="p.status === 'Menunggu'" class="act-btn act-confirm" title="Konfirmasi"
                  @click="handleKonfirmasi(p)" :disabled="actionLoading === p.id">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                </button>
                <button v-if="p.status === 'Menunggu' || p.status === 'Aktif'" class="act-btn act-batal"
                  title="Batalkan" @click="openBatal(p)" :disabled="actionLoading === p.id">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                  </svg>
                </button>
                <router-link v-if="p.status === 'Menunggu'" :to="`/peminjaman/${p.id}/edit`" class="act-btn act-edit"
                  title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </router-link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="store.meta.last_page > 1" class="pagination">
        <button class="pg-btn" :disabled="store.meta.current_page === 1" @click="goPage(store.meta.current_page - 1)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6" />
          </svg>
        </button>
        <span class="pg-info">{{ store.meta.current_page }} / {{ store.meta.last_page }}</span>
        <button class="pg-btn" :disabled="store.meta.current_page === store.meta.last_page"
          @click="goPage(store.meta.current_page + 1)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Modal konfirmasi batal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showBatal" class="modal-backdrop" @click.self="showBatal = false">
          <div class="modal modal-sm">
            <div class="modal-body confirm-body">
              <div class="confirm-ico danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                  <line x1="12" y1="9" x2="12" y2="13" />
                  <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
              </div>
              <h3 class="confirm-title">Batalkan Peminjaman?</h3>
              <p class="confirm-desc">Peminjaman <strong>{{ batalTarget?.warga?.nama_warga ??
                batalTarget?.warga?.nama_warga
                  }}</strong> akan dibatalkan dan stok dikembalikan.</p>
              <div class="modal-footer">
                <button class="btn-cancel" @click="showBatal = false">Tidak</button>
                <button class="btn-submit btn-danger" :disabled="actionLoading" @click="submitBatal">
                  <span v-if="actionLoading" class="spinner"></span>
                  Ya, Batalkan
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
          <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePeminjamanStore } from '@/stores/peminjaman'
import StatusBadge from '@/components/StatusBadge.vue'

const store = usePeminjamanStore()

const filters = ref({ status: '', search: '' })
const actionLoading = ref(null)
const showBatal = ref(false)
const batalTarget = ref(null)
const toast = ref({ show: false, type: 'success', message: '' })

// ── Helpers ───────────────────────────────────────────────────────────────
const COLORS = ['#1e3a5f', '#16a34a', '#d97706', '#6366f1', '#0891b2', '#be185d', '#7c3aed']
function avatarColor(name = '') {
  let h = 0; for (const c of name) h = c.charCodeAt(0) + ((h << 5) - h)
  return COLORS[Math.abs(h) % COLORS.length]
}
function initials(name = '') { return (name || '').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }
function isOverdue(p) {
  return p.status === 'Aktif' && p.tgl_rencana_kembali && new Date(p.tgl_rencana_kembali) < new Date()
}

// ── Stats ─────────────────────────────────────────────────────────────────
const stats = computed(() => [
  { label: 'Total', value: store.allPeminjamans.length, color: '#1e3a5f' },
  { label: 'Menunggu', value: store.allPeminjamans.filter(p => p.status === 'Menunggu').length, color: '#d97706' },
  { label: 'Aktif', value: store.allPeminjamans.filter(p => p.status === 'Aktif' && !isOverdue(p)).length, color: '#16a34a' },
  { label: 'Telat', value: store.allPeminjamans.filter(p => isOverdue(p)).length, color: '#dc2626' },
  { label: 'Sebagian', value: store.allPeminjamans.filter(p => p.status === 'Sebagian Kembali').length, color: '#f59e0b' },
  { label: 'Selesai', value: store.allPeminjamans.filter(p => p.status === 'Selesai').length, color: '#6366f1' },
  { label: 'Rusak/Hilang', value: store.allPeminjamans.filter(p => p.status === 'Rusak/Hilang').length, color: '#c2410c' },
  { label: 'Batal', value: store.allPeminjamans.filter(p => p.status === 'Batal').length, color: '#64748b' },
])

// Tambah 'Sebagian Kembali' di statusOptions
const statusOptions = [
  { label: 'Semua', value: '', color: '#94a3b8' },
  { label: 'Menunggu', value: 'Menunggu', color: '#d97706' },
  { label: 'Aktif', value: 'Aktif', color: '#16a34a' },
  { label: 'Telat', value: 'Telat', color: '#dc2626' },
  { label: 'Sebagian Kembali', value: 'Sebagian Kembali', color: '#f59e0b' },
  { label: 'Selesai', value: 'Selesai', color: '#6366f1' },
  { label: 'Rusak/Hilang', value: 'Rusak/Hilang', color: '#c2410c' },
  { label: 'Batal', value: 'Batal', color: '#64748b' },
]

// filteredPeminjamans — tambah case 'Sebagian Kembali'
const filteredPeminjamans = computed(() => {
  let list = store.peminjamans

  if (filters.value.status) {
    if (filters.value.status === 'Telat') {
      list = list.filter(p => isOverdue(p))
    } else if (filters.value.status === 'Aktif') {
      list = list.filter(p => p.status === 'Aktif' && !isOverdue(p))
    } else {
      list = list.filter(p => p.status === filters.value.status)
    }
  }

  if (filters.value.search.trim()) {
    const kw = filters.value.search.toLowerCase()
    list = list.filter(p =>
      (p.warga?.nama_warga ?? p.warga?.nama ?? '').toLowerCase().includes(kw) ||
      (p.barang?.nama_barang ?? p.barang?.nama ?? '').toLowerCase().includes(kw)
    )
  }

  return list
})

// doFetch — tambah 'Sebagian Kembali' dikirim langsung ke backend
function doFetch(page = 1) {
  const params = { page, per_page: 15 }
  if (filters.value.status === 'Telat' || filters.value.status === 'Aktif') {
    params.status = 'Aktif'
  } else if (filters.value.status) {
    params.status = filters.value.status
  }
  if (filters.value.search) params.search = filters.value.search
  store.fetchPeminjamans(params)
}

function setStatus(val) {
  filters.value.status = val
  doFetch()
}

function goPage(page) { doFetch(page) }

// ── Toast ─────────────────────────────────────────────────────────────────
let toastTimer = null
function showToast(type, message) {
  clearTimeout(toastTimer)
  toast.value = { show: true, type, message }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// ── Aksi ──────────────────────────────────────────────────────────────────
async function handleKonfirmasi(p) {
  actionLoading.value = p.id
  try {
    await store.konfirmasiPeminjaman(p.id)
    showToast('success', `Peminjaman ${p.warga?.nama_warga ?? p.warga?.nama_warga} berhasil dikonfirmasi.`)
  } catch (e) {
    showToast('error', e?.response?.data?.message ?? 'Gagal mengonfirmasi.')
  } finally { actionLoading.value = null }
}

function openBatal(p) { batalTarget.value = p; showBatal.value = true }

async function submitBatal() {
  actionLoading.value = batalTarget.value.id
  try {
    await store.batalPeminjaman(batalTarget.value.id)
    showToast('success', 'Peminjaman berhasil dibatalkan.')
    showBatal.value = false
  } catch (e) {
    showToast('error', e?.response?.data?.message ?? 'Gagal membatalkan.')
  } finally { actionLoading.value = null }
}

onMounted(() => {
  store.fetchAllPeminjamans()
  doFetch()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

.page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 28px 32px;
  min-height: 100vh;
  background: #F4F6F9;
  color: #1a1f2e;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: #1e3a5f;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.header-icon svg {
  width: 22px;
  height: 22px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.page-sub {
  font-size: 13px;
  color: #7a8499;
  margin: 2px 0 0;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #1e3a5f;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  text-decoration: none;
  transition: background .2s;
}

.btn-primary svg {
  width: 16px;
  height: 16px;
}

.btn-primary:hover {
  background: #162d4a;
}

/* Stats */
.stats-row {
  display: flex;
  gap: 14px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.qty-cell {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
}

.qty-partial {
  font-size: 11px;
  color: #d97706;
  font-weight: 600;
  font-family: 'DM Mono', monospace;
}

.stat-card {
  flex: 1;
  min-width: 90px;
  background: #fff;
  border-radius: 12px;
  padding: 16px 20px;
  border: 1px solid #e8ecf4;
  position: relative;
  overflow: hidden;
}

.stat-num {
  display: block;
  font-size: 26px;
  font-weight: 700;
  color: #1a1f2e;
  font-family: 'DM Mono', monospace;
}

.stat-label {
  font-size: 12px;
  color: #7a8499;
  font-weight: 500;
}

.stat-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--accent);
  opacity: .7;
}

/* Toolbar */
.toolbar {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.search-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #e8ecf4;
  border-radius: 10px;
  padding: 9px 14px;
  flex: 1;
  max-width: 320px;
  transition: border-color .2s;
}

.search-wrap:focus-within {
  border-color: #1e3a5f;
}

.search-wrap svg {
  width: 16px;
  height: 16px;
  color: #a0aec0;
  flex-shrink: 0;
}

.search-wrap input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-family: inherit;
  color: #1a1f2e;
  width: 100%;
}

.search-wrap input::placeholder {
  color: #a0aec0;
}

.filter-chips {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.chip {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 500;
  border: 1.5px solid #e8ecf4;
  background: #fff;
  color: #7a8499;
  cursor: pointer;
  font-family: inherit;
  transition: all .15s;
}

.chip.active {
  background: #1e3a5f;
  color: #fff;
  border-color: #1e3a5f;
}

.chip:not(.active):hover {
  border-color: #c4ccdb;
  color: #1a1f2e;
}

.chip-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Table */
.table-card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8ecf4;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #e8ecf4;
}

.data-table th {
  padding: 13px 18px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 600;
  color: #7a8499;
  text-transform: uppercase;
  letter-spacing: .5px;
}

.th-right {
  text-align: right;
}

.data-row {
  border-bottom: 1px solid #f1f4f9;
  animation: fadeUp .3s ease both;
  transition: background .15s;
}

.data-row:last-child {
  border-bottom: none;
}

.data-row:hover {
  background: #f8fafd;
}

.data-table td {
  padding: 13px 18px;
  vertical-align: middle;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.person-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}

.person-name {
  font-size: 14px;
  font-weight: 600;
  color: #1a1f2e;
}

.person-sub {
  font-size: 12px;
  color: #a0aec0;
  font-family: 'DM Mono', monospace;
}

.barang-name {
  font-size: 14px;
  color: #3d4a5c;
  font-weight: 500;
}

.qty-badge {
  display: inline-block;
  background: #f1f4f9;
  border-radius: 6px;
  padding: 2px 10px;
  font-size: 13px;
  font-weight: 700;
  font-family: 'DM Mono', monospace;
  color: #1a1f2e;
}

.date-text {
  font-size: 13px;
  color: #7a8499;
}

.overdue {
  color: #dc2626 !important;
  font-weight: 600;
}

/* Actions */
.action-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 5px;
}

.act-btn {
  width: 30px;
  height: 30px;
  border-radius: 7px;
  border: 1.5px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all .15s;
  background: transparent;
  text-decoration: none;
}

.act-btn svg {
  width: 14px;
  height: 14px;
}

.act-btn:disabled {
  opacity: .35;
  cursor: not-allowed;
}

.act-view {
  color: #2563eb;
  border-color: #dbeafe;
}

.act-view:hover {
  background: #eff6ff;
}

.act-confirm {
  color: #16a34a;
  border-color: #dcfce7;
}

.act-confirm:hover {
  background: #f0fdf4;
}

.act-edit {
  color: #d97706;
  border-color: #fef3c7;
}

.act-edit:hover {
  background: #fffbeb;
}

.act-batal {
  color: #dc2626;
  border-color: #fee2e2;
}

.act-batal:hover {
  background: #fef2f2;
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 16px;
  border-top: 1px solid #f1f4f9;
}

.pg-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e8ecf4;
  background: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #7a8499;
  transition: all .15s;
}

.pg-btn:disabled {
  opacity: .35;
  cursor: not-allowed;
}

.pg-btn:not(:disabled):hover {
  border-color: #1e3a5f;
  color: #1e3a5f;
}

.pg-btn svg {
  width: 14px;
  height: 14px;
}

.pg-info {
  font-size: 13px;
  font-weight: 500;
  color: #7a8499;
}

/* Skeleton */
.skeleton-wrap {
  padding: 8px 0;
}

.skel-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-bottom: 1px solid #f1f4f9;
}

.skel-avatar {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  flex-shrink: 0;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.skel-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skel-line {
  height: 10px;
  border-radius: 4px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.w55 {
  width: 55%;
}

.w35 {
  width: 35%;
}

.w70 {
  width: 70px;
}

.skel-pill {
  width: 70px;
  height: 22px;
  border-radius: 20px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.skel-btns {
  width: 90px;
  height: 22px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* Empty */
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 64px 24px;
  gap: 8px;
}

.empty-ico {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: #f1f4f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c4ccdb;
  margin-bottom: 6px;
}

.empty-ico svg {
  width: 28px;
  height: 28px;
}

.empty-title {
  font-size: 15px;
  font-weight: 600;
  color: #3d4a5c;
  margin: 0;
}

.empty-sub {
  font-size: 13px;
  color: #9aa3b5;
  margin: 0;
}

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 25, 50, .5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
  overflow: hidden;
}

.modal-sm {
  max-width: 390px;
}

.modal-body {
  padding: 24px;
}

.confirm-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px;
}

.confirm-ico {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.confirm-ico.danger {
  background: #fef2f2;
  color: #dc2626;
}

.confirm-ico svg {
  width: 26px;
  height: 26px;
}

.confirm-title {
  font-size: 17px;
  font-weight: 700;
  color: #1a1f2e;
  margin: 0;
}

.confirm-desc {
  font-size: 14px;
  color: #7a8499;
  margin: 0;
  line-height: 1.6;
}

.modal-footer {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
  width: 100%;
}

.btn-cancel {
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  border: 1.5px solid #e8ecf4;
  background: #fff;
  color: #7a8499;
  cursor: pointer;
  font-family: inherit;
}

.btn-cancel:hover {
  border-color: #c4ccdb;
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  background: #1e3a5f;
  color: #fff;
  cursor: pointer;
  font-family: inherit;
}

.btn-submit.btn-danger {
  background: #dc2626;
}

.btn-submit:disabled {
  opacity: .6;
  cursor: not-allowed;
}

.spinner {
  width: 13px;
  height: 13px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, .3);
  border-top-color: #fff;
  animation: spin .7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Toast */
.toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  font-family: inherit;
  box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
  max-width: 340px;
}

.toast svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.toast-success {
  background: #1a1f2e;
  color: #fff;
}

.toast-error {
  background: #dc2626;
  color: #fff;
}

/* Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity .2s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.toast-enter-active {
  transition: all .3s cubic-bezier(.34, 1.4, .64, 1);
}

.toast-leave-active {
  transition: all .2s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

@media (max-width: 768px) {
  .page {
    padding: 16px;
  }

  .stat-card {
    min-width: calc(33% - 10px);
  }

  .data-table th:nth-child(4),
  .data-table td:nth-child(4),
  .data-table th:nth-child(5),
  .data-table td:nth-child(5) {
    display: none;
  }
}
</style>