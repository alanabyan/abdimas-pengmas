<template>
  <main class="kas-page">
    <header class="page-header">
      <div class="brand-block">
        <span class="brand-mark">AP</span>
        <div>
          <p class="eyebrow">{{ auth.user?.role || 'Kas' }}</p>
          <h1>Kas &amp; Donasi</h1>
        </div>
      </div>

      <div class="header-actions">
        <button class="ghost-button" type="button" @click="goBack">Kembali ke dashboard</button>
        <button class="logout-button" type="button" @click="logout">
          <LogOut :size="16" /> Keluar
        </button>
      </div>
    </header>

    <section class="summary-grid">
      <div class="summary-card masuk">
        <span>Total masuk</span>
        <strong>{{ formatCurrency(summary.total_masuk || 0) }}</strong>
      </div>
      <div class="summary-card keluar">
        <span>Total keluar</span>
        <strong>{{ formatCurrency(summary.total_keluar || 0) }}</strong>
      </div>
      <div class="summary-card saldo">
        <span>Saldo</span>
        <strong>{{ formatCurrency(summary.saldo || 0) }}</strong>
      </div>
    </section>

    <section class="report-toolbar">
      <label class="month-picker">
        <span>Bulan</span>
        <input v-model="currentMonth" type="month" @change="loadTransactions" />
      </label>

      <label class="month-picker">
        <span>Minggu</span>
        <input v-model="currentWeek" type="date" @change="loadWeeklyTransactions" />
      </label>

      <button class="export-button" type="button" @click="exportPdf">
        Export PDF Bulan
      </button>
      <button class="export-button secondary" type="button" @click="exportWeeklyPdf">
        Export PDF Mingguan
      </button>
    </section>

    <section class="content-grid">
      <div class="panel form-panel">
        <div class="panel-header">
          <h2>Catat transaksi</h2>
        </div>

        <form v-if="canManage" class="transaction-form" @submit.prevent="submitTransaction">
          <div class="field-row">
            <label>
              Judul
              <input v-model="form.judul" type="text" placeholder="Contoh: Donasi Jumat" required />
            </label>
          </div>

          <div class="field-row two-cols">
            <label>
              Jenis
              <select v-model="form.jenis" required>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
              </select>
            </label>

            <label>
              Kategori
              <input v-model="form.kategori" type="text" placeholder="infaq, qurban, operasional" required />
            </label>
          </div>

          <div class="field-row two-cols">
            <label>
              Nominal
              <input v-model.number="form.nominal" type="number" min="1" step="1000" required />
            </label>

            <label>
              Tanggal
              <input v-model="form.tanggal" type="date" required />
            </label>
          </div>

          <div class="field-row">
            <label>
              Keterangan
              <textarea v-model="form.keterangan" rows="3" placeholder="Opsional"></textarea>
            </label>
          </div>

          <div v-if="message" :class="['message', message.type]">
            {{ message.text }}
          </div>

          <button class="primary-button" type="submit" :disabled="submitting">
            {{ submitting ? 'Menyimpan...' : 'Simpan transaksi' }}
          </button>
        </form>
        <p v-else class="read-only-note">Akses bendahara hanya untuk melihat data kas.</p>
      </div>

      <div class="panel list-panel">
        <div class="panel-header">
          <h2>Riwayat transaksi</h2>
        </div>

        <div v-if="loading" class="empty-state">Memuat data kas...</div>
        <div v-else-if="!transactions.length" class="empty-state">Belum ada transaksi kas.</div>
        <div v-else class="transaction-list">
          <article v-for="item in transactions" :key="item.id" class="transaction-item">
            <div class="transaction-main">
              <span class="transaction-type" :class="item.jenis">{{ item.jenis }}</span>
              <h3>{{ item.judul }}</h3>
              <small>{{ item.kategori }} · {{ formatDate(item.tanggal) }}</small>
            </div>
            <div class="transaction-amount" :class="item.jenis">
              {{ item.jenis === 'masuk' ? '+' : '-' }} {{ formatCurrency(item.nominal) }}
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="panel donation-panel">
      <div class="panel-header donation-header">
        <div>
          <h2>Donasi Ziswaf</h2>
          <p>Data donasi dan bukti transfer yang masuk melalui formulir publik.</p>
        </div>
        <button class="ghost-button" type="button" :disabled="infaqLoading" @click="loadDonations">Muat ulang</button>
      </div>

      <p v-if="infaqError" class="message error">{{ infaqError }}</p>
      <div v-else-if="infaqLoading" class="empty-state">Memuat donasi...</div>
      <div v-else-if="!donations.length" class="empty-state">Belum ada donasi Ziswaf.</div>
      <div v-else class="table-wrap">
        <table class="donation-table">
          <thead>
            <tr>
              <th>Nama / Blok</th>
              <th>Jenis</th>
              <th>Nominal</th>
              <th>Bukti transfer</th>
              <th>Status</th>
              <th v-if="canManage">Verifikasi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in donations" :key="item.id">
              <td><strong>{{ item.nama }}</strong><small>{{ item.blok_rumah }} · {{ item.telepon }}</small></td>
              <td class="capitalize">{{ item.jenis }}</td>
              <td>{{ formatCurrency(item.jumlah) }}</td>
              <td>
                <button v-if="item.has_receipt" type="button" class="receipt-button" @click="downloadReceipt(item)">Lihat bukti</button>
                <span v-else>Belum diunggah</span>
              </td>
              <td><span class="status-pill" :class="item.status">{{ formatStatus(item.status) }}</span></td>
              <td v-if="canManage">
                <select
                  v-if="item.status === 'menunggu_konfirmasi'"
                  class="status-select"
                  :value="item.status"
                  @change="updateDonationStatus(item, $event.target.value)"
                >
                  <option value="menunggu_konfirmasi">Menunggu konfirmasi</option>
                  <option value="terkonfirmasi">Terkonfirmasi</option>
                  <option value="ditolak">Ditolak</option>
                </select>
                <span v-else>—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="infaqPagination.last_page > 1" class="pagination-bar">
        <button type="button" :disabled="infaqPagination.current_page <= 1 || infaqLoading" @click="loadDonations(infaqPagination.current_page - 1)">Sebelumnya</button>
        <span>Halaman {{ infaqPagination.current_page }} dari {{ infaqPagination.last_page }}</span>
        <button type="button" :disabled="infaqPagination.current_page >= infaqPagination.last_page || infaqLoading" @click="loadDonations(infaqPagination.current_page + 1)">Berikutnya</button>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import kasService from '@/services/kasService'
