<template>
    <div class="form-page">

        <!-- ── Header ─────────────────────────────────────────────────── -->
        <div class="page-top">
            <div class="page-top__left">
                <RouterLink to="/barang" class="btn-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        width="15" height="15">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Kembali
                </RouterLink>
                <div>
                    <h1 class="pg-title">Edit Barang</h1>
                    <p class="pg-sub">Perbarui informasi barang inventaris.</p>
                </div>
            </div>
            <RouterLink v-if="barang" :to="`/barang/${route.params.id}`" class="btn-detail">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    width="13" height="13">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                Lihat Detail
            </RouterLink>
        </div>

        <!-- Loading state -->
        <div v-if="loadingBarang" class="loading-state">
            <div class="loading-spinner"></div>
            <p>Memuat data barang...</p>
        </div>

        <!-- ── Content grid ────────────────────────────────────────────── -->
        <div v-else class="content-grid">

            <!-- LEFT: Form ───────────────────────────────────────────────── -->
            <div class="form-col">

                <!-- Section: Informasi Dasar -->
                <div class="form-card">
                    <div class="form-card__header">
                        <div class="form-card__icon form-card__icon--blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Informasi Dasar</h2>
                    </div>

                    <div class="form-body">
                        <div class="field-group">
                            <label class="field-label">Nama Barang <span class="required">*</span></label>
                            <input v-model="form.nama_barang" type="text" class="field-input"
                                :class="{ 'field-input--error': errors.nama_barang }"
                                placeholder="Contoh: Kursi Lipat, Tenda, Karpet..." />
                            <p v-if="errors.nama_barang" class="field-error">{{ errors.nama_barang }}</p>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Kategori <span class="required">*</span></label>
                            <select v-model="form.kategori_id" class="field-input"
                                :class="{ 'field-input--error': errors.kategori_id }">
                                <option value="">Pilih kategori...</option>
                                <option v-for="k in kategoris" :key="k.id" :value="k.id">
                                    {{ k.ikon ? k.ikon + ' ' : '' }}{{ k.nama }}
                                </option>
                            </select>
                            <p v-if="errors.kategori_id" class="field-error">{{ errors.kategori_id }}</p>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Deskripsi</label>
                            <textarea v-model="form.deskripsi" class="field-input field-textarea"
                                placeholder="Keterangan tambahan tentang barang ini..." rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Stok & Kondisi -->
                <div class="form-card">
                    <div class="form-card__header">
                        <div class="form-card__icon form-card__icon--green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <line x1="8" y1="6" x2="21" y2="6" />
                                <line x1="8" y1="12" x2="21" y2="12" />
                                <line x1="8" y1="18" x2="21" y2="18" />
                                <line x1="3" y1="6" x2="3.01" y2="6" />
                                <line x1="3" y1="12" x2="3.01" y2="12" />
                                <line x1="3" y1="18" x2="3.01" y2="18" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Stok & Kondisi</h2>
                    </div>

                    <div class="form-body">
                        <!-- Stok info banner jika ada yang dipinjam -->
                        <div v-if="stokDipinjam > 0" class="stok-warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <span>
                                Saat ini <strong>{{ stokDipinjam }} unit</strong> sedang dipinjam.
                                Stok total tidak bisa dikurangi di bawah jumlah yang sedang dipinjam.
                            </span>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Jumlah Stok Total <span class="required">*</span></label>
                                <div class="number-input-wrap">
                                    <button type="button" class="num-btn"
                                        @click="form.stok_total = Math.max(stokDipinjam || 1, form.stok_total - 1)">−</button>
                                    <input v-model.number="form.stok_total" type="number" :min="stokDipinjam || 1"
                                        class="field-input num-input"
                                        :class="{ 'field-input--error': errors.stok_total }" />
                                    <button type="button" class="num-btn" @click="form.stok_total++">+</button>
                                </div>
                                <p v-if="errors.stok_total" class="field-error">{{ errors.stok_total }}</p>
                                <!-- Stok tersedia preview -->
                                <p v-if="barang" class="stok-preview">
                                    Tersedia setelah simpan:
                                    <strong :class="stokTersediaBaru < 0 ? 'text-red' : 'text-green'">
                                        {{ Math.max(0, form.stok_total - stokDipinjam) }} unit
                                    </strong>
                                </p>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Kondisi <span class="required">*</span></label>
                                <div class="kondisi-options">
                                    <label v-for="k in kondisiList" :key="k.value" class="kondisi-opt"
                                        :class="[`kondisi-opt--${k.cls}`, { 'kondisi-opt--active': form.kondisi === k.value }]">
                                        <input type="radio" :value="k.value" v-model="form.kondisi" class="sr-only" />
                                        <span class="kondisi-dot-sm" :class="`kondisi-dot-sm--${k.cls}`"></span>
                                        {{ k.label }}
                                    </label>
                                </div>
                                <p v-if="errors.kondisi" class="field-error">{{ errors.kondisi }}</p>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Lokasi Penyimpanan</label>
                            <div class="input-icon-wrap">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" width="14" height="14">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <input v-model="form.lokasi" type="text" class="field-input field-input--icon"
                                    placeholder="Contoh: Gudang Lt.1, Lemari Utama..." />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="form-actions">
                    <RouterLink to="/barang" class="btn-cancel">Batal</RouterLink>
                    <button class="btn-submit" :disabled="submitting" @click="handleSubmit">
                        <svg v-if="submitting" class="spin-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" width="15" height="15">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" width="15" height="15">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </div>

            <!-- RIGHT: Foto & Info ─────────────────────────────────────── -->
            <div class="right-col">

                <!-- Foto management -->
                <div class="form-card">
                    <div class="form-card__header">
                        <div class="form-card__icon form-card__icon--orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="15" height="15">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                        </div>
                        <h2 class="form-card__title">Foto Barang</h2>
                    </div>

                    <div class="form-body">
                        <div class="foto-dropzone"
                            :class="{ 'foto-dropzone--has-foto': currentFotoUrl || fotoPreview, 'foto-dropzone--drag': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="$refs.fotoInput.click()">
                            <img v-if="fotoPreview || currentFotoUrl" :src="fotoPreview || currentFotoUrl" alt="Preview"
                                class="foto-preview-img" />
                            <div v-else class="dropzone-inner">
                                <div class="dropzone-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" width="28" height="28">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                </div>
                                <p class="dropzone-text">Klik atau seret foto ke sini</p>
                                <p class="dropzone-sub">PNG, JPG, WEBP — maks. 2 MB</p>
                            </div>
                            <!-- New badge -->
                            <div v-if="fotoPreview" class="foto-new-badge">Foto Baru</div>
                        </div>
                        <input ref="fotoInput" type="file" accept="image/*" class="sr-only" @change="onFotoChange" />

                        <div class="foto-actions" v-if="currentFotoUrl || fotoPreview">
                            <button type="button" class="foto-action-btn" @click="$refs.fotoInput.click()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="13" height="13">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                Ganti
                            </button>
                            <button type="button" class="foto-action-btn foto-action-btn--danger"
                                @click="fotoPreview ? clearFotoNew() : confirmHapusFoto()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="13" height="13">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                {{ fotoPreview ? 'Batalkan' : 'Hapus Foto' }}
                            </button>
                        </div>
                        <p v-if="errors.foto" class="field-error" style="margin-top:6px">{{ errors.foto }}</p>
                    </div>
                </div>

                <!-- Info barang saat ini -->
                <div class="info-card" v-if="barang">
                    <p class="info-card__title">Informasi Saat Ini</p>
                    <div class="info-stok-grid">
                        <div class="info-stok-item">
                            <p class="info-stok-val info-stok-val--green">{{ barang.stok_tersedia ?? '—' }}</p>
                            <p class="info-stok-label">Tersedia</p>
                        </div>
                        <div class="info-stok-divider"></div>
                        <div class="info-stok-item">
                            <p class="info-stok-val info-stok-val--orange">{{ stokDipinjam }}</p>
                            <p class="info-stok-label">Dipinjam</p>
                        </div>
                        <div class="info-stok-divider"></div>
                        <div class="info-stok-item">
                            <p class="info-stok-val">{{ barang.stok_total ?? '—' }}</p>
                            <p class="info-stok-label">Total</p>
                        </div>
                    </div>
                    <div class="info-meta">
                        <div class="info-meta-row">
                            <span class="info-meta-label">Kondisi saat ini</span>
                            <span class="badge-kondisi" :class="kondisiClass(barang.kondisi)">{{ barang.kondisi
                                }}</span>
                        </div>
                        <div class="info-meta-row" v-if="barang.lokasi">
                            <span class="info-meta-label">Lokasi</span>
                            <span class="info-meta-val">{{ barang.lokasi }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-card">
                    <p class="tips-title">⚠️ Catatan Penting</p>
                    <ul class="tips-list">
                        <li>Stok total tidak bisa dikurangi di bawah jumlah yang sedang dipinjam</li>
                        <li>Stok tersedia akan otomatis dihitung ulang saat stok total berubah</li>
                        <li>Foto lama akan dihapus otomatis saat diganti</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal hapus foto -->
        <div v-if="showHapusFotoModal" class="modal-overlay" @click.self="showHapusFotoModal = false">
            <div class="modal">
                <div class="modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="22" height="22">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                </div>
                <h3 class="modal-title">Hapus Foto?</h3>
                <p class="modal-body">Foto barang akan dihapus permanen dari server.</p>
                <div class="modal-actions">
                    <button class="btn-cancel-modal" @click="showHapusFotoModal = false">Batal</button>
                    <button class="btn-confirm-delete" :disabled="hapusingFoto" @click="doHapusFoto">
                        {{ hapusingFoto ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useBarangStore } from '@/stores/barang'
import { useKategoriStore } from '@/stores/kategori'
import barangService from '@/services/barangService'
import { useToast } from 'vue-toastification'

const router = useRouter()
const route = useRoute()
const store = useBarangStore()
const kategoriStore = useKategoriStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const barang = ref(null)
const loadingBarang = ref(true)
const submitting = ref(false)
const fotoPreview = ref(null)
const fotoFile = ref(null)
const currentFotoUrl = ref(null)
const isDragging = ref(false)
const showHapusFotoModal = ref(false)
const hapusingFoto = ref(false)
const fotoInput = ref(null)

const form = ref({
    nama_barang: '',
    kategori_id: '',
    deskripsi: '',
    stok_total: 1,
    kondisi: 'Baik',
    lokasi: '',
})

const errors = ref({})

const kondisiList = [
    { value: 'Baik', label: 'Baik', cls: 'baik' },
    { value: 'Rusak Ringan', label: 'Rusak Ringan', cls: 'ringan' },
    { value: 'Rusak Berat', label: 'Rusak Berat', cls: 'berat' },
]

// ── Computed ────────────────────────────────────────────────────────
const kategoris = computed(() => kategoriStore.kategoris)

const stokDipinjam = computed(() => {
    if (!barang.value) return 0
    return (barang.value.stok_total ?? 0) - (barang.value.stok_tersedia ?? 0)
})

const stokTersediaBaru = computed(() => form.value.stok_total - stokDipinjam.value)

// ── Methods ─────────────────────────────────────────────────────────
async function loadData() {
    loadingBarang.value = true
    try {
        const [barangData] = await Promise.all([
            barangService.getOne(route.params.id),
            loadKategoris(),
        ])
        barang.value = barangData
        // Prefill form
        form.value = {
            nama_barang: barangData.nama_barang ?? '',
            kategori_id: barangData.kategori_id ?? barangData.kategori?.id ?? '',
            deskripsi: barangData.deskripsi ?? '',
            stok_total: barangData.stok_total ?? 1,
            kondisi: barangData.kondisi ?? 'Baik',
            lokasi: barangData.lokasi ?? '',
        }
        currentFotoUrl.value = barangData.foto_url ?? null
    } catch {
        toast.error('Gagal memuat data barang.')
        router.push('/barang')
    } finally {
        loadingBarang.value = false
    }
}


async function loadKategoris() {
    if (!kategoriStore.kategoris.length) {
        await kategoriStore.fetchKategoris()
    }
}

function validate() {
    const e = {}
    if (!form.value.nama_barang.trim()) e.nama_barang = 'Nama barang wajib diisi.'
    if (!form.value.kategori_id) e.kategori_id = 'Pilih kategori terlebih dahulu.'
    if (!form.value.stok_total || form.value.stok_total < 1) e.stok_total = 'Stok minimal 1.'
    if (form.value.stok_total < stokDipinjam.value) e.stok_total = `Stok tidak bisa dikurangi di bawah ${stokDipinjam.value} (sedang dipinjam).`
    if (!form.value.kondisi) e.kondisi = 'Pilih kondisi barang.'
    if (fotoFile.value && fotoFile.value.size > 2 * 1024 * 1024) e.foto = 'Ukuran foto maksimal 2 MB.'
    errors.value = e
    return Object.keys(e).length === 0
}

async function handleSubmit() {
    if (!validate()) return
    submitting.value = true
    try {
        const fd = new FormData()
        Object.entries(form.value).forEach(([k, v]) => {
            if (v !== null && v !== undefined) fd.append(k, v)
        })
        if (fotoFile.value) fd.append('foto', fotoFile.value)
        await store.updateBarang(route.params.id, fd)
        toast.success('Barang berhasil diperbarui!')
        router.push('/barang')
    } catch (err) {
        const errData = err?.response?.data?.errors
        if (errData) {
            const mapped = {}
            Object.entries(errData).forEach(([k, v]) => { mapped[k] = Array.isArray(v) ? v[0] : v })
            errors.value = mapped
        }
        toast.error(err?.response?.data?.message || 'Gagal memperbarui barang.')
    } finally {
        submitting.value = false
    }
}

function onFotoChange(e) {
    const file = e.target.files?.[0]
    if (!file) return
    fotoFile.value = file
    fotoPreview.value = URL.createObjectURL(file)
}

function onDrop(e) {
    isDragging.value = false
    const file = e.dataTransfer.files?.[0]
    if (!file || !file.type.startsWith('image/')) return
    fotoFile.value = file
    fotoPreview.value = URL.createObjectURL(file)
}

function clearFotoNew() {
    fotoFile.value = null
    fotoPreview.value = null
    if (fotoInput.value) fotoInput.value.value = ''
}

function confirmHapusFoto() {
    showHapusFotoModal.value = true
}

async function doHapusFoto() {
    hapusingFoto.value = true
    try {
        await barangService.hapusFoto(route.params.id)
        currentFotoUrl.value = null
        barang.value = { ...barang.value, foto_url: null }
        showHapusFotoModal.value = false
        toast.success('Foto berhasil dihapus.')
    } catch {
        toast.error('Gagal menghapus foto.')
    } finally {
        hapusingFoto.value = false
    }
}

function kondisiClass(kondisi) {
    if (kondisi === 'Baik') return 'badge-kondisi--baik'
    if (kondisi === 'Rusak Ringan') return 'badge-kondisi--ringan'
    return 'badge-kondisi--berat'
}

onMounted(() => loadData())
</script>

<style scoped>
.form-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    max-width: 960px;
}

.page-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-top__left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-decoration: none;
    border: 1.5px solid #e5e7eb;
    padding: 7px 12px;
    border-radius: 9px;
    background: white;
    transition: all 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 2px;
}

