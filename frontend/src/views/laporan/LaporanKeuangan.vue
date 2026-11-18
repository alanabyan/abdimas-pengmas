<template>
  <div class="laporan-page">
    <div class="page-top">
      <div class="breadcrumb">
        <RouterLink :to="basePath" class="bc-link">Laporan</RouterLink>
        <span class="bc-sep">›</span>
        <span class="bc-current">Laporan Keuangan</span>
      </div>

      <div class="page-title-row">
        <div>
          <h1 class="pg-title">Laporan Keuangan</h1>
          <p class="pg-sub">Ringkasan infaq, qurban, dan transaksi terbaru bendahara.</p>
        </div>

        <div class="toolbar">
          <label class="month-picker">
            <span>Bulan</span>
            <input v-model="selectedMonth" type="month" />
          </label>
          <button type="button" class="export-button" @click="exportCsv">Ekspor CSV</button>
          <button type="button" class="export-button pdf-button" @click="exportPdf">Ekspor PDF</button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="state-center">
      <span class="spinner-lg"></span>
      <p>Memuat laporan keuangan...</p>
    </div>

    <template v-else>
      <div class="summary-grid">
        <div class="stat-card green-card">
          <span>Total Infaq</span>
          <strong>{{ formatCurrency(report.summary.total_infaq || 0) }}</strong>
        </div>
        <div class="stat-card blue-card">
          <span>Total Qurban</span>
          <strong>{{ formatCurrency(report.summary.total_qurban || 0) }}</strong>
        </div>
        <div class="stat-card amber-card">
          <span>Total Keseluruhan</span>
          <strong>{{ formatCurrency(report.summary.total_keseluruhan || 0) }}</strong>
        </div>
      </div>

      <div class="panel-grid">
        <div class="panel">
          <div class="panel-head">
            <h3>Rekap 6 bulan</h3>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Bulan</th>
                  <th>Infaq</th>
                  <th>Qurban</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in report.monthly" :key="row.month">
                  <td>{{ row.label }}</td>
                  <td>{{ formatCurrency(row.infaq || 0) }}</td>
                  <td>{{ formatCurrency(row.qurban || 0) }}</td>
                  <td>{{ formatCurrency(row.total || 0) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head">
            <h3>Transaksi terbaru</h3>
          </div>

          <ul class="transaction-list">
            <li v-for="item in report.transactions" :key="`${item.type}-${item.id}`">
              <div>
                <span class="badge" :class="item.type === 'qurban' ? 'badge-qurban' : 'badge-infaq'">
                  {{ item.type === 'qurban' ? 'Qurban' : 'Infaq' }}
                </span>
                <p>{{ item.nama }}</p>
              </div>
              <div class="amount-block">
                <strong>{{ formatCurrency(item.jumlah || 0) }}</strong>
                <small>{{ item.status }}</small>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import infaqService from '@/services/infaqService'

const route = useRoute()
const loading = ref(false)
const report = ref({ summary: {}, monthly: [], transactions: [] })
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const basePath = computed(() => route.path.startsWith('/bendahara') ? '/bendahara/laporan' : '/laporan')

const selectedMonthLabel = computed(() => {
  if (!selectedMonth.value) return 'semua periode'
  return new Date(`${selectedMonth.value}-01T00:00:00`).toLocaleDateString('id-ID', {
    month: 'long',
    year: 'numeric',
  })
})

async function loadReport() {
  loading.value = true

  try {
    const data = await infaqService.getReport(selectedMonth.value)
    report.value = data || report.value
  } catch (error) {
    console.error('Gagal memuat laporan keuangan', error)
  } finally {
    loading.value = false
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
}

function exportCsv() {
  const rows = [
    ['Periode', selectedMonthLabel.value],
    ['Kategori', 'Total'],
    ['Infaq', report.value.summary.total_infaq || 0],
    ['Qurban', report.value.summary.total_qurban || 0],
    ['Keseluruhan', report.value.summary.total_keseluruhan || 0],
    [],
    ['Bulan', 'Infaq', 'Qurban', 'Total'],
    ...((report.value.monthly || []).map((row) => [row.label, row.infaq || 0, row.qurban || 0, row.total || 0])),
  ]

  const csv = rows.map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const anchor = document.createElement('a')
  anchor.href = url
  anchor.download = `laporan-keuangan-${selectedMonth.value || 'semua-periode'}.csv`
  anchor.click()
  URL.revokeObjectURL(url)
}

async function exportPdf() {
  try {
    const response = await infaqService.getReportPdf(selectedMonth.value)
    const blob = new Blob([response], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    const anchor = document.createElement('a')
    anchor.href = url
    anchor.download = `laporan-keuangan-${selectedMonth.value || 'semua-periode'}.pdf`
    anchor.click()
    URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Gagal mengekspor PDF laporan keuangan', error)
  }
}

watch(selectedMonth, () => {
  loadReport()
})

onMounted(() => {
  loadReport()
})
</script>

<style scoped>
.laporan-page {
  font-family: 'DM Sans', 'Inter', sans-serif;
}
.page-top {
  margin-bottom: 20px;
}
.breadcrumb {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  font-size: 11px;
  color: #6b7280;
}
.bc-link { color: #176b4b; text-decoration: none; font-weight: 700; }
.bc-sep { color: #9ca3af; }
.bc-current { color: #374151; }
.page-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.pg-title {
  margin: 0;
  font-size: 20px;
  font-weight: 800;
  color: #111827;
}
.pg-sub {
  margin: 6px 0 0;
  color: #6b7280;
  font-size: 13px;
}
.toolbar {
  display: flex;
  align-items: end;
  gap: 12px;
  flex-wrap: wrap;
}
.month-picker {
  display: grid;
  gap: 6px;
  color: #374151;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}
.month-picker input {
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  background: #fff;
  color: #111827;
  font-size: 12px;
}
.export-button {
  border: 0;
  border-radius: 10px;
  padding: 9px 14px;
  background: linear-gradient(135deg, #176b4b 0%, #1d7a5b 100%);
  color: white;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
  box-shadow: 0 6px 16px rgba(23, 107, 75, 0.18);
}

.export-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(23, 107, 75, 0.2);
  filter: brightness(1.04);
}

.export-button:active {
  transform: translateY(0);
  box-shadow: 0 4px 10px rgba(23, 107, 75, 0.14);
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}
.stat-card {
  background: white;
  border: 1px solid #dfe7e0;
  border-radius: 16px;
  padding: 18px 16px;
}
.stat-card span {
  display: block;
  font-size: 9px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.stat-card strong {
  display: block;
  margin-top: 10px;
  font-size: 24px;
  font-weight: 800;
  color: #111827;
}
.green-card { background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); }
.blue-card { background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); }
.amber-card { background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); }
.panel-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 20px;
}
.panel {
  background: white;
  border: 1px solid #dfe7e0;
  border-radius: 16px;
  padding: 18px;
}
.panel-head h3 {
  margin: 0 0 12px;
  font-size: 15px;
  font-weight: 800;
  color: #111827;
}
.table-wrap {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 10px 12px;
  border-bottom: 1px solid #edf0ee;
  text-align: left;
  font-size: 12px;
  color: #374151;
}
th {
  color: #6b7280;
  font-size: 9px;
  text-transform: uppercase;
  background: #f8fafc;
}
.transaction-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 12px;
}
.transaction-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #edf0ee;
}
.transaction-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}
.badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
}
.badge-infaq {
  background: #ecfdf5;
  color: #166534;
}
.badge-qurban {
  background: #eff6ff;
  color: #1d4ed8;
}
.transaction-list p {
  margin: 8px 0 0;
  color: #111827;
  font-size: 12px;
  font-weight: 600;
}
.amount-block {
  text-align: right;
}
.amount-block strong {
  display: block;
  font-size: 12px;
  color: #111827;
}
.amount-block small {
  color: #6b7280;
  font-size: 10px;
  text-transform: capitalize;
}
.state-center {
  display: grid;
  place-items: center;
  min-height: 220px;
  background: white;
  border: 1px solid #dfe7e0;
  border-radius: 16px;
  color: #6b7280;
}
.spinner-lg {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(23, 107, 75, 0.15);
  border-top-color: #176b4b;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 12px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@media (max-width: 860px) {
  .summary-grid,
  .panel-grid {
    grid-template-columns: 1fr;
  }
}
</style>
