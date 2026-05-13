<template>
    <div class="page">

        <div class="back-row">
            <router-link to="/peminjaman" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Kembali ke Daftar
            </router-link>
        </div>

        <div v-if="store.loading" class="detail-skel">
            <div class="skel-head"></div>
            <div class="skel-body">
                <div v-for="i in 6" :key="i" class="skel-field"></div>
            </div>
        </div>

        <div v-else-if="!p" class="not-found">
            <p>Data peminjaman tidak ditemukan.</p>
            <router-link to="/peminjaman" class="btn-primary">Kembali</router-link>
        </div>

        <template v-else>
            <div class="detail-grid">

                <div class="main-card">
                    <div class="card-header">
                        <div class="card-title-row">
                            <div class="avatar-lg" :style="{ background: avatarColor(wargaNama) }">{{
                                initials(wargaNama) }}</div>
                            <div>
                                <h2 class="warga-name">{{ wargaNama }}</h2>
                                <p class="warga-sub">{{ p.warga?.no_hp ?? '-' }} · {{ p.warga?.rt_rw ?? '' }}</p>
                            </div>
                        </div>
                        <StatusBadge :status="p.status" size="lg" />
                    </div>

                    <div class="divider"></div>

                    <div class="fields-grid">
                        <div class="field">
                            <span class="field-label">Barang</span>
                            <span class="field-value">{{ p.barang?.nama_barang ?? '-' }}</span>
                        </div>
                        <div class="field">
                            <span class="field-label">Jumlah Pinjam</span>
                            <span class="field-value mono">{{ p.jumlah }} unit</span>
                        </div>

                        <div v-if="p.jumlah_kembali" class="field">
                            <span class="field-label">Sudah Dikembalikan</span>
                            <span class="field-value mono"
                                :class="p.jumlah_kembali < p.jumlah ? 'text-amber' : 'text-green'">
                                {{ p.jumlah_kembali }} / {{ p.jumlah }} unit
                            </span>
                        </div>
                        <div v-if="p.jumlah_kembali && p.jumlah_kembali < p.jumlah" class="field">
                            <span class="field-label">Sisa Belum Kembali</span>
                            <span class="field-value mono text-red">{{ p.jumlah - p.jumlah_kembali }} unit</span>
                        </div>

                        <div class="field">
                            <span class="field-label">Kondisi Barang</span>
                            <span class="field-value">{{ p.kondisi_pinjam ?? '-' }}</span>
                        </div>
                        <div class="field">
                            <span class="field-label">Dicatat oleh</span>
                            <span class="field-value">{{ p.marbot?.nama_marbot ?? '-' }}</span>
                        </div>
                        <div class="field full">
                            <span class="field-label">Keperluan</span>
                            <span class="field-value">{{ p.keperluan }}</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar">

                    <div class="side-card">
                        <h3 class="side-title">Tanggal</h3>
                        <div class="timeline">
                            <div class="tl-item">
                                <div class="tl-dot blue"></div>
                                <div>
                                    <div class="tl-label">Tanggal Pinjam</div>
                                    <div class="tl-val">{{ fmtDate(p.tgl_pinjam) }}</div>
                                </div>
                            </div>
                            <div class="tl-line"></div>
                            <div class="tl-item">
                                <div class="tl-dot" :class="isOverdue ? 'red' : 'green'"></div>
                                <div>
                                    <div class="tl-label">Rencana Kembali</div>
                                    <div class="tl-val" :class="{ 'text-red': isOverdue }">
                                        {{ fmtDate(p.tgl_rencana_kembali) }}
                                        <span v-if="isOverdue" class="overdue-tag">Terlambat {{ overdueDay }}
                                            hari</span>
                                    </div>
                                </div>
                            </div>
                            <template v-if="p.tgl_kembali_aktual">
                                <div class="tl-line"></div>
                                <div class="tl-item">
                                    <div class="tl-dot purple"></div>
                                    <div>
                                        <div class="tl-label">Tanggal Dikembalikan</div>
                                        <div class="tl-val">{{ fmtDate(p.tgl_kembali_aktual) }}</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="side-card">
                        <h3 class="side-title">Aksi</h3>
                        <div class="action-list">
                            <button v-if="p.status === 'Menunggu'" class="act-full act-green" :disabled="actionLoading"
                                @click="handleKonfirmasi">
                                <span v-if="actionLoading === 'konfirmasi'" class="spinner"></span>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                Konfirmasi Peminjaman
                            </button>

                            <router-link v-if="p.status === 'Menunggu'" :to="`/peminjaman/${p.id}/edit`"
                                class="act-full act-yellow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                Edit Peminjaman
                            </router-link>

                            <button v-if="p.status === 'Menunggu' || p.status === 'Aktif'" class="act-full act-red"
                                :disabled="actionLoading" @click="showBatal = true">
                                <span v-if="actionLoading === 'batal'" class="spinner"></span>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                </svg>
                                Batalkan Peminjaman
                            </button>

                            <div v-if="p.status === 'Sebagian Kembali'" class="act-info act-info--amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <span>
                                    {{ p.jumlah_kembali }} dari {{ p.jumlah }} unit sudah dikembalikan.
                                    Masih ada <strong>{{ p.jumlah - p.jumlah_kembali }} unit</strong> yang belum
                                    dikembalikan.
                                </span>
                            </div>

                            <div v-if="['Selesai', 'Batal', 'Rusak/Hilang'].includes(p.status)" class="act-info">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                Peminjaman sudah
                                {{ p.status === 'Selesai' ? 'selesai' : p.status === 'Batal' ? 'dibatalkan' : 'ditandai rusak/hilang' }},
                                tidak ada aksi yang tersedia.
                            </div>
                        </div>
                    </div>

                    <div v-if="p.kondisi_kembali || p.jumlah_kembali" class="side-card">
                        <h3 class="side-title">Kondisi Pengembalian</h3>

                        <div v-if="p.status === 'Sebagian Kembali'" class="return-progress">
                            <div class="return-progress-bar-wrap">
                                <div class="return-progress-bar-fill"
                                    :style="{ width: Math.round((p.jumlah_kembali / p.jumlah) * 100) + '%' }">
                                </div>
                            </div>
                            <span class="return-progress-pct">
                                {{ Math.round((p.jumlah_kembali / p.jumlah) * 100) }}% dikembalikan
                            </span>
                        </div>

                        <div v-if="p.kondisi_kembali" class="field" style="margin-top: 10px;">
                            <span class="field-label">Kondisi Terakhir</span>
                            <span class="field-value">{{ p.kondisi_kembali }}</span>
                        </div>
                        <div v-if="p.catatan" class="field" style="margin-top: 10px;">
                            <span class="field-label">Catatan</span>
                            <span class="field-value muted">{{ p.catatan }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showBatal" class="modal-backdrop" @click.self="showBatal = false">
                    <div class="modal modal-sm">
                        <div class="modal-body confirm-body">
                            <div class="confirm-ico danger">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path
                                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                    <line x1="12" y1="9" x2="12" y2="13" />
                                    <line x1="12" y1="17" x2="12.01" y2="17" />
                                </svg>
                            </div>
                            <h3 class="confirm-title">Batalkan Peminjaman?</h3>
                            <p class="confirm-desc">Peminjaman <strong>{{ wargaNama }}</strong> akan dibatalkan dan stok
                                dikembalikan ke gudang.</p>
                            <div class="modal-footer">
                                <button class="btn-cancel" @click="showBatal = false">Tidak</button>
                                <button class="btn-submit btn-danger" :disabled="actionLoading" @click="handleBatal">
                                    <span v-if="actionLoading === 'batal'" class="spinner"></span>
                                    Ya, Batalkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="toast">
                <div v-if="toast.show" :class="['toast', `toast-${toast.type}`]">
                    <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
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
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePeminjamanStore } from '@/stores/peminjaman'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const store = usePeminjamanStore()

