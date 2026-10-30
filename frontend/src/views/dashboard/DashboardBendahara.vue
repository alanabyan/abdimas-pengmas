<template>
  <main class="finance-page">
    <header class="finance-header">
      <a class="finance-brand" href="/"><span>AP</span><strong>Abdimas Pengmas</strong></a>
      <button class="logout-button" type="button" @click="logout"><LogOut :size="16" /> Keluar</button>
    </header>

    <section class="finance-content">
      <p class="eyebrow">Ruang bendahara</p>
      <h1>Assalamu'alaikum, {{ auth.user?.nama || auth.user?.nama_bendahara || 'Bendahara' }}.</h1>
      <p class="intro">Kelola amanah infaq dan skema qurban dengan transparan serta mudah dipantau.</p>

      <div class="subnav" aria-label="Submenu bendahara">
        <button type="button" :class="['tab-button', { active: activeTab === 'infaq' }]" @click="goToTab('infaq')">
          Infaq &amp; sadaqah
        </button>
        <button type="button" :class="['tab-button', { active: activeTab === 'qurban' }]" @click="goToTab('qurban')">
          Qurban
        </button>
        <button type="button" :class="['tab-button', { active: activeTab === 'kas' }]" @click="goToTab('kas')">
          Kas Masuk &amp; Keluar
        </button>
      </div>

      <section class="finance-report-panel" v-if="financeReport.summary">
        <div class="report-overview">
          <div class="report-box">
            <span>Total Infaq</span>
            <strong>{{ formatCurrency(financeReport.summary.total_infaq || 0) }}</strong>
          </div>
          <div class="report-box">
            <span>Total Qurban</span>
            <strong>{{ formatCurrency(financeReport.summary.total_qurban || 0) }}</strong>
          </div>
          <div class="report-box">
            <span>Total Keseluruhan</span>
            <strong>{{ formatCurrency(financeReport.summary.total_keseluruhan || 0) }}</strong>
          </div>
        </div>

        <div class="report-grid">
          <div class="mini-panel">
            <div class="mini-panel-header">
              <h3>Rekap 6 bulan</h3>
            </div>
            <ul class="mini-list">
              <li v-for="item in financeReport.monthly || []" :key="item.month">
                <span>{{ item.label }}</span>
                <strong>{{ formatCurrency(item.total) }}</strong>
              </li>
            </ul>
          </div>

          <div class="mini-panel">
            <div class="mini-panel-header">
              <h3>Transaksi terbaru</h3>
            </div>
            <ul class="mini-list transaction-list">
              <li v-for="item in financeReport.transactions || []" :key="`${item.type}-${item.id}`">
                <div>
                  <span class="transaction-type">{{ item.type }}</span>
                  <small>{{ item.nama }}</small>
                </div>
                <strong>{{ formatCurrency(item.jumlah) }}</strong>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <div v-if="activeTab === 'infaq'">
        <div class="summary-row">
          <div><span>JUMLAH PENGAJUAN</span><strong>{{ summary.total_pengajuan }}</strong></div>
          <div><span>MENUNGGU KONFIRMASI</span><strong>{{ summary.menunggu_konfirmasi }}</strong></div>
          <div><span>TOTAL NOMINAL DIAJUKAN</span><strong>{{ formatCurrency(summary.total_nominal) }}</strong></div>
        </div>

        <section v-if="summary.rekap_harian?.length || summary.rekap_bulanan?.length" class="recap-section">
          <div class="recap-grid">
            <div class="mini-panel">
              <div class="mini-panel-header">
                <h3>Rekap 7 hari</h3>
              </div>
              <ul class="mini-list">
                <li v-for="item in summary.rekap_harian" :key="item.date || item.label">
                  <span>{{ item.label }}</span>
                  <strong>{{ formatCurrency(item.total) }}</strong>
                </li>
              </ul>
            </div>

            <div class="mini-panel">
              <div class="mini-panel-header">
                <h3>Rekap 6 bulan</h3>
              </div>
              <ul class="mini-list">
                <li v-for="item in summary.rekap_bulanan" :key="item.period || item.label">
                  <span>{{ item.label }}</span>
                  <strong>{{ formatCurrency(item.total) }}</strong>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <section class="submission-section">
          <div class="section-head">
            <div>
              <h2>Pengajuan terbaru</h2>
              <p>Pengajuan belum menandakan pembayaran telah diterima.</p>
            </div>
            <button type="button" class="refresh-button" :disabled="loading" @click="loadSubmissions">
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
                    <td><strong>{{ item.nama }}</strong><small v-if="item.catatan">{{ item.catatan }}</small></td>
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
                  <select class="status-select" :value="selectedSubmission.status" @change="updateInfaqStatus(selectedSubmission, $event.target.value)">
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
      </div>

      <section v-else class="qurban-section">
        <div class="qurban-header">
          <div>
            <p class="qurban-kicker">Skema qurban</p>
            <h2>Aturan pembagian satu sapi untuk 7 orang</h2>
          </div>
          <div class="qurban-badge">1 sapi = 7 porsi</div>
        </div>

        <div class="summary-row qurban-summary">
          <div>
            <span>KUOTA TOTAL</span>
            <strong>{{ qurbanSummary.kuota_total || 7 }}</strong>
          </div>
          <div>
            <span>TERDAFTAR</span>
            <strong>{{ qurbanSummary.total_terdaftar || 0 }}</strong>
          </div>
          <div>
            <span>KUOTA TERSEDIA</span>
            <strong>{{ qurbanSummary.kuota_tersedia || 0 }}</strong>
          </div>
        </div>

        <div class="qurban-status-grid" v-if="qurbanRegistrations.length">
          <div class="qurban-status-card success">
            <span>Sudah lunas</span>
            <strong>{{ qurbanRegistrations.filter((item) => item.status === 'lunas').length }}</strong>
          </div>
          <div class="qurban-status-card info">
            <span>Terkonfirmasi</span>
            <strong>{{ qurbanRegistrations.filter((item) => item.status === 'terkonfirmasi').length }}</strong>
          </div>
          <div class="qurban-status-card warning">
            <span>Menunggu</span>
            <strong>{{ qurbanRegistrations.filter((item) => item.status === 'terdaftar').length }}</strong>
          </div>
          <div class="qurban-status-card danger">
            <span>Total tagihan belum lunas</span>
            <strong>{{ formatCurrency(qurbanRegistrations.filter((item) => item.status !== 'lunas').reduce((sum, item) => sum + Number(item.selisih_nominal || item.nominal_terbaru || item.jumlah || 0), 0)) }}</strong>
          </div>
        </div>

        <section v-if="qurbanSummary.rekap_harian?.length || qurbanSummary.rekap_bulanan?.length" class="recap-section">
          <div class="recap-grid">
            <div class="mini-panel">
              <div class="mini-panel-header">
                <h3>Rekap 7 hari</h3>
              </div>
              <ul class="mini-list">
                <li v-for="item in qurbanSummary.rekap_harian" :key="item.date || item.label">
                  <span>{{ item.label }}</span>
                  <strong>{{ formatCurrency(item.total) }}</strong>
                </li>
              </ul>
            </div>

            <div class="mini-panel">
              <div class="mini-panel-header">
                <h3>Rekap 6 bulan</h3>
              </div>
              <ul class="mini-list">
                <li v-for="item in qurbanSummary.rekap_bulanan" :key="item.period || item.label">
                  <span>{{ item.label }}</span>
                  <strong>{{ formatCurrency(item.total) }}</strong>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <div class="qurban-grid">
          <div class="rule-card">
            <h3>Ketentuan umum</h3>
            <ul>
              <li>1 ekor sapi dapat dibagi untuk 7 orang peserta.</li>
              <li>Setiap peserta memperoleh 1/7 bagian dari hak qurban sapi.</li>
              <li>Nominal kontribusi per orang dihitung berdasarkan total biaya sapi dibagi 7.</li>
              <li>Bendahara dapat menerima pendaftaran hingga kuota 7 peserta terisi.</li>
            </ul>

            <div class="calculator-box">
              <h3>Hitung estimasi per orang</h3>
              <div class="field-group">
                <label>Harga sapi saat ini</label>
                <input v-model.number="qurbanTotal" type="number" min="0" step="1000" />
              </div>
              <div class="field-group">
                <label>Jumlah pembagi</label>
                <input v-model.number="qurbanParticipants" type="number" min="1" max="7" step="1" />
              </div>
              <div class="calc-result">
                <span>Per orang</span>
                <strong>{{ formatCurrency(qurbanPerPerson) }}</strong>
              </div>
              <p class="price-note">Jika harga sapi naik setelah pendaftaran awal, selisih otomatis dihitung untuk peserta yang belum lunas. Peserta yang sudah lunas tetap tidak bertambah.</p>
              <button class="submit-button secondary" type="button" @click="saveQurbanPrice" :disabled="updatingQurbanPrice">
                {{ updatingQurbanPrice ? 'Menyimpan...' : 'Simpan harga sapi' }}
              </button>
            </div>
          </div>

          <div class="rule-card form-card">
            <h3>Tambah peserta qurban</h3>
            <form class="qurban-form" @submit.prevent="submitQurban">
              <div v-for="(name, index) in qurbanForm.names" :key="index" class="field-group">
                <label>Nama peserta {{ index + 1 }}</label>
                <input v-model="qurbanForm.names[index]" type="text" :placeholder="`Peserta ${index + 1}`" />
              </div>
              <div class="field-group">
                <label>Telepon</label>
                <input v-model="qurbanForm.telepon" type="text" required placeholder="0812..." />
              </div>
              <div class="field-group">
                <label>Email</label>
                <input v-model="qurbanForm.email" type="email" placeholder="opsional" />
              </div>
              <div class="field-group">
                <label>Jumlah kontribusi</label>
                <input v-model.number="qurbanForm.jumlah" type="number" min="1000" step="1000" required />
              </div>
              <div class="field-group">
                <label>Catatan</label>
                <textarea v-model="qurbanForm.catatan" rows="3" placeholder="Opsional"></textarea>
              </div>

              <div v-if="qurbanMessage" :class="['form-message', qurbanMessage.type]">
                {{ qurbanMessage.text }}
              </div>

              <button class="submit-button" type="submit" :disabled="qurbanSubmitting || (qurbanSummary.kuota_tersedia || 0) <= 0">
                {{ qurbanSubmitting ? 'Menyimpan...' : 'Simpan pendaftaran' }}
              </button>
            </form>
          </div>
        </div>

        <div class="qurban-list">
          <div class="section-head qurban-head">
            <div>
              <h3>Daftar peserta</h3>
              <p>Setiap nama merepresentasikan 1/7 bagian dari satu sapi.</p>
            </div>
            <button class="refresh-button" type="button" @click="loadQurban" :disabled="qurbanLoading">
              <RefreshCw :size="15" /> Muat ulang
            </button>
          </div>

          <div class="status-filter-row">
            <button v-for="option in qurbanStatusOptions" :key="option.value" type="button" :class="['filter-chip', { active: qurbanStatusFilter === option.value }]" @click="qurbanStatusFilter = option.value">
              {{ option.label }}
            </button>
          </div>

          <div class="info-banner" v-if="qurbanRegistrations.length">
            <strong>Konfirmasi pembayaran:</strong>
            <span>{{ qurbanRegistrations.filter((item) => item.status === 'terkonfirmasi' || item.status === 'lunas').length }} peserta sudah dikonfirmasi,</span>
            <span>{{ qurbanRegistrations.filter((item) => item.status === 'terdaftar').length }} menunggu konfirmasi.</span>
          </div>

          <div v-if="qurbanLoading" class="empty-state">Memuat daftar qurban...</div>
          <div v-else-if="!filteredQurbanRegistrations.length" class="empty-state">Belum ada pendaftar qurban pada filter ini.</div>
          <div v-else class="qurban-table-layout">
            <div class="table-wrap qurban-table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>Bagian</th>
                    <th>Awal</th>
                    <th>Baru</th>
                    <th>Tambahan</th>
                    <th>Telepon</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredQurbanRegistrations" :key="item.id" :class="{ selected: selectedQurbanRegistration && selectedQurbanRegistration.id === item.id }" @click="selectedQurbanRegistration = item">
                    <td>
                      <div class="participant-name-wrap">
                        <strong>{{ item.nama }}</strong>
                        <small v-if="item.email">{{ item.email }}</small>
                        <small v-if="item.selisih_nominal > 0" class="payment-delta-badge">Tagihan tambahan: {{ formatCurrency(item.selisih_nominal) }}</small>
                      </div>
                    </td>
                    <td>{{ item.bagian }}</td>
                    <td>{{ formatCurrency(item.nominal_awal || item.jumlah || 0) }}</td>
                    <td>{{ formatCurrency(item.nominal_terbaru || item.jumlah || 0) }}</td>
                    <td>{{ formatCurrency(item.selisih_nominal || 0) }}</td>
                    <td>{{ item.telepon }}</td>
                    <td>
                      <div class="status-stack">
                        <select class="status-select" :value="item.status" @change="updateQurbanStatus(item, $event.target.value)">
                          <option value="terdaftar">Terdaftar</option>
                          <option value="terkonfirmasi">Terkonfirmasi</option>
                          <option value="lunas">Lunas</option>
                          <option value="ditolak">Ditolak</option>
                        </select>
                        <button
                          v-if="item.status !== 'lunas' && item.status !== 'terkonfirmasi'"
                          type="button"
                          class="mini-button"
                          @click.stop="updateQurbanStatus(item, 'terkonfirmasi')"
                        >
                          Konfirmasi
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <aside v-if="selectedQurbanRegistration" class="detail-panel qurban-detail-panel">
              <div class="detail-header">
                <span>Detail konfirmasi</span>
                <span class="status-pill light">{{ formatStatus(selectedQurbanRegistration.status) }}</span>
              </div>

              <div class="detail-body">
                <div class="detail-row">
                  <label>Nama</label>
                  <strong>{{ selectedQurbanRegistration.nama }}</strong>
                </div>
                <div class="detail-row">
                  <label>Telepon</label>
                  <strong>{{ selectedQurbanRegistration.telepon }}</strong>
                </div>
                <div class="detail-row">
                  <label>Nominal awal</label>
                  <strong>{{ formatCurrency(selectedQurbanRegistration.nominal_awal || selectedQurbanRegistration.jumlah || 0) }}</strong>
                </div>
                <div class="detail-row">
                  <label>Nominal terbaru</label>
                  <strong>{{ formatCurrency(selectedQurbanRegistration.nominal_terbaru || selectedQurbanRegistration.jumlah || 0) }}</strong>
                </div>
                <div class="detail-row">
                  <label>Tambahan</label>
                  <strong>{{ formatCurrency(selectedQurbanRegistration.selisih_nominal || 0) }}</strong>
                </div>
                <div class="detail-row">
                  <label>Tanggal konfirmasi</label>
                  <strong>{{ selectedQurbanRegistration.tanggal_konfirmasi ? formatDate(selectedQurbanRegistration.tanggal_konfirmasi) : 'Belum dikonfirmasi' }}</strong>
                </div>
                <div class="detail-row full">
                  <label>Aksi cepat</label>
                  <div class="inline-actions">
                    <button type="button" class="mini-button" @click="updateQurbanStatus(selectedQurbanRegistration, 'terkonfirmasi')">Tandai terkonfirmasi</button>
                    <button type="button" class="mini-button light" @click="updateQurbanStatus(selectedQurbanRegistration, 'lunas')">Tandai lunas</button>
                  </div>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </section>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { LogOut, RefreshCw } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import infaqService from '@/services/infaqService'
