<template>
    <div class="form-page">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <RouterLink to="/warga" class="bc-link">Data Warga</RouterLink>
            <span class="bc-sep">›</span>
            <span class="bc-current">Tambah Warga</span>
        </div>

        <div class="form-layout">
            <!-- Form card -->
            <div class="form-card">
                <div class="form-card__header">
                    <div class="form-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" width="20" height="20">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" y1="8" x2="19" y2="14" />
                            <line x1="22" y1="11" x2="16" y2="11" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="form-card__title">Data Warga Baru</h2>
                        <p class="form-card__sub">Isi data lengkap warga yang akan didaftarkan</p>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" novalidate>
                    <!-- Nama -->
                    <div class="field">
                        <label class="field-label">Nama Lengkap <span class="required">*</span></label>
                        <input v-model="form.nama_warga" type="text" class="field-input"
                            :class="{ 'field-input--error': errors.nama_warga }" placeholder="Contoh: Budi Santoso"
                            @input="errors.nama = ''" />
                        <p v-if="errors.nama_warga" class="field-error">{{ errors.nama_warga }}</p>
                    </div>

                    <!-- No HP -->
                    <div class="field">
                        <label class="field-label">Nomor HP / WhatsApp <span class="required">*</span></label>
                        <div class="input-prefix-wrap" :class="{ 'input-prefix-wrap--error': errors.no_hp }">
                            <span class="input-prefix">+62</span>
                            <input v-model="form.no_hp" type="tel" class="field-input-inner" placeholder="812-3456-7890"
                                @input="errors.no_hp = ''" />
                        </div>
                        <p v-if="errors.no_hp" class="field-error">{{ errors.no_hp }}</p>
                    </div>

                    <!-- RT/RW -->
                    <div class="field">
                        <label class="field-label">RT / RW <span class="optional">(opsional)</span></label>
                        <div class="rtrw-wrap">
                            <div class="rtrw-field">
                                <span class="rtrw-label">RT</span>
                                <input v-model="rt" type="text" class="field-input" placeholder="001" maxlength="3"
                                    @input="buildRtRw" />
                            </div>
                            <span class="rtrw-sep">/</span>
                            <div class="rtrw-field">
                                <span class="rtrw-label">RW</span>
                                <input v-model="rw" type="text" class="field-input" placeholder="005" maxlength="3"
                                    @input="buildRtRw" />
                            </div>
                        </div>
                        <p class="field-hint">Contoh: RT 004 / RW 012</p>
                    </div>

                    <!-- Alamat -->
                    <div class="field">
                        <label class="field-label">Alamat Lengkap <span class="required">*</span></label>
                        <textarea v-model="form.alamat" class="field-textarea"
                            :class="{ 'field-input--error': errors.alamat }"
                            placeholder="Jl. Melati No. 45, Desa Sukasari, Kec. Serpong, Tangerang Selatan" rows="3"
                            @input="errors.alamat = ''"></textarea>
                        <p v-if="errors.alamat" class="field-error">{{ errors.alamat }}</p>
                    </div>

                    <!-- Server error -->
                    <div v-if="serverError" class="server-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16"
                            height="16">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        {{ serverError }}
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <RouterLink to="/warga" class="btn-cancel">Batal</RouterLink>
                        <button type="submit" class="btn-submit" :disabled="submitting">
                            <svg v-if="!submitting" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" width="15" height="15">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span class="spinner" v-if="submitting"></span>
                            {{ submitting ? 'Menyimpan...' : 'Simpan Warga' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tips sidebar -->
            <div class="tips-col">
                <div class="tips-card">
                    <p class="tips-title">💡 Panduan Pengisian</p>
                    <ul class="tips-list">
                        <li>Gunakan nama lengkap sesuai KTP.</li>
                        <li>Nomor HP aktif untuk konfirmasi peminjaman.</li>
                        <li>RT/RW memudahkan filter pencarian warga.</li>
                        <li>Alamat lengkap mempercepat identifikasi peminjam.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useWargaStore } from '@/stores/warga'
import { useToast } from 'vue-toastification'

const store = useWargaStore()
const router = useRouter()
const toast = useToast()

const submitting = ref(false)
const serverError = ref('')
const rt = ref('')
const rw = ref('')

const form = reactive({
    nama_warga: '',
    no_hp: '',
    rt_rw: '',
    alamat: '',
})

const errors = reactive({ nama: '', no_hp: '', alamat: '' })

function buildRtRw() {
    if (rt.value || rw.value) {
        form.rt_rw = `RT ${rt.value.padStart(3, '0')} / RW ${rw.value.padStart(3, '0')}`
    } else {
        form.rt_rw = ''
    }
}

function validate() {
    let ok = true
    errors.nama_warga = errors.no_hp = errors.alamat = ''
    if (!form.nama_warga.trim()) { errors.nama = 'Nama wajib diisi.'; ok = false }
    if (!form.no_hp.trim()) { errors.no_hp = 'Nomor HP wajib diisi.'; ok = false }
    if (!form.alamat.trim()) { errors.alamat = 'Alamat wajib diisi.'; ok = false }
    return ok
}

async function handleSubmit() {
    if (!validate()) return
    submitting.value = true
    serverError.value = ''
    try {
        await store.createWarga({
            nama_warga: form.nama_warga.trim(),
            no_hp: form.no_hp.trim(),
            rt_rw: form.rt_rw || null,
            alamat: form.alamat.trim(),
        })
        toast.success(`Warga ${form.nama_warga} berhasil ditambahkan!`)
        router.push('/warga')
    } catch (err) {
        serverError.value = err?.response?.data?.message || 'Gagal menyimpan data. Coba lagi.'
    } finally {
        submitting.value = false
    }
}
</script>

<style scoped>
.form-page {
    font-family: 'DM Sans', 'Inter', sans-serif;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    margin-bottom: 20px;
    color: #9ca3af;
}

.bc-link {
    color: #16a34a;
    text-decoration: none;
    font-weight: 500;
}

.bc-link:hover {
    text-decoration: underline;
}

.bc-sep {
    color: #d1d5db;
}

.bc-current {
    color: #374151;
    font-weight: 500;
}

.form-layout {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 16px;
    align-items: start;
}

.form-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #f0f0f0;
    padding: 24px;
}