const showBatal = ref(false)
const actionLoading = ref(null)
const toast = ref({ show: false, type: 'success', message: '' })

const p = computed(() => store.currentPeminjaman)
const wargaNama = computed(() => p.value?.warga?.nama_warga ?? p.value?.warga?.nama ?? '-')

// Overdue check
const isOverdue = computed(() => {
    if (!p.value) return false
    if (!['Aktif', 'Terlambat', 'Sebagian Kembali'].includes(p.value.status)) return false
    return p.value.tgl_rencana_kembali && new Date(p.value.tgl_rencana_kembali) < new Date()
})
const overdueDay = computed(() => {
    if (!isOverdue.value) return 0
    return Math.floor((new Date() - new Date(p.value.tgl_rencana_kembali)) / 86400000)
})

// Helpers
const COLORS = ['#1e3a5f', '#16a34a', '#d97706', '#6366f1', '#0891b2', '#be185d', '#7c3aed']
function avatarColor(name = '') {
    let h = 0; for (const c of name) h = c.charCodeAt(0) + ((h << 5) - h)
    return COLORS[Math.abs(h) % COLORS.length]
}
function initials(name = '') { return (name || '').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() }
function fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-' }

let toastTimer = null
function showToast(type, message) {
    clearTimeout(toastTimer)
    toast.value = { show: true, type, message }
    toastTimer = setTimeout(() => { toast.value.show = false }, 3500)
}