import qurbanService from '@/services/qurbanService'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const activeTab = ref(route.name === 'KeuanganQurban' ? 'qurban' : 'infaq')
const submissions = ref([])
const selectedSubmission = ref(null)
const loading = ref(false)
const errorMessage = ref('')
const summary = ref({ total_pengajuan: 0, total_nominal: 0, menunggu_konfirmasi: 0 })
const pagination = ref({ current_page: 1, last_page: 1 })

const qurbanTotal = ref(18000000)
const qurbanParticipants = ref(7)
const qurbanSummary = ref({ kuota_total: 7, kuota_tersedia: 7, total_terdaftar: 0, total_nominal: 0, harga_sapi: 18000000, nominal_per_orang: 2571429 })
const qurbanRegistrations = ref([])
const selectedQurbanRegistration = ref(null)
const qurbanLoading = ref(false)
const qurbanSubmitting = ref(false)
const updatingQurbanPrice = ref(false)
const qurbanMessage = ref(null)
const qurbanStatusFilter = ref('all')
const financeReport = ref({ summary: null, monthly: [], transactions: [] })
const qurbanStatusOptions = [
  { value: 'all', label: 'Semua' },
  { value: 'terdaftar', label: 'Terdaftar' },
  { value: 'terkonfirmasi', label: 'Terkonfirmasi' },
  { value: 'lunas', label: 'Lunas' },
  { value: 'ditolak', label: 'Ditolak' },
]
const qurbanForm = ref({
  names: Array.from({ length: 7 }, () => ''),
  telepon: '',
  email: '',
  jumlah: 2857143,
  catatan: '',
})

