<template>
  <main class="dashboard-overview">
    <header class="page-header">
      <div>
        <p class="eyebrow">Ruang bendahara</p>
        <h1>Dashboard</h1>
      </div>
      <div class="date-chip">{{ hariIni }}</div>
    </header>

    <section class="stats-grid">
      <article v-for="card in statCards" :key="card.label" class="stat-card" :class="card.variant">
        <div class="stat-icon">
          <component :is="card.icon" :size="18" />
        </div>
        <div class="stat-label">{{ card.label }}</div>
        <div class="stat-value">{{ card.value }}</div>
      </article>
    </section>

    <section class="panel-grid">
      <div class="panel">
        <div class="panel-header">
          <h2>Ringkasan keuangan</h2>
        </div>
        <ul v-if="financeReport.monthly?.length" class="rows-list">
          <li v-for="item in financeReport.monthly" :key="item.month || item.label">
            <span>{{ item.label }}</span>
            <strong>{{ formatCurrency(item.total) }}</strong>
          </li>
        </ul>
        <div v-else class="empty-state">Belum ada data keuangan untuk bulan ini.</div>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h2>Transaksi terbaru</h2>
        </div>
        <ul v-if="financeReport.transactions?.length" class="rows-list compact">
          <li v-for="item in financeReport.transactions" :key="`${item.type}-${item.id}`">
            <div>
              <span class="transaction-type">{{ item.type }}</span>
              <small>{{ item.nama }}</small>
            </div>
            <strong>{{ formatCurrency(item.jumlah) }}</strong>
          </li>
        </ul>
        <div v-else class="empty-state">Belum ada transaksi yang tercatat.</div>
      </div>
    </section>

    <section class="panel qurban-panel">
      <div class="panel-header">
        <h2>Status qurban</h2>
      </div>
      <div class="status-grid">
        <div class="status-box success">
          <span>Sudah lunas</span>
          <strong>{{ qurbanRegistrations.filter(item => item.status === 'lunas').length }}</strong>
        </div>
        <div class="status-box info">
          <span>Terkonfirmasi</span>
          <strong>{{ qurbanRegistrations.filter(item => item.status === 'terkonfirmasi').length }}</strong>
        </div>
        <div class="status-box warning">
          <span>Menunggu</span>
          <strong>{{ qurbanRegistrations.filter(item => item.status === 'terdaftar').length }}</strong>
        </div>
        <div class="status-box danger">
          <span>Tagihan belum lunas</span>
          <strong>{{ formatCurrency(qurbanRegistrations.filter(item => item.status !== 'lunas').reduce((total, item) => total + Number(item.selisih_nominal || item.nominal_terbaru || item.jumlah || 0), 0)) }}</strong>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { CircleDollarSign, Landmark, PackageSearch, Wallet } from 'lucide-vue-next'
import infaqService from '@/services/infaqService'
import qurbanService from '@/services/qurbanService'

const financeReport = ref({ summary: null, monthly: [], transactions: [] })
const qurbanRegistrations = ref([])

const hariIni = new Date().toLocaleDateString('id-ID', {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
})

const statCards = computed(() => [
  { label: 'Total Infaq', value: formatCurrency(financeReport.value.summary?.total_infaq || 0), variant: 'blue', icon: Wallet },
  { label: 'Total Qurban', value: formatCurrency(financeReport.value.summary?.total_qurban || 0), variant: 'green', icon: CircleDollarSign },
  { label: 'Total Keseluruhan', value: formatCurrency(financeReport.value.summary?.total_keseluruhan || 0), variant: 'purple', icon: Landmark },
  { label: 'Menunggu Konfirmasi', value: String(qurbanRegistrations.value.filter(item => item.status === 'terdaftar').length || 0), variant: 'amber', icon: PackageSearch },
])

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

async function loadFinanceReport() {
  try {
    const response = await infaqService.getReport()
    financeReport.value = response || financeReport.value
  } catch (error) {
    console.error('Gagal memuat laporan keuangan', error)
  }
}

async function loadQurban() {
  try {
    const response = await qurbanService.getRegistrations()
    qurbanRegistrations.value = response.data || []
  } catch (error) {
    console.error('Gagal memuat ringkasan qurban', error)
  }
}

onMounted(() => {
  loadFinanceReport()
  loadQurban()
})
</script>

<style scoped>
.dashboard-overview {
  min-height: calc(100vh - 80px);
  padding: 28px 28px 42px;
  color: #1d2e26;
  font-family: 'DM Sans', sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #176b4b;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  font-size: clamp(28px, 3vw, 40px);
  line-height: 1.2;
  font-weight: 800;
}

.date-chip {
  padding: 10px 14px;
  border-radius: 999px;
  background: white;
  border: 1px solid #dfe9e2;
  color: #4b6259;
  font-size: 12px;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}

.stat-card {
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  background: white;
  padding: 18px 18px 16px;
  display: grid;
  gap: 14px;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  color: white;
}

.stat-card.blue .stat-icon { background: linear-gradient(135deg, #2f7fe8, #6cb4ff); }
.stat-card.green .stat-icon { background: linear-gradient(135deg, #0b8a64, #2bc38a); }
.stat-card.purple .stat-icon { background: linear-gradient(135deg, #7856d8, #b39aff); }
.stat-card.amber .stat-icon { background: linear-gradient(135deg, #d98e1f, #f9c567); }

.stat-label {
  color: #61756d;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.stat-value {
  font-size: clamp(20px, 2vw, 28px);
  font-weight: 800;
  color: #153728;
  line-height: 1.2;
}

.panel-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.panel {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  padding: 20px 18px;
}

.panel-header {
  margin-bottom: 14px;
}

.panel-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
}

.rows-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

.rows-list li {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #edf1ee;
  color: #49615a;
  font-size: 13px;
}

.rows-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.rows-list.compact li {
  align-items: flex-start;
}

.rows-list small {
  display: block;
  margin-top: 3px;
  color: #73857d;
}

.transaction-type {
  display: inline-block;
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #176b4b;
}

.empty-state {
  padding: 20px 10px 8px;
  color: #6d7e77;
  font-size: 13px;
}

.qurban-panel {
  margin-top: 0;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.status-box {
  border-radius: 16px;
  border: 1px solid #dfe9e2;
  padding: 16px 14px;
  display: grid;
  gap: 8px;
}

.status-box span {
  color: #65736b;
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
}

.status-box strong {
  font-size: 22px;
  line-height: 1.2;
  font-weight: 800;
}

.status-box.success { background: #ecfaf2; border-color: #cfeadc; }
.status-box.info { background: #eef5ff; border-color: #d4e5ff; }
.status-box.warning { background: #fff8e8; border-color: #f4e2bf; }
.status-box.danger { background: #fff3f1; border-color: #f5d4ce; }

@media (max-width: 980px) {
  .stats-grid,
  .panel-grid,
  .status-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .dashboard-overview {
    padding: 18px 16px 28px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .stats-grid,
  .panel-grid,
  .status-grid {
    grid-template-columns: 1fr;
  }
}
</style>
