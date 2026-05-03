<template>
  <div class="marbot-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <div class="header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <div>
          <h1 class="page-title">Manajemen Marbot</h1>
          <p class="page-subtitle">Kelola akun penjaga masjid</p>
        </div>
      </div>
      <button class="btn-primary" @click="openCreateModal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Tambah Marbot
      </button>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-number">{{ marbots.length }}</span>
        <span class="stat-label">Total Marbot</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-number aktif">{{ activeCount }}</span>
        <span class="stat-label">Aktif</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-number nonaktif">{{ inactiveCount }}</span>
        <span class="stat-label">Nonaktif</span>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input v-model="searchQuery" type="text" placeholder="Cari nama atau email..." @input="onSearch" />
        <button v-if="searchQuery" class="clear-search" @click="searchQuery = ''; onSearch()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
      <div class="filter-tabs">
        <button :class="['filter-tab', { active: filterAktif === null }]" @click="filterAktif = null">Semua</button>
        <button :class="['filter-tab', { active: filterAktif === true }]" @click="filterAktif = true">Aktif</button>
        <button :class="['filter-tab', { active: filterAktif === false }]"
          @click="filterAktif = false">Nonaktif</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <!-- Loading skeleton -->
      <div v-if="store.loading" class="skeleton-list">
        <div v-for="i in 5" :key="i" class="skeleton-row">
          <div class="skeleton-avatar"></div>
          <div class="skeleton-lines">
            <div class="skeleton-line w60"></div>
            <div class="skeleton-line w40"></div>
          </div>
          <div class="skeleton-badge"></div>
          <div class="skeleton-actions"></div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="filteredMarbots.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <p class="empty-title">Tidak ada marbot ditemukan</p>
        <p class="empty-desc">Coba ubah filter atau tambah marbot baru</p>
      </div>

      <!-- Table content -->
      <table v-else class="marbot-table">
        <thead>
          <tr>
            <th>Marbot</th>
            <th>Email</th>
            <th>Status</th>
            <th>Bergabung</th>
            <th class="th-actions">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(marbot, idx) in filteredMarbots" :key="marbot.id" class="table-row"
            :style="{ animationDelay: `${idx * 40}ms` }">
            <td>
              <div class="marbot-info">
                <div class="avatar" :style="{ background: getAvatarColor(marbot.nama_marbot) }">
                  {{ getInitials(marbot.nama_marbot) }}
                </div>
                <span class="marbot-name">{{ marbot.nama_marbot }}</span>
              </div>
            </td>
            <td>
              <span class="marbot-email">{{ marbot.email }}</span>
            </td>
            <td>
              <span :class="['badge', marbot.aktif ? 'badge-aktif' : 'badge-nonaktif']">
                <span class="badge-dot"></span>
                {{ marbot.aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td>
              <span class="date-text">{{ formatDate(marbot.created_at) }}</span>
            </td>
            <td>
              <div class="action-group">
                <button class="action-btn btn-edit" @click="openEditModal(marbot)" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button class="action-btn btn-reset" @click="openResetModal(marbot)" title="Reset Password">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                  </svg>
                </button>
                <button class="action-btn btn-delete" @click="confirmDelete(marbot)" title="Nonaktifkan"
                  :disabled="!marbot.aktif">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ======================================= -->
    <!-- MODAL: Tambah / Edit Marbot             -->
    <!-- ======================================= -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showFormModal" class="modal-backdrop" @click.self="closeFormModal">
          <div class="modal">
            <div class="modal-header">
              <h2 class="modal-title">{{ isEditing ? 'Edit Marbot' : 'Tambah Marbot Baru' }}</h2>
              <button class="modal-close" @click="closeFormModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>
            <form class="modal-body" @submit.prevent="submitForm">
              <div class="form-group">
                <label>Nama Marbot</label>
                <input v-model="form.nama_marbot" type="text" placeholder="Masukkan nama lengkap"
                  :class="{ error: formErrors.nama_marbot }" />
                <span v-if="formErrors.nama_marbot" class="error-msg">{{ formErrors.nama_marbot }}</span>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" type="email" placeholder="contoh@email.com"
                  :class="{ error: formErrors.email }" />
                <span v-if="formErrors.email" class="error-msg">{{ formErrors.email }}</span>
              </div>
              <template v-if="!isEditing">
                <div class="form-group">
                  <label>Password</label>
                  <div class="input-eye">
                    <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                      placeholder="Minimal 8 karakter" :class="{ error: formErrors.password }" />
                    <button type="button" class="eye-btn" @click="showPass = !showPass">
                      <svg v-if="!showPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                      </svg>
                      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                          d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                      </svg>
                    </button>
                  </div>
                  <span v-if="formErrors.password" class="error-msg">{{ formErrors.password }}</span>
                </div>
                <div class="form-group">
                  <label>Konfirmasi Password</label>
                  <input v-model="form.password_confirmation" :type="showPass ? 'text' : 'password'"
                    placeholder="Ulangi password" :class="{ error: formErrors.password_confirmation }" />
                  <span v-if="formErrors.password_confirmation" class="error-msg">{{ formErrors.password_confirmation
                    }}</span>
                </div>
              </template>
              <template v-if="isEditing">
                <div class="form-group">
                  <label>Status</label>
                  <div v-if="isSelf" class="self-status-notice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="8" x2="12" y2="12" />
                      <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    Tidak dapat mengubah status akun sendiri.
                  </div>
                  <div v-else class="toggle-row">
                    <label class="toggle-switch">
                      <input type="checkbox" v-model="form.aktif" />
                      <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">{{ form.aktif ? 'Aktif' : 'Nonaktif' }}</span>
                  </div>
                </div>
              </template>

              <!-- Server error -->
              <div v-if="serverError" class="server-error">{{ serverError }}</div>

              <div class="modal-footer">
                <button type="button" class="btn-cancel" @click="closeFormModal">Batal</button>
                <button type="submit" class="btn-submit" :disabled="submitting">
                  <span v-if="submitting" class="spinner"></span>
                  {{ isEditing ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ======================================= -->
    <!-- MODAL: Reset Password                   -->
    <!-- ======================================= -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showResetModal" class="modal-backdrop" @click.self="showResetModal = false">
          <div class="modal modal-sm">
            <div class="modal-header">
              <h2 class="modal-title">Reset Password</h2>
              <button class="modal-close" @click="showResetModal = false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>
            <div class="modal-body">
              <p class="reset-desc">Reset password untuk <strong>{{ selectedMarbot?.nama_marbot }}</strong>. Marbot akan
                otomatis logout.</p>
              <div class="form-group">
                <label>Password Baru</label>
                <div class="input-eye">
                  <input v-model="resetForm.password_baru" :type="showResetPass ? 'text' : 'password'"
                    placeholder="Minimal 8 karakter" :class="{ error: resetErrors.password_baru }" />
                  <button type="button" class="eye-btn" @click="showResetPass = !showResetPass">
                    <svg v-if="!showResetPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path
                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                      <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                  </button>
                </div>
                <span v-if="resetErrors.password_baru" class="error-msg">{{ resetErrors.password_baru }}</span>
              </div>
              <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input v-model="resetForm.password_baru_confirmation" :type="showResetPass ? 'text' : 'password'"
                  placeholder="Ulangi password baru" :class="{ error: resetErrors.password_baru_confirmation }" />
                <span v-if="resetErrors.password_baru_confirmation" class="error-msg">{{
                  resetErrors.password_baru_confirmation }}</span>
              </div>
              <div v-if="serverError" class="server-error">{{ serverError }}</div>
              <div class="modal-footer">
                <button class="btn-cancel" @click="showResetModal = false">Batal</button>
                <button class="btn-submit btn-warning" :disabled="submitting" @click="submitReset">
                  <span v-if="submitting" class="spinner"></span>
                  Reset Password
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ======================================= -->
    <!-- MODAL: Konfirmasi Nonaktifkan           -->
    <!-- ======================================= -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showDeleteModal" class="modal-backdrop" @click.self="showDeleteModal = false">
          <div class="modal modal-sm">
            <div class="modal-body confirm-body">
              <div class="confirm-icon danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                </svg>
              </div>
              <h3 class="confirm-title">Nonaktifkan Marbot?</h3>
              <p class="confirm-desc">
                Akun <strong>{{ selectedMarbot?.nama_marbot }}</strong> akan dinonaktifkan.
                Riwayat peminjaman tetap tersimpan.
              </p>
              <div class="modal-footer">
                <button class="btn-cancel" @click="showDeleteModal = false">Batal</button>
                <button class="btn-submit btn-danger" :disabled="submitting" @click="submitDelete">
                  <span v-if="submitting" class="spinner"></span>
                  Ya, Nonaktifkan
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Toast notification -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
        <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        {{ toast.message }}
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMarbotStore } from '@/stores/marbot'
import { useAuthStore } from '@/stores/auth'

const store = useMarbotStore()
const authStore = useAuthStore()

// ── State ──────────────────────────────────────────────────────────────────
const searchQuery = ref('')
const filterAktif = ref(null)

const showFormModal = ref(false)
const showResetModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const selectedMarbot = ref(null)
const submitting = ref(false)
const serverError = ref('')
const showPass = ref(false)
const showResetPass = ref(false)

const form = ref({ nama_marbot: '', email: '', password: '', password_confirmation: '', aktif: true })
const formErrors = ref({})

const resetForm = ref({ password_baru: '', password_baru_confirmation: '' })
const resetErrors = ref({})

const toast = ref({ show: false, type: 'success', message: '' })

// ── Computed ───────────────────────────────────────────────────────────────
const marbots = computed(() => store.marbots?.data ?? store.marbots ?? [])

// true jika marbot yang sedang diedit adalah diri sendiri
const isSelf = computed(() => authStore.user?.id === selectedMarbot.value?.id)

const activeCount = computed(() => marbots.value.filter(m => m.aktif).length)
const inactiveCount = computed(() => marbots.value.filter(m => !m.aktif).length)

const filteredMarbots = computed(() => {
  return marbots.value.filter(m => {
    const q = searchQuery.value.toLowerCase()
    const matchSearch = !q ||
      m.nama_marbot?.toLowerCase().includes(q) ||
      m.email?.toLowerCase().includes(q)
    const matchAktif = filterAktif.value === null || m.aktif === filterAktif.value
    return matchSearch && matchAktif
  })
})

// ── Lifecycle ──────────────────────────────────────────────────────────────
onMounted(() => store.fetchMarbots())

// ── Helpers ────────────────────────────────────────────────────────────────
function getInitials(name = '') {
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

const avatarColors = ['#2E7D32', '#1565C0', '#6A1B9A', '#AD1457', '#E65100', '#00695C', '#4527A0']
function getAvatarColor(name = '') {
  let hash = 0
  for (const c of name) hash = c.charCodeAt(0) + ((hash << 5) - hash)
  return avatarColors[Math.abs(hash) % avatarColors.length]
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function showToast(type, message) {
  toast.value = { show: true, type, message }
  setTimeout(() => { toast.value.show = false }, 3000)
}

function onSearch() { /* filtering is reactive */ }

// ── Form Modal ─────────────────────────────────────────────────────────────
function openCreateModal() {
  isEditing.value = false
  selectedMarbot.value = null
  form.value = { nama_marbot: '', email: '', password: '', password_confirmation: '', aktif: true }
  formErrors.value = {}
  serverError.value = ''
  showPass.value = false
  showFormModal.value = true
}

function openEditModal(marbot) {
  isEditing.value = true
  selectedMarbot.value = marbot
  form.value = { nama_marbot: marbot.nama_marbot, email: marbot.email, aktif: marbot.aktif }
  formErrors.value = {}
  serverError.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
}

function validateForm() {
  const errors = {}
  if (!form.value.nama_marbot?.trim()) errors.nama_marbot = 'Nama wajib diisi.'
  if (!form.value.email?.trim()) errors.email = 'Email wajib diisi.'
  else if (!/\S+@\S+\.\S+/.test(form.value.email)) errors.email = 'Format email tidak valid.'
  if (!isEditing.value) {
    if (!form.value.password) errors.password = 'Password wajib diisi.'
    else if (form.value.password.length < 8) errors.password = 'Password minimal 8 karakter.'
    if (form.value.password !== form.value.password_confirmation)
      errors.password_confirmation = 'Password tidak cocok.'
  }
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

async function submitForm() {
  if (!validateForm()) return
  submitting.value = true
  serverError.value = ''
  try {
    if (isEditing.value) {
      await store.updateMarbot(selectedMarbot.value.id, form.value)
      showToast('success', 'Data marbot berhasil diperbarui.')
    } else {
      await store.createMarbot(form.value)
      showToast('success', 'Akun marbot berhasil dibuat.')
    }
    closeFormModal()
  } catch (err) {
    const msg = err?.response?.data?.message || 'Terjadi kesalahan. Coba lagi.'
    serverError.value = msg
  } finally {
    submitting.value = false
  }
}

// ── Reset Password ─────────────────────────────────────────────────────────
function openResetModal(marbot) {
  selectedMarbot.value = marbot
  resetForm.value = { password_baru: '', password_baru_confirmation: '' }
  resetErrors.value = {}
  serverError.value = ''
  showResetPass.value = false
  showResetModal.value = true
}

async function submitReset() {
  const errors = {}
  if (!resetForm.value.password_baru) errors.password_baru = 'Password baru wajib diisi.'
  else if (resetForm.value.password_baru.length < 8) errors.password_baru = 'Minimal 8 karakter.'
  if (resetForm.value.password_baru !== resetForm.value.password_baru_confirmation)
    errors.password_baru_confirmation = 'Password tidak cocok.'
  resetErrors.value = errors
  if (Object.keys(errors).length) return

  submitting.value = true
  serverError.value = ''
  try {
    await store.resetPassword(selectedMarbot.value.id, resetForm.value)
    showToast('success', `Password ${selectedMarbot.value.nama_marbot} berhasil direset.`)
    showResetModal.value = false
  } catch (err) {
    serverError.value = err?.response?.data?.message || 'Terjadi kesalahan.'
  } finally {
    submitting.value = false
  }
}

// ── Delete / Nonaktifkan ───────────────────────────────────────────────────
function confirmDelete(marbot) {
  selectedMarbot.value = marbot
  serverError.value = ''
  showDeleteModal.value = true
}

async function submitDelete() {
  submitting.value = true
  try {
    await store.deleteMarbot(selectedMarbot.value.id)
    showToast('success', `Akun ${selectedMarbot.value.nama_marbot} berhasil dinonaktifkan.`)
    showDeleteModal.value = false
  } catch (err) {
    showToast('error', err?.response?.data?.message || 'Gagal menonaktifkan akun.')
    showDeleteModal.value = false
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

/* ── Root & Layout ─────────────────────────────────────────────────────── */
.marbot-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  padding: 28px 32px;
  min-height: 100vh;
  background: #F4F6F9;
  color: #1a1f2e;
}

/* ── Header ─────────────────────────────────────────────────────────────── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-icon {
  width: 48px;
  height: 48px;
  background: #16a34a;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.header-icon svg {
  width: 22px;
  height: 22px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #1a1f2e;
  margin: 0;
  line-height: 1.2;
}

.page-subtitle {
  font-size: 13px;
  color: #7a8499;
  margin: 2px 0 0;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s, transform 0.1s ease-in-out;
}

.btn-primary svg {
  width: 16px;
  height: 16px;
}

.btn-primary:hover {
  background: transparent;
  border: 1px solid #16a34a;
  color: #16a34a;
}

.btn-primary:active {
  transform: scale(0.98);
}

/* ── Stats Bar ─────────────────────────────────────────────────────────── */
.stats-bar {
  display: flex;
  align-items: center;
  gap: 0;
  background: #fff;
  border-radius: 12px;
  padding: 16px 24px;
  margin-bottom: 20px;
  border: 1px solid #e8ecf4;
  width: fit-content;
  gap: 24px;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.stat-number {
  font-size: 22px;
  font-weight: 700;
  color: #1a1f2e;
  font-family: 'DM Mono', monospace;
}

.stat-number.aktif {
  color: #16a34a;
}

.stat-number.nonaktif {
  color: #dc2626;
}

.stat-label {
  font-size: 12px;
  color: #7a8499;
  font-weight: 500;
}

.stat-divider {
  width: 1px;
  height: 32px;
  background: #e8ecf4;
}

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #e8ecf4;
  border-radius: 10px;
  padding: 9px 14px;
  flex: 1;
  max-width: 340px;
  transition: border-color 0.2s;
}

.search-box:focus-within {
  border-color: #1e3a5f;
}

.search-box svg {
  width: 16px;
  height: 16px;
  color: #a0aec0;
  flex-shrink: 0;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 14px;
  font-family: inherit;
  color: #1a1f2e;
  width: 100%;
}

.search-box input::placeholder {
  color: #a0aec0;
}

.clear-search {
  border: none;
  background: none;
  cursor: pointer;
  color: #a0aec0;
  padding: 0;
  display: flex;
  align-items: center;
}

.clear-search svg {
  width: 14px;
  height: 14px;
}

.clear-search:hover {
  color: #1a1f2e;
}

.filter-tabs {
  display: flex;
  gap: 6px;
}

.filter-tab {
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  border: 1px solid #e8ecf4;
  background: #fff;
  color: #7a8499;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s ease-in-out;
}

.filter-tab.active {
  background: #16a34a;
  color: #fff;
}

.filter-tab:not(.active):hover {
  border-color: #16a34a;
  color: #16a34a;
}

/* ── Table ─────────────────────────────────────────────────────────────── */
.table-wrapper {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e8ecf4;
  overflow: hidden;
}

.marbot-table {
  width: 100%;
  border-collapse: collapse;
}

.marbot-table thead tr {
  background: #f8fafc;
  border-bottom: 1px solid #e8ecf4;
}

.marbot-table th {
  padding: 13px 20px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #7a8499;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.th-actions {
  text-align: right;
}

.table-row {
  border-bottom: 1px solid #f1f4f9;
  animation: fadeUp 0.3s ease both;
  transition: background 0.15s;
}

.table-row:last-child {
  border-bottom: none;
}

.table-row:hover {
  background: #f8fafd;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.marbot-table td {
  padding: 14px 20px;
  vertical-align: middle;
}

.marbot-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.marbot-name {
  font-size: 14px;
  font-weight: 600;
  color: #1a1f2e;
}

.marbot-email {
  font-size: 13px;
  color: #7a8499;
  font-family: 'DM Mono', monospace;
}

.date-text {
  font-size: 13px;
  color: #9aa3b5;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-aktif {
  background: #dcfce7;
  color: #16a34a;
}

.badge-nonaktif {
  background: #fee2e2;
  color: #dc2626;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  animation: pulse-dot 2s infinite;
}

.badge-nonaktif .badge-dot {
  animation: none;
}

@keyframes pulse-dot {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.4;
  }
}

/* ── Action Buttons ────────────────────────────────────────────────────── */
.action-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
}

.action-btn svg {
  width: 15px;
  height: 15px;
}

.btn-edit {
  color: #2563eb;
  border-color: #dbeafe;
}

.btn-edit:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.btn-reset {
  color: #d97706;
  border-color: #fef3c7;
}

.btn-reset:hover {
  background: #fffbeb;
  border-color: #fde68a;
}

.btn-delete {
  color: #dc2626;
  border-color: #fee2e2;
}

.btn-delete:hover {
  background: #fef2f2;
  border-color: #fecaca;
}

.btn-delete:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

/* ── Skeleton ──────────────────────────────────────────────────────────── */
.skeleton-list {
  padding: 12px 0;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 20px;
  border-bottom: 1px solid #f1f4f9;
}

.skeleton-row:last-child {
  border-bottom: none;
}

.skeleton-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
  flex-shrink: 0;
}

.skeleton-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton-line {
  height: 10px;
  border-radius: 4px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.w60 {
  width: 60%;
}

.w40 {
  width: 40%;
}

.skeleton-badge {
  width: 60px;
  height: 22px;
  border-radius: 20px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
  background-size: 200%;
  animation: shimmer 1.4s infinite;
}

.skeleton-actions {
  width: 90px;
  height: 22px;
  border-radius: 6px;
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

/* ── Empty State ───────────────────────────────────────────────────────── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 64px 24px;
  gap: 8px;
}

.empty-icon {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: #f1f4f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c4ccdb;
  margin-bottom: 8px;
}

.empty-icon svg {
  width: 30px;
  height: 30px;
}

.empty-title {
  font-size: 15px;
  font-weight: 600;
  color: #3d4a5c;
  margin: 0;
}

.empty-desc {
  font-size: 13px;
  color: #9aa3b5;
  margin: 0;
}

/* ── Modal ─────────────────────────────────────────────────────────────── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 25, 50, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modal-sm {
  max-width: 400px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid #f1f4f9;
}

.modal-title {
  font-size: 17px;
  font-weight: 700;
  margin: 0;
  color: #1a1f2e;
}

.modal-close {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: none;
  background: #f1f4f9;
  cursor: pointer;
  color: #7a8499;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.modal-close svg {
  width: 14px;
  height: 14px;
}

.modal-close:hover {
  background: #e8ecf4;
  color: #1a1f2e;
}

.modal-body {
  padding: 20px 24px;
}

/* ── Form ──────────────────────────────────────────────────────────────── */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #3d4a5c;
}

.form-group input {
  border: 1.5px solid #e8ecf4;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  font-family: inherit;
  color: #1a1f2e;
  outline: none;
  transition: border-color 0.2s;
  width: 100%;
  box-sizing: border-box;
}

.form-group input:focus {
  border-color: #1e3a5f;
}

.form-group input.error {
  border-color: #dc2626;
}

.error-msg {
  font-size: 12px;
  color: #dc2626;
}

.input-eye {
  position: relative;
}

.input-eye input {
  padding-right: 42px;
}

.eye-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: none;
  cursor: pointer;
  color: #a0aec0;
  padding: 0;
  display: flex;
  align-items: center;
}

.eye-btn svg {
  width: 16px;
  height: 16px;
}

.eye-btn:hover {
  color: #1a1f2e;
}

.toggle-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 22px;
}

.toggle-switch input {
  display: none;
}

.toggle-slider {
  position: absolute;
  inset: 0;
  cursor: pointer;
  border-radius: 22px;
  background: #e2e8f0;
  transition: background 0.2s;
}

.toggle-slider::before {
  content: '';
  position: absolute;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #fff;
  left: 3px;
  top: 3px;
  transition: transform 0.2s;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked+.toggle-slider {
  background: #16a34a;
}

.toggle-switch input:checked+.toggle-slider::before {
  transform: translateX(18px);
}

.toggle-label {
  font-size: 14px;
  color: #3d4a5c;
  font-weight: 500;
}

.server-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: #dc2626;
  margin-top: 12px;
}

/* ── Modal Footer ──────────────────────────────────────────────────────── */
.modal-footer {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
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
  transition: border-color 0.15s;
}

.btn-cancel:hover {
  border-color: #c4ccdb;
  color: #1a1f2e;
}

.btn-submit {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  background: #1e3a5f;
  color: #fff;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s, opacity 0.15s;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-submit:not(:disabled):hover {
  background: #162d4a;
}

.btn-submit.btn-warning {
  background: #d97706;
}

.btn-submit.btn-warning:not(:disabled):hover {
  background: #b45309;
}

.btn-submit.btn-danger {
  background: #dc2626;
}

.btn-submit.btn-danger:not(:disabled):hover {
  background: #b91c1c;
}

/* ── Spinner ───────────────────────────────────────────────────────────── */
.spinner {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ── Confirm Modal ─────────────────────────────────────────────────────── */
.confirm-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 8px;
}

.confirm-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}

.confirm-icon.danger {
  background: #fef2f2;
  color: #dc2626;
}

.confirm-icon svg {
  width: 26px;
  height: 26px;
}

.confirm-title {
  font-size: 17px;
  font-weight: 700;
  color: #1a1f2e;
  margin: 0;
}

.confirm-desc {
  font-size: 14px;
  color: #7a8499;
  margin: 0;
  line-height: 1.6;
}

/* ── Reset Modal specific ──────────────────────────────────────────────── */
.reset-desc {
  font-size: 14px;
  color: #7a8499;
  margin: 0 0 16px;
  line-height: 1.6;
}

/* ── Toast ─────────────────────────────────────────────────────────────── */
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
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
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

.toast-error {
  background: #dc2626;
  color: #fff;
}

.self-status-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: #92400e;
  font-weight: 500;
}

.self-status-notice svg {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  color: #d97706;
}

/* ── Transitions ───────────────────────────────────────────────────────── */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s;
}

.modal-enter-active .modal,
.modal-leave-active .modal {
  transition: transform 0.25s cubic-bezier(0.34, 1.4, 0.64, 1), opacity 0.2s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal {
  transform: scale(0.94) translateY(12px);
  opacity: 0;
}

.modal-leave-to .modal {
  transform: scale(0.96);
  opacity: 0;
}

.toast-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.4, 0.64, 1);
}

.toast-leave-active {
  transition: all 0.2s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(16px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* ── Responsive ────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .marbot-page {
    padding: 16px;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    max-width: 100%;
  }

  .marbot-table {
    font-size: 13px;
  }

  .marbot-table th:nth-child(4),
  .marbot-table td:nth-child(4) {
    display: none;
  }
}
</style>