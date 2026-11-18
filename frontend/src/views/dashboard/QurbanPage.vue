<template>
  <main class="qurban-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Qurban</p>
        <h1>Manajemen Qurban</h1>
      </div>
      <div class="badge">1 sapi = {{ qurbanSummary.kuota_total || 7 }} porsi</div>
    </header>

    <section class="summary-row">
      <div>
        <span>Kuota Total</span>
        <strong>{{ qurbanSummary.kuota_total || 7 }}</strong>
      </div>
      <div>
        <span>Terdaftar</span>
        <strong>{{ qurbanSummary.total_terdaftar || 0 }}</strong>
      </div>
      <div>
        <span>Kuota Tersedia</span>
        <strong>{{ qurbanSummary.kuota_tersedia || 0 }}</strong>
      </div>
    </section>

    <section class="status-grid" v-if="qurbanRegistrations.length">
      <div class="status-card success">
        <span>Sudah lunas</span>
        <strong>{{ qurbanRegistrations.filter((item) => item.status === 'lunas').length }}</strong>
      </div>
      <div class="status-card info">
        <span>Terkonfirmasi</span>
        <strong>{{ qurbanRegistrations.filter((item) => item.status === 'terkonfirmasi').length }}</strong>
      </div>
      <div class="status-card warning">
        <span>Menunggu</span>
        <strong>{{ qurbanRegistrations.filter((item) => item.status === 'terdaftar').length }}</strong>
      </div>
      <div class="status-card danger">
        <span>Tagihan belum lunas</span>
        <strong>{{ formatCurrency(qurbanRegistrations.filter((item) => item.status !== 'lunas').reduce((sum, item) => sum + Math.max(0, Number(item.jumlah || 0) - Number(item.total_dibayar || 0)), 0)) }}</strong>
      </div>
    </section>

    <section class="panel-grid">
      <div class="panel">
        <div class="panel-header">
          <h2>Ketentuan umum</h2>
        </div>
        <ul class="rule-list">
          <li>1 ekor sapi dapat dibagi untuk 7 orang peserta.</li>
          <li>Setiap peserta memperoleh 1/7 bagian dari hak qurban sapi.</li>
          <li>Nominal kontribusi per orang menggunakan total harga sapi dibagi kuota.</li>
          <li>Jika harga sapi naik setelah pendaftaran, selisih otomatis dihitung untuk peserta yang belum lunas.</li>
        </ul>

        <div class="calculator-box">
          <h3>Hitung estimasi per orang</h3>
          <div class="field-group">
            <label>Harga sapi saat ini</label>
            <input v-model.number="qurbanTotal" type="number" min="0" step="1000" :disabled="!canManage" />
          </div>
          <div class="field-group">
            <label>Jumlah pembagi</label>
            <input v-model.number="qurbanParticipants" type="number" min="1" max="7" step="1" :disabled="!canManage" />
          </div>
          <div class="calc-result">
            <span>Per orang</span>
            <strong>{{ formatCurrency(qurbanPerPerson) }}</strong>
          </div>
          <button v-if="canManage" class="primary-button" type="button" @click="saveQurbanPrice" :disabled="updatingQurbanPrice">
            {{ updatingQurbanPrice ? 'Menyimpan...' : 'Simpan harga sapi' }}
          </button>
        </div>
      </div>

      <div v-if="canManage" class="panel">
        <div class="panel-header">
          <h2>Tambah peserta qurban</h2>
        </div>
        <form class="qurban-form" @submit.prevent="submitQurban">
          <div class="field-group">
            <label>ID / kelompok sapi</label>
            <input v-model="qurbanForm.sapi_id" type="text" required placeholder="contoh: SAPI-01" />
          </div>
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

          <button class="primary-button" type="submit" :disabled="qurbanSubmitting || (qurbanSummary.kuota_tersedia || 0) <= 0">
            {{ qurbanSubmitting ? 'Menyimpan...' : 'Simpan pendaftaran' }}
          </button>
        </form>
      </div>
      <div v-else class="panel read-only-panel">
        <div class="panel-header"><h2>Mode baca</h2></div>
        <p>Akun bendahara hanya dapat melihat data peserta dan progres pembayaran Qurban.</p>
      </div>
    </section>

    <section class="list-section">
      <div class="section-head">
        <div>
          <h2>Daftar peserta</h2>
          <p>Setiap nama mewakili satu porsi dari satu sapi.</p>
        </div>
        <button class="refresh-button" type="button" @click="loadQurban" :disabled="qurbanLoading">
          <RefreshCw :size="15" /> Muat ulang
        </button>
      </div>

      <div class="filter-row">
        <button v-for="option in qurbanStatusOptions" :key="option.value" type="button" :class="['filter-chip', { active: qurbanStatusFilter === option.value }]" @click="qurbanStatusFilter = option.value">
          {{ option.label }}
        </button>
      </div>

      <div v-if="qurbanLoading" class="empty-state">Memuat daftar qurban...</div>
      <div v-else-if="!filteredQurbanRegistrations.length" class="empty-state">Belum ada pendaftar qurban pada filter ini.</div>
      <div v-else class="table-layout">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Sapi</th>
                <th>Nama</th>
                <th>Bagian</th>
                <th>Target</th>
                <th>Terbayar</th>
                <th>Progres</th>
                <th>Cicilan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredQurbanRegistrations" :key="item.id" @click="openQurbanDetail(item)">
                <td><strong>{{ item.sapi_id || 'SAPI-01' }}</strong></td>
                <td>
                  <div class="participant-name-wrap">
                    <strong>{{ item.nama }}</strong>
                    <small>{{ item.telepon }}<span v-if="item.email"> · {{ item.email }}</span></small>
                  </div>
                </td>
                <td>{{ item.bagian }}</td>
                <td>{{ formatCurrency(item.jumlah) }}</td>
                <td>{{ formatCurrency(item.total_dibayar) }}</td>
                <td>
                  <div class="progress-cell">
                    <div class="progress-track"><span :style="{ width: `${item.progres_pembayaran || 0}%` }"></span></div>
                    <small>{{ item.progres_pembayaran || 0 }}%</small>
                  </div>
                </td>
                <td>{{ item.jumlah_pembayaran || 0 }} kali</td>
                <td>
                  <div class="status-stack">
                    <select v-if="canManage && !item.jumlah_pembayaran" class="status-select" :value="item.status" @click.stop @change="updateQurbanStatus(item, $event.target.value)">
                      <option value="terdaftar">Terdaftar</option>
                      <option value="terkonfirmasi">Terkonfirmasi</option>
                      <option value="ditolak">Ditolak</option>
                    </select>
                    <span v-else class="status-pill" :class="item.status">{{ formatStatus(item.status) }}</span>
                    <button v-if="canManage && item.status === 'terdaftar'" type="button" class="mini-button" @click.stop="updateQurbanStatus(item, 'terkonfirmasi')">Konfirmasi</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="selectedQurbanRegistration" class="modal-backdrop" @click.self="closeQurbanDetail">
          <section class="detail-modal" role="dialog" aria-modal="true" :aria-labelledby="`qurban-detail-${selectedQurbanRegistration.id}`">
            <header class="detail-header">
              <div>
                <span>Detail sapi &amp; peserta</span>
                <h3 :id="`qurban-detail-${selectedQurbanRegistration.id}`">{{ selectedQurbanRegistration.nama }}</h3>
              </div>
              <button type="button" class="close-button" aria-label="Tutup detail" @click="closeQurbanDetail">×</button>
            </header>
            <div class="detail-body">
              <div class="detail-row"><label>ID sapi / kelompok</label><strong>{{ selectedQurbanRegistration.sapi_id || 'SAPI-01' }}</strong></div>
              <div class="detail-row"><label>Bagian sapi</label><strong>{{ selectedQurbanRegistration.bagian }}</strong></div>
              <div class="detail-row"><label>Telepon</label><strong>{{ selectedQurbanRegistration.telepon }}</strong></div>
              <div class="detail-row"><label>Nominal target</label><strong>{{ formatCurrency(selectedQurbanRegistration.jumlah) }}</strong></div>
              <div class="detail-row"><label>Total dibayar</label><strong>{{ formatCurrency(selectedQurbanRegistration.total_dibayar) }}</strong></div>
              <div class="detail-row"><label>Status</label><strong class="capitalize">{{ formatStatus(selectedQurbanRegistration.status) }}</strong></div>
              <div class="detail-row full">
                <label>Progres pembayaran</label>
                <div class="progress-track large"><span :style="{ width: `${selectedQurbanRegistration.progres_pembayaran || 0}%` }"></span></div>
                <small>{{ selectedQurbanRegistration.progres_pembayaran || 0 }}% · {{ formatCurrency(Math.max(0, Number(selectedQurbanRegistration.jumlah || 0) - Number(selectedQurbanRegistration.total_dibayar || 0))) }} tersisa</small>
              </div>
              <div class="detail-row full">
                <label>Riwayat cicilan</label>
                <ol v-if="selectedQurbanRegistration.payments?.length" class="payment-history">
                  <li v-for="payment in selectedQurbanRegistration.payments" :key="payment.id">
                    <span>Pembayaran ke-{{ payment.payment_number }} · {{ formatDate(payment.paid_at) }}</span>
                    <strong>{{ formatCurrency(payment.amount) }}</strong>
                    <small v-if="payment.note">{{ payment.note }}</small>
                  </li>
                </ol>
                <p v-else class="no-payments">Belum ada pembayaran tercatat.</p>
              </div>
              <form v-if="canManage && selectedQurbanRegistration.status !== 'lunas' && selectedQurbanRegistration.status !== 'ditolak'" class="payment-form detail-row full" @submit.prevent="submitPayment">
                <label>Catat pembayaran berikutnya</label>
                <div class="payment-fields">
                  <input v-model.number="paymentForm.amount" type="number" min="1" :max="remainingAmount" required placeholder="Nominal pembayaran" />
                  <input v-model="paymentForm.paid_at" type="date" required aria-label="Tanggal pembayaran" />
                </div>
                <input v-model.trim="paymentForm.note" type="text" maxlength="500" placeholder="Keterangan (opsional)" />
                <p v-if="paymentError" class="form-message error">{{ paymentError }}</p>
                <button class="primary-button" type="submit" :disabled="paymentSubmitting || remainingAmount <= 0">
                  {{ paymentSubmitting ? 'Menyimpan...' : `Catat pembayaran ke-${(selectedQurbanRegistration.jumlah_pembayaran || 0) + 1}` }}
                </button>
              </form>
            </div>
          </section>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RefreshCw } from 'lucide-vue-next'
