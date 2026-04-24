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
                    <h1 class="pg-title">Tambah Barang</h1>
                    <p class="pg-sub">Isi formulir berikut untuk menambahkan barang inventaris baru.</p>
                </div>
            </div>
        </div>

        <!-- ── Content grid ────────────────────────────────────────────── -->
        <div class="content-grid">

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
                        <!-- Nama Barang -->
                        <div class="field-group">
                            <label class="field-label">Nama Barang <span class="required">*</span></label>
                            <input v-model="form.nama_barang" type="text" class="field-input"
                                :class="{ 'field-input--error': errors.nama_barang }"
                                placeholder="Contoh: Kursi Lipat, Tenda, Karpet..." />
                            <p v-if="errors.nama_barang" class="field-error">{{ errors.nama_barang }}</p>
                        </div>

                        <!-- Kategori -->
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

                        <!-- Deskripsi -->
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
                        <div class="field-row">
                            <!-- Stok -->
                            <div class="field-group">
                                <label class="field-label">Jumlah Stok <span class="required">*</span></label>
                                <div class="number-input-wrap">
                                    <button type="button" class="num-btn"
                                        @click="form.stok_total = Math.max(1, form.stok_total - 1)">−</button>
                                    <input v-model.number="form.stok_total" type="number" min="1"
                                        class="field-input num-input"
                                        :class="{ 'field-input--error': errors.stok_total }" />
                                    <button type="button" class="num-btn" @click="form.stok_total++">+</button>
                                </div>
                                <p v-if="errors.stok_total" class="field-error">{{ errors.stok_total }}</p>
                            </div>

                            <!-- Kondisi -->
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

                        <!-- Lokasi -->
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
                        {{ submitting ? 'Menyimpan...' : 'Simpan Barang' }}
                    </button>
                </div>
            </div>

            <!-- RIGHT: Foto & Preview ─────────────────────────────────────── -->
            <div class="right-col">

                <!-- Upload foto -->
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
                        <!-- Preview / dropzone -->
                        <div class="foto-dropzone"
                            :class="{ 'foto-dropzone--has-foto': fotoPreview, 'foto-dropzone--drag': isDragging }"
                            @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @drop.prevent="onDrop"
                            @click="$refs.fotoInput.click()">
                            <img v-if="fotoPreview" :src="fotoPreview" alt="Preview" class="foto-preview-img" />
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
                        </div>
                        <input ref="fotoInput" type="file" accept="image/*" class="sr-only" @change="onFotoChange" />

                        <!-- Foto actions -->
                        <div v-if="fotoPreview" class="foto-actions">
                            <button type="button" class="foto-action-btn" @click="$refs.fotoInput.click()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="13" height="13">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                Ganti Foto
                            </button>
                            <button type="button" class="foto-action-btn foto-action-btn--danger" @click="clearFoto">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" width="13" height="13">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                        <p v-if="errors.foto" class="field-error" style="margin-top:6px">{{ errors.foto }}</p>
                    </div>
                </div>

                <!-- Preview card -->
                <div class="preview-card" v-if="form.nama_barang">
                    <p class="preview-label">Pratinjau Kartu</p>
                    <div class="preview-item">
                        <div class="preview-img-wrap">
                            <img v-if="fotoPreview" :src="fotoPreview" class="preview-img" />
                            <div v-else class="preview-img-placeholder" :style="{ background: itemColor(form.nama_barang) }">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" width="16" height="16" style="color:white;opacity:.8">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                            </div>
                        </div>
                        <div class="preview-info">
                            <p class="preview-name">{{ form.nama_barang || 'Nama Barang' }}</p>
                            <p class="preview-kat">{{ selectedKategoriLabel }}</p>
                        </div>
                        <div class="preview-stok">
                            <p class="preview-stok-val">{{ form.stok_total }}</p>
                            <p class="preview-stok-label">Stok</p>
                        </div>
                    </div>
                    <div class="preview-badges">
                        <span v-if="form.kondisi" class="badge-kondisi" :class="kondisiClass(form.kondisi)">{{
                            form.kondisi }}</span>
                        <span v-if="form.lokasi" class="preview-lokasi">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="10" height="10">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            {{ form.lokasi }}
                        </span>
                    </div>
                </div>

                <!-- Tips card -->
                <div class="tips-card">
                    <p class="tips-title">💡 Tips Pengisian</p>
                    <ul class="tips-list">
                        <li>Gunakan nama yang jelas dan spesifik</li>
                        <li>Stok awal = seluruh unit yang dimiliki</li>
                        <li>Foto membantu warga mengenali barang</li>
                        <li>Catat lokasi agar mudah dicari</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useBarangStore } from '@/stores/barang'
