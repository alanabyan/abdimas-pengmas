<template>
    <div class="page">

        <div class="back-row">
            <a href="#" @click.prevent="router.push('/peminjaman')" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>

        <div class="page-header">
            <div class="header-left">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                        <line x1="12" y1="11" x2="12" y2="17" />
                        <line x1="9" y1="14" x2="15" y2="14" />
                    </svg>
                </div>
                <div>
                    <h1 class="page-title">Tambah Peminjaman</h1>
                    <p class="page-sub">Isi data peminjaman barang baru</p>
                </div>
            </div>
        </div>

        <div class="form-card">
            <form @submit.prevent="simpanData" class="form-inner">

                <div class="form-row">

                    <div class="form-group" style="position: relative;">
                        <label class="form-label">Peminjam (Warga)</label>
                        <div class="search-select" :class="{ error: errors.warga_id, open: dropdownOpen }">
                            <input v-model="wargaSearch" @focus="dropdownOpen = true" @blur="onWargaBlur" @input="dropdownOpen = true"
                                :placeholder="selectedWargaLabel || '-- Cari atau Pilih Peminjam --'" class="search-select-input"
                                autocomplete="off" />
                            <svg class="search-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <ul v-if="dropdownOpen && filteredWarga.length" class="search-select-list">
                                <li v-for="w in filteredWarga" :key="w.id" @mousedown.prevent="pilihWarga(w)"
                                    :class="{ active: form.warga_id === w.id }">
                                    {{ w.nama_warga }}
                                </li>
                            </ul>
                            <div v-if="dropdownOpen && wargaSearch && !filteredWarga.length" class="search-select-empty">
                                Tidak ada warga ditemukan.
                            </div>
                        </div>
                        <span v-if="errors.warga_id" class="err-msg">{{ errors.warga_id }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label form-label-accent">Filter Berdasarkan Kategori</label>
                        <select v-model="selectedKategori" @change="onKategoriChange" class="form-control form-control-accent">
                            <option value="">-- Semua Kategori --</option>
                            <option v-for="k in daftarKategori" :key="k.id" :value="k.id">
                                {{ k.nama }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Barang</label>
                    <div class="search-select" :class="{ error: errors.barang_id }">
                        <input v-model="barangSearch" @focus="barangDropdownOpen = true" @blur="onBarangBlur"
                            @input="barangDropdownOpen = true" :placeholder="selectedBarangLabel || '-- Cari atau Pilih Barang --'"
                            class="search-select-input" autocomplete="off" />
                        <svg class="search-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <ul v-if="barangDropdownOpen && filteredBarangSearch.length" class="search-select-list">
                            <li v-for="b in filteredBarangSearch" :key="b.id" @mousedown.prevent="pilihBarang(b)"
                                :class="{ active: form.barang_id === b.id }">
                                <span>{{ b.nama_barang }}</span>
                                <span class="stok-badge" :class="{ 'stok-habis': b.stok_tersedia === 0 }">
                                    Stok: {{ b.stok_tersedia }}
                                </span>
                            </li>
                        </ul>
                        <div v-if="barangDropdownOpen && barangSearch && !filteredBarangSearch.length" class="search-select-empty">
                            Tidak ada barang ditemukan.
                        </div>
                        <div v-if="barangDropdownOpen && !barangSearch && !daftarBarangTerfilter.length && selectedKategori"
                            class="search-select-empty">
                            Tidak ada barang di kategori ini.
                        </div>
                    </div>
                    <span v-if="errors.barang_id" class="err-msg">{{ errors.barang_id }}</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jumlah Pinjam</label>
                        <input v-model.number="form.jumlah" type="number" min="1" :max="stokBarangDipilih ?? undefined" required
                            @input="onJumlahInput" :class="['form-control', { error: stokTerlampaui || errors.jumlah }]" />
                        <p v-if="stokTerlampaui" class="err-msg">
                            Melebihi stok! Tersedia: <strong>{{ stokBarangDipilih }}</strong> unit.
                        </p>
                        <p v-else-if="errors.jumlah" class="err-msg">{{ errors.jumlah }}</p>
                        <p v-else-if="stokBarangDipilih !== null" class="hint-msg">
                            Stok tersedia: {{ stokBarangDipilih }} unit
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kondisi Barang</label>
                        <select v-model="form.kondisi_pinjam" required class="form-control">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keperluan</label>
                    <textarea v-model="form.keperluan" required rows="3" placeholder="Tuliskan keperluan peminjaman..."
                        :class="['form-control', { error: errors.keperluan }]"></textarea>
                    <span v-if="errors.keperluan" class="err-msg">{{ errors.keperluan }}</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Pinjam</label>
                        <input v-model="form.tgl_pinjam" type="date" required @change="onTglPinjamChange"
                            :class="['form-control', { error: errors.tgl_pinjam }]" />
                        <span v-if="errors.tgl_pinjam" class="err-msg">{{ errors.tgl_pinjam }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Rencana Kembali</label>
                        <input v-model="form.tgl_rencana_kembali" type="date" required :min="form.tgl_pinjam" :max="maxDate"
                            :class="['form-control', { error: errors.tgl_rencana_kembali }]" />
                        <span v-if="errors.tgl_rencana_kembali" class="err-msg">{{ errors.tgl_rencana_kembali }}</span>
                    </div>
                </div>

                <div v-if="serverError" class="server-error">{{ serverError }}</div>

                <div class="form-footer">
                    <button type="button" @click="router.push('/peminjaman')" class="btn-cancel">Batal</button>
                    <button type="submit" :disabled="stokTerlampaui || submitting"
                        :class="['btn-submit', { 'btn-disabled': stokTerlampaui }]">
                        <span v-if="submitting" class="spinner"></span>
                        Simpan Peminjaman
                    </button>
                </div>

            </form>
        </div>

        <Teleport to="body">
            <Transition name="toast">
                <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
                    <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <svg v-else-if="toast.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    {{ toast.message }}
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { usePeminjamanStore } from '@/stores/peminjaman'
import api from '@/services/api'

const router = useRouter()
const store = usePeminjamanStore()

const daftarWarga = ref([])
const daftarBarang = ref([])
const daftarKategori = ref([])
const selectedKategori = ref('')
const submitting = ref(false)
const serverError = ref('')
const errors = ref({})
const toast = ref({ show: false, type: 'success', message: '' })

const maxDate = '2100-12-31'

const form = ref({
    warga_id: '',
    barang_id: '',
    jumlah: 1,
    kondisi_pinjam: 'Baik',
    keperluan: '',
    tgl_pinjam: '',
    tgl_rencana_kembali: '',
})

// ── Toast ─────────────────────────────────────────────────────────────────
let toastTimer = null
function showToast(type, message) {
    clearTimeout(toastTimer)
    toast.value = { show: true, type, message }
    toastTimer = setTimeout(() => { toast.value.show = false }, 4000)
}

// ── Warga Search Dropdown ─────────────────────────────────────────────────
const wargaSearch = ref('')
const dropdownOpen = ref(false)

const selectedWargaLabel = computed(() => {
    if (!form.value.warga_id) return ''
    const w = daftarWarga.value.find(w => w.id === form.value.warga_id)
    return w?.nama_warga ?? ''
})

const filteredWarga = computed(() => {
    if (!wargaSearch.value.trim()) return daftarWarga.value
    return daftarWarga.value.filter(w =>
        w.nama_warga.toLowerCase().includes(wargaSearch.value.toLowerCase())
    )
})

function pilihWarga(w) {
    form.value.warga_id = w.id
    wargaSearch.value = ''
    dropdownOpen.value = false
}

function onWargaBlur() {
    setTimeout(() => { dropdownOpen.value = false }, 150)
}

// ── Stok ──────────────────────────────────────────────────────────────────
const stokBarangDipilih = computed(() => {
    if (!form.value.barang_id) return null
    const b = daftarBarang.value.find(b => b.id === form.value.barang_id)
    return b ? b.stok_tersedia : null
})

const stokTerlampaui = computed(() => {
    if (stokBarangDipilih.value === null) return false
    return form.value.jumlah > stokBarangDipilih.value
})

function onJumlahInput() {
    if (stokTerlampaui.value) {
        showToast('warning', `Stok tidak cukup! Tersedia ${stokBarangDipilih.value}, kamu meminta ${form.value.jumlah}.`)
    }
}

function onKategoriChange() {
    form.value.barang_id = ''
    form.value.jumlah = 1
    barangSearch.value = ''
}

const barangSearch = ref('')
const barangDropdownOpen = ref(false)

const selectedBarangLabel = computed(() => {
    if (!form.value.barang_id) return ''
    const b = daftarBarang.value.find(b => b.id === form.value.barang_id)
    return b ? `${b.nama_barang} — (Stok: ${b.stok_tersedia})` : ''
})

// Filter by kategori first, then by search text
const daftarBarangTerfilter = computed(() => {
    if (!selectedKategori.value) return daftarBarang.value
    return daftarBarang.value.filter(b => b.kategori_id == selectedKategori.value)
})

const filteredBarangSearch = computed(() => {
    if (!barangSearch.value.trim()) return daftarBarangTerfilter.value
    return daftarBarangTerfilter.value.filter(b =>
        b.nama_barang.toLowerCase().includes(barangSearch.value.toLowerCase())
    )
})

function pilihBarang(b) {
    form.value.barang_id = b.id
    form.value.jumlah = 1
    barangSearch.value = ''
    barangDropdownOpen.value = false
}

function onBarangBlur() {
    setTimeout(() => { barangDropdownOpen.value = false }, 150)
}

// ── Tanggal guard ─────────────────────────────────────────────────────────
function onTglPinjamChange() {
    if (form.value.tgl_rencana_kembali && form.value.tgl_rencana_kembali < form.value.tgl_pinjam) {
        form.value.tgl_rencana_kembali = form.value.tgl_pinjam
    }
}

// Watch reaktif — menangkap perubahan dari APAPUN termasuk arrow key keyboard
watch(() => form.value.tgl_rencana_kembali, (newVal) => {
    if (!newVal) return

    // blok tahun aneh seperti 275760
    const year = new Date(newVal).getFullYear()

    if (year > 2050 || year < 2000) {
        form.value.tgl_rencana_kembali = form.value.tgl_pinjam
        showToast('warning', 'Tanggal tidak valid.')
        return
    }

    if (
        form.value.tgl_pinjam &&
        newVal < form.value.tgl_pinjam
    ) {
        form.value.tgl_rencana_kembali = form.value.tgl_pinjam
    }
})

// ── Validasi ──────────────────────────────────────────────────────────────
function validate() {
    const e = {}
    if (!form.value.warga_id) e.warga_id = 'Peminjam wajib dipilih.'
    if (!form.value.barang_id) e.barang_id = 'Barang wajib dipilih.'
    if (!form.value.jumlah || form.value.jumlah < 1) e.jumlah = 'Jumlah minimal 1.'
    if (!form.value.keperluan?.trim()) e.keperluan = 'Keperluan wajib diisi.'
    if (!form.value.tgl_pinjam) e.tgl_pinjam = 'Tanggal pinjam wajib diisi.'
    if (!form.value.tgl_rencana_kembali) e.tgl_rencana_kembali = 'Rencana kembali wajib diisi.'
    else if (form.value.tgl_rencana_kembali < form.value.tgl_pinjam)
        e.tgl_rencana_kembali = 'Harus setelah atau sama dengan tanggal pinjam.'
    const tahunKembali = new Date(form.value.tgl_rencana_kembali).getFullYear()

    if (tahunKembali > 2100 || tahunKembali < 2000) {
        e.tgl_rencana_kembali = 'Tahun tidak valid.'
    }
    errors.value = e
    return Object.keys(e).length === 0
}

// ── Fetch data pilihan ────────────────────────────────────────────────────
const fetchDataPilihan = async () => {
    try {
        const [resWarga, resBarang, resKategori] = await Promise.all([
            api.get('/warga'),
            api.get('/barang'),
            api.get('/kategori'),
        ])
        daftarWarga.value = resWarga.data.data ?? resWarga.data
        daftarBarang.value = resBarang.data.data ?? resBarang.data
        daftarKategori.value = resKategori.data.data ?? resKategori.data
    } catch {
        showToast('error', 'Gagal memuat data. Periksa koneksi server.')
    }
}

onMounted(fetchDataPilihan)

// ── Submit ────────────────────────────────────────────────────────────────
const simpanData = async () => {
    if (!validate()) return
    if (stokTerlampaui.value) {
        showToast('error', `Tidak dapat menyimpan. Stok tersedia hanya ${stokBarangDipilih.value}.`)
        return
    }

    console.log('Payload dikirim:', JSON.stringify(form.value))

    submitting.value = true
    serverError.value = ''
    try {
        await store.createPeminjaman(form.value)
        showToast('success', 'Data Peminjaman Berhasil Disimpan!')
        setTimeout(() => router.push('/peminjaman'), 1200)
    } catch (e) {
        const msg = e?.response?.data?.message ?? 'Gagal menyimpan data.'
        serverError.value = msg
        showToast('error', msg)
    } finally {
        submitting.value = false
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

/* ─────────────────────────── Base ─────────────────────────── */
.page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 28px 32px;
    min-height: 100vh;
    background: #F4F6F9;
    color: #1a1f2e;
}

/* ─────────────────────────── Back link ─────────────────────── */
.back-row {
    margin-bottom: 20px;
    position: relative;
    z-index: 10; /* Biar gampang diklik, nggak ketutupan div lain */
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #7a8499;
    text-decoration: none;
    transition: color .15s;
    cursor: pointer;
}

.back-link svg {
    width: 16px;
    height: 16px;
}

.back-link:hover {
    color: #1e3a5f;
}

/* ─────────────────────────── Page header ───────────────────── */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.header-icon {
    width: 48px;
    height: 48px;
    background: #1e3a5f;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}

.header-icon svg {
    width: 22px;
    height: 22px;
}

.page-title {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.page-sub {
    font-size: 13px;
    color: #7a8499;
    margin: 2px 0 0;
}

/* ─────────────────────────── Form card ─────────────────────── */
.form-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e8ecf4;
    padding: 28px;
}

.form-inner {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* ─────────────────────────── Labels ────────────────────────── */
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #3d4a5c;
}

.form-label-accent {
    color: #059669;
}

/* ─────────────────────────── Controls ──────────────────────── */
.form-control {
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    font-family: inherit;
    color: #1a1f2e;
    background: #fff;
    outline: none;
    transition: border-color .2s, background .2s;
    width: 100%;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: #1e3a5f;
}

.form-control.error {
    border-color: #dc2626;
    background: #fff5f5;
}

textarea.form-control {
    resize: vertical;
}

.form-control-accent {
    border-color: #a7f3d0;
    background: #f0fdf4;
    color: #065f46;
    font-weight: 500;
}

.form-control-accent:focus {
    border-color: #059669;
}

/* ─────────────────────────── Search Select ─────────────────── */
.search-select {
    position: relative;
}

.search-select-input {
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 10px 40px 10px 14px;
    font-size: 14px;
    font-family: inherit;
    color: #1a1f2e;
    background: #fff;
    outline: none;
    width: 100%;
    box-sizing: border-box;
    transition: border-color .2s, background .2s;
}

.search-select-input:focus {
    border-color: #1e3a5f;
}

.search-select.error .search-select-input {
    border-color: #dc2626;
    background: #fff5f5;
}

.search-select-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #7a8499;
    pointer-events: none;
}

.search-select-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    max-height: 200px;
    overflow-y: auto;
    list-style: none;
    margin: 0;
    padding: 4px 0;
    z-index: 100;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .09);
}

.search-select-list li {
    padding: 9px 14px;
    font-size: 14px;
    cursor: pointer;
    color: #1a1f2e;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background .12s;
}

.search-select-list li:hover,
.search-select-list li.active {
    background: #f0f4ff;
    color: #1e3a5f;
    font-weight: 500;
}

.stok-badge {
    font-size: 11px;
    background: #e2e8f0;
    color: #475569;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}

.stok-habis {
    background: #fee2e2;
    color: #dc2626;
}

.search-select-empty {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    color: #7a8499;
    z-index: 100;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .09);
}

/* ─────────────────────────── Helper text ───────────────────── */
.err-msg {
    font-size: 12px;
    color: #dc2626;
}

.hint-msg {
    font-size: 12px;
    color: #7a8499;
}

/* ─────────────────────────── Server error ──────────────────── */
.server-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    color: #dc2626;
}

/* ─────────────────────────── Footer buttons ────────────────── */
.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 4px;
}