.btn-back:hover {
    border-color: #d1d5db;
    color: #374151;
    background: #f9fafb;
}

.btn-detail {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    font-weight: 600;
    color: #2563eb;
    text-decoration: none;
    border: 1.5px solid #bfdbfe;
    padding: 7px 14px;
    border-radius: 9px;
    background: #eff6ff;
    transition: all 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-detail:hover {
    background: #dbeafe;
}

.pg-title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.pg-sub {
    font-size: 13px;
    color: #6b7280;
    margin: 2px 0 0;
}

/* Loading */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px;
    gap: 12px;
    color: #9ca3af;
    font-size: 14px;
}

.loading-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #f0f0f0;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 272px;
    gap: 16px;
    align-items: start;
}

.form-col,
.right-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Form card */
.form-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.form-card__header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid #f0f0f0;
    background: #fafafa;
}

.form-card__icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.form-card__icon--blue {
    background: #eff6ff;
    color: #2563eb;
}

.form-card__icon--green {
    background: #f0fdf4;
    color: #16a34a;
}

.form-card__icon--orange {
    background: #fff7ed;
    color: #ea580c;
}

.form-card__title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.form-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Fields */
.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.field-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
}

.required {
    color: #ef4444;
    margin-left: 2px;
}

.field-input {
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #111827;
    background: white;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
}