import { useKategoriStore } from '@/stores/kategori'
import { useToast } from 'vue-toastification'

const router = useRouter()
const store = useBarangStore()
const kategoriStore = useKategoriStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const submitting = ref(false)
const fotoPreview = ref(null)
const fotoFile = ref(null)
const isDragging = ref(false)
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

const selectedKategoriLabel = computed(() => {
    const k = kategoris.value.find(k => k.id == form.value.kategori_id)
    return k ? `${k.ikon ?? ''} ${k.nama}`.trim() : 'Pilih Kategori'
})

// ── Methods ─────────────────────────────────────────────────────────
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
        Object.entries(form.value).forEach(([k, v]) => { if (v !== '' && v !== null) fd.append(k, v) })
        if (fotoFile.value) fd.append('foto', fotoFile.value)
        await store.createBarang(fd)
        toast.success('Barang berhasil ditambahkan!')
        router.push('/barang')
    } catch (err) {
        const msg = err?.response?.data?.message
        const errData = err?.response?.data?.errors
        if (errData) {
            const mapped = {}
            Object.entries(errData).forEach(([k, v]) => { mapped[k] = Array.isArray(v) ? v[0] : v })
            errors.value = mapped
        }
        toast.error(msg || 'Gagal menyimpan barang.')
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

function clearFoto() {
    fotoFile.value = null
    fotoPreview.value = null
    if (fotoInput.value) fotoInput.value.value = ''
}

// ── Helpers ──────────────────────────────────────────────────────────
const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function itemColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}

function kondisiClass(kondisi) {
    if (kondisi === 'Baik') return 'badge-kondisi--baik'
    if (kondisi === 'Rusak Ringan') return 'badge-kondisi--ringan'
    return 'badge-kondisi--berat'
}

onMounted(() => loadKategoris())
</script>

<style scoped>
.form-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    max-width: 960px;
}

/* ── Header ──────────────────────────────────────────────────────── */
.page-top {
    margin-bottom: 20px;
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

/* ── Grid ────────────────────────────────────────────────────────── */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 272px;
    gap: 16px;
    align-items: start;
}

.form-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.right-col {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ── Form card ───────────────────────────────────────────────────── */
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

/* ── Fields ──────────────────────────────────────────────────────── */
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

.field-input--error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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
    display: flex;
    align-items: center;
    gap: 4px;
}

.field-error::before {
    content: '⚠';
    font-size: 10px;
}

/* Input with icon */
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

/* Number input */
.number-input-wrap {
    display: flex;
    align-items: center;
    gap: 0;
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
    font-weight: 500;
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

/* Kondisi radio options */
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

.kondisi-opt:hover {
    border-color: #d1d5db;
    color: #374151;
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

/* ── Foto dropzone ────────────────────────────────────────────────── */
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
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
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

/* ── Preview card ─────────────────────────────────────────────────── */
.preview-card {
    background: white;
    border-radius: 11px;
    border: 1px solid #f0f0f0;
    padding: 14px;
}

.preview-label {
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 10px;
}

.preview-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.preview-img-wrap {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid #f0f0f0;
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-info {
    flex: 1;
    min-width: 0;
}

.preview-name {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-kat {
    font-size: 11px;
    color: #9ca3af;
    margin: 1px 0 0;
}

.preview-stok {
    text-align: right;
    flex-shrink: 0;
}

.preview-stok-val {
    font-size: 18px;
    font-weight: 800;
    color: #16a34a;
    margin: 0;
    line-height: 1;
}

.preview-stok-label {
    font-size: 10px;
    color: #9ca3af;
    margin: 2px 0 0;
}

.preview-badges {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.badge-kondisi {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
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

.preview-lokasi {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    color: #6b7280;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
}

/* ── Tips card ────────────────────────────────────────────────────── */
.tips-card {
    background: #f0fdf4;
    border-radius: 11px;
    border: 1px solid #bbf7d0;
    padding: 14px 16px;
}

.tips-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #166534;
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
    color: #166534;
    opacity: 0.8;
    line-height: 1.5;
}

/* ── Action buttons ───────────────────────────────────────────────── */
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

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
}

/* ── Responsive ──────────────────────────────────────────────────── */
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