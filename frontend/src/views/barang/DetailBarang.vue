<template>
    <div class="">

        <!-- ── Breadcrumb ──────────────────────────────────────────── -->
        <div class="breadcrumb">
            <RouterLink to="/barang" class="bc-link">Barang</RouterLink>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                width="13" height="13" class="bc-sep">
                <polyline points="9 18 15 12 9 6" />
            </svg>
            <span class="bc-current">Detail Barang</span>
        </div>

        <!-- ── Loading ─────────────────────────────────────────────── -->
        <div v-if="loading" class="loading-wrap">
            <div class="spinner"></div>
            <p>Memuat data barang...</p>
        </div>

        <!-- ── Not found ───────────────────────────────────────────── -->
        <div v-else-if="!barang" class="empty-page">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"
                style="color:#d1d5db">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p>Barang tidak ditemukan.</p>
            <RouterLink to="/barang" class="btn-back-link">Kembali ke Daftar</RouterLink>
        </div>

        <!-- ── Content ─────────────────────────────────────────────── -->
        <template v-else>
            <div class="card shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06),0px_1px_1px_-0.5px_rgba(0,0,0,0.06),0px_3px_3px_-1.5px_rgba(0,0,0,0.06),_0px_6px_6px_-3px_rgba(0,0,0,0.06),0px_12px_12px_-6px_rgba(0,0,0,0.06),0px_24px_24px_-12px_rgba(0,0,0,0.06)]">

                <!-- Asset ID pill -->
                <div class="asset-id-row">
                    <span class="asset-pill">
                        <span class="asset-dot"></span>
                        ASET ID: {{ formatId(barang.id) }}
                    </span>
                    <span class="kat-badge" :class="kategoriClass(barang.kategori?.nama)">
                        {{ barang.kategori?.nama ?? '—' }}
                    </span>
                </div>

                <!-- ── Foto ─────────────────────────────────────────── -->
                <div class="foto-block">
                    <img v-if="barang.foto_url" :src="barang.foto_url" :alt="barang.nama_barang" class="foto-img" />
                    <div v-else class="foto-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="52"
                            height="52" style="color:#d1d5db">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Tidak ada foto</span>
                    </div>

                    <!-- Ganti foto overlay -->
                    <label class="foto-overlay-btn">
                        <input type="file" accept="image/*" class="hidden-input" @change="onFotoChange" />
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="13" height="13">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Ganti Foto
                    </label>
                </div>

                <!-- ── Nama & Deskripsi ─────────────────────────────── -->
                <div class="nama-block">
                    <h1 class="barang-nama">{{ barang.nama_barang }}</h1>
                    <p class="barang-desc" v-if="barang.deskripsi">{{ barang.deskripsi }}</p>
                    <p class="barang-desc barang-desc--empty" v-else>Tidak ada deskripsi.</p>
                </div>

                <!-- ── Info grid ────────────────────────────────────── -->
                <div class="info-grid">

                    <!-- Stok -->
                    <div class="info-card">
                        <div class="info-icon info-icon--green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                height="18">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <p class="info-label">Stok Saat Ini</p>
                        <p class="info-stok">
                            <span class="stok-num" :class="barang.stok_tersedia > 0 ? 'num--ok' : 'num--warn'">
                                {{ String(barang.stok_tersedia).padStart(2, '0') }}
                            </span>
                            <span class="stok-total">/ {{ String(barang.stok_total).padStart(2, '0') }} Tersedia</span>
                        </p>
                    </div>

                    <!-- Lokasi -->
                    <div class="info-card">
                        <div class="info-icon info-icon--gray">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                height="18">
                                <path
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="info-label">Lokasi Penyimpanan</p>
                        <p class="info-val">{{ barang.lokasi || 'Belum ditentukan' }}</p>
                    </div>

                    <!-- Kondisi -->
                    <div class="info-card">
                        <div class="info-icon" :class="kondisiIconClass(barang.kondisi)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                height="18">
                                <path v-if="barang.kondisi === 'Baik'"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path v-else-if="barang.kondisi === 'Rusak Ringan'"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                <path v-else d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="info-label">Kondisi Barang</p>
                        <p class="info-val">{{ kondisiLabel(barang.kondisi) }}</p>
                    </div>

                    <!-- Status -->
                    <div class="info-card">
                        <div class="info-icon"
                            :class="barang.stok_tersedia > 0 ? 'info-icon--green' : 'info-icon--orange'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18"
                                height="18">
                                <path v-if="barang.stok_tersedia > 0"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path v-else d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="info-label">Status</p>
                        <p class="info-val">
                            <span class="status-badge"
                                :class="barang.stok_tersedia > 0 ? 'status--ok' : 'status--warn'">
                                {{ barang.stok_tersedia > 0 ? 'Tersedia' : 'Dipinjam' }}
                            </span>
                        </p>
                    </div>

                </div>

                <!-- Kondisi detail banner -->
                <div class="kondisi-banner" :class="kondisiBannerClass(barang.kondisi)">
                    <div class="kondisi-banner__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="16" height="16">
                            <path v-if="barang.kondisi === 'Baik'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path v-else-if="barang.kondisi === 'Rusak Ringan'"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <path v-else d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="kondisi-banner__body">
                        <p class="kondisi-banner__label">Kondisi Barang</p>
                        <p class="kondisi-banner__val">{{ kondisiLabel(barang.kondisi) }}</p>
                    </div>
                    <span class="aset-pill-badge"
                        :class="barang.stok_tersedia > 0 ? 'aset-pill--ok' : 'aset-pill--warn'">
                        {{ barang.stok_tersedia > 0 ? 'Aset Tersedia' : 'Aset Dipinjam' }}
                    </span>
                </div>

                <!-- ── Action buttons ───────────────────────────────── -->
                <div class="actions">
                    <button class="btn-edit" @click="openEdit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="15" height="15">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit Barang
                    </button>
                    <button class="btn-delete" @click="showDeleteModal = true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="15" height="15">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6l-1 14H6L5 6" />
                            <path d="M10 11v6" />
                            <path d="M14 11v6" />
                            <path d="M9 6V4h6v2" />
                        </svg>
                        Hapus Barang
                    </button>
                </div>

            </div>
        </template>

        <!-- ── Modal Edit ──────────────────────────────────────────── -->
        <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
            <div class="modal">
                <div class="modal-top">
                    <h3 class="modal-title">Edit Barang</h3>
                    <button class="modal-close" @click="showEditModal = false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="18" height="18">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="modal-form">
                    <!-- Foto preview -->
                    <div class="form-foto-wrap">
                        <div class="form-foto">
                            <img v-if="editFotoPreview" :src="editFotoPreview" alt="preview" />
                            <div v-else class="form-foto-empty">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="22"
                                    height="22" style="color:#d1d5db">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label class="btn-foto">
                                <input type="file" accept="image/*" class="hidden-input" @change="onEditFotoChange" />
                                {{ editFotoPreview ? 'Ganti Foto' : 'Upload Foto' }}
                            </label>
                            <p class="foto-hint">Maks. 2MB · JPG, PNG</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Barang <span class="req">*</span></label>
                        <input v-model="editForm.nama_barang" type="text" class="form-input" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori <span class="req">*</span></label>
                        <select v-model="editForm.kategori_id" class="form-input" required>
                            <option value="" disabled>Pilih kategori...</option>
                            <option v-for="kat in kategoriList" :key="kat.id" :value="kat.id">{{ kat.nama }}</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Stok Total <span class="req">*</span></label>
                            <input v-model.number="editForm.stok_total" type="number" class="form-input" min="1"
                                required />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kondisi <span class="req">*</span></label>
                            <select v-model="editForm.kondisi" class="form-input" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Penyimpanan</label>
                        <input v-model="editForm.lokasi" type="text" class="form-input"
                            placeholder="Contoh: Gudang Lt.1" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea v-model="editForm.deskripsi" class="form-input form-textarea" rows="3"
                            placeholder="Deskripsi barang..."></textarea>
                    </div>

                    <div v-if="editError" class="form-error">{{ editError }}</div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="showEditModal = false">Batal</button>
                        <button type="submit" class="btn-submit" :disabled="submitting">
                            <svg v-if="submitting" class="spin-icon" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" width="14" height="14">
                                <path stroke-linecap="round"
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Modal Hapus ─────────────────────────────────────────── -->
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
            <div class="modal modal--sm">
                <div class="modal-del-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="24" height="24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <h3 class="modal-title">Hapus Barang?</h3>
                <p class="modal-body">
                    Data <strong>{{ barang?.nama }}</strong> akan dihapus permanen.
                    Barang yang sedang dipinjam tidak dapat dihapus.
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" @click="showDeleteModal = false">Batal</button>
                    <button class="btn-confirm-delete" :disabled="deleting" @click="doDelete">
                        {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useBarangStore } from '@/stores/barang'