.field-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.field-input::placeholder {
    color: #c4c9d1;
}

.field-input--error {
    border-color: #fca5a5;
    background: #fff5f5;
}

.field-textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.6;
}

.field-error {
    font-size: 11.5px;
    color: #ef4444;
    margin: 0;
}

.field-error::before {
    content: '⚠ ';
}

.input-icon-wrap {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.field-input--icon {
    padding-left: 34px;
}

/* Stok warning */
.stok-warning {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 9px;
    font-size: 12.5px;
    color: #92400e;
    line-height: 1.5;
}

.stok-preview {
    font-size: 11.5px;
    color: #6b7280;
    margin: 0;
}

.text-green {
    color: #16a34a;
}

.text-red {
    color: #ef4444;
}

/* Number input */
.number-input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    overflow: hidden;
    background: white;
    transition: border-color 0.2s;
}

.number-input-wrap:focus-within {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.num-btn {
    width: 36px;
    height: 40px;
    border: none;
    background: #f9fafb;
    color: #374151;
    font-size: 17px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
    flex-shrink: 0;
    font-family: inherit;
}

.num-btn:hover {
    background: #f0fdf4;
    color: #16a34a;
}

.num-input {
    flex: 1;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
    padding: 9px 4px;
    min-width: 0;
}

.num-input::-webkit-inner-spin-button,
.num-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
}