import infaqService from '@/services/infaqService'

const auth = useAuthStore()
const router = useRouter()
const canManage = computed(() => auth.user?.role === 'marbot')

const loading = ref(false)
const submitting = ref(false)
const message = ref(null)
const currentMonth = ref(new Date().toISOString().slice(0, 7))
const currentWeek = ref(new Date().toISOString().slice(0, 10))
const summary = ref({ total_masuk: 0, total_keluar: 0, saldo: 0 })
const transactions = ref([])
const donations = ref([])
const infaqLoading = ref(false)
const infaqError = ref('')
const infaqPagination = ref({ current_page: 1, last_page: 1 })
const form = ref({
  judul: '',
  jenis: 'masuk',
  kategori: 'infaq',
  nominal: 0,
  keterangan: '',
  tanggal: new Date().toISOString().slice(0, 10),
})

async function loadTransactions() {
  loading.value = true
  try {
    const response = await kasService.index(currentMonth.value)
    summary.value = response.summary || summary.value
    transactions.value = response.data || []
    currentWeek.value = new Date().toISOString().slice(0, 10)
  } catch (error) {
    message.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Gagal memuat transaksi kas.',
    }
  } finally {
    loading.value = false
  }
}

async function loadWeeklyTransactions() {
  loading.value = true
  try {
    const response = await kasService.weekly(currentWeek.value)
    summary.value = response.summary || summary.value
    transactions.value = response.data || []
  } catch (error) {
    message.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Gagal memuat ringkasan kas mingguan.',
    }
  } finally {
    loading.value = false
  }
}

