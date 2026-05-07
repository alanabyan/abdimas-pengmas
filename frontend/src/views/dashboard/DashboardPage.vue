<template>
  <div class="page">

    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <LayoutGrid :size="22" />
        </div>
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-sub">Selamat datang, <strong>{{ authStore.user?.nama_marbot ?? 'Marbot' }}</strong> — {{
            hariIni }}</p>
        </div>
      </div>
      <button class="btn-refresh" :class="{ spinning: refreshing }" @click="loadAll(true)" title="Refresh data">
        <RefreshCw :size="16" />
      </button>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div v-for="(card, idx) in statCards" :key="card.key" class="stat-card" :class="card.variant"
        :style="{ animationDelay: `${idx * 50}ms` }">
        <div class="stat-card-top">
          <div class="stat-icon-wrap">
            <component :is="card.icon" :size="18" />
          </div>
        </div>
        <div class="stat-num">
          <span v-if="loading" class="skel-num"></span>
          <span v-else>{{ stat[card.key] ?? 0 }}</span>
        </div>
        <div class="stat-label">{{ card.label }}</div>
        <div class="stat-bar-line" :style="{ background: card.color }"></div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">

      <!-- Bar Chart: Tren Peminjaman per Kategori -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Tren Peminjaman per Kategori</h3>
            <p class="chart-sub">12 bulan terakhir</p>
          </div>
          <!-- Legend dinamis dari data -->
          <div class="chart-legend-wrap">
            <template v-if="!loading && grafikPeminjaman.length">
              <div v-for="(kat, i) in grafikPeminjaman" :key="i" class="legend-item">
                <span class="legend-dot" :style="{ background: barColors[i % barColors.length] }"></span>
                <span class="legend-label">{{ kat.label }}</span>
              </div>
            </template>
          </div>
        </div>
        <div class="chart-body">
          <div v-if="loading" class="chart-skel">
            <div v-for="i in 12" :key="i" class="skel-bar" :style="{ height: `${35 + (i * 17) % 55}%` }"></div>
          </div>
          <div v-else-if="!grafikPeminjaman.length" class="chart-empty">
            <TrendingUp :size="32" class="empty-ico" />
            <span>Belum ada data peminjaman.</span>
          </div>
          <div v-else class="bar-chart-wrap">
            <canvas ref="barChartCanvas" style="width:100%; height:200px;"></canvas>
          </div>
        </div>
      </div>

      <!-- Bar Chart: Barang per Kategori -->
      <div class="chart-card chart-narrow">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Barang per Kategori</h3>
            <p class="chart-sub">Distribusi inventaris</p>
          </div>
        </div>
        <div class="chart-body overflow-y-scroll">
          <div v-if="loading" class="cat-skel">
            <div v-for="i in 5" :key="i" class="skel-cat-row">
              <div class="skel-line w45"></div>
              <div class="skel-line w80"></div>
            </div>
          </div>
          <div v-else-if="!grafikBarang.length" class="chart-empty">
            <Package :size="32" class="empty-ico" />
            <span>Belum ada data barang.</span>
          </div>
          <div v-else class="bar-list">
            <div v-for="(item, i) in grafikBarangSorted" :key="i" class="bar-row"
              :style="{ animationDelay: `${i * 60}ms` }">
              <div class="bar-info">
                <span class="bar-label">{{ item.label }}</span>
                <span class="bar-count mono">{{ item.jumlah_barang }}</span>
              </div>
              <div class="bar-track">
                <div class="bar-fill" :style="{
                  width: `${(item.jumlah_barang / maxBarang) * 100}%`,
                  background: barColors[i % barColors.length],
                }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Quick Links -->
    <div class="quick-section">
      <h3 class="quick-title">Akses Cepat</h3>
      <div class="quick-grid">
        <router-link v-for="(q, idx) in quickLinks" :key="q.to" :to="q.to" class="quick-card"
          :style="{ animationDelay: `${100 + idx * 40}ms` }">
          <div class="quick-icon" :style="{ background: q.bg, color: q.color }">
            <component :is="q.icon" :size="16" />
          </div>
          <span class="quick-label">{{ q.label }}</span>
          <ChevronRight :size="14" class="quick-arrow" />
        </router-link>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import dashboardService from '@/services/dashboardService'

import {
  LayoutGrid,
  RefreshCw,
  Package,
  Tag,
  Users,
  ClipboardCheck,
  Clock,
  AlertTriangle,
  TrendingUp,
  ChevronRight,
  Plus,
  ClipboardList,
  Box,
  UserCheck,
  Settings,
  RotateCcw,
} from 'lucide-vue-next'

import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const authStore = useAuthStore()

const loading = ref(false)
const refreshing = ref(false)

const stat = ref({})
const grafikBarang = ref([])
const grafikPeminjaman = ref([])

const barChartCanvas = ref(null)
let barChartInstance = null

// ── Tanggal ───────────────────────────────────────────────────────────────
const hariIni = new Date().toLocaleDateString('id-ID', {
  weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
})