const qurbanPerPerson = computed(() => {
  const total = Number(qurbanSummary.value.harga_sapi || qurbanTotal.value || 0)
  const people = Number(qurbanSummary.value.kuota_total || qurbanParticipants.value || 1)
  return Math.ceil(total / people)
})

const filteredQurbanRegistrations = computed(() => {
  if (qurbanStatusFilter.value === 'all') {
    return qurbanRegistrations.value
  }

  return qurbanRegistrations.value.filter((item) => item.status === qurbanStatusFilter.value)
})

watch(
  qurbanRegistrations,
  (list) => {
    if (!list.length) {
      selectedQurbanRegistration.value = null
      return
    }

    const selectedId = selectedQurbanRegistration.value?.id
    if (!selectedId || !list.some((item) => item.id === selectedId)) {
      selectedQurbanRegistration.value = list[0]
    }
  },
  { immediate: true }
)

watch(
  () => route.name,
  (name) => {
    if (name === 'KasBendahara') {
      activeTab.value = 'kas'
      return
    }

    if (name === 'DashboardBendahara' || !name) {
      activeTab.value = 'infaq'
    }
  },
  { immediate: true }
)

function goToTab(tab) {
  activeTab.value = tab

  if (tab === 'kas') {
    router.push({ name: 'KasBendahara' })
    return
  }

  if (route.name !== 'DashboardBendahara') {
    router.replace({ name: 'DashboardBendahara' })
  }
}

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
  } catch {
    errorMessage.value = 'Daftar pengajuan belum dapat dimuat.'
  } finally {
    loading.value = false
  }
}

