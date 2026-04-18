<template>
    <div class="form-page">
        <div class="breadcrumb">
            <RouterLink to="/warga" class="bc-link">Data Warga</RouterLink>
            <span class="bc-sep">›</span>
            <span class="bc-current">Edit Warga</span>
        </div>

        <div v-if="loading" class="loading-state">
            <span class="spinner-lg"></span>
            <p>Memuat data warga...</p>
        </div>

        <div v-else-if="!currentWarga" class="not-found">
            <p>Data warga tidak ditemukan.</p>
            <RouterLink to="/warga" class="btn-cancel">Kembali ke Daftar</RouterLink>
        </div>

        <div v-else class="form-layout">
            <div class="form-card">
                <div class="form-card__header">
                    <div class="form-card__avatar" :style="{ background: avatarColor(currentWarga.nama_warga) }">
                        {{ initials(currentWarga.nama_warga) }}
                    </div>
                    <div>
                        <h2 class="form-card__title">Edit Data Warga</h2>
                        <p class="form-card__sub">{{ currentWarga.nama_warga }}</p>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" novalidate>
                    <div class="field">
                        <label class="field-label">Nama Lengkap <span class="required">*</span></label>
                        <input v-model="form.nama_warga" type="text" class="field-input"
                            :class="{ 'field-input--error': errors.nama_warga }" placeholder="Nama lengkap"
                            @input="errors.nama_warga = ''" />
                        <p v-if="errors.nama_warga" class="field-error">{{ errors.nama_warga }}</p>
                    </div>

                    <div class="field">
                        <label class="field-label">Nomor HP <span class="required">*</span></label>
                        <input v-model="form.no_hp" type="tel" class="field-input"
                            :class="{ 'field-input--error': errors.no_hp }" placeholder="0812-xxxx-xxxx"
                            @input="errors.no_hp = ''" />
                        <p v-if="errors.no_hp" class="field-error">{{ errors.no_hp }}</p>
                    </div>

                    <div class="field">
                        <label class="field-label">RT / RW <span class="optional">(opsional)</span></label>
                        <input v-model="form.rt_rw" type="text" class="field-input"
                            placeholder="Contoh: RT 004 / RW 012" />
                    </div>

                    <div class="field">
                        <label class="field-label">Alamat Lengkap <span class="required">*</span></label>
                        <textarea v-model="form.alamat" class="field-textarea"
                            :class="{ 'field-input--error': errors.alamat }" rows="3"
                            @input="errors.alamat = ''"></textarea>
                        <p v-if="errors.alamat" class="field-error">{{ errors.alamat }}</p>
                    </div>

                    <div v-if="serverError" class="server-error">{{ serverError }}</div>

                    <div class="form-actions">
                        <RouterLink to="/warga" class="btn-cancel">Batal</RouterLink>
                        <button type="submit" class="btn-submit" :disabled="submitting">
                            {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useWargaStore } from '@/stores/warga'
import { useToast } from 'vue-toastification'

const store = useWargaStore()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const loading = ref(true)
const submitting = ref(false)
const serverError = ref('')
const currentWarga = ref(null)

const form = reactive({ nama_warga: '', no_hp: '', rt_rw: '', alamat: '' })
const errors = reactive({ nama_warga: '', no_hp: '', alamat: '' })

const COLORS = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function avatarColor(nama = '') {
    let h = 0
    for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return COLORS[h % COLORS.length]
}
function initials(nama = '') {
    return nama.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

onMounted(async () => {
    try {
        await store.fetchWarga(route.params.id)
        currentWarga.value = store.currentWarga
        if (currentWarga.value) {
            form.nama_warga = currentWarga.value.nama_warga
            form.no_hp = currentWarga.value.no_hp
            form.rt_rw = currentWarga.value.rt_rw || ''
            form.alamat = currentWarga.value.alamat
        }
    } catch {
        currentWarga.value = null
    } finally {
        loading.value = false
    }
})

function validate() {
    let ok = true
    errors.nama_warga = errors.no_hp = errors.alamat = ''
    if (!form.nama_warga.trim()) { errors.nama_warga = 'Nama wajib diisi.'; ok = false }
    if (!form.no_hp.trim()) { errors.no_hp = 'Nomor HP wajib diisi.'; ok = false }
    if (!form.alamat.trim()) { errors.alamat = 'Alamat wajib diisi.'; ok = false }
    return ok
}

async function handleSubmit() {
    if (!validate()) return
    submitting.value = true
    serverError.value = ''
    try {
        await store.updateWarga(route.params.id, {
            nama_warga: form.nama_warga.trim(), no_hp: form.no_hp.trim(),
            rt_rw: form.rt_rw || null, alamat: form.alamat.trim(),
        })
        toast.success('Data warga berhasil diperbarui!')
        router.push('/warga')
    } catch (err) {
        serverError.value = err?.response?.data?.message || 'Gagal menyimpan perubahan.'
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

.bc-sep {
    color: #d1d5db;
}

.bc-current {
    color: #374151;
    font-weight: 500;
}

.loading-state,
.not-found {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 60px;
    color: #9ca3af;
    font-size: 14px;
}

.spinner-lg {
    width: 28px;
    height: 28px;
    border: 3px solid #e5e7eb;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.form-layout {
    display: grid;
    grid-template-columns: 1fr;
    max-width: 520px;
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

.form-card__avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    color: white;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
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

.server-error {
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
</style>