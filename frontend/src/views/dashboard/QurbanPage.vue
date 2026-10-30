<template>
  <main class="qurban-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Qurban</p>
        <h1>Manajemen Qurban</h1>
      </div>
      <div class="badge">1 sapi = 7 porsi</div>
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
        <strong>{{ formatCurrency(qurbanRegistrations.filter((item) => item.status !== 'lunas').reduce((sum, item) => sum + Number(item.selisih_nominal || item.nominal_terbaru || item.jumlah || 0), 0)) }}</strong>
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
          <button class="primary-button" type="button" @click="saveQurbanPrice" :disabled="updatingQurbanPrice">
            {{ updatingQurbanPrice ? 'Menyimpan...' : 'Simpan harga sapi' }}
          </button>
        </div>
      </div>

      <div class="panel">
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
                <th>Awal</th>
                <th>Baru</th>
                <th>Tambahan</th>
                <th>Telepon</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredQurbanRegistrations" :key="item.id" :class="{ selected: selectedQurbanRegistration && selectedQurbanRegistration.id === item.id }" @click="selectedQurbanRegistration = item">
                <td><strong>{{ item.sapi_id || 'SAPI-01' }}</strong></td>
                <td>
                  <div class="participant-name-wrap">
                    <strong>{{ item.nama }}</strong>
                    <small v-if="item.email">{{ item.email }}</small>
                    <small v-if="item.selisih_nominal > 0" class="delta-badge">Tagihan tambahan: {{ formatCurrency(item.selisih_nominal) }}</small>
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
                    <button v-if="item.status !== 'lunas' && item.status !== 'terkonfirmasi'" type="button" class="mini-button" @click.stop="updateQurbanStatus(item, 'terkonfirmasi')">Konfirmasi</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <aside v-if="selectedQurbanRegistration" class="detail-panel">
          <div class="detail-header">
            <span>Detail konfirmasi</span>
            <span class="status-pill light">{{ formatStatus(selectedQurbanRegistration.status) }}</span>
          </div>
          <div class="detail-body">
            <div class="detail-row">
              <label>ID sapi / kelompok</label>
              <strong>{{ selectedQurbanRegistration.sapi_id || 'SAPI-01' }}</strong>
            </div>
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
    </section>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RefreshCw } from 'lucide-vue-next'
import qurbanService from '@/services/qurbanService'

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

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatStatus(value) {
  return (value || '').replaceAll('_', ' ')
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

td strong, td small { display: block; }

td small { color: #7c8c84; margin-top: 5px; }

tr.selected td { background: #edf9f1; }

.participant-name-wrap { display: flex; flex-direction: column; gap: 5px; }
.delta-badge {
  margin-top: 5px;
  background: #fff2d9;
  color: #8d4f1d;
  font-weight: 700;
  border-radius: 999px;
  padding: 4px 8px;
  width: fit-content;
}

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

.inline-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

@media (max-width: 980px) {
  .status-grid,
  .panel-grid,
  .summary-row,
  .table-layout {
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
}
</style>