async function loadQurban() {
  qurbanLoading.value = true
  qurbanMessage.value = null

  try {
    const response = await qurbanService.getRegistrations()
    qurbanSummary.value = response.summary || qurbanSummary.value
    qurbanRegistrations.value = response.data || []
    qurbanTotal.value = Number(response.summary?.harga_sapi || qurbanTotal.value || 18000000)
    qurbanParticipants.value = Number(response.summary?.kuota_total || qurbanParticipants.value || 7)
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Daftar qurban belum dapat dimuat.',
    }
  } finally {
    qurbanLoading.value = false
  }
}

async function loadFinanceReport() {
  try {
    const response = await infaqService.getReport()
    financeReport.value = response || financeReport.value
  } catch (error) {
    console.error('Gagal memuat laporan keuangan', error)
  }
}

async function saveQurbanPrice() {
  updatingQurbanPrice.value = true
  try {
    const response = await qurbanService.updateSetting({
      harga_sapi: Number(qurbanTotal.value),
      kuota_total: Number(qurbanParticipants.value),
    })
    qurbanSummary.value = {
      ...qurbanSummary.value,
      ...response.data,
      harga_sapi: response.data.harga_sapi,
      kuota_total: response.data.kuota_total,
      nominal_per_orang: response.data.nominal_per_orang,
    }
    qurbanMessage.value = { type: 'success', text: response.message || 'Harga sapi berhasil disimpan.' }
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Harga sapi gagal disimpan.',
    }
  } finally {
    updatingQurbanPrice.value = false
  }
}

