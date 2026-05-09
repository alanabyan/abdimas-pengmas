<template>
  <div v-if="dataPinjam" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-box">

      <!-- Header -->
      <div class="modal-header">
        <div class="header-left">
          <div class="header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              width="18" height="18">
              <path d="M9 14l-4-4 4-4" />
              <path d="M5 10h11a4 4 0 0 1 0 8h-1" />
            </svg>
          </div>
          <span>Konfirmasi Barang Kembali</span>
        </div>
        <button class="btn-close" @click="$emit('close')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            width="16" height="16">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">

        <!-- Info peminjam -->
        <div class="info-card">
          <div class="info-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              width="14" height="14" class="info-icon">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <span class="info-label">Peminjam</span>
            <span class="info-val">{{ dataPinjam?.warga?.nama_warga || 'Tidak diketahui' }}</span>
          </div>
          <div class="info-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              width="14" height="14" class="info-icon">
              <rect x="2" y="7" width="20" height="14" rx="2" />
              <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
            </svg>
            <span class="info-label">Barang</span>
            <span class="info-val">{{ dataPinjam?.barang?.nama_barang || 'Barang Terhapus' }}</span>
          </div>
        </div>

        <!-- Progress pengembalian jika sudah ada sebagian -->
        <div v-if="sudahKembali > 0" class="progress-card">
          <div class="progress-header">
            <span class="progress-label">Progress Pengembalian</span>
            <span class="progress-frac">{{ sudahKembali }} / {{ dataPinjam.jumlah }} unit</span>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill" :style="{ width: progressPct + '%' }"></div>
          </div>
          <p class="progress-hint">Sisa <strong>{{ sisaBelum }} unit</strong> belum dikembalikan</p>
        </div>

        <!-- Jumlah dikembalikan -->
        <div class="field-group">
          <label class="field-label">Jumlah Dikembalikan Sekarang</label>
          <div class="jumlah-wrapper">
            <button type="button" class="jumlah-btn" :disabled="form.jumlah_kembali <= 1" @click="kurang">−</button>
            <input type="number" v-model.number="form.jumlah_kembali" :min="1" :max="sisaBelum" class="jumlah-input" />
            <button type="button" class="jumlah-btn" :disabled="form.jumlah_kembali >= sisaBelum"
              @click="tambah">+</button>
          </div>
          <p class="jumlah-hint">
            <span>Maks: <strong>{{ sisaBelum }} unit</strong></span>
            <span v-if="form.jumlah_kembali < sisaBelum" class="sisa-badge">
              Sisa {{ sisaBelum - form.jumlah_kembali }} unit akan tetap tercatat sebagai tanggungan
            </span>
          </p>
        </div>

        <!-- Kondisi selector -->
        <div class="field-group">
          <label class="field-label">Kondisi Saat Ini</label>
          <div class="kondisi-grid">
            <button v-for="opt in kondisiOptions" :key="opt.value" type="button" class="kondisi-btn"
              :class="[`kondisi-btn--${opt.color}`, { 'kondisi-btn--active': form.kondisi_kembali === opt.value }]"
              @click="selectKondisi(opt.value)">
              <span class="kondisi-icon">{{ opt.icon }}</span>
              <span class="kondisi-text">{{ opt.label }}</span>
              <span v-if="form.kondisi_kembali === opt.value" class="kondisi-check">✓</span>
            </button>
          </div>

          <transition name="fade">
            <div v-if="selectedOption" class="kondisi-desc" :class="`kondisi-desc--${selectedOption.color}`">
              {{ selectedOption.desc }}
            </div>
          </transition>
        </div>

        <!-- Catatan -->
        <div class="field-group">
          <label class="field-label">
            Catatan Marbot
            <span v-if="catatanWajib" class="required-badge">Wajib diisi</span>
          </label>
          <textarea v-model="form.catatan" :placeholder="catatanPlaceholder"
            :class="['catatan-input', { 'catatan-input--error': catatanError }]" rows="3"></textarea>
          <transition name="fade">
            <p v-if="catatanError" class="error-msg">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                width="12" height="12">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
              Catatan wajib diisi jika kondisi bukan Baik.
            </p>
          </transition>
        </div>

      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn-batal" @click="$emit('close')">Batal</button>
        <button type="button" class="btn-simpan" :disabled="loading || !canSubmit" @click="submitValidasi">
          <svg v-if="loading" class="spin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" width="15" height="15">
            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
          </svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            width="15" height="15">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          {{ loading ? 'Menyimpan...' : 'Simpan Data' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import api from '@/services/api'

const props = defineProps({
  dataPinjam: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'success'])
const loading = ref(false)
const submitted = ref(false)

const form = reactive({
  kondisi_kembali: 'Baik',
  catatan: '',
  jumlah_kembali: 1,
})

// Sudah dikembalikan sebelumnya (dari pengembalian parsial sebelumnya)
const sudahKembali = computed(() => props.dataPinjam?.jumlah_kembali ?? 0)

// Sisa yang belum dikembalikan = total pinjam - yang sudah kembali
const sisaBelum = computed(() => (props.dataPinjam?.jumlah ?? 0) - sudahKembali.value)

// Progress bar percentage
const progressPct = computed(() => {
  if (!props.dataPinjam?.jumlah) return 0
  return Math.round((sudahKembali.value / props.dataPinjam.jumlah) * 100)
})

// Reset form setiap kali dataPinjam berubah
watch(
  () => props.dataPinjam,
  (val) => {
    if (val) {
      form.jumlah_kembali = sisaBelum.value
      form.kondisi_kembali = 'Baik'
      form.catatan = ''
      submitted.value = false
    }
  },
  { immediate: true }
)

// Jaga jumlah tetap dalam batas saat diketik manual
watch(
  () => form.jumlah_kembali,
  (val) => {
    const max = sisaBelum.value
    if (!val || val < 1) form.jumlah_kembali = 1
    if (val > max) form.jumlah_kembali = max
  }
)

const kondisiOptions = [
  {
    value: 'Baik',
    label: 'Baik / Lengkap',
    icon: '✅',
    color: 'baik',
    desc: 'Barang kembali dalam kondisi lengkap dan baik.',
  },
  {
    value: 'Rusak Ringan',
    label: 'Rusak Ringan',
    icon: '🔧',
    color: 'ringan',
    desc: 'Ada kerusakan kecil tapi masih bisa digunakan. Contoh: lecet, kotor, atau sedikit penyok.',
  },
  {
    value: 'Rusak Berat',
    label: 'Rusak Berat',
    icon: '⚠️',
    color: 'berat',
    desc: 'Barang mengalami kerusakan parah dan tidak dapat digunakan lagi.',
  },
  {
    value: 'Hilang',
    label: 'Hilang',
    icon: '❌',
    color: 'hilang',
    desc: 'Barang tidak bisa dikembalikan. Stok total akan dikurangi permanen.',
  },
]

const selectedOption = computed(() =>
  kondisiOptions.find(o => o.value === form.kondisi_kembali)
)

const catatanWajib = computed(() => form.kondisi_kembali !== 'Baik')

const catatanError = computed(() =>
  submitted.value && catatanWajib.value && !form.catatan.trim()
)

const canSubmit = computed(() => {
  if (catatanWajib.value && !form.catatan.trim()) return false
  return true
})

const catatanPlaceholder = computed(() => {
  switch (form.kondisi_kembali) {
    case 'Rusak Ringan': return 'Contoh: Tiang tenda sedikit bengkok, kain ada noda...'
    case 'Rusak Berat': return 'Jelaskan kerusakan yang terjadi secara detail...'
    case 'Hilang': return 'Jelaskan alasan atau kronologi barang hilang...'
    default: return 'Catatan tambahan (opsional)...'
  }
})

function tambah() {
  if (form.jumlah_kembali < sisaBelum.value) form.jumlah_kembali++
}

function kurang() {
  if (form.jumlah_kembali > 1) form.jumlah_kembali--
}

function selectKondisi(value) {
  form.kondisi_kembali = value
  if (value === 'Baik') submitted.value = false
}

const submitValidasi = async () => {
  submitted.value = true
  if (!canSubmit.value) return
  if (!props.dataPinjam?.id) return alert('ID Peminjaman tidak ditemukan!')

  loading.value = true
  try {
    const response = await api.post(`/pengembalian/${props.dataPinjam.id}`, {
      kondisi_kembali: form.kondisi_kembali,
      jumlah_kembali: form.jumlah_kembali,
      catatan: form.catatan || null,
    })

    emit('success', response.data)
    emit('close')
  } catch (error) {
    const msg = error?.response?.data?.message || 'Gagal menyimpan data pengembalian.'
    alert(msg)
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(6, 78, 59, 0.35);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 16px;
}

.modal-box {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.18);
  overflow: hidden;
  animation: popIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.92) translateY(8px);
  }

  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* ── Header ── */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #065f46;
  padding: 16px 20px;
  color: white;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.1px;
}

