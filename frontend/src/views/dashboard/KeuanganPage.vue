<template>
  <main class="finance-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Keuangan</p>
        <h1>Infaq &amp; Sadaqah</h1>
      </div>
    </header>

    <section class="summary-row">
      <div>
        <span>Jumlah Pengajuan</span>
        <strong>{{ summary.total_pengajuan }}</strong>
      </div>
      <div>
        <span>Menunggu Konfirmasi</span>
        <strong>{{ summary.menunggu_konfirmasi }}</strong>
      </div>
      <div>
        <span>Total Nominal</span>
        <strong>{{ formatCurrency(summary.total_nominal) }}</strong>
      </div>
    </section>

    <section class="panel-grid" v-if="summary.rekap_harian?.length || summary.rekap_bulanan?.length">
      <div class="panel">
        <div class="panel-header">
          <h2>Rekap 7 hari</h2>
        </div>
        <ul class="mini-list">
          <li v-for="item in summary.rekap_harian" :key="item.date || item.label">
            <span>{{ item.label }}</span>
            <strong>{{ formatCurrency(item.total) }}</strong>
          </li>
        </ul>
      </div>

      <div class="panel">
        <div class="panel-header">
          <h2>Rekap 6 bulan</h2>
        </div>
        <ul class="mini-list">
          <li v-for="item in summary.rekap_bulanan" :key="item.period || item.label">
            <span>{{ item.label }}</span>
            <strong>{{ formatCurrency(item.total) }}</strong>
          </li>
        </ul>
      </div>
    </section>

    <section class="submission-section">
      <div class="section-head">
        <div>
          <h2>Pengajuan terbaru</h2>
          <p>Daftar pengajuan menunggu proses verifikasi bendahara.</p>
        </div>
        <button class="refresh-button" type="button" :disabled="loading" @click="loadSubmissions">
          <RefreshCw :size="15" /> Muat ulang
        </button>
      </div>

      <p v-if="errorMessage" class="message error">{{ errorMessage }}</p>
      <div v-else-if="loading" class="empty-state">Memuat pengajuan...</div>
      <div v-else-if="!submissions.length" class="empty-state">Belum ada pengajuan infaq atau sadaqah.</div>
      <div v-else class="submission-grid">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in submissions" :key="item.id" :class="{ selected: selectedSubmission && selectedSubmission.id === item.id }" @click="selectedSubmission = item">
                <td>
                  <strong>{{ item.nama }}</strong>
                  <small v-if="item.catatan">{{ item.catatan }}</small>
                </td>
                <td class="capitalize">{{ item.jenis }}</td>
                <td>{{ formatCurrency(item.jumlah) }}</td>
                <td><span class="status-pill">{{ formatStatus(item.status) }}</span></td>
                <td>{{ formatDate(item.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <aside v-if="selectedSubmission" class="detail-panel">
          <div class="detail-header">
            <span>Detail pengajuan</span>
            <span class="status-pill light">{{ formatStatus(selectedSubmission.status) }}</span>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <label>Nama</label>
              <strong>{{ selectedSubmission.nama }}</strong>
            </div>
            <div class="detail-row">
              <label>Jenis</label>
              <strong class="capitalize">{{ selectedSubmission.jenis }}</strong>
            </div>
            <div class="detail-row">
              <label>Nominal</label>
              <strong>{{ formatCurrency(selectedSubmission.jumlah) }}</strong>
            </div>
            <div class="detail-row">
              <label>Telepon</label>
              <strong>{{ selectedSubmission.telepon }}</strong>
            </div>
            <div v-if="selectedSubmission.email" class="detail-row">
              <label>Email</label>
              <strong>{{ selectedSubmission.email }}</strong>
            </div>
            <div v-if="selectedSubmission.catatan" class="detail-row full">
              <label>Catatan</label>
              <p>{{ selectedSubmission.catatan }}</p>
            </div>
            <div class="detail-row">
              <label>Tanggal</label>
              <strong>{{ formatDate(selectedSubmission.created_at) }}</strong>
            </div>
            <div class="detail-row full">
              <label>Status pengajuan</label>
              <select class="status-select" :value="selectedSubmission.status" @change="updateStatus(selectedSubmission, $event.target.value)">
                <option value="menunggu_konfirmasi">Menunggu konfirmasi</option>
                <option value="terkonfirmasi">Terkonfirmasi</option>
                <option value="ditolak">Ditolak</option>
              </select>
            </div>
          </div>
        </aside>
      </div>

      <div v-if="pagination.last_page > 1" class="pagination-bar">
        <button type="button" :disabled="pagination.current_page <= 1 || loading" @click="loadSubmissions(pagination.current_page - 1)">Sebelumnya</button>
        <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <button type="button" :disabled="pagination.current_page >= pagination.last_page || loading" @click="loadSubmissions(pagination.current_page + 1)">Berikutnya</button>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RefreshCw } from 'lucide-vue-next'
import infaqService from '@/services/infaqService'

const submissions = ref([])
const selectedSubmission = ref(null)
const loading = ref(false)
const errorMessage = ref('')
const summary = ref({ total_pengajuan: 0, total_nominal: 0, menunggu_konfirmasi: 0 })
const pagination = ref({ current_page: 1, last_page: 1 })

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatDate(value) {
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
}

function formatStatus(value) {
  return (value || '').replaceAll('_', ' ')
}

async function loadSubmissions(page = 1) {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await infaqService.getSubmissions(page)
    submissions.value = response.data || []
    summary.value = response.summary || summary.value
    pagination.value = response.pagination || pagination.value

    if (submissions.value.length && !selectedSubmission.value) {
      selectedSubmission.value = submissions.value[0]
    }
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'Daftar pengajuan belum dapat dimuat.'
  } finally {
    loading.value = false
  }
}

async function updateStatus(item, status) {
  try {
    const response = await infaqService.updateStatus(item.id, { status })
    item.status = response.data?.status || status
    if (selectedSubmission.value?.id === item.id) {
      selectedSubmission.value = { ...selectedSubmission.value, status: item.status }
    }
    errorMessage.value = ''
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || 'Status infaq gagal diperbarui.'
  }
}

onMounted(() => {
  loadSubmissions()
})
</script>

<style scoped>
.finance-page {
  min-height: calc(100vh - 80px);
  padding: 28px 28px 40px;
  color: #1d2e26;
  font-family: 'DM Sans', sans-serif;
}

.page-header {
  margin-bottom: 18px;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 10px;
  color: #176b4b;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

h1 {
  margin: 0;
  font-size: clamp(28px, 3vw, 36px);
  font-weight: 800;
}

.summary-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  overflow: hidden;
  margin-bottom: 22px;
}

.summary-row > div {
  padding: 20px 22px;
}

.summary-row > div + div {
  border-left: 1px solid #edf1ee;
}

.summary-row span {
  display: block;
  color: #6c7f75;
  font-size: 10px;
  text-transform: uppercase;
  font-weight: 800;
}

.summary-row strong {
  display: block;
  margin-top: 10px;
  font-size: clamp(22px, 2vw, 26px);
  font-weight: 800;
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
  padding: 18px;
}

.panel-header h2 {
  margin: 0 0 12px;
  font-size: 18px;
}

.mini-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 8px;
}