async function submitQurban() {
  const names = (qurbanForm.value.names || [])
    .map((item) => String(item || '').trim())
    .filter(Boolean)

  if (!names.length || !qurbanForm.value.telepon || !qurbanForm.value.jumlah) {
    qurbanMessage.value = { type: 'error', text: 'Minimal satu nama peserta, telepon, dan jumlah kontribusi wajib diisi.' }
    return
  }

  qurbanSubmitting.value = true
  qurbanMessage.value = null

  try {
    const payload = {
      ...qurbanForm.value,
      nama: names,
      jumlah: Number(qurbanForm.value.jumlah),
    }

    const response = await qurbanService.createRegistration(payload)
    qurbanMessage.value = { type: 'success', text: response.message || 'Pendaftaran qurban berhasil disimpan.' }
    qurbanForm.value = {
      names: Array.from({ length: 7 }, () => ''),
      telepon: '',
      email: '',
      jumlah: 2857143,
      catatan: '',
    }
    qurbanSummary.value = { ...qurbanSummary.value, ...response.data }
    await loadQurban()
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Pendaftaran qurban gagal disimpan.',
    }
  } finally {
    qurbanSubmitting.value = false
  }
}

async function updateInfaqStatus(item, status) {
  try {
    const response = await infaqService.updateStatus(item.id, { status })
    item.status = response.data?.status || status
    if (selectedSubmission.value?.id === item.id) {
      selectedSubmission.value = { ...selectedSubmission.value, status: item.status }
    }
    errorMessage.value = ''
    qurbanMessage.value = { type: 'success', text: 'Status infaq berhasil diperbarui.' }
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Status infaq gagal diperbarui.',
    }
  }
}