// Actions
async function handleKonfirmasi() {
    actionLoading.value = 'konfirmasi'
    try {
        await store.konfirmasiPeminjaman(p.value.id)
        showToast('success', 'Peminjaman berhasil dikonfirmasi.')
    } catch (e) {
        showToast('error', e?.response?.data?.message ?? 'Gagal mengonfirmasi.')
    } finally { actionLoading.value = null }
}

async function handleBatal() {
    actionLoading.value = 'batal'
    try {
        await store.batalPeminjaman(p.value.id)
        showToast('success', 'Peminjaman berhasil dibatalkan.')
        showBatal.value = false
        setTimeout(() => router.push('/peminjaman'), 1200)
    } catch (e) {
        showToast('error', e?.response?.data?.message ?? 'Gagal membatalkan.')
    } finally { actionLoading.value = null }
}

onMounted(() => store.fetchPeminjaman(route.params.id))
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
    margin-bottom: 20px;
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
}

.back-link svg {
    width: 16px;
    height: 16px;
}

.back-link:hover {
    color: #1e3a5f;
}

/* Grid layout */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
}

/* Main card */
.main-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e8ecf4;
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px;
    gap: 16px;
}

.card-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}

.avatar-lg {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
}

.warga-name {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    color: #1a1f2e;
}

.warga-sub {
    font-size: 13px;
    color: #7a8499;
    margin: 3px 0 0;
    font-family: 'DM Mono', monospace;
}

.divider {
    height: 1px;
    background: #f1f4f9;
    margin: 0 24px;
}

.fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: 8px 24px 24px;
}

.field {
    padding: 14px 0;
    border-bottom: 1px solid #f1f4f9;
}

.field.full {
    grid-column: 1 / -1;
}

.field-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 5px;
}

.field-value {
    font-size: 14px;
    color: #1a1f2e;
    font-weight: 500;
}

.field-value.mono {
    font-family: 'DM Mono', monospace;
}

.field-value.muted {
    color: #7a8499;
}

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.side-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e8ecf4;
    padding: 20px;
}

.side-title {
    font-size: 13px;
    font-weight: 700;
    color: #7a8499;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 16px;
}

/* Peminjaman.vue */
.qty-cell {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.qty-partial {
    font-size: 11px;
    color: #d97706;
    font-weight: 600;
    font-family: 'DM Mono', monospace;
}

/* PeminjamanDetail.vue */
.text-amber {
    color: #d97706;
}

.text-green {
    color: #16a34a;
}

.text-red {
    color: #dc2626;
}

.act-info--amber {
    background: #fffbeb;
    border: 1px solid #fbbf24;
    color: #92400e;
    border-radius: 8px;
}

.return-progress {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.return-progress-bar-wrap {
    width: 100%;
    height: 6px;
    background: #fef3c7;
    border-radius: 99px;
    overflow: hidden;
}

.return-progress-bar-fill {
    height: 100%;
    background: #16a34a;
    border-radius: 99px;
    transition: width 0.4s ease;
}

.return-progress-pct {
    font-size: 12px;
    font-weight: 600;
    color: #16a34a;
}

/* Timeline */
.timeline {
    display: flex;
    flex-direction: column;
}

.tl-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.tl-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}

.tl-dot.blue {
    background: #2563eb;
}

.tl-dot.green {
    background: #16a34a;
}

.tl-dot.red {
    background: #dc2626;
}

.tl-dot.purple {
    background: #7c3aed;
}

.tl-line {
    width: 1px;
    height: 20px;
    background: #e8ecf4;
    margin: 4px 0 4px 4.5px;
}

.tl-label {
    font-size: 11.5px;
    font-weight: 600;
    color: #a0aec0;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.tl-val {
    font-size: 14px;
    font-weight: 600;
    color: #1a1f2e;
    margin-top: 2px;
}

.text-red {
    color: #dc2626 !important;
}

.overdue-tag {
    display: inline-block;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    margin-left: 4px;
}

/* Action buttons */
.action-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.act-full {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 11px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-family: inherit;
    text-decoration: none;
    transition: opacity .15s, filter .15s;
}

.act-full svg {
    width: 16px;
    height: 16px;
}

.act-full:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.act-green {
    background: #dcfce7;
    color: #15803d;
}

.act-green:not(:disabled):hover {
    filter: brightness(.95);
}

.act-yellow {
    background: #fef3c7;
    color: #92400e;
}

.act-yellow:hover {
    filter: brightness(.95);
}

.act-red {
    background: #fee2e2;
    color: #dc2626;
}

.act-red:not(:disabled):hover {
    filter: brightness(.95);
}

.act-info {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 13px;
    color: #7a8499;
    display: flex;
    gap: 8px;
    align-items: flex-start;
    line-height: 1.5;
}

.act-info svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
}