// ── Stat Cards ────────────────────────────────────────────────────────────
const statCards = [
  { key: 'total_barang', label: 'Total Barang', variant: 'card-blue', color: '#1e3a5f', icon: Package },
  { key: 'total_kategori', label: 'Kategori', variant: 'card-indigo', color: '#6366f1', icon: Tag },
  { key: 'total_warga', label: 'Total Warga', variant: 'card-teal', color: '#0891b2', icon: Users },
  { key: 'peminjaman_aktif', label: 'Peminjaman Aktif', variant: 'card-green', color: '#16a34a', icon: ClipboardCheck },
  { key: 'peminjaman_terlambat', label: 'Terlambat', variant: 'card-amber', color: '#d97706', icon: Clock },
  { key: 'barang_rusak_hilang', label: 'Rusak / Hilang', variant: 'card-red', color: '#dc2626', icon: AlertTriangle },
]

// ── Quick Links ───────────────────────────────────────────────────────────
const quickLinks = [
  { to: '/peminjaman/tambah', label: 'Tambah Peminjaman', bg: '#eff6ff', color: '#1e3a5f', icon: Plus },
  { to: '/peminjaman', label: 'Daftar Peminjaman', bg: '#f0fdf4', color: '#16a34a', icon: ClipboardList },
  { to: '/barang', label: 'Kelola Barang', bg: '#faf5ff', color: '#7c3aed', icon: Box },
  { to: '/warga', label: 'Data Warga', bg: '#ecfeff', color: '#0891b2', icon: UserCheck },
  { to: '/marbot', label: 'Manajemen Marbot', bg: '#fff7ed', color: '#d97706', icon: Settings },
  { to: '/pengembalian', label: 'Validasi Pengembalian', bg: '#fef2f2', color: '#dc2626', icon: RotateCcw },
]

// ── Warna palette ─────────────────────────────────────────────────────────
const barColors = ['#1e3a5f', '#6366f1', '#0891b2', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#be185d', '#0f766e']

// ── Bar chart inventaris ───────────────────────────────────────────────────
const grafikBarangSorted = computed(() =>
  [...grafikBarang.value].sort((a, b) => b.jumlah_barang - a.jumlah_barang),
)
const maxBarang = computed(() =>
  Math.max(...grafikBarangSorted.value.map(i => i.jumlah_barang), 1),
)

// ── Helper: format label bulan ────────────────────────────────────────────
function formatBulan(label) {
  if (!label) return ''
  const [, m] = label.split('-')
  return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][parseInt(m) - 1] ?? label
}

