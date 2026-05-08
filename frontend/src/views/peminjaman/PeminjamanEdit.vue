<template>
    <div class="page">

        <div class="back-row">
            <router-link :to="`/peminjaman/${route.params.id}`" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Kembali ke Detail
            </router-link>
        </div>

        <div class="page-header">
            <div class="header-left">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="page-title">Edit Peminjaman</h1>
                    <p class="page-sub">Hanya bisa diubah selama status masih Menunggu</p>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="store.loading" class="form-card">
            <div class="skel-body">
                <div v-for="i in 8" :key="i" class="skel-field"></div>
            </div>
        </div>

        <!-- Tidak bisa edit -->
        <div v-else-if="p && p.status !== 'Menunggu'" class="locked-notice">
            <div class="locked-ico">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
            </div>
            <div>
                <p class="locked-title">Peminjaman tidak dapat diubah</p>
                <p class="locked-sub">Status saat ini: <strong>{{ p.status }}</strong>. Hanya peminjaman berstatus
                    <strong>Menunggu</strong> yang bisa diedit.
                </p>
            </div>
            <router-link :to="`/peminjaman/${route.params.id}`" class="btn-primary">Lihat Detail</router-link>
        </div>

        <!-- Form -->
        <div v-else-if="p" class="form-card">
            <form @submit.prevent="submitEdit" class="form-inner">

                <!-- Warga (Searchable Dropdown) -->
                <div class="form-group" style="position: relative;">
                    <label>Peminjam (Warga)</label>
                    <div class="search-select" :class="{ error: errors.warga_id, focused: dropdownOpen }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <input v-model="wargaSearch" :placeholder="selectedWargaLabel || '-- Cari peminjam...'"
                            :class="{ 'has-value': selectedWargaLabel && !dropdownOpen }" @focus="dropdownOpen = true"
                            @blur="onWargaBlur" />
                        <button v-if="form.warga_id" type="button" class="clear-btn"
                            @mousedown.prevent="form.warga_id = ''; wargaSearch = ''">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="dropdownOpen" class="warga-dropdown">
                        <div v-if="!filteredWarga.length" class="warga-empty">Tidak ada hasil</div>
                        <button v-for="w in filteredWarga" :key="w.id" type="button"
                            :class="['warga-option', { active: form.warga_id === w.id }]"
                            @mousedown.prevent="pilihWarga(w)">
                            <div class="warga-avatar" :style="{ background: avatarColor(w.nama_warga) }">
                                {{ initials(w.nama_warga) }}
                            </div>
                            <span>{{ w.nama_warga }}</span>
                            <svg v-if="form.warga_id === w.id" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" class="check-ico">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </button>
                    </div>
                    <span v-if="errors.warga_id" class="err-msg">{{ errors.warga_id }}</span>
                </div>

                <!-- Barang (readonly) -->
                <div class="form-group">
                    <label>Barang</label>
                    <div class="readonly-field">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        {{ p.barang?.nama_barang ?? p.barang?.nama ?? '-' }}
                        <span class="readonly-hint">Barang tidak bisa diubah setelah dibuat</span>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Jumlah -->
                    <div class="form-group">
                        <label>Jumlah Pinjam</label>
                        <input v-model.number="form.jumlah" type="number" min="1" :max="stokMaks" required
                            :class="{ error: errors.jumlah || stokTerlampaui }" @input="onJumlahInput" />
                        <span v-if="stokTerlampaui" class="err-msg">
                            Melebihi stok tersedia ({{ stokMaks }} unit)
                        </span>
                        <span v-else-if="errors.jumlah" class="err-msg">{{ errors.jumlah }}</span>
                        <span v-else-if="stokMaks !== null" class="hint-msg">Stok tersedia: {{ stokMaks }} unit</span>
                    </div>

                    <!-- Kondisi -->
                    <div class="form-group">
                        <label>Kondisi Barang</label>
                        <select v-model="form.kondisi_pinjam" required :class="{ error: errors.kondisi_pinjam }">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                        </select>
                        <span v-if="errors.kondisi_pinjam" class="err-msg">{{ errors.kondisi_pinjam }}</span>
                    </div>
                </div>

                <!-- Keperluan -->
                <div class="form-group">
                    <label>Keperluan</label>
                    <textarea v-model="form.keperluan" required rows="3" placeholder="Tuliskan keperluan peminjaman..."
                        :class="{ error: errors.keperluan }"></textarea>
                    <span v-if="errors.keperluan" class="err-msg">{{ errors.keperluan }}</span>
                </div>

                <div class="form-row">
                    <!-- Tgl Pinjam -->
                    <div class="form-group">
                        <label>Tanggal Pinjam</label>
                        <input v-model="form.tgl_pinjam" type="date" required :class="{ error: errors.tgl_pinjam }" />
                        <span v-if="errors.tgl_pinjam" class="err-msg">{{ errors.tgl_pinjam }}</span>
                    </div>

                    <!-- Tgl Rencana Kembali -->
                    <div class="form-group">
                        <label>Rencana Kembali</label>
                        <input v-model="form.tgl_rencana_kembali" type="date" required :min="form.tgl_pinjam"
                            :max="maxDate" :class="['form-control', { error: errors.tgl_rencana_kembali }]" />
                        <span v-if="errors.tgl_rencana_kembali" class="err-msg">{{ errors.tgl_rencana_kembali }}</span>
                    </div>
                </div>

                <!-- Server error -->
                <div v-if="serverError" class="server-error">{{ serverError }}</div>

                <!-- Footer -->
                <div class="form-footer">
                    <router-link :to="`/peminjaman/${route.params.id}`" class="btn-cancel">Batal</router-link>
                    <button type="submit" :disabled="submitting || stokTerlampaui"
                        :class="['btn-submit', { 'btn-disabled': stokTerlampaui }]">
                        <span v-if="submitting" class="spinner"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Toast -->
        <Teleport to="body">
            <Transition name="toast">
                <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
                    <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <svg v-else-if="toast.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
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
import { useRoute, useRouter } from 'vue-router'
import { usePeminjamanStore } from '@/stores/peminjaman'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const store = usePeminjamanStore()