.header-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* ── Body ── */
.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-height: 75vh;
  overflow-y: auto;
}

/* Info card */
.info-card {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.info-icon {
  color: #16a34a;
  flex-shrink: 0;
}

.info-label {
  color: #6b7280;
  min-width: 64px;
}

.info-val {
  color: #111827;
  font-weight: 600;
}

/* Progress card */
.progress-card {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.progress-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.progress-label {
  font-size: 11px;
  font-weight: 700;
  color: #92400e;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.progress-frac {
  font-size: 13px;
  font-weight: 700;
  color: #92400e;
  font-family: monospace;
}

.progress-bar-wrap {
  width: 100%;
  height: 6px;
  background: #fef3c7;
  border-radius: 99px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: #d97706;
  border-radius: 99px;
  transition: width 0.4s ease;
}

.progress-hint {
  font-size: 12px;
  color: #92400e;
  margin: 0;
}

/* Field group */
.field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-label {
  font-size: 11px;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.required-badge {
  font-size: 10px;
  font-weight: 600;
  background: #fef3c7;
  color: #92400e;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: none;
  letter-spacing: 0;
}

/* Jumlah control */
.jumlah-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.jumlah-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1.5px solid #e5e7eb;
  background: white;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #374151;
  font-family: inherit;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
  flex-shrink: 0;
}

.jumlah-btn:hover:not(:disabled) {
  background: #f0fdf4;
  border-color: #86efac;
  color: #16a34a;
}

.jumlah-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.jumlah-input {
  flex: 1;
  text-align: center;
  padding: 7px 8px;
  border: 1.5px solid #e5e7eb;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  outline: none;
  font-family: inherit;
  transition: border-color 0.2s, box-shadow 0.2s;
  -moz-appearance: textfield;
}

.jumlah-input::-webkit-inner-spin-button,
.jumlah-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
}

.jumlah-input:focus {
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.jumlah-hint {
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.jumlah-hint strong {
  color: #6b7280;
}

.sisa-badge {
  font-size: 11px;
  font-weight: 600;
  background: #fef3c7;
  color: #92400e;
  padding: 2px 7px;
  border-radius: 4px;
}

/* Kondisi grid */
.kondisi-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.kondisi-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 2px solid #e5e7eb;
  background: white;
  cursor: pointer;
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  transition: all 0.15s;
  text-align: left;
  font-family: inherit;
  position: relative;
}

.kondisi-icon {
  font-size: 16px;
  flex-shrink: 0;
}

.kondisi-text {
  flex: 1;
  line-height: 1.3;
}

.kondisi-check {
  position: absolute;
  top: 6px;
  right: 8px;
  font-size: 11px;
  font-weight: 700;
}

.kondisi-btn--baik:hover {
  border-color: #86efac;
  background: #f0fdf4;
}

.kondisi-btn--ringan:hover {
  border-color: #fcd34d;
  background: #fffbeb;
}

.kondisi-btn--berat:hover {
  border-color: #fdba74;
  background: #fff7ed;
}

.kondisi-btn--hilang:hover {
  border-color: #fca5a5;
  background: #fef2f2;
}

.kondisi-btn--baik.kondisi-btn--active {
  border-color: #16a34a;
  background: #f0fdf4;
  color: #14532d;
}

.kondisi-btn--ringan.kondisi-btn--active {
  border-color: #d97706;
  background: #fffbeb;
  color: #78350f;
}

.kondisi-btn--berat.kondisi-btn--active {
  border-color: #ea580c;
  background: #fff7ed;
  color: #7c2d12;
}

.kondisi-btn--hilang.kondisi-btn--active {
  border-color: #dc2626;
  background: #fef2f2;
  color: #7f1d1d;
}

.kondisi-btn--baik.kondisi-btn--active .kondisi-check {
  color: #16a34a;
}

.kondisi-btn--ringan.kondisi-btn--active .kondisi-check {
  color: #d97706;
}

.kondisi-btn--berat.kondisi-btn--active .kondisi-check {
  color: #ea580c;
}

.kondisi-btn--hilang.kondisi-btn--active .kondisi-check {
  color: #dc2626;
}

.kondisi-desc {
  font-size: 12px;
  padding: 9px 12px;
  border-radius: 8px;
  line-height: 1.5;
}

.kondisi-desc--baik {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.kondisi-desc--ringan {
  background: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
}

.kondisi-desc--berat {
  background: #fff7ed;
  color: #7c2d12;
  border: 1px solid #fed7aa;
}

.kondisi-desc--hilang {
  background: #fef2f2;
  color: #7f1d1d;
  border: 1px solid #fecaca;
}

/* Catatan */
.catatan-input {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: 13px;
  color: #111827;
  font-family: inherit;
  resize: vertical;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
  line-height: 1.5;
}

.catatan-input:focus {
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.catatan-input::placeholder {
  color: #c4c9d1;
}

.catatan-input--error {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.error-msg {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: #dc2626;
  margin: 0;
}

/* ── Footer ── */
.modal-footer {
  display: flex;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
}

.btn-batal {
  flex: 1;
  padding: 11px;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  font-size: 13.5px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}

.btn-batal:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.btn-simpan {
  flex: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 11px;
  border: none;
  border-radius: 10px;
  background: #065f46;
  color: white;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
  box-shadow: 0 2px 8px rgba(6, 95, 70, 0.3);
}

.btn-simpan:hover:not(:disabled) {
  background: #047857;
}

.btn-simpan:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.spin-icon {
  animation: spin 0.7s linear infinite;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>