/* Kondisi */
.kondisi-options {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.kondisi-opt {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s;
    background: white;
}

.kondisi-opt--baik.kondisi-opt--active {
    border-color: #16a34a;
    background: #f0fdf4;
    color: #166534;
}

.kondisi-opt--ringan.kondisi-opt--active {
    border-color: #f59e0b;
    background: #fffbeb;
    color: #92400e;
}

.kondisi-opt--berat.kondisi-opt--active {
    border-color: #ef4444;
    background: #fef2f2;
    color: #991b1b;
}

.kondisi-dot-sm {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.kondisi-dot-sm--baik {
    background: #16a34a;
}

.kondisi-dot-sm--ringan {
    background: #f59e0b;
}

.kondisi-dot-sm--berat {
    background: #ef4444;
}

/* Foto */
.foto-dropzone {
    width: 100%;
    aspect-ratio: 4/3;
    border: 2px dashed #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fafafa;
    position: relative;
}

.foto-dropzone:hover {
    border-color: #86efac;
    background: #f0fdf4;
}

.foto-dropzone--drag {
    border-color: #16a34a;
    background: #f0fdf4;
}

.foto-dropzone--has-foto {
    border-style: solid;
    border-color: #e5e7eb;
    background: transparent;
}

.dropzone-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px;
    text-align: center;
}

.dropzone-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: white;
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
}