const daftarWarga = ref([])
const submitting = ref(false)
const serverError = ref('')
const errors = ref({})
const toast = ref({ show: false, type: 'success', message: '' })

const form = ref({
    warga_id: '',
    jumlah: 1,
    kondisi_pinjam: 'Baik',
    keperluan: '',
    tgl_pinjam: '',
    tgl_rencana_kembali: '',
})

// ── Searchable dropdown ───────────────────────────────────────────────────
const wargaSearch = ref('')
const dropdownOpen = ref(false)

const COLORS = ['#1e3a5f', '#16a34a', '#d97706', '#6366f1', '#0891b2', '#be185d', '#7c3aed']
function avatarColor(name = '') {
    let h = 0
    for (const c of name) h = c.charCodeAt(0) + ((h << 5) - h)
    return COLORS[Math.abs(h) % COLORS.length]
}
function initials(name = '') {
    return (name || '').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

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

// ── Computed ──────────────────────────────────────────────────────────────
const p = computed(() => store.currentPeminjaman)

const stokMaks = computed(() => {
    if (!p.value?.barang) return null
    const stokTersedia = p.value.barang.stok_tersedia ?? null
    if (stokTersedia === null) return null
    return stokTersedia + (p.value.jumlah ?? 0)
})

const stokTerlampaui = computed(() => {
    if (stokMaks.value === null) return false
    return form.value.jumlah > stokMaks.value
})

function onJumlahInput() {
    if (stokTerlampaui.value) {
        showToast('warning', `Stok tidak cukup! Maksimal bisa dipinjam: ${stokMaks.value} unit.`)
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────
function populateForm() {
    if (!p.value) return
    form.value = {
        warga_id: p.value.warga_id ?? '',
        jumlah: p.value.jumlah ?? 1,
        kondisi_pinjam: p.value.kondisi_pinjam ?? 'Baik',
        keperluan: p.value.keperluan ?? '',
        tgl_pinjam: p.value.tgl_pinjam?.substring(0, 10) ?? '',
        tgl_rencana_kembali: p.value.tgl_rencana_kembali?.substring(0, 10) ?? '',
    }
}

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

function validate() {
    const e = {}
    if (!form.value.warga_id) e.warga_id = 'Peminjam wajib dipilih.'
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

let toastTimer = null
function showToast(type, message) {
    clearTimeout(toastTimer)
    toast.value = { show: true, type, message }
    toastTimer = setTimeout(() => { toast.value.show = false }, 4000)
}

async function submitEdit() {
    if (!validate()) return
    if (stokTerlampaui.value) {
        showToast('error', `Stok tidak cukup. Maksimal: ${stokMaks.value} unit.`)
        return
    }
    submitting.value = true
    serverError.value = ''
    try {
        await store.updatePeminjaman(route.params.id, form.value)
        showToast('success', 'Peminjaman berhasil diperbarui.')
        setTimeout(() => router.push(`/peminjaman/${route.params.id}`), 1200)
    } catch (e) {
        const msg = e?.response?.data?.message ?? 'Gagal menyimpan perubahan.'
        serverError.value = msg
        showToast('error', msg)
    } finally { submitting.value = false }
}

async function fetchWarga() {
    try {
        const res = await api.get('/warga')
        daftarWarga.value = res.data.data ?? res.data
    } catch { /* silent */ }
}

onMounted(async () => {
    await store.fetchPeminjaman(route.params.id)
    populateForm()
    fetchWarga()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

.page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 28px 32px;
    min-height: 100vh;
    background: #F4F6F9;
    color: #1a1f2e;
}

.back-row {
    margin-bottom: 18px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #7a8499;
    text-decoration: none;
}

.back-link svg {
    width: 16px;
    height: 16px;
}

.back-link:hover {
    color: #1e3a5f;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.header-icon {
    width: 46px;
    height: 46px;
    background: #1e3a5f;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.header-icon svg {
    width: 20px;
    height: 20px;
}

.page-title {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
}

.page-sub {
    font-size: 13px;
    color: #7a8499;
    margin: 2px 0 0;
}

/* Locked notice */
.locked-notice {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #fde68a;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 14px;
}

.locked-ico {
    width: 52px;
    height: 52px;
    background: #fffbeb;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d97706;
}

.locked-ico svg {
    width: 24px;
    height: 24px;
}

.locked-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1f2e;
    margin: 0;
}

.locked-sub {
    font-size: 14px;
    color: #7a8499;
    margin: 4px 0 0;
}

/* Form card */
.form-card {
    background: #fff;
    border-radius: 16px;
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

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #3d4a5c;
}

.optional {
    color: #a0aec0;
    font-weight: 400;
}

.form-group input,
.form-group select,
.form-group textarea {
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    font-family: inherit;
    color: #1a1f2e;
    outline: none;
    transition: border-color .2s;
    background: #fff;
    width: 100%;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #1e3a5f;
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
    border-color: #dc2626;
    background: #fff5f5;
}

.form-group textarea {
    resize: vertical;
}

/* Searchable dropdown */
.search-select {
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 9px 12px;
    background: #fff;
    transition: border-color .2s;
    box-sizing: border-box;
}

.search-select.focused {
    border-color: #1e3a5f;
}

.search-select.error {
    border-color: #dc2626;
    background: #fff5f5;
}

.search-select>svg {
    width: 15px;
    height: 15px;
    color: #a0aec0;
    flex-shrink: 0;
}

.search-select input {
    border: none !important;
    padding: 0 !important;
    border-radius: 0 !important;
    font-size: 14px;
    color: #1a1f2e;
    flex: 1;
    outline: none;
    background: transparent;
    min-width: 0;
    width: auto;
    box-sizing: border-box;
}

.search-select input.has-value {
    color: #1a1f2e;
    font-weight: 500;
}

.search-select input::placeholder {
    color: #a0aec0;
    font-weight: 400;
}

.search-select input.has-value::placeholder {
    color: #1a1f2e;
    font-weight: 500;
}

.clear-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px;
    color: #a0aec0;
    display: flex;
    border-radius: 4px;
    flex-shrink: 0;
    transition: color .15s;
}

.clear-btn:hover {
    color: #dc2626;
}

.clear-btn svg {
    width: 14px;
    height: 14px;
}

.warga-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1.5px solid #e8ecf4;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
    z-index: 100;
    max-height: 220px;
    overflow-y: auto;
    padding: 4px;
}

.warga-option {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 10px;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #1a1f2e;
    text-align: left;
    transition: background .12s;
    box-sizing: border-box;
}

.warga-option:hover {
    background: #f1f5f9;
}

.warga-option.active {
    background: #eff6ff;
    color: #1e3a5f;
    font-weight: 600;
}

.warga-avatar {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

.check-ico {
    width: 14px;
    height: 14px;
    color: #1e3a5f;
    margin-left: auto;
    flex-shrink: 0;
}

.warga-empty {
    padding: 16px;
    text-align: center;
    font-size: 13px;
    color: #a0aec0;
}

/* Readonly field */
.readonly-field {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e8ecf4;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    color: #7a8499;
    font-weight: 500;
}

.readonly-field svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: #c4ccdb;
}

.readonly-hint {
    margin-left: auto;
    font-size: 11px;
    color: #c4ccdb;
    font-style: italic;
}

.err-msg {
    font-size: 12px;
    color: #dc2626;
}

.hint-msg {
    font-size: 12px;
    color: #7a8499;
}

.server-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    color: #dc2626;
}

.form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 4px;
}

.btn-cancel {
    padding: 10px 20px;
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

.btn-primary {
    display: inline-flex;
    align-items: center;
    background: #1e3a5f;
    color: #fff;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

/* Skeleton */
.skel-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.skel-field {
    height: 56px;
    border-radius: 10px;
    background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
    background-size: 200%;
    animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

/* Spinner */
.spinner {
    width: 14px;
    height: 14px;
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

/* Toast */
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

@media (max-width: 640px) {
    .page {
        padding: 16px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .skel-body {
        grid-template-columns: 1fr;
    }
}
</style>