.form-card__header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f5f5f5;
}

.form-card__icon {
    width: 42px;
    height: 42px;
    background: #f0fdf4;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    flex-shrink: 0;
}

.form-card__title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.form-card__sub {
    font-size: 12px;
    color: #9ca3af;
    margin: 2px 0 0;
}

.field {
    margin-bottom: 18px;
}

.field-label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.required {
    color: #ef4444;
}

.optional {
    color: #9ca3af;
    font-weight: 400;
}

.field-input,
.field-textarea {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    padding: 10px 12px;
    font-size: 13.5px;
    color: #111827;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
    background: white;
    box-sizing: border-box;
}

.field-input:focus,
.field-textarea:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.field-input--error {
    border-color: #ef4444;
}

.field-textarea {
    resize: vertical;
    min-height: 80px;
}

.field-error {
    font-size: 11.5px;
    color: #ef4444;
    margin: 4px 0 0;
    font-weight: 500;
}

.field-hint {
    font-size: 11.5px;
    color: #9ca3af;
    margin: 4px 0 0;
}

.input-prefix-wrap {
    display: flex;
    align-items: stretch;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    overflow: hidden;
    transition: border-color 0.2s;
}

.input-prefix-wrap:focus-within {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.input-prefix-wrap--error {
    border-color: #ef4444;
}

.input-prefix {
    padding: 0 12px;
    background: #f9fafb;
    border-right: 1.5px solid #e5e7eb;
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.field-input-inner {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px 12px;
    font-size: 13.5px;
    color: #111827;
    font-family: inherit;
    background: transparent;
}

.rtrw-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rtrw-field {
    flex: 1;
    position: relative;
}

.rtrw-label {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    pointer-events: none;
}

.rtrw-field .field-input {
    padding-left: 32px;
}

.rtrw-sep {
    color: #d1d5db;
    font-size: 18px;
    font-weight: 300;
}

.server-error {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    color: #dc2626;
    margin-bottom: 16px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 8px;
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
    display: flex;
    align-items: center;
    transition: background 0.15s;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-submit {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 10px 22px;
    border: none;
    border-radius: 9px;
    background: #16a34a;
    color: white;
    font-size: 13px;
    font-weight: 600;
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

.spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Tips */
.tips-card {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    padding: 16px;
}

.tips-title {
    font-size: 13px;
    font-weight: 700;
    color: #166534;
    margin: 0 0 10px;
}

.tips-list {
    margin: 0;
    padding-left: 16px;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.tips-list li {
    font-size: 12.5px;
    color: #166534;
    line-height: 1.5;
}

@media (max-width:700px) {
    .form-layout {
        grid-template-columns: 1fr;
    }

    .tips-col {
        order: -1;
    }
}
</style>