.mini-list li {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #edf1ee;
  color: #4f655d;
  font-size: 12px;
}

.mini-list li:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.submission-section {
  margin-top: 12px;
}

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
}

.section-head h2 {
  margin: 0 0 6px;
  font-size: 20px;
}

.section-head p {
  margin: 0;
  color: #6d7e77;
  font-size: 12px;
}

.refresh-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: white;
  border: 1px solid #dfe9e2;
  color: #3f594e;
  border-radius: 10px;
  padding: 9px 12px;
  font-weight: 700;
  cursor: pointer;
}

.submission-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.7fr) minmax(260px, 0.8fr);
  gap: 18px;
}

.table-wrap {
  overflow-x: auto;
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 620px;
}

th {
  background: #edf2ee;
  color: #5b6a63;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 9px;
  padding: 12px 14px;
  text-align: left;
}

td {
  padding: 14px;
  border-top: 1px solid #edf1ee;
  color: #254234;
  font-size: 11px;
  vertical-align: top;
  cursor: pointer;
}

td strong, td small {
  display: block;
}

td small {
  color: #7c8c84;
  margin-top: 5px;
}

tr.selected td {
  background: #edf9f1;
}

.capitalize { text-transform: capitalize; }

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 5px 8px;
  border-radius: 999px;
  background: #fff4dd;
  color: #8a621d;
  font-size: 9px;
  text-transform: capitalize;
}

.status-pill.light {
  background: #e8f7ed;
  color: #0d6b3d;
}

.detail-panel {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  overflow: hidden;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 18px 18px 12px;
  border-bottom: 1px solid #edf1ee;
  font-size: 12px;
  font-weight: 700;
}

.detail-body {
  display: grid;
  gap: 14px;
  padding: 18px;
}

.detail-row {
  display: grid;
  gap: 5px;
}

.detail-row label {
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
  color: #66796e;
}

.detail-row strong,
.detail-row p {
  margin: 0;
  color: #153728;
  font-size: 12px;
  line-height: 1.6;
}

.detail-row.full { grid-column: 1 / -1; }

.status-select {
  width: 100%;
  border: 1px solid #d9e3db;
  background: #f7faf8;
  color: #153728;
  border-radius: 10px;
  padding: 9px 10px;
  font: inherit;
}

.message {
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 12px;
  margin-bottom: 12px;
}

.message.error {
  background: #fff1ee;
  border: 1px solid #efd2ce;
  color: #a13d36;
}

.empty-state {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  padding: 28px 18px;
  text-align: center;
  color: #6d7e77;
}

.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 12px;
  color: #64766d;
  font-size: 10px;
}

.pagination-bar button {
  background: white;
  border: 1px solid #dfe9e2;
  color: #3e564c;
  padding: 8px 10px;
  border-radius: 10px;
  font: inherit;
  cursor: pointer;
}

.pagination-bar button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 900px) {
  .panel-grid,
  .submission-grid,
  .summary-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .finance-page {
    padding: 18px 16px 30px;
  }

  .section-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