import barangService from '@/services/barangService'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const store = useBarangStore()
const toast = useToast()

// ── State ──────────────────────────────────────────────────────────
const barang = ref(null)
const loading = ref(false)
const kategoriList = ref([])

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editError = ref('')
const editFotoFile = ref(null)
const editFotoPreview = ref(null)

const editForm = ref({
    kategori_id: '',
    nama_barang: '',
    deskripsi: '',
    stok_total: 1,
    kondisi: 'Baik',
    lokasi: '',
})

// ── Fetch ───────────────────────────────────────────────────────────
async function fetchBarang() {
    loading.value = true
    try {
        barang.value = await barangService.getOne(route.params.id)
    } catch (e) {
        console.error(e)
        barang.value = null
    } finally {
        loading.value = false
    }
}

async function fetchKategori() {
    try {
        const { default: api } = await import('@/services/api')
        const res = await api.get('/kategori').then(r => r.data)
        kategoriList.value = Array.isArray(res) ? res : (res.data ?? [])
    } catch (e) {
        console.error('Gagal memuat kategori:', e)
    }
}

// ── Ganti foto langsung ─────────────────────────────────────────────
async function onFotoChange(e) {
    const file = e.target.files[0]
    if (!file || !barang.value) return
    try {
        const fd = new FormData()
        fd.append('foto', file)
        const res = await barangService.uploadFoto(barang.value.id, fd)
        barang.value = { ...barang.value, foto_url: res.foto_url }
        toast.success('Foto berhasil diperbarui.')
    } catch {
        toast.error('Gagal mengupload foto.')
    }
}