async function submitTransaction() {
  if (!canManage.value) return
  submitting.value = true
  message.value = null

  try {
    await kasService.store({
      ...form.value,
      nominal: Number(form.value.nominal),
    })

    message.value = {
      type: 'success',
      text: 'Transaksi kas berhasil disimpan.',
    }

    form.value = {
      judul: '',
      jenis: 'masuk',
      kategori: 'infaq',
      nominal: 0,
      keterangan: '',
      tanggal: new Date().toISOString().slice(0, 10),
    }

    await loadTransactions()
  } catch (error) {
    message.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Transaksi gagal disimpan.',
    }
  } finally {
    submitting.value = false
  }
}

async function loadDonations(page = 1) {
  infaqLoading.value = true
  infaqError.value = ''

  try {
    const response = await infaqService.getSubmissions(page)
    donations.value = response.data || []
    infaqPagination.value = response.pagination || infaqPagination.value
  } catch (error) {
    infaqError.value = error?.response?.data?.message || 'Daftar donasi gagal dimuat.'
  } finally {
    infaqLoading.value = false
  }
}

async function updateDonationStatus(item, status) {
  if (!canManage.value) return

  try {
    await infaqService.updateStatus(item.id, { status })
    item.status = status
    infaqError.value = ''
    await loadTransactions()
  } catch (error) {
    infaqError.value = error?.response?.data?.message || 'Status donasi gagal diperbarui.'
  }
}

async function downloadReceipt(item) {
  try {
    const blob = await infaqService.downloadReceipt(item.id)
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `bukti-ziswaf-${item.id}.${item.proof_extension || 'file'}`
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    infaqError.value = error?.response?.data?.message || 'Bukti transfer gagal diunduh.'
  }
}