/* Skeleton */
.detail-skel {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e8ecf4;
    padding: 24px;
}

.skel-head {
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(90deg, #f0f2f5 25%, #e8ecf4 50%, #f0f2f5 75%);
    background-size: 200%;
    animation: shimmer 1.4s infinite;
    margin-bottom: 24px;
}

.skel-body {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.skel-field {
    height: 48px;
    border-radius: 8px;
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

/* Not found */
.not-found {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 80px 24px;
}

/* Modal */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(15, 25, 50, .5);
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
    box-shadow: 0 20px 60px rgba(0, 0, 0, .15);
    overflow: hidden;
}

.modal-sm {
    max-width: 390px;
}

.modal-body {
    padding: 24px;
}

.confirm-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
}

.confirm-ico {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}

.confirm-ico.danger {
    background: #fef2f2;
    color: #dc2626;
}

.confirm-ico svg {
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

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    width: 100%;
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
}

.btn-cancel:hover {
    border-color: #c4ccdb;
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
}

.btn-submit.btn-danger {
    background: #dc2626;
}

.btn-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #1e3a5f;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

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

.toast-error {
    background: #dc2626;
    color: #fff;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity .2s;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
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

/* ── RESPONSIVE MODE ────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .detail-grid {
        grid-template-columns: 1fr 280px;
    }
}

@media (max-width: 768px) {
    .page {
        padding: 16px 20px;
    }

    .detail-grid {
        grid-template-columns: 1fr; /* Sidebar pindah ke bawah Main Card */
        gap: 16px;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .card-title-row {
        width: 100%;
        flex-wrap: wrap; /* Biar kalau panjang dia ke bawah, ga motong layar */
    }

    .fields-grid {
        grid-template-columns: 1fr; /* Field berjejer satu kolom ke bawah */
        padding: 16px;
        gap: 0;
    }

    .field {
        flex-direction: column; 
        align-items: flex-start; 
        gap: 4px; 
        padding: 12px 0;
    }
    
    .field-value {
        text-align: left; /* Teks value lurus ke kiri */
    }

    .sidebar {
        order: 2; /* Sidebar di bawah */
    }

    .main-card {
        order: 1; /* Main Card di atas */
    }
    
    /* Toast nempel bawah */
    .toast { 
        left: 20px; 
        right: 20px; 
        bottom: 20px; 
        max-width: none; 
        justify-content: center; 
    }
}

@media (max-width: 480px) {
    .page {
        padding: 12px 16px;
    }

    .avatar-lg {
        width: 44px;
        height: 44px;
        font-size: 16px;
    }

    .warga-name {
        font-size: 18px;
        line-height: 1.2;
    }
    
    .warga-sub {
        font-size: 12px;
    }

    .act-full {
        padding: 12px;
        font-size: 13px;
    }
    
    /* Modal Button numpuk */
    .modal-footer { 
        flex-direction: column; 
        gap: 10px; 
    }
    .btn-cancel, .btn-submit { 
        width: 100%; 
        justify-content: center; 
    }
    .btn-submit { order: 1; }
    .btn-cancel { order: 2; }
}
</style>