// ── Edit modal ──────────────────────────────────────────────────────
function openEdit() {
    editError.value = ''
    editFotoFile.value = null
    editForm.value = {
        kategori_id: barang.value.kategori_id,
        nama_barang: barang.value.nama_barang,
        deskripsi: barang.value.deskripsi ?? '',
        stok_total: barang.value.stok_total,
        kondisi: barang.value.kondisi,
        lokasi: barang.value.lokasi ?? '',
    }
    editFotoPreview.value = barang.value.foto_url ?? null
    showEditModal.value = true
}

function onEditFotoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    editFotoFile.value = file
    editFotoPreview.value = URL.createObjectURL(file)
}

async function submitEdit() {
    submitting.value = true
    editError.value = ''
    try {
        const fd = new FormData()
        Object.entries(editForm.value).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') fd.append(k, v)
        })
        if (editFotoFile.value) fd.append('foto', editFotoFile.value)

        const updated = await store.updateBarang(barang.value.id, fd)
        barang.value = updated
        showEditModal.value = false
        toast.success('Data barang berhasil diperbarui.')
    } catch (e) {
        const errors = e?.response?.data?.errors
        editError.value = errors
            ? Object.values(errors).flat().join(', ')
            : (e?.response?.data?.message ?? 'Terjadi kesalahan.')
    } finally {
        submitting.value = false
    }
}

// ── Hapus ───────────────────────────────────────────────────────────
async function doDelete() {
    deleting.value = true
    try {
        await store.deleteBarang(barang.value.id)
        toast.success('Barang berhasil dihapus.')
        router.push('/barang')
    } catch (e) {
        toast.error(e?.response?.data?.message ?? 'Gagal menghapus barang.')
        showDeleteModal.value = false
    } finally {
        deleting.value = false
    }
}