async function exportPdf() {
  try {
    const blob = await kasService.exportPdf(currentMonth.value)
    const url = window.URL.createObjectURL(new Blob([blob], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `laporan-kas-${currentMonth.value || 'all'}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    message.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Laporan kas gagal diekspor.',
    }
  }
}

async function exportWeeklyPdf() {
  try {
    const blob = await kasService.exportWeeklyPdf(currentWeek.value)
    const url = window.URL.createObjectURL(new Blob([blob], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `laporan-kas-mingguan-${currentWeek.value || 'all'}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    message.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Laporan kas mingguan gagal diekspor.',
    }
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatDate(value) {
  return new Date(value).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function formatStatus(value) {
  return (value || '').replaceAll('_', ' ')
}

function goBack() {
  router.push({ name: auth.user?.role === 'bendahara' ? 'DashboardBendahara' : 'Dashboard' })
}

async function logout() {
  await auth.logout()
  router.push({ name: 'Login' })
}

onMounted(() => {
  loadTransactions()
  loadDonations()
})
</script>

<style scoped>
.kas-page {
  min-height: 100vh;
  background: #f5f7f3;
  color: #1f2b28;
  padding: 32px 24px 60px;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.brand-block {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-mark {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #1f8a5d, #b7d7be);
  color: white;
  font-weight: 800;
}

.eyebrow {
  margin: 0;
  color: #3e8d6a;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 10px;
  font-weight: 700;
}

.page-header h1 {
  margin: 4px 0 0;
  font-size: clamp(1.8rem, 2vw, 2.8rem);
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.ghost-button,
.logout-button,
.primary-button {
  border: 0;
  border-radius: 12px;
  padding: 11px 16px;
  font-weight: 600;
  cursor: pointer;
}

.ghost-button {
  background: #edf5ee;
  color: #1d5d42;
}

.logout-button {
  background: #fff;
  color: #2f3a36;
  border: 1px solid #dfe8e1;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.summary-grid {
  max-width: 1200px;
  margin: 0 auto 24px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.report-toolbar {
  max-width: 1200px;
  margin: 0 auto 20px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
}

.month-picker {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #42554a;
}

.month-picker input {
  min-width: 150px;
}

.export-button {
  background: #1d6b4a;
  color: white;
  padding: 10px 14px;
  border: 0;
  border-radius: 12px;
  font-weight: 700;
  cursor: pointer;
}

.export-button.secondary {
  background: #f0f5f1;
  color: #1b3a2f;
  border: 1px solid #dfe8e1;
}

.summary-card {
  background: white;
  border: 1px solid #e6ece5;
  border-radius: 18px;
  padding: 20px 22px;
  box-shadow: 0 10px 24px rgba(23, 41, 29, 0.04);
}

.summary-card span {
  display: block;
  color: #607164;
  font-size: 0.75rem;
  margin-bottom: 8px;
}

.summary-card strong {
  font-size: clamp(1.35rem, 2vw, 2.1rem);
}

.summary-card.masuk strong { color: #1f8a5d; }
.summary-card.keluar strong { color: #b94a4a; }
.summary-card.saldo strong { color: #1f3d82; }

.content-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 440px) minmax(0, 1fr);
  gap: 20px;
}

.donation-panel {
  max-width: 1200px;
  margin: 20px auto 0;
}

.donation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.donation-header h2 { margin-bottom: 6px; }
.donation-header p, .read-only-note { color: #6d7c74; font-size: 0.85rem; }
.donation-header p { margin: 0 0 14px; }
.donation-table { min-width: 700px; }
.donation-table td { vertical-align: middle; }
.donation-table td strong, .donation-table td small { display: block; }
.donation-table td small { color: #6d7c74; margin-top: 4px; }
.receipt-button { border: 0; background: transparent; padding: 0; color: #176b4b; font-weight: 700; text-decoration: underline; cursor: pointer; }
.status-pill { display: inline-flex; border-radius: 999px; padding: 5px 8px; background: #fff4dd; color: #8a621d; font-size: 0.7rem; text-transform: capitalize; }
.status-pill.terkonfirmasi { background: #e8f7ed; color: #0d6b3d; }
.status-pill.ditolak { background: #fff1f1; color: #b84d4d; }
.status-select { min-width: 165px; border: 1px solid #dfe8e1; background: #fbfcfb; border-radius: 9px; padding: 8px; color: #1f2b28; font: inherit; }
.capitalize { text-transform: capitalize; }
.pagination-bar { display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 12px; color: #64766d; font-size: 0.75rem; }
.pagination-bar button { background: white; border: 1px solid #dfe8e1; color: #3e564c; padding: 8px 10px; border-radius: 10px; cursor: pointer; }
.pagination-bar button:disabled { opacity: 0.5; cursor: not-allowed; }

.panel {
  background: white;
  border: 1px solid #e5ebe5;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 10px 24px rgba(23, 41, 29, 0.04);
}

.panel-header h2 {
  margin: 0 0 16px;
  font-size: 1.15rem;
}

.transaction-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field-row {
  display: flex;
  flex-direction: column;
}

.two-cols {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-weight: 600;
  font-size: 0.8rem;
  color: #42554a;
}

input, select, textarea {
  width: 100%;
  border: 1px solid #dfe8e1;
  background: #fbfcfb;
  border-radius: 12px;
  padding: 10px 12px;
  font: inherit;
  color: #1f2b28;
  box-sizing: border-box;
}

textarea { resize: vertical; }

.primary-button {
  background: linear-gradient(135deg, #1d7d52, #1b654b);
  color: white;
  padding: 12px 18px;
}

.primary-button:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.message {
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 0.86rem;
  font-weight: 600;
}

.message.success {
  background: #eafaf2;
  color: #1d6c3d;
}

.message.error {
  background: #fff1f1;
  color: #b84d4d;
}

.transaction-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.transaction-item {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 14px;
  background: #f8faf7;
  border: 1px solid #ebf0ea;
  border-radius: 14px;
}

.transaction-main h3 {
  margin: 0;
  font-size: 1rem;
}

.transaction-main small {
  color: #6d7c74;
}

.transaction-type {
  display: inline-flex;
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.transaction-type.masuk {
  background: #eafaf2;
  color: #1d6c3d;
}

.transaction-type.keluar {
  background: #fff1f1;
  color: #b84d4d;
}

.transaction-amount {
  font-weight: 800;
  font-size: 1rem;
  white-space: nowrap;
  align-self: center;
}

.transaction-amount.masuk { color: #1f8a5d; }
.transaction-amount.keluar { color: #b94a4a; }

.empty-state {
  color: #6d7c74;
  font-size: 0.92rem;
  padding: 18px 0;
}

@media (max-width: 880px) {
  .summary-grid,
  .content-grid,
  .two-cols {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