async function updateQurbanStatus(item, status) {
  try {
    await qurbanService.updateStatus(item.id, { status })
    item.status = status
    qurbanMessage.value = { type: 'success', text: 'Status qurban berhasil diperbarui.' }
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Status qurban gagal diperbarui.',
    }
  }
}

async function logout() {
  await auth.logout()
  router.push({ name: 'Login' })
}

onMounted(() => {
  loadSubmissions()
  loadQurban()
  loadFinanceReport()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap');
.finance-page { min-height: 100vh; background: #f4f6f1; color: #20342a; font-family: 'DM Sans', sans-serif; }
.finance-header { height: 68px; padding: 0 max(22px, calc((100% - 1120px) / 2)); background: white; border-bottom: 1px solid #e0e6dc; display: flex; align-items: center; justify-content: space-between; }
.finance-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
.finance-brand span { display: grid; place-items: center; width: 34px; height: 34px; color: white; background: #176b4b; border-radius: 50% 50% 50% 6px; font: 800 11px 'Manrope', sans-serif; }
.finance-brand strong { font: 800 13px 'Manrope', sans-serif; }
.logout-button, .refresh-button { display: inline-flex; align-items: center; gap: 8px; color: #42554a; border: 1px solid #d9e2d8; background: white; padding: 9px 12px; cursor: pointer; font: 600 11px 'DM Sans', sans-serif; }
.finance-content { max-width: 1120px; margin: auto; padding: 54px 22px 70px; }
.eyebrow { margin-bottom: 11px; color: #176b4b; font-size: 10px; text-transform: uppercase; font-weight: 800; }
h1 { margin: 0; font: 800 32px/1.2 'Manrope', sans-serif; letter-spacing: 0; }
.intro { color: #718077; font-size: 13px; margin: 12px 0 22px; }
.subnav { display: inline-flex; gap: 8px; background: #edf2ea; padding: 6px; border-radius: 12px; margin-bottom: 26px; }
.tab-button { border: 0; background: transparent; color: #4f6259; padding: 10px 16px; border-radius: 10px; font: 700 12px 'DM Sans', sans-serif; cursor: pointer; }
.tab-button.active { background: white; color: #0f5132; box-shadow: 0 1px 2px rgba(29, 82, 57, 0.08); }
.summary-row { display: grid; grid-template-columns: repeat(3, 1fr); border: 1px solid #dce4da; background: white; }
.summary-row > div { padding: 20px 22px; }
.summary-row > div + div { border-left: 1px solid #e1e7df; }
.summary-row span { display: block; color: #76837a; font-size: 9px; font-weight: 800; }
.summary-row strong { display: block; margin-top: 10px; font: 800 24px 'Manrope', sans-serif; }
.submission-section { margin-top: 10px; }
.section-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 17px; }
.section-head h2 { margin: 0 0 5px; font: 800 19px 'Manrope', sans-serif; letter-spacing: 0; }
.section-head p { color: #76837a; font-size: 11px; margin: 0; }
.refresh-button:disabled { opacity: .6; }
.submission-grid { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.8fr); gap: 20px; }
.table-wrap { overflow-x: auto; border: 1px solid #dce4da; background: white; }
table { width: 100%; border-collapse: collapse; text-align: left; min-width: 620px; }
th { background: #edf2ea; color: #637169; text-transform: uppercase; font-size: 9px; padding: 12px 14px; }
td { border-top: 1px solid #edf0eb; padding: 14px; color: #43534a; font-size: 11px; vertical-align: top; cursor: pointer; }
td strong, td small { display: block; }
td strong { color: #24382d; font-weight: 700; }
td small { color: #849087; margin-top: 5px; max-width: 220px; line-height: 1.5; }
.capitalize { text-transform: capitalize; }
tr.selected td { background: #edf9f1; }
.status-pill { display: inline-block; padding: 5px 8px; background: #fff4dd; color: #96601f; font-size: 9px; text-transform: capitalize; border-radius: 999px; }
.status-pill.light { background: #e8f7ed; color: #0a5f3c; }
.detail-panel { background: white; border: 1px solid #dce4da; min-height: 100%; }
.detail-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 18px 12px; border-bottom: 1px solid #edf0eb; color: #1d432f; font: 700 12px 'DM Sans', sans-serif; }
.detail-body { padding: 18px; display: grid; gap: 14px; }
.detail-row { display: grid; gap: 5px; }
.detail-row label { font-size: 9px; color: #6d7d75; text-transform: uppercase; font-weight: 800; }
.detail-row strong, .detail-row p { margin: 0; color: #123126; font-size: 12px; line-height: 1.6; }
.detail-row.full { grid-column: 1 / -1; }
.empty-state, .message { padding: 25px; text-align: center; background: white; border: 1px solid #dce4da; color: #718077; font-size: 12px; }
.pagination-bar { display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 13px; color: #718077; font-size: 10px; }
.pagination-bar button { border: 1px solid #d9e2d8; background: white; padding: 8px 10px; color: #42554a; font: 600 10px 'DM Sans', sans-serif; cursor: pointer; }
.pagination-bar button:disabled { opacity: .5; cursor: not-allowed; }
.message.error { color: #a5352b; background: #fff5f3; }
.qurban-section { margin-top: 10px; }
.qurban-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
.qurban-kicker { margin: 0 0 8px; color: #176b4b; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.qurban-header h2 { margin: 0; font: 800 26px/1.2 'Manrope', sans-serif; }
.qurban-badge { background: #e8f7ed; color: #0d5e3c; padding: 10px 14px; border-radius: 999px; font: 800 11px 'DM Sans', sans-serif; }
.qurban-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.rule-card { background: white; border: 1px solid #dce4da; border-radius: 20px; padding: 24px; }
.rule-card h3 { margin: 0 0 16px; font: 800 16px 'Manrope', sans-serif; }
.rule-card ul { margin: 0; padding-left: 18px; color: #42554a; display: grid; gap: 10px; font-size: 13px; line-height: 1.6; }
.calculator-box { margin-top: 22px; padding-top: 22px; border-top: 1px solid #edf0eb; }
.field-group { display: grid; gap: 8px; margin-bottom: 14px; }
.field-group label { font-size: 11px; color: #5b6b62; font-weight: 700; }
.field-group input, .field-group textarea { width: 100%; border: 1px solid #d7e0d9; border-radius: 10px; padding: 11px 12px; background: #f9faf8; color: #153627; font: 600 13px 'DM Sans', sans-serif; }
.field-group textarea { resize: vertical; }
.calc-result { margin-top: 18px; padding-top: 18px; border-top: 1px solid #edf0eb; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.calc-result span { color: #65736b; font-size: 11px; text-transform: uppercase; font-weight: 800; }
.calc-result strong { font: 800 20px 'Manrope', sans-serif; }
.qurban-summary { margin-bottom: 18px; }
.qurban-status-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 0 0 22px; }
.qurban-status-card { background: white; border: 1px solid #dce4da; border-radius: 16px; padding: 16px 18px; display: grid; gap: 8px; }
.qurban-status-card span { color: #65736b; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.qurban-status-card strong { font: 800 19px 'Manrope', sans-serif; }
.qurban-status-card.success { background: #ecfaf2; border-color: #cfeadc; }
.qurban-status-card.info { background: #eef5ff; border-color: #d5e4ff; }
.qurban-status-card.warning { background: #fff8e8; border-color: #f2e2b8; }
.qurban-status-card.danger { background: #fff3f1; border-color: #f7d7d0; }
.qurban-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
.form-card { display: block; }
.price-note { margin: 12px 0 0; font-size: 11px; line-height: 1.6; color: #5f7067; }
.qurban-form { display: grid; gap: 10px; }
.form-message { padding: 10px 12px; border-radius: 10px; font-size: 12px; }
.form-message.success { background: #eafaf1; color: #0f6a3c; }
.form-message.error { background: #fff0ef; color: #a2392e; }
.submit-button { border: none; background: #176b4b; color: white; border-radius: 12px; padding: 12px 16px; font: 700 12px 'DM Sans', sans-serif; cursor: pointer; }
.submit-button.secondary { margin-top: 8px; background: #0f5132; }
.submit-button:disabled { opacity: .6; cursor: not-allowed; }
.status-filter-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.filter-chip { border: 1px solid #d9e2d8; background: white; color: #42554a; padding: 8px 10px; border-radius: 999px; font: 700 10px 'DM Sans', sans-serif; cursor: pointer; }
.filter-chip.active { background: #eafaf1; border-color: #ccebd8; color: #0f6a3c; }
.info-banner { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; background: #eefaf3; border: 1px solid #cfead9; color: #1b4d3c; padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; font-size: 11px; }
.participant-name-wrap { display: flex; flex-direction: column; gap: 5px; }
.payment-delta-badge { color: #8d4f1d; background: #fff2d9; border-radius: 999px; padding: 4px 8px; font-weight: 700; width: fit-content; }
.status-stack { display: grid; gap: 8px; }
.mini-button { border: 1px solid #c9d9cf; background: #176b4b; color: white; border-radius: 8px; padding: 7px 10px; cursor: pointer; font: 700 10px 'DM Sans', sans-serif; }
.status-select { width: 100%; border: 1px solid #d7e0d9; background: #f7faf7; color: #123126; border-radius: 8px; padding: 8px 10px; font: 600 11px 'DM Sans', sans-serif; }
.qurban-list { margin-top: 26px; }
.qurban-head { margin-bottom: 12px; }
.qurban-table-layout { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr); gap: 18px; margin-top: 12px; }
.qurban-table-wrap { margin-top: 0; }
.qurban-detail-panel { min-height: 100%; }
.inline-actions { display: flex; flex-wrap: wrap; gap: 8px; }
.mini-button.light { background: #edf5ef; color: #1f5b3f; border-color: #cfe2d4; }
.recap-section { margin-top: 18px; }
.recap-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.mini-panel { background: white; border: 1px solid #dce4da; border-radius: 16px; padding: 16px 18px; }
.mini-panel-header { margin-bottom: 12px; }
.mini-panel-header h3 { margin: 0; font: 800 14px 'Manrope', sans-serif; }
.mini-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.mini-list li { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #edf0eb; font-size: 12px; }
.mini-list li:last-child { border-bottom: 0; }
.mini-list span { color: #66756d; }
.mini-list strong { font-size: 12px; }
.finance-report-panel { margin: 22px 0 12px; }
.report-overview { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
.report-box { background: white; border: 1px solid #dce4da; border-radius: 14px; padding: 18px 16px; }
.report-box span { display: block; color: #65736b; font-size: 9px; font-weight: 800; text-transform: uppercase; }
.report-box strong { display: block; margin-top: 6px; font: 800 20px 'Manrope', sans-serif; }
.transaction-list li { align-items: flex-start; }
.transaction-type { display: inline-block; margin-bottom: 4px; text-transform: uppercase; font-size: 9px; font-weight: 800; color: #176b4b; }
.transaction-list small { display: block; color: #64736e; }
@media (max-width: 860px) { .submission-grid, .qurban-grid, .recap-grid, .report-overview { grid-template-columns: 1fr; } }
@media (max-width: 620px) { .finance-content { padding: 36px 16px 50px; } h1 { font-size: 27px; } .summary-row { grid-template-columns: 1fr; } .summary-row > div { padding: 16px 18px; } .summary-row > div + div { border-left: 0; border-top: 1px solid #e1e7df; } .summary-row strong { font-size: 21px; } .section-head, .qurban-header { flex-direction: column; align-items: flex-start; } .section-head p { max-width: 220px; line-height: 1.5; } }
</style>