.dropzone-text {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.dropzone-sub {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 0;
}

.foto-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.foto-new-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #16a34a;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}

.foto-actions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

.foto-action-btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.foto-action-btn:hover {
    background: #f9fafb;
}

.foto-action-btn--danger {
    color: #dc2626;
    border-color: #fecaca;
}

.foto-action-btn--danger:hover {
    background: #fef2f2;
}

/* Info card */
.info-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
}

.info-card__title {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 12px;
}

.info-stok-grid {
    display: flex;
    align-items: center;
    background: #f9fafb;
    border-radius: 9px;
    padding: 12px 0;
    margin-bottom: 12px;
}

.info-stok-item {
    flex: 1;
    text-align: center;
}

.info-stok-val {
    font-size: 20px;
    font-weight: 800;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.info-stok-val--green {
    color: #16a34a;
}

.info-stok-val--orange {
    color: #f59e0b;
}

.info-stok-label {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    margin: 3px 0 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.info-stok-divider {
    width: 1px;
    height: 32px;
    background: #e5e7eb;
    flex-shrink: 0;
}

.info-meta {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.info-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f9fafb;
    font-size: 12.5px;
    gap: 8px;
}

.info-meta-row:last-child {
    border-bottom: none;
}

.info-meta-label {
    color: #9ca3af;
    font-weight: 500;
}

.info-meta-val {
    color: #111827;
    font-weight: 500;
    text-align: right;
}

/* Badge kondisi */
.badge-kondisi {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.badge-kondisi--baik {
    background: #dcfce7;
    color: #166534;
}

.badge-kondisi--ringan {
    background: #fef3c7;
    color: #92400e;
}

.badge-kondisi--berat {
    background: #fee2e2;
    color: #991b1b;
}

/* Tips card */
.tips-card {
    background: #fffbeb;
    border-radius: 11px;
    border: 1px solid #fde68a;
    padding: 14px 16px;
}

.tips-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #92400e;
    margin: 0 0 8px;
}

.tips-list {
    margin: 0;
    padding-left: 16px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.tips-list li {
    font-size: 12px;
    color: #92400e;
    opacity: 0.85;
    line-height: 1.5;
}

/* Form actions */
.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    font-family: inherit;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 22px;
    border-radius: 9px;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}

.btn-submit:hover:not(:disabled) {
    background: #15803d;
}

.btn-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.spin-icon {
    animation: spin 0.8s linear infinite;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal {
    background: white;
    border-radius: 16px;
    padding: 24px;
    max-width: 360px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    color: #dc2626;
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px;
}

.modal-body {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0 0 20px;
    line-height: 1.6;
}

.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

.btn-cancel-modal {
    padding: 9px 18px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    font-family: inherit;
}

.btn-cancel-modal:hover {
    background: #f9fafb;
}

.btn-confirm-delete {
    padding: 9px 18px;
    border: none;
    border-radius: 9px;
    background: #dc2626;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-confirm-delete:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-confirm-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 820px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .right-col {
        order: -1;
    }

    .foto-dropzone {
        aspect-ratio: 16/9;
    }

    .field-row {
        grid-template-columns: 1fr;
    }
}
</style>