import qurbanService from '@/services/qurbanService'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const canManage = computed(() => auth.user?.role === 'marbot')
const qurbanTotal = ref(18000000)
const qurbanParticipants = ref(7)
const qurbanSummary = ref({ kuota_total: 7, kuota_tersedia: 7, total_terdaftar: 0, total_nominal: 0, harga_sapi: 18000000 })
const qurbanRegistrations = ref([])
const selectedQurbanRegistration = ref(null)
const qurbanLoading = ref(false)
const qurbanSubmitting = ref(false)
const updatingQurbanPrice = ref(false)
const qurbanMessage = ref(null)
const qurbanStatusFilter = ref('all')
const paymentSubmitting = ref(false)
const paymentError = ref('')
const paymentForm = ref({ amount: 0, paid_at: new Date().toISOString().slice(0, 10), note: '' })

const qurbanStatusOptions = [
  { value: 'all', label: 'Semua' },
  { value: 'terdaftar', label: 'Terdaftar' },
  { value: 'terkonfirmasi', label: 'Terkonfirmasi' },
  { value: 'lunas', label: 'Lunas' },
  { value: 'ditolak', label: 'Ditolak' },
]

const qurbanForm = ref({
  sapi_id: 'SAPI-01',
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

const remainingAmount = computed(() => Math.max(
  0,
  Number(selectedQurbanRegistration.value?.jumlah || 0) - Number(selectedQurbanRegistration.value?.total_dibayar || 0),
))

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatDate(value) {
  if (!value) return '—'
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
}

function formatStatus(value) {
  return (value || '').replaceAll('_', ' ')
}

function openQurbanDetail(item) {
  selectedQurbanRegistration.value = item
  paymentForm.value = {
    amount: Math.max(0, Number(item.jumlah || 0) - Number(item.total_dibayar || 0)),
    paid_at: new Date().toISOString().slice(0, 10),
    note: '',
  }
  paymentError.value = ''
}

function closeQurbanDetail() {
  selectedQurbanRegistration.value = null
  paymentError.value = ''
}

async function loadQurban() {
  qurbanLoading.value = true
  qurbanMessage.value = null

  try {
    const response = await qurbanService.getRegistrations()
    qurbanSummary.value = response.summary || qurbanSummary.value
    qurbanRegistrations.value = response.data || []
    if (selectedQurbanRegistration.value) {
      selectedQurbanRegistration.value = qurbanRegistrations.value.find(
        (item) => item.id === selectedQurbanRegistration.value.id,
      ) || null
    }
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

async function saveQurbanPrice() {
  if (!canManage.value) return
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
  if (!canManage.value) return
  const names = (qurbanForm.value.names || []).map((item) => String(item || '').trim()).filter(Boolean)

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
      sapi_id: 'SAPI-01',
      names: Array.from({ length: 7 }, () => ''),
      telepon: '',
      email: '',
      jumlah: 2857143,
      catatan: '',
    }
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

async function updateQurbanStatus(item, status) {
  if (!canManage.value) return
  try {
    await qurbanService.updateStatus(item.id, { status })
    item.status = status
    qurbanMessage.value = { type: 'success', text: 'Status qurban berhasil diperbarui.' }
    await loadQurban()
  } catch (error) {
    qurbanMessage.value = {
      type: 'error',
      text: error?.response?.data?.message || 'Status qurban gagal diperbarui.',
    }
  }
}

async function submitPayment() {
  if (!canManage.value || !selectedQurbanRegistration.value) return

  paymentSubmitting.value = true
  paymentError.value = ''

  try {
    await qurbanService.addPayment(selectedQurbanRegistration.value.id, {
      amount: Number(paymentForm.value.amount),
      paid_at: paymentForm.value.paid_at,
      note: paymentForm.value.note || null,
    })
    await loadQurban()
    if (selectedQurbanRegistration.value) {
      paymentForm.value.amount = remainingAmount.value
      paymentForm.value.note = ''
    }
    qurbanMessage.value = { type: 'success', text: 'Pembayaran berhasil dicatat.' }
  } catch (error) {
    paymentError.value = error?.response?.data?.message || 'Pembayaran gagal dicatat.'
  } finally {
    paymentSubmitting.value = false
  }
}

onMounted(() => {
  loadQurban()
})
</script>

<style scoped>
.qurban-page {
  min-height: calc(100vh - 80px);
  padding: 28px 28px 40px;
  color: #1d2e26;
  font-family: 'DM Sans', sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
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
  font-size: clamp(28px, 3vw, 36px);
  font-weight: 800;
}

.badge {
  padding: 10px 14px;
  border-radius: 999px;
  background: #ebfaf1;
  color: #0e6a3d;
  font-size: 11px;
  font-weight: 800;
}

.summary-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  overflow: hidden;
  margin-bottom: 18px;
}

.summary-row > div {
  padding: 20px 22px;
}

.summary-row > div + div {
  border-left: 1px solid #edf1ee;
}

.summary-row span {
  display: block;
  font-size: 10px;
  color: #6c7f75;
  text-transform: uppercase;
  font-weight: 800;
}

.summary-row strong {
  display: block;
  margin-top: 10px;
  font-size: clamp(22px, 2vw, 26px);
  font-weight: 800;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 22px;
}

.status-card {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 16px;
  padding: 16px 14px;
  display: grid;
  gap: 8px;
}

.status-card span {
  color: #65736b;
  font-size: 9px;
  font-weight: 800;
  text-transform: uppercase;
}

.status-card strong {
  font-size: 22px;
  font-weight: 800;
}

.status-card.success { background: #ecfaf2; border-color: #cfeadc; }
.status-card.info { background: #eef5ff; border-color: #d4e5ff; }
.status-card.warning { background: #fff8e8; border-color: #f4e2bf; }
.status-card.danger { background: #fff3f1; border-color: #f5d4ce; }

.panel-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
  margin-bottom: 22px;
}

.read-only-panel {
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: #64736b;
  line-height: 1.7;
}

.read-only-panel p { margin: 0; font-size: 13px; }

.panel {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  padding: 20px 18px;
}

.panel-header h2 {
  margin: 0 0 12px;
  font-size: 18px;
}

.rule-list {
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 8px;
  color: #42554d;
  font-size: 13px;
  line-height: 1.6;
}

.calculator-box {
  margin-top: 22px;
  padding-top: 22px;
  border-top: 1px solid #edf1ee;
}

.calculator-box h3 {
  margin: 0 0 12px;
  font-size: 16px;
}

.field-group {
  display: grid;
  gap: 8px;
  margin-bottom: 12px;
}

.field-group label {
  font-size: 11px;
  color: #5b6b62;
  font-weight: 700;
}

.field-group input,
.field-group textarea {
  width: 100%;
  border: 1px solid #d9e3db;
  background: #f9faf8;
  border-radius: 10px;
  padding: 11px 12px;
  font: inherit;
  color: #153728;
}

.field-group textarea { resize: vertical; }

.calc-result {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 14px 0 18px;
  padding-top: 18px;
  border-top: 1px solid #edf1ee;
}

.calc-result span {
  color: #65736b;
  font-size: 11px;
  text-transform: uppercase;
  font-weight: 800;
}

.calc-result strong {
  font-size: 22px;
  font-weight: 800;
}

.primary-button {
  border: none;
  background: #176b4b;
  color: white;
  border-radius: 12px;
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
}

.primary-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.qurban-form {
  display: grid;
  gap: 10px;
}

.form-message {
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 12px;
}

.form-message.success { background: #eafaf1; color: #0f6a3c; }
.form-message.error { background: #fff0ef; color: #a2392e; }

.list-section {
  background: transparent;
}

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
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

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.filter-chip {
  border: 1px solid #d9e2d8;
  background: white;
  color: #42554a;
  padding: 8px 10px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
}

.filter-chip.active {
  background: #eafaf1;
  border-color: #ccebd8;
  color: #0f6a3c;
}

.empty-state {
  background: white;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  padding: 28px 18px;
  text-align: center;
  color: #6d7e77;
}

.table-layout {
  display: block;
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
  min-width: 920px;
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

tbody tr:hover td { background: #f4faf5; }

td strong, td small { display: block; }

td small { color: #7c8c84; margin-top: 5px; }

.participant-name-wrap { display: flex; flex-direction: column; gap: 5px; }

.status-stack { display: grid; gap: 8px; }
.status-select {
  width: 100%;
  border: 1px solid #d9e3db;
  background: #f7faf8;
  color: #153728;
  border-radius: 10px;
  padding: 9px 10px;
  font: inherit;
}

.mini-button {
  border: 1px solid #c9d9cf;
  background: #176b4b;
  color: white;
  border-radius: 8px;
  padding: 7px 10px;
  cursor: pointer;
  font-weight: 700;
}

.mini-button.light {
  background: #edf5ef;
  color: #1f5b3f;
  border-color: #cfe2d4;
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

.status-pill.lunas { background: #e8f7ed; color: #0d6b3d; }
.status-pill.ditolak { background: #fff1f1; color: #b84d4d; }

.progress-cell { display: grid; grid-template-columns: minmax(70px, 1fr) 32px; align-items: center; gap: 8px; min-width: 110px; }
.progress-track { height: 8px; overflow: hidden; border-radius: 999px; background: #e6eee8; }
.progress-track span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #1d7d52, #55bd83); transition: width .2s ease; }
.progress-track.large { height: 11px; }
.progress-cell small, .detail-row small { color: #6d7e77; font-size: 10px; }

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

.detail-row strong {
  color: #153728;
  font-size: 12px;
}

.detail-row.full {
  grid-column: 1 / -1;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgba(15, 31, 22, .58);
}

.detail-modal {
  width: min(760px, 100%);
  max-height: min(88vh, 900px);
  overflow: auto;
  border: 1px solid #dfe9e2;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 24px 70px rgba(0, 0, 0, .2);
}

.detail-header h3 { margin: 6px 0 0; color: #153728; font-size: 19px; }
.close-button { border: 0; background: #edf5ef; color: #1d5d42; border-radius: 10px; width: 34px; height: 34px; font-size: 22px; cursor: pointer; }
.detail-body { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.payment-history { display: grid; gap: 8px; padding: 0; margin: 0; list-style: none; }
.payment-history li { display: grid; grid-template-columns: 1fr auto; gap: 5px 12px; border: 1px solid #e6eee7; border-radius: 10px; padding: 10px 12px; font-size: 11px; }
.payment-history li span { color: #53665b; }
.payment-history li strong { color: #176b4b; }
.payment-history li small { grid-column: 1 / -1; }
.no-payments { margin: 0; color: #6d7e77; font-size: 12px; }
.payment-form { padding-top: 14px; border-top: 1px solid #edf1ee; }
.payment-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.payment-form input { width: 100%; box-sizing: border-box; border: 1px solid #d9e3db; border-radius: 10px; padding: 10px 12px; background: #f9faf8; color: #153728; font: inherit; }
.capitalize { text-transform: capitalize; }

@media (max-width: 980px) {
  .status-grid,
  .panel-grid,
  .summary-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .qurban-page {
    padding: 18px 16px 30px;
  }

  .page-header,
  .section-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-body { grid-template-columns: 1fr; }
  .payment-fields { grid-template-columns: 1fr; }
}
</style>