// ── Helpers ─────────────────────────────────────────────────────────
function formatId(id) {
    const y = new Date().getFullYear()
    return `MB-${y}-${String(id).padStart(3, '0')}`
}

function kategoriClass(nama) {
    const map = {
        'Elektronik': 'kat--blue',
        'Furniture': 'kat--amber',
        'Alat Masak': 'kat--orange',
        'Sound System': 'kat--purple',
        'Alat Listrik': 'kat--green',
    }
    return map[nama] ?? 'kat--gray'
}

function kondisiIconClass(kondisi) {
    if (kondisi === 'Baik') return 'info-icon--green'
    if (kondisi === 'Rusak Ringan') return 'info-icon--orange'
    return 'info-icon--red'
}

function kondisiLabel(kondisi) {
    if (kondisi === 'Baik') return 'Sangat Baik · Siap Pakai'
    if (kondisi === 'Rusak Ringan') return 'Rusak Ringan · Perlu Perawatan'
    return 'Rusak Berat · Tidak Dapat Dipakai'
}

function kondisiBannerClass(kondisi) {
    if (kondisi === 'Baik') return 'kondisi-banner--ok'
    if (kondisi === 'Rusak Ringan') return 'kondisi-banner--warn'
    return 'kondisi-banner--err'
}

// ── Init ────────────────────────────────────────────────────────────
onMounted(() => {
    fetchBarang()
    fetchKategori()
})

watch(() => route.params.id, () => fetchBarang())
</script>

<style scoped>
.detail-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
    max-width: 560px;
    margin: 0 auto;
    padding-bottom: 48px;
}

/* Breadcrumb */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 16px;
}

.bc-link {
    font-size: 13px;
    color: #16a34a;
    font-weight: 500;
    text-decoration: none;
}

.bc-link:hover {
    text-decoration: underline;
}

.bc-sep {
    color: #9ca3af;
}

.bc-current {
    font-size: 13px;
    color: #6b7280;
}

/* Loading */
.loading-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px;
    gap: 14px;
    color: #9ca3af;
    font-size: 14px;
}

.spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Empty */
.empty-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 80px;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
}

.btn-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    padding: 9px 18px;
    border-radius: 9px;
    text-decoration: none;
}

/* Main card */
.card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Asset ID row */
.asset-id-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 0;
    flex-wrap: wrap;
    gap: 8px;
}

.asset-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #16a34a;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.asset-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #16a34a;
    flex-shrink: 0;
}

.kat-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.kat--green {
    background: #dcfce7;
    color: #166534;
}

.kat--blue {
    background: #dbeafe;
    color: #1d4ed8;
}

.kat--amber {
    background: #fef3c7;
    color: #92400e;
}

.kat--orange {
    background: #ffedd5;
    color: #9a3412;
}

.kat--purple {
    background: #ede9fe;
    color: #5b21b6;
}

.kat--gray {
    background: #f1f5f9;
    color: #475569;
}

/* Foto */
.foto-block {
    position: relative;
    margin: 14px 16px 0;
    border-radius: 12px;
    overflow: hidden;
    background: #f3f4f6;
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.foto-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.foto-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #9ca3af;
}

.foto-overlay-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(0, 0, 0, 0.55);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 7px;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: background 0.15s;
}

.foto-overlay-btn:hover {
    background: rgba(0, 0, 0, 0.75);
}

.hidden-input {
    display: none;
}

/* Nama & Deskripsi */
.nama-block {
    padding: 16px 16px 0;
}

.barang-nama {
    font-size: 22px;
    font-weight: 800;
    color: #111827;
    line-height: 1.2;
    margin: 0 0 8px;
}

.barang-desc {
    font-size: 13.5px;
    color: #6b7280;
    line-height: 1.7;
    margin: 0;
}

.barang-desc--empty {
    color: #d1d5db;
    font-style: italic;
}