.btn-cancel {
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    border: 1.5px solid #e8ecf4;
    background: #fff;
    color: #7a8499;
    cursor: pointer;
    font-family: inherit;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: border-color .15s;
}

.btn-cancel:hover {
    border-color: #c4ccdb;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    background: #1e3a5f;
    color: #fff;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s;
}

.btn-submit:not(:disabled):hover {
    background: #162d4a;
}

.btn-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

/* ─────────────────────────── Spinner ───────────────────────── */
.spinner {
    width: 13px;
    height: 13px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, .3);
    border-top-color: #fff;
    animation: spin .7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ─────────────────────────── Toast ─────────────────────────── */
.toast {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
    max-width: 340px;
}

.toast svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.toast-success {
    background: #1a1f2e;
    color: #fff;
}

.toast-warning {
    background: #d97706;
    color: #fff;
}

.toast-error {
    background: #dc2626;
    color: #fff;
}

/* ─────────────────────────── Transitions ───────────────────── */
.toast-enter-active {
    transition: all .3s cubic-bezier(.34, 1.4, .64, 1);
}

.toast-leave-active {
    transition: all .2s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(16px) scale(.95);
}

.toast-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

/* ── RESPONSIVE MODE ────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .page {
        padding: 16px 20px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .form-card {
        padding: 20px;
    }

    .form-row {
        grid-template-columns: 1fr; /* Form row berubah jadi 1 kolom bertumpuk ke bawah */
        gap: 16px;
    }
    
    .toast {
        left: 20px;
        right: 20px;
        bottom: 20px;
        max-width: none;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 20px;
    }
    
    .form-footer {
        flex-direction: column; /* Tombol simpan dan batal numpuk vertikal */
        gap: 12px;
    }

    .btn-submit, .btn-cancel {
        width: 100%;
        justify-content: center;
    }
    
    .btn-submit {
        order: 1; /* Tombol simpan di atas */
    }
    
    .btn-cancel {
        order: 2; /* Tombol batal di bawah */
    }
}
</style>