// ── Render grouped bar chart ──────────────────────────────────────────────
async function renderBarChart() {
  await nextTick()
  if (!barChartCanvas.value || !grafikPeminjaman.value.length) return

  if (barChartInstance) {
    barChartInstance.destroy()
    barChartInstance = null
  }

  // Ambil label bulan dari dataset pertama
  const labels = grafikPeminjaman.value[0]?.data.map(d => formatBulan(d.bulan)) ?? []

  const datasets = grafikPeminjaman.value.map((kat, i) => {
    const color = barColors[i % barColors.length]
    return {
      label: kat.label,
      data: kat.data.map(d => d.total),
      backgroundColor: color + 'cc',   // sedikit transparan
      borderColor: color,
      borderWidth: 1.5,
      borderRadius: 4,
      borderSkipped: false,
    }
  })

  barChartInstance = new Chart(barChartCanvas.value, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} peminjaman`,
          },
        },
      },
      scales: {
        x: {
          stacked: true,
          grid: { display: false },
          ticks: {
            color: '#a0aec0',
            font: { size: 11 },
            autoSkip: false,
            maxRotation: 0,
          },
          border: { display: false },
        },
        y: {
          stacked: true,
          beginAtZero: true,
          grid: { color: '#f1f4f9', lineWidth: 1 },
          ticks: { color: '#a0aec0', font: { size: 11 }, precision: 0 },
          border: { display: false },
        },
      },
    },
  })
}

// Re-render chart saat data berubah
watch(grafikPeminjaman, () => {
  if (grafikPeminjaman.value.length) renderBarChart()
}, { deep: true })

// ── Fetch ─────────────────────────────────────────────────────────────────
async function loadAll(isRefresh = false) {
  if (isRefresh) refreshing.value = true
  else loading.value = true

  try {
    const [s, b, p] = await Promise.all([
      dashboardService.getStatistik(),
      dashboardService.getGrafikBarang(),
      dashboardService.getGrafikPeminjaman(),
    ])
    stat.value = s
    grafikBarang.value = b
    grafikPeminjaman.value = p
  } catch { /* silent */ }
  finally {
    loading.value = false
    refreshing.value = false
  }
}

onMounted(() => loadAll())

onBeforeUnmount(() => {
  if (barChartInstance) barChartInstance.destroy()
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

/* ── Header ─────────────────────────────────────────────────────────────── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 26px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: #16a34a;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
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

.page-sub strong {
  color: #1a1f2e;
}

.btn-refresh {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1.5px solid #e8ecf4;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #7a8499;
  transition: all .2s;
}

.btn-refresh:hover {
  border-color: #1e3a5f;
  color: #1e3a5f;
}

.btn-refresh.spinning :deep(svg) {
  animation: spin .8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ── Stat Cards ─────────────────────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 14px;
  margin-bottom: 22px;
}

.stat-card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8ecf4;
  padding: 18px 20px 14px;
  position: relative;
  overflow: hidden;
  transition: transform .15s, box-shadow .15s;
  animation: fadeUp .4s ease both;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, .07);
}

.stat-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 12px;
}

.stat-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-blue .stat-icon-wrap {
  background: #eff6ff;
  color: #1e3a5f;
}

.card-indigo .stat-icon-wrap {
  background: #eef2ff;
  color: #6366f1;
}

.card-teal .stat-icon-wrap {
  background: #ecfeff;
  color: #0891b2;
}

.card-green .stat-icon-wrap {
  background: #f0fdf4;
  color: #16a34a;
}

.card-amber .stat-icon-wrap {
  background: #fffbeb;
  color: #d97706;
}

.card-red .stat-icon-wrap {
  background: #fef2f2;
  color: #dc2626;
}

.stat-num {
  font-size: 28px;
  font-weight: 700;
  color: #1a1f2e;
  font-family: 'DM Mono', monospace;
  line-height: 1;
  margin-bottom: 4px;
  min-height: 34px;
  display: flex;
  align-items: center;
}

.skel-num {
  display: inline-block;
  width: 60px;
  height: 28px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.stat-label {
  font-size: 12px;
  color: #7a8499;
  font-weight: 500;
}

.stat-bar-line {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  opacity: .7;
}

/* ── Charts Row ─────────────────────────────────────────────────────────── */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 16px;
  margin-bottom: 22px;
}

.chart-card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8ecf4;
  overflow: hidden;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 20px 22px 0;
  gap: 12px;
}

.chart-title {
  font-size: 15px;
  font-weight: 700;
  color: #16a34a;
  margin: 0;
}

.chart-sub {
  font-size: 12px;
  color: #a0aec0;
  margin: 2px 0 0;
}

/* Legend dinamis */
.chart-legend-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 14px;
  justify-content: flex-end;
  max-width: 320px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 2px;
  flex-shrink: 0;
}

.legend-label {
  font-size: 11px;
  color: #7a8499;
  white-space: nowrap;
}

.chart-body {
  padding: 16px 22px 20px;
}

.chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #c4ccdb;
  font-size: 13px;
  padding: 32px 0;
}

.empty-ico {
  color: #d8dde8;
}

/* Bar chart wrapper */
.bar-chart-wrap {
  position: relative;
  height: 200px;
}

.bar-chart-wrap canvas {
  width: 100% !important;
  height: 200px !important;
}

/* Skeleton bar chart */
.chart-skel {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  height: 200px;
  padding-bottom: 4px;
}

.skel-bar {
  flex: 1;
  border-radius: 4px 4px 0 0;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

/* Bar list (inventaris) */
.bar-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  height: 18.4rem;
}

.bar-row {
  animation: fadeUp .4s ease both;
}

.bar-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.bar-label {
  font-size: 13px;
  font-weight: 500;
  color: #3d4a5c;
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.bar-count {
  font-size: 13px;
  font-weight: 700;
  font-family: 'DM Mono', monospace;
  color: #1a1f2e;
}

.bar-track {
  height: 6px;
  background: #f1f4f9;
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width .8s cubic-bezier(.34, 1.4, .64, 1);
}

/* Skeleton inventaris */
.cat-skel {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.skel-cat-row {
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

.w45 {
  width: 45%;
}

.w80 {
  width: 80%;
}

/* ── Quick Links ─────────────────────────────────────────────────────────── */
.quick-section {
  margin-bottom: 8px;
}

.quick-title {
  font-size: 13px;
  font-weight: 700;
  color: #7a8499;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin: 0 0 12px;
}

.quick-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
}

.quick-card {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e8ecf4;
  padding: 14px 16px;
  text-decoration: none;
  color: #1a1f2e;
  transition: transform .15s, box-shadow .15s, border-color .15s;
  animation: fadeUp .4s ease both;
}

.quick-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, .07);
  border-color: #c4ccdb;
}

.quick-icon {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.quick-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #3d4a5c;
  flex: 1;
  line-height: 1.3;
}

.quick-arrow {
  color: #c4ccdb;
  flex-shrink: 0;
  transition: color .15s, transform .15s;
}

.quick-card:hover .quick-arrow {
  color: #7a8499;
  transform: translateX(2px);
}

/* ── Shared ──────────────────────────────────────────────────────────────── */
.mono {
  font-family: 'DM Mono', monospace;
}

.overflow-y-scroll {
  overflow-y: auto;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .quick-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 900px) {
  .charts-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .page {
    padding: 16px;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .quick-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .chart-legend-wrap {
    max-width: 100%;
  }
}
</style>