/* Info grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 16px;
}

.info-card {
    background: #f9fafb;
    border: 1px solid #f0f0f0;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-bottom: 2px;
}

.info-icon--green {
    background: #dcfce7;
    color: #16a34a;
}

.info-icon--gray {
    background: #f1f5f9;
    color: #9ca3af;
}

.info-icon--orange {
    background: #ffedd5;
    color: #ea580c;
}

.info-icon--red {
    background: #fee2e2;
    color: #dc2626;
}

.info-label {
    font-size: 10.5px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.info-stok {
    display: flex;
    align-items: baseline;
    gap: 4px;
    margin: 0;
}

.stok-num {
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
}

.num--ok {
    color: #111827;
}

.num--warn {
    color: #ea580c;
}

.stok-total {
    font-size: 11.5px;
    color: #9ca3af;
    font-weight: 500;
}

.info-val {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.status-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}

.status--ok {
    background: #dcfce7;
    color: #166534;
}

.status--warn {
    background: #ffedd5;
    color: #9a3412;
}

/* Kondisi banner */
.kondisi-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 16px 16px;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1.5px solid;
    flex-wrap: wrap;
}

.kondisi-banner--ok {
    background: #f0fdf4;
    border-color: #86efac;
    color: #16a34a;
}

.kondisi-banner--warn {
    background: #fefce8;
    border-color: #fef08a;
    color: #ca8a04;
}

.kondisi-banner--err {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.kondisi-banner__icon {
    flex-shrink: 0;
}

.kondisi-banner__body {
    flex: 1;
    min-width: 0;
}

.kondisi-banner__label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.65;
    margin: 0;
}

.kondisi-banner__val {
    font-size: 13px;
    font-weight: 700;
    margin: 2px 0 0;
}

.aset-pill-badge {
    display: inline-flex;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

.aset-pill--ok {
    background: #dcfce7;
    color: #166534;
}

.aset-pill--warn {
    background: #ffedd5;
    color: #9a3412;
}

/* Actions */
.actions {
    display: flex;
    gap: 10px;
    padding: 0 16px 16px;
}

.btn-edit {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    background: #16a34a;
    color: white;
    font-size: 13.5px;
    font-weight: 600;
    padding: 11px 16px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}

.btn-edit:hover {
    background: #15803d;
}

.btn-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    background: none;
    color: #dc2626;
    font-size: 13.5px;
    font-weight: 600;
    padding: 11px 16px;
    border-radius: 10px;
    border: 1.5px solid #fecaca;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
}

.btn-delete:hover {
    background: #fef2f2;
}

/* ── Modal ─────────────────────────────────────────────────────────── */
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
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.modal--sm {
    max-width: 380px;
}

.modal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.modal-close {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: none;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6b7280;
}

.modal-close:hover {
    background: #e5e7eb;
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.form-foto-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.form-foto {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.form-foto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.form-foto-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-foto {
    display: inline-flex;
    align-items: center;
    padding: 7px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
    background: white;
}

.btn-foto:hover {
    border-color: #16a34a;
    color: #16a34a;
    background: #f0fdf4;
}

.foto-hint {
    font-size: 11px;
    color: #9ca3af;
    margin: 4px 0 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.form-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.req {
    color: #dc2626;
}

.form-input {
    padding: 9px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    font-size: 13px;
    color: #111827;
    background: white;
    outline: none;
    font-family: inherit;
    transition: border-color 0.2s;
    width: 100%;
    box-sizing: border-box;
}

.form-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12.5px;
    color: #dc2626;
}

.modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 4px;
}

.btn-cancel {
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

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 20px;
    border: none;
    border-radius: 9px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-submit:hover:not(:disabled) {
    background: #15803d;
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@keyframes rotate {
    to {
        transform: rotate(360deg);
    }
}

.spin-icon {
    animation: rotate 0.8s linear infinite;
}

.modal-del-icon {
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

.modal-body {
    font-size: 13.5px;
    color: #6b7280;
    margin: 8px 0 20px;
    line-height: 1.6;
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
</style>