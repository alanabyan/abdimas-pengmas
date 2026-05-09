<template>
  <div class="page">

    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 12l2 2 4-4" />
            <path d="M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-6l-2-2H5a2 2 0 0 0-2 2z" />
          </svg>
        </div>
        <div>
          <h1 class="page-title">Pengembalian Barang</h1>
          <p class="page-sub">Daftar warga yang belum mengembalikan barang inventaris</p>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card" v-for="s in stats" :key="s.label" :style="{ '--accent': s.color }">
        <span class="stat-num">{{ s.value }}</span>
        <span class="stat-label">{{ s.label }}</span>
        <div class="stat-bar"></div>
      </div>
    </div>

    <!-- Table card -->
    <div class="table-card">

      <!-- Loading skeleton -->
      <div v-if="loading" class="skeleton-wrap">
        <div v-for="i in 5" :key="i" class="skel-row">
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
      <div v-else-if="listData.length === 0" class="empty">
        <div class="empty-ico">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
        </div>
        <p class="empty-title">Tidak ada tanggungan pengembalian</p>
        <p class="empty-sub">Semua barang telah dikembalikan</p>
      </div>

      <!-- Table -->
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Peminjam</th>
            <th>Barang</th>
            <th>Jml</th>
            <th>Tgl Pinjam</th>
            <th>Batas Kembali</th>
            <th class="th-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in listData" :key="item.id" class="data-row"
            :style="{ animationDelay: `${idx * 35}ms` }">
            <!-- Peminjam -->
            <td>
              <div class="person-cell">
                <div class="avatar" :style="{ background: avatarColor(item.warga?.nama_warga) }">
                  {{ initials(item.warga?.nama_warga) }}
                </div>
                <div>
                  <div class="person-name">{{ item.warga?.nama_warga ?? '-' }}</div>
                  <div class="person-sub">{{ item.warga?.no_hp ?? '' }}</div>
                </div>
              </div>
            </td>

            <!-- Barang & Kategori -->
            <td>
              <div class="barang-name">{{ item.barang?.nama_barang ?? '-' }}</div>
            </td>

            <!-- Jumlah -->
            <td>
              <div class="qty-cell">
                <span class="qty-badge">{{ item.jumlah }}</span>
                <span v-if="item.status === 'Sebagian Kembali' && item.jumlah_kembali" class="qty-partial">
                  {{ item.jumlah_kembali }}/{{ item.jumlah }} kembali
                </span>
              </div>
            </td>

            <!-- Tgl Pinjam -->
            <td>
              <span class="date-text">{{ fmtDate(item.tgl_pinjam) }}</span>
            </td>

            <!-- Batas Kembali -->
            <td>
              <span class="date-text" :class="{ overdue: isOverdue(item) }">
                {{ fmtDate(item.tgl_rencana_kembali) }}
                <span v-if="isOverdue(item)" class="overdue-tag">Telat</span>
              </span>
            </td>

            <!-- Aksi -->
            <td>
              <div class="action-group">
                <button class="act-btn act-proses" title="Proses Kembali" @click="bukaValidasi(item)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 .49-3.5" />
                  </svg>
                  Proses Kembali
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal ValidasiKembali -->
    <ValidasiKembali v-if="isModalOpen" :dataPinjam="selectedItem" @close="isModalOpen = false" @success="fetchData" />

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
import ValidasiKembali from '@/views/pengembalian/ValidasiKembali.vue'

const store = usePeminjamanStore()
const isModalOpen = ref(false)
const selectedItem = ref(null)
const toast = ref({ show: false, type: 'success', message: '' })

const listData = computed(() => store.pengembalians)
const loading = computed(() => store.loading)

// ── Stats ─────────────────────────────────────────────────────────────────
const stats = computed(() => [
  { label: 'Belum Kembali', value: listData.value.length, color: '#1e3a5f' },
  { label: 'Terlambat', value: listData.value.filter(p => isOverdue(p)).length, color: '#dc2626' },
  { label: 'Tepat Waktu', value: listData.value.filter(p => !isOverdue(p)).length, color: '#16a34a' },
])

// ── Helpers ───────────────────────────────────────────────────────────────
const COLORS = ['#1e3a5f', '#16a34a', '#d97706', '#6366f1', '#0891b2', '#be185d', '#7c3aed']

function avatarColor(name = '') {
  let h = 0
  for (const c of (name || '')) h = c.charCodeAt(0) + ((h << 5) - h)
  return COLORS[Math.abs(h) % COLORS.length]
}

function initials(name = '') {
  return (name || '').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

function fmtDate(d) {
  return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
}

function isOverdue(item) {
  return item.tgl_rencana_kembali && new Date(item.tgl_rencana_kembali) < new Date()
}

// ── Toast ─────────────────────────────────────────────────────────────────
let toastTimer = null
function showToast(type, message) {
  clearTimeout(toastTimer)
  toast.value = { show: true, type, message }
  toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// ── Fetch & aksi ──────────────────────────────────────────────────────────
const fetchData = async () => {
  try {
    await store.fetchPengembalians()
  } catch {
    showToast('error', 'Gagal memuat data pengembalian.')
  }
}

function bukaValidasi(item) {
  selectedItem.value = item
  isModalOpen.value = true
}

onMounted(fetchData)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

/* ── Base ── */
.page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 28px 32px;
  min-height: 100vh;
  background: #F4F6F9;
  color: #1a1f2e;
}

/* ── Page header ── */
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

/* ── Stats ── */
.stats-row {
  display: flex;
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  flex: 1;
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

/* ── Table card ── */
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

/* ── Cells ── */
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
  margin-bottom: 4px;
}

.kategori-badge {
  display: inline-block;
  background: #f1f4f9;
  color: #5f6a7a;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  text-transform: uppercase;
  letter-spacing: .4px;
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

.overdue-tag {
  display: inline-block;
  background: #fee2e2;
  color: #dc2626;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  padding: 1px 6px;
  margin-left: 4px;
  letter-spacing: .3px;
}

/* ── Action ── */
.action-group {
  display: flex;
  justify-content: flex-start;
}

.act-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  font-family: inherit;
  transition: all .15s;
}

.act-btn svg {
  width: 13px;
  height: 13px;
}

.act-proses {
  background: #fef3c7;
  color: #92400e;
  border: 1.5px solid #fde68a;
}

.act-proses:hover {
  background: #fde68a;
}

/* ── Empty ── */
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
  background: #f0fdf4;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #16a34a;
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

/* ── Skeleton ── */
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
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
  flex-shrink: 0;
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
  width: 110px;
  height: 32px;
  border-radius: 8px;
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

/* ── Toast ── */
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

/* ── Responsive ── */
@media (max-width: 768px) {
  .page {
    padding: 16px;
  }

  .stats-row {
    flex-wrap: wrap;
  }

  .stat-card {
    min-width: calc(50% - 7px);
  }

  .data-table th:nth-child(4),
  .data-table td:nth-child(4) {
    display: none;
  }
}
</style>