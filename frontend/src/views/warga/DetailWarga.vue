<template>
    <div class="dp">

        <!-- ── Top bar ── -->
        <div class="topbar">
            <RouterLink to="/warga" class="topbar__back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                    width="16" height="16">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </RouterLink>
            <div class="topbar__label">
                <span class="topbar__tag">PROFIL WARGA</span>
                <h1 class="topbar__title">Detail Warga</h1>
            </div>
        </div>

        <!-- ── Loading ── -->
        <div v-if="loading" class="fullpage-center">
            <div class="ring-loader"></div>
            <p class="loading-text">Memuat data warga…</p>
        </div>

        <!-- ── Not found ── -->
        <div v-else-if="!warga" class="fullpage-center">
            <div class="notfound-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                </svg>
            </div>
            <p class="notfound-text">Data warga tidak ditemukan.</p>
            <RouterLink to="/warga" class="btn-primary">Kembali ke Daftar</RouterLink>
        </div>

        <!-- ── Main layout ── -->
        <div v-else class="main-layout">

            <!-- ════ LEFT PANEL ════ -->
            <aside class="left-panel">

                <!-- Hero profile -->
                <div class="hero-card">
                    <div class="hero-bg" :style="{ background: heroBg(warga.nama_warga) }">
                        <div class="hero-bg__noise"></div>
                        <div class="hero-orb hero-orb--1"></div>
                        <div class="hero-orb hero-orb--2"></div>
                    </div>
                    <div class="hero-body">
                        <div class="hero-avatar" :style="{ background: avatarColor(warga.nama_warga) }">
                            <span>{{ initials(warga.nama_warga) }}</span>
                        </div>
                        <div class="hero-name-row">
                            <h2 class="hero-name">{{ warga.nama_warga }}</h2>
                            <span class="hero-badge" :class="statusClass">{{ statusLabel }}</span>
                        </div>
                        <p class="hero-sub">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12"
                                height="12">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            {{ warga.rt_rw || 'RT/RW belum diisi' }}
                        </p>
                        <div class="hero-actions">
                            <RouterLink :to="`/warga/${warga.id}/edit`" class="btn-hero btn-hero--edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" width="13" height="13">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                                Edit Profil
                            </RouterLink>
                            <button class="btn-hero btn-hero--del" @click="showDeleteModal = true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                    stroke-linecap="round" width="13" height="13">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                    <path d="M9 6V4h6v2" />
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info card -->
                <div class="info-card">
                    <div class="info-card__header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13"
                            height="13">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        Informasi Pribadi
                    </div>
                    <div class="info-rows">
                        <div class="info-row">
                            <div class="info-row__icon" style="background:#eff6ff;color:#2563eb">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13"
                                    height="13">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.56 3.35 2 2 0 0 1 3.53 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 5.86 5.86l.88-.88a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-row__label">Nomor HP</p>
                                <p class="info-row__val">{{ warga.no_hp }}</p>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__icon" style="background:#f0fdf4;color:#16a34a">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13"
                                    height="13">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-row__label">RT / RW</p>
                                <p class="info-row__val">{{ warga.rt_rw || '—' }}</p>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__icon" style="background:#fff7ed;color:#ea580c">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13"
                                    height="13">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-row__label">Alamat Lengkap</p>
                                <p class="info-row__val">{{ warga.alamat }}</p>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__icon" style="background:#f5f3ff;color:#7c3aed">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13"
                                    height="13">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-row__label">Terdaftar Sejak</p>
                                <p class="info-row__val">{{ formatDate(warga.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan stat -->
                <div class="summary-card">
                    <p class="summary-card__title">Ringkasan Peminjaman</p>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <p class="summary-item__num">{{ totalPeminjaman }}</p>
                            <p class="summary-item__label">Total</p>
                        </div>
                        <div class="summary-item summary-item--yellow">
                            <p class="summary-item__num">{{ jumlahAktif }}</p>
                            <p class="summary-item__label">Aktif</p>
                        </div>
                        <div class="summary-item summary-item--red">
                            <p class="summary-item__num">{{ jumlahTerlambat }}</p>
                            <p class="summary-item__label">Terlambat</p>
                        </div>
                        <div class="summary-item summary-item--gray">
                            <p class="summary-item__num">{{ jumlahSelesai }}</p>
                            <p class="summary-item__label">Selesai</p>
                        </div>
                    </div>
                </div>

                <!-- Help card -->
                <div class="help-card">
                    <div class="help-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18"
                            height="18">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <p class="help-card__title">Butuh Bantuan?</p>
                        <p class="help-card__sub">Jika data warga perlu diverifikasi, hubungi pengurus.</p>
                    </div>
                </div>

            </aside>

            <!-- ════ RIGHT PANEL ════ -->
            <main class="right-panel">

                <!-- Active banner -->
                <div v-if="jumlahAktif > 0" class="active-banner">
                    <div class="active-banner__dot"></div>
                    <div class="active-banner__text">
                        <strong>{{ jumlahAktif }} item</strong> sedang dipinjam oleh warga ini
                        <span v-if="jumlahTerlambat > 0" class="active-banner__warn">· {{ jumlahTerlambat }}
                            terlambat</span>
                    </div>
                    <span class="active-banner__pill">AKTIF</span>
                </div>

                <!-- Riwayat card -->
                <div class="riwayat-card">
                    <div class="riwayat-card__head">
                        <div class="riwayat-card__title-row">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15"
                                height="15" style="color:#16a34a">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="2" />
                                <line x1="9" y1="12" x2="15" y2="12" />
                                <line x1="9" y1="16" x2="13" y2="16" />
                            </svg>
                            <span class="riwayat-card__title">Daftar Riwayat Peminjaman</span>
                        </div>
                        <div class="tabs">
                            <button v-for="t in tabs" :key="t.key" class="tab"
                                :class="{ 'tab--on': activeTab === t.key }" @click="activeTab = t.key">
                                {{ t.label }}
                                <span class="tab__count" :class="{ 'tab__count--on': activeTab === t.key }">{{
                                    t.count }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div v-if="loadingRiwayat" class="riwayat-loading">
                        <div v-for="i in 4" :key="i" class="sk-row">
                            <div class="sk-thumb"></div>
                            <div class="sk-body">
                                <div class="sk-line" style="width:50%"></div>
                                <div class="sk-line" style="width:30%"></div>
                            </div>
                            <div class="sk-line" style="width:72px;margin-left:auto"></div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-else-if="!filteredRiwayat.length" class="riwayat-empty">
                        <div class="riwayat-empty__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28"
                                height="28">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="2" />
                            </svg>
                        </div>
                        <p>Tidak ada riwayat peminjaman</p>
                    </div>

                    <!-- List -->
                    <div v-else class="riwayat-list">
                        <div v-for="(p, idx) in filteredRiwayat" :key="p.id" class="pinjam-row"
                            :style="{ animationDelay: idx * 40 + 'ms' }">

                            <div class="pinjam-thumb">
                                <img v-if="p.barang?.foto_url" :src="p.barang.foto_url" :alt="p.barang?.nama_barang" />
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                    width="18" height="18">
                                    <rect x="2" y="3" width="20" height="14" rx="2" />
                                    <path d="M8 21h8M12 17v4" />
                                </svg>
                            </div>

                            <div class="pinjam-body">
                                <p class="pinjam-body__name">{{ p.barang?.nama_barang ?? 'Barang tidak diketahui' }}
                                </p>
                                <div class="pinjam-body__meta">
                                    <span class="meta-chip">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            width="10" height="10">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        Dipinjam {{ formatDate(p.tgl_pinjam) }}
                                    </span>
                                    <span v-if="p.tgl_rencana_kembali" class="meta-chip">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            width="10" height="10">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        Kembali {{ formatDate(p.tgl_rencana_kembali) }}
                                    </span>
                                    <span v-if="p.jumlah" class="meta-chip meta-chip--qty">× {{ p.jumlah }}</span>
                                </div>
                                <p v-if="p.catatan" class="pinjam-body__note">{{ p.catatan }}</p>
                            </div>

                            <div class="pinjam-right">
                                <span class="status-pill" :class="pillClass(p.status)">{{ p.status }}</span>
                                <span v-if="isLate(p)" class="late-chip">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        width="10" height="10">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
                                    +{{ lateDays(p) }}h
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="riwayatMeta.last_page > 1" class="pag-bar">
                        <button class="pag-btn" :disabled="riwayatMeta.current_page === 1"
                            @click="fetchRiwayat(riwayatMeta.current_page - 1)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>
                        <span class="pag-label">{{ riwayatMeta.current_page }} / {{ riwayatMeta.last_page }}</span>
                        <button class="pag-btn" :disabled="riwayatMeta.current_page === riwayatMeta.last_page"
                            @click="fetchRiwayat(riwayatMeta.current_page + 1)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" width="14" height="14">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- ── Modal Hapus ── -->
        <Transition name="fade">
            <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
                <div class="modal">
                    <div class="modal-warn-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22"
                            height="22">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <h3 class="modal-title">Hapus Data Warga?</h3>
                    <p class="modal-body">
                        Data <strong>{{ warga?.nama_warga }}</strong> akan dihapus secara permanen dan tidak dapat
                        dikembalikan. Warga dengan peminjaman aktif tidak dapat dihapus.
                    </p>
                    <div class="modal-foot">
                        <button class="btn-cancel" @click="showDeleteModal = false">Batalkan</button>
                        <button class="btn-del-confirm" :disabled="deleting" @click="doDelete">
                            <svg v-if="!deleting" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2" stroke-linecap="round" width="13" height="13">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6l-1 14H6L5 6" />
                                <path d="M9 6V4h6v2" />
                            </svg>
                            <span v-if="deleting" class="mini-spin"></span>
                            {{ deleting ? 'Menghapus…' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useWargaStore } from '@/stores/warga'
import { useToast } from 'vue-toastification'
import wargaService from '@/services/wargaService'

const store = useWargaStore()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const warga = ref(null)
const loading = ref(true)
const riwayat = ref([])
const riwayatMeta = ref({ current_page: 1, last_page: 1, total: 0 })
const loadingRiwayat = ref(false)
const activeTab = ref('semua')
const showDeleteModal = ref(false)
const deleting = ref(false)

const jumlahAktif = computed(() => riwayat.value.filter(p => ['Aktif', 'Menunggu'].includes(p.status)).length)
const jumlahTerlambat = computed(() => riwayat.value.filter(p => p.status === 'Terlambat').length)
const jumlahSelesai = computed(() => riwayat.value.filter(p => p.status === 'Selesai').length)
const totalPeminjaman = computed(() => warga.value?.peminjamans_count ?? riwayat.value.length)

const statusLabel = computed(() => {
    if (jumlahTerlambat.value > 0) return 'Terlambat'
    if (jumlahAktif.value > 0) return 'Ada Peminjaman'
    return 'Tidak Ada Peminjaman'
})
const statusClass = computed(() => {
    if (jumlahTerlambat.value > 0) return 'badge--red'
    if (jumlahAktif.value > 0) return 'badge--green'
    return 'badge--gray'
})

const tabs = computed(() => [
    { key: 'semua', label: 'Semua', count: riwayat.value.length },
    { key: 'aktif', label: 'Aktif', count: jumlahAktif.value + jumlahTerlambat.value },
    { key: 'selesai', label: 'Selesai', count: jumlahSelesai.value },
])

const filteredRiwayat = computed(() => {
    if (activeTab.value === 'aktif') return riwayat.value.filter(p => ['Aktif', 'Menunggu', 'Terlambat'].includes(p.status))
    if (activeTab.value === 'selesai') return riwayat.value.filter(p => p.status === 'Selesai')
    return riwayat.value
})

async function loadWarga() {
    loading.value = true
    try { warga.value = await wargaService.getOne(route.params.id) }
    catch { warga.value = null }
    finally { loading.value = false }
}

async function fetchRiwayat(page = 1) {
    loadingRiwayat.value = true
    try {
        const res = await wargaService.getRiwayat(route.params.id, { page, per_page: 10 })
        riwayat.value = res.data
        riwayatMeta.value = { current_page: res.current_page, last_page: res.last_page, total: res.total }
    } catch {
        riwayat.value = warga.value?.peminjamans ?? []
    } finally {
        loadingRiwayat.value = false
    }
}

async function doDelete() {
    deleting.value = true
    try {
        await store.deleteWarga(warga.value.id)
        toast.success(`Data ${warga.value.nama_warga} berhasil dihapus.`)
        router.push('/warga')
    } catch (err) {
        toast.error(err?.response?.data?.message || 'Gagal menghapus data warga.')
    } finally {
        deleting.value = false
    }
}

const PALETTE = ['#16a34a', '#0891b2', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#059669', '#2563eb']
function avatarColor(nama = '') {
    let h = 0; for (const c of nama) h = (h * 31 + c.charCodeAt(0)) & 0xfffffff
    return PALETTE[h % PALETTE.length]
}
function heroBg(nama = '') {
    const c = avatarColor(nama)
    return `linear-gradient(135deg, #0f1a0f 0%, ${c}55 100%)`
}
function initials(nama = '') {
    return nama.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
function pillClass(status) {
    return { 'Aktif': 'pill--aktif', 'Menunggu': 'pill--menunggu', 'Terlambat': 'pill--terlambat', 'Selesai': 'pill--selesai' }[status] ?? ''
}
function isLate(p) {
    if (!p.tgl_rencana_kembali || p.status === 'Selesai') return false
    return new Date(p.tgl_rencana_kembali) < new Date()
}
function lateDays(p) {
    return Math.floor((new Date() - new Date(p.tgl_rencana_kembali)) / 86400000)
}

onMounted(async () => { await loadWarga(); if (warga.value) fetchRiwayat() })
</script>

<style scoped>
.dp {
    font-family: 'DM Sans', 'Inter', sans-serif;
    min-height: 100vh;
    background: #f4f6f4;
    padding-bottom: 48px;
}

/* ── Topbar ── */
.topbar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 20px 0 22px;
}

.topbar__back {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    border: 1.5px solid #e5e7eb;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s;
    flex-shrink: 0;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.topbar__back:hover {
    border-color: #16a34a;
    color: #16a34a;
    background: #f0fdf4;
}

.topbar__tag {
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
    color: #16a34a;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.topbar__title {
    font-size: 19px;
    font-weight: 800;
    color: #111827;
    margin: 0;
}

/* ── Full page center ── */
.fullpage-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 14px;
    min-height: 56vh;
}

.ring-loader {
    width: 38px;
    height: 38px;
    border: 3px solid #e5e7eb;
    border-top-color: #16a34a;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}

.loading-text {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
}

.notfound-icon {
    width: 68px;
    height: 68px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
}

.notfound-text {
    color: #6b7280;
    font-size: 14px;
    margin: 0;
}

.btn-primary {
    padding: 10px 22px;
    background: #16a34a;
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
}

/* ── Main layout ── */
.main-layout {
    display: grid;
    grid-template-columns: 292px 1fr;
    gap: 16px;
    align-items: start;
}

/* ════ LEFT ════ */
.left-panel {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Hero card */
.hero-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: white;
}

.hero-bg {
    height: 88px;
    position: relative;
    overflow: hidden;
}

.hero-bg__noise {
    position: absolute;
    inset: 0;
    opacity: 0.35;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.1'/%3E%3C/svg%3E");
}

.hero-orb {
    position: absolute;
    border-radius: 50%;
    opacity: 0.25;
}

.hero-orb--1 {
    width: 90px;
    height: 90px;
    background: rgba(255, 255, 255, 0.25);
    top: -24px;
    right: 16px;
}

.hero-orb--2 {
    width: 55px;
    height: 55px;
    background: rgba(255, 255, 255, 0.12);
    top: 32px;
    left: 24px;
}

.hero-body {
    padding: 0 18px 18px;
}

.hero-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 3px solid white;
    color: white;
    font-size: 21px;
    font-weight: 900;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18);
}

.hero-name-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 5px;
}

.hero-name {
    font-size: 15.5px;
    font-weight: 800;
    color: #111827;
    margin: 0;
    line-height: 1.25;
}

.hero-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 2px 9px;
    border-radius: 20px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 2px;
}

.badge--green {
    background: #dcfce7;
    color: #166534;
}

.badge--red {
    background: #fee2e2;
    color: #991b1b;
}

.badge--gray {
    background: #f1f5f9;
    color: #64748b;
}

.hero-sub {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #9ca3af;
    margin: 0 0 16px;
}

.hero-actions {
    display: flex;
    gap: 8px;
}

.btn-hero {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    font-family: inherit;
    border: 1.5px solid;
}

.btn-hero--edit {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #16a34a;
}

.btn-hero--edit:hover {
    background: #dcfce7;
}

.btn-hero--del {
    background: #fff5f5;
    border-color: #fecaca;
    color: #dc2626;
}

.btn-hero--del:hover {
    background: #fee2e2;
}

/* Info card */
.info-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
}

.info-card__header {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #6b7280;
    padding: 13px 16px 10px;
    border-bottom: 1px solid #f5f5f5;
    background: #fafafa;
}

.info-rows {
    padding: 2px 0;
}

.info-row {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 11px 16px;
    border-bottom: 1px solid #f9fafb;
    transition: background 0.12s;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row:hover {
    background: #fafdfb;
}

.info-row__icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.info-row__label {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0 0 2px;
}

.info-row__val {
    font-size: 13px;
    color: #111827;
    font-weight: 600;
    margin: 0;
    line-height: 1.45;
}

/* Summary card */
.summary-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #f0f0f0;
    padding: 14px 16px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
}

.summary-card__title {
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #6b7280;
    margin: 0 0 12px;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.summary-item {
    background: #f9fafb;
    border-radius: 11px;
    padding: 13px;
    text-align: center;
    border: 1px solid #f0f0f0;
}

.summary-item--yellow {
    background: #fefce8;
    border-color: #fef08a;
}

.summary-item--red {
    background: #fef2f2;
    border-color: #fecaca;
}

.summary-item--gray {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.summary-item__num {
    font-size: 26px;
    font-weight: 900;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.summary-item--yellow .summary-item__num {
    color: #92400e;
}

.summary-item--red .summary-item__num {
    color: #991b1b;
}

.summary-item__label {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 700;
    margin: 5px 0 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Help card */
.help-card {
    background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    align-items: flex-start;
    gap: 11px;
}

.help-card__icon {
    width: 34px;
    height: 34px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.help-card__title {
    font-size: 13px;
    font-weight: 700;
    color: white;
    margin: 0 0 3px;
}

.help-card__sub {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.72);
    margin: 0;
    line-height: 1.45;
}

/* ════ RIGHT ════ */
.right-panel {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 0;
}

/* Active banner */
.active-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    border: 1.5px solid #bbf7d0;
    border-radius: 13px;
    padding: 13px 18px;
    box-shadow: 0 2px 12px rgba(22, 163, 74, 0.08);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.active-banner__dot {
    width: 9px;
    height: 9px;
    background: #16a34a;
    border-radius: 50%;
    flex-shrink: 0;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4);
    }

    50% {
        box-shadow: 0 0 0 5px rgba(22, 163, 74, 0);
    }
}

.active-banner__text {
    flex: 1;
    font-size: 13px;
    color: #374151;
    font-weight: 500;
}

.active-banner__warn {
    color: #dc2626;
    font-weight: 700;
}

.active-banner__pill {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    background: #dcfce7;
    color: #166534;
    padding: 3px 10px;
    border-radius: 20px;
    flex-shrink: 0;
}

/* Riwayat card */
.riwayat-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
}

.riwayat-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 14px;
    border-bottom: 1px solid #f5f5f5;
    background: #fafafa;
    flex-wrap: wrap;
    gap: 12px;
}

.riwayat-card__title-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.riwayat-card__title {
    font-size: 12.5px;
    font-weight: 800;
    color: #111827;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

/* Tabs */
.tabs {
    display: flex;
    background: #efefef;
    border-radius: 9px;
    padding: 3px;
    gap: 2px;
}

.tab {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 13px;
    border: none;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.tab--on {
    background: white;
    color: #111827;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.tab__count {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    background: #e5e7eb;
    color: #6b7280;
    font-size: 10px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.tab__count--on {
    background: #16a34a;
    color: white;
}

/* Skeleton */
.riwayat-loading {
    padding: 4px 0;
}

.sk-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #f9fafb;
}

.sk-thumb {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: #f1f5f9;
    flex-shrink: 0;
    animation: shimmer 1.4s ease-in-out infinite;
}

.sk-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.sk-line {
    height: 11px;
    border-radius: 6px;
    background: #f1f5f9;
    animation: shimmer 1.4s ease-in-out infinite;
}

@keyframes shimmer {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.4;
    }
}

/* Empty */
.riwayat-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 60px 24px;
    color: #9ca3af;
    font-size: 13.5px;
}

.riwayat-empty__icon {
    width: 62px;
    height: 62px;
    background: #f8fafc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    border: 1.5px dashed #e2e8f0;
}

/* List */
.riwayat-list {
    padding: 2px 0;
}

.pinjam-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 15px 20px;
    border-bottom: 1px solid #f9fafb;
    transition: background 0.12s;
    animation: rowIn 0.25s ease both;
}

@keyframes rowIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.pinjam-row:last-child {
    border-bottom: none;
}

.pinjam-row:hover {
    background: #fafdfb;
}

.pinjam-thumb {
    width: 48px;
    height: 48px;
    border-radius: 11px;
    background: #f1f5f9;
    border: 1px solid #eef0f2;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    flex-shrink: 0;
}

.pinjam-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pinjam-body {
    flex: 1;
    min-width: 0;
}

.pinjam-body__name {
    font-size: 13.5px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pinjam-body__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 4px;
}

.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #6b7280;
    background: #f4f4f5;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 500;
}

.meta-chip--qty {
    background: #eff6ff;
    color: #2563eb;
    font-weight: 800;
}

.pinjam-body__note {
    font-size: 11.5px;
    color: #9ca3af;
    font-style: italic;
    margin: 0;
}

.pinjam-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
    flex-shrink: 0;
}

.status-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
}

.pill--aktif {
    background: #dcfce7;
    color: #166534;
}

.pill--menunggu {
    background: #dbeafe;
    color: #1e40af;
}

.pill--terlambat {
    background: #fee2e2;
    color: #991b1b;
}

.pill--selesai {
    background: #f1f5f9;
    color: #64748b;
}

.late-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10.5px;
    font-weight: 700;
    color: #dc2626;
    background: #fef2f2;
    padding: 2px 7px;
    border-radius: 6px;
}

/* Pagination */
.pag-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 14px 20px;
    border-top: 1px solid #f0f0f0;
}

.pag-btn {
    width: 32px;
    height: 32px;
    border: 1.5px solid #e5e7eb;
    background: white;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #374151;
    transition: all 0.15s;
}

.pag-btn:hover:not(:disabled) {
    border-color: #16a34a;
    color: #16a34a;
    background: #f0fdf4;
}

.pag-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.pag-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #6b7280;
}

/* ── Modal ── */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(4px);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal {
    background: white;
    border-radius: 20px;
    padding: 28px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 28px 72px rgba(0, 0, 0, 0.2);
    animation: popIn 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-warn-icon {
    width: 50px;
    height: 50px;
    background: #fef2f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    margin-bottom: 16px;
}

.modal-title {
    font-size: 17px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px;
}

.modal-body {
    font-size: 13.5px;
    color: #6b7280;
    margin: 0 0 22px;
    line-height: 1.65;
}

.modal-foot {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 10px 20px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-del-confirm {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    background: #dc2626;
    color: white;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}

.btn-del-confirm:hover:not(:disabled) {
    background: #b91c1c;
}

.btn-del-confirm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.mini-spin {
    width: 13px;
    height: 13px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ── Responsive ── */
@media (max-width: 860px) {
    .main-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 520px) {
    .riwayat-card__head {
        flex-direction: column;
        align-items: flex-start;
    }

    .pinjam-row {
        padding: 12px 14px;
    }

    .meta-chip:not(.meta-chip--qty) {
        display: none;
    }
}
</style>