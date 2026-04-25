<template>
    <div class="laporan-page text-left">
        <div class="page-top">
            <div class="breadcrumb">
                <RouterLink to="/laporan" class="bc-link">Laporan</RouterLink>
                <span class="bc-sep">›</span>
                <span class="bc-current">Laporan Stok Barang</span>
            </div>
            <div class="page-title-row">
                <div>
                    <h1 class="pg-title">Laporan Stok Barang</h1>
                    <p class="pg-sub">Rekap ketersediaan stok seluruh barang inventaris per kategori.</p>
                </div>
                <button class="btn-refresh" @click="fetchData" :disabled="loading">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        width="14" height="14" :class="{ 'rotating': loading }">
                        <polyline points="23 4 23 10 17 10" />
                        <path d="M20.49 15a9 9 0 1 1-.49-3.34" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <div v-if="loading" class="state-center card-pad">
            <span class="spinner-lg"></span>
            <p>Memuat data stok...</p>
        </div>

        <template v-else-if="dataStok.length">
            <div class="grand-total-row">
                <div class="gt-card gt-card--total shadow-sm">
                    <p class="gt-val">{{ grandTotal.stok_total }}</p>
                    <p class="gt-label">Total Unit Barang</p>
                </div>
                <div class="gt-card gt-card--green shadow-sm">
                    <p class="gt-val">{{ grandTotal.stok_tersedia }}</p>
                    <p class="gt-label">Unit Tersedia</p>
                </div>
                <div class="gt-card gt-card--orange shadow-sm">
                    <p class="gt-val">{{ grandTotal.stok_dipinjam }}</p>
                    <p class="gt-label">Unit Dipinjam</p>
                </div>
                <div class="gt-card gt-card--blue shadow-sm">
                    <p class="gt-val">{{ dataStok.length }}</p>
                    <p class="gt-label">Kategori</p>
                </div>
            </div>

            <div v-for="kategori in dataStok" :key="kategori.kategori" class="kategori-block shadow-sm">
                <div class="kat-header" @click="toggleKat(kategori.kategori)">
                    <div class="kat-header__left">
                        <div class="kat-icon">📦</div>
                        <div>
                            <p class="kat-name">{{ kategori.kategori }}</p>
                            <p class="kat-sub">{{ kategori.barangs?.length || 0 }} Jenis Barang</p>
                        </div>
                    </div>
                    <div class="kat-header__stats">
                        <span class="kat-stat kat-stat--total">Total: {{ kategori.stok_total }}</span>
                        <span class="kat-stat kat-stat--green">Ada: {{ kategori.stok_tersedia }}</span>
                        <span class="kat-stat kat-stat--orange" v-if="kategori.stok_dipinjam > 0">Pinjam: {{ kategori.stok_dipinjam }}</span>
                    </div>
                    <svg :class="{ 'rotate-180': collapsed[kategori.kategori] }" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="16" height="16"
                        style="color:#9ca3af;transition:transform 0.2s;flex-shrink:0;">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>

                <div v-if="!collapsed[kategori.kategori]" class="kat-table-wrap">
                    <table class="stok-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Lokasi</th>
                                <th>Kondisi</th>
                                <th class="th-center">Stok Total</th>
                                <th class="th-center">Tersedia</th>
                                <th class="th-center">Dipinjam</th>
                                <th class="th-center">Ketersediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="barang in kategori.barangs" :key="barang.id">
                                <td><p class="td-name">{{ barang.nama_barang }}</p></td>
                                <td class="td-loc">{{ barang.lokasi || '—' }}</td>
                                <td>
                                    <span class="badge-kondisi" :class="kondisiClass(barang.kondisi)">{{ barang.kondisi }}</span>
                                </td>
                                <td class="td-center">{{ barang.stok_total }}</td>
                                <td class="td-center td-green">{{ barang.stok_tersedia }}</td>
                                <td class="td-center td-orange">{{ barang.stok_total - barang.stok_tersedia }}</td>
                                <td class="td-center">
                                    <div class="progress-wrap">
                                        <div class="progress-bar">
                                            <div class="progress-fill"
                                                :class="progressClass(barang.stok_tersedia, barang.stok_total)"
                                                :style="{ width: progressPct(barang.stok_tersedia, barang.stok_total) + '%' }">
                                            </div>
                                        </div>
                                        <span class="progress-pct">{{ progressPct(barang.stok_tersedia, barang.stok_total) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="laporan-footer shadow-sm">
                <p>Laporan dibuat pada: <strong>{{ now }}</strong></p>
                <p>Data bersumber dari sistem inventaris SIMBA v2.0</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import laporanService from '@/services/laporanService'
import { useToast } from 'vue-toastification'

const toast = useToast()
const loading = ref(false)
const dataStok = ref([])
const collapsed = reactive({})
const now = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })

const grandTotal = computed(() => {
    let total = 0, tersedia = 0, dipinjam = 0
    dataStok.value.forEach(kat => {
        total += Number(kat.stok_total || 0)
        tersedia += Number(kat.stok_tersedia || 0)
        dipinjam += (Number(kat.stok_total || 0) - Number(kat.stok_tersedia || 0))
    })
    return { stok_total: total, stok_tersedia: tersedia, stok_dipinjam: dipinjam }
})

async function fetchData() {
    loading.value = true
    try {
        const res = await laporanService.getStok()
        const rawData = res.data ?? res
        dataStok.value = rawData.map(kat => ({
            ...kat,
            barangs: kat.barangs || [],
            stok_total: Number(kat.stok_total || 0),
            stok_tersedia: Number(kat.stok_tersedia || 0),
            stok_dipinjam: Number(kat.stok_total || 0) - Number(kat.stok_tersedia || 0)
        }))
    } catch { toast.error('Gagal memuat data stok.') }
    finally { loading.value = false }
}

function toggleKat(nama) { collapsed[nama] = !collapsed[nama] }
function progressPct(tersedia, total) { return total ? Math.round((tersedia / total) * 100) : 0 }
function progressClass(tersedia, total) {
    const pct = progressPct(tersedia, total)
    if (pct < 30) return 'fill--low'
    if (pct < 70) return 'fill--mid'
    return 'fill--high'
}
function kondisiClass(k) {
    if (k === 'Baik') return 'k--baik'
    if (k === 'Rusak Ringan') return 'k--ringan'
    return 'k--berat'
}
onMounted(fetchData)
</script>

<style scoped>
/* COPAST SEMUA CSS LAMA DISINI */
.laporan-page { font-family: 'DM Sans', sans-serif; }
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; margin-bottom: 10px; color: #9ca3af; }
.bc-link { color: #16a34a; text-decoration: none; font-weight: 500; }
.bc-sep { color: #d1d5db; }
.bc-current { color: #374151; font-weight: 500; }
.page-top { margin-bottom: 20px; }
.pg-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
.pg-sub { font-size: 13px; color: #6b7280; margin: 3px 0 0; }
.btn-refresh { display: inline-flex; align-items: center; gap: 7px; background: white; color: #374151; font-size: 13px; font-weight: 600; padding: 9px 16px; border-radius: 9px; border: 1.5px solid #e5e7eb; cursor: pointer; transition: all 0.2s; }
.btn-refresh:hover { background: #f9fafb; border-color: #d1d5db; }
.grand-total-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
.gt-card { background: white; border-radius: 12px; border: 1px solid #f0f0f0; padding: 16px; text-align: center; }
.gt-val { font-size: 28px; font-weight: 800; margin: 0; line-height: 1; }
.gt-label { font-size: 11.5px; font-weight: 500; margin: 6px 0 0; }
.gt-card--total .gt-val { color: #111827; }
.gt-card--green { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.gt-card--orange { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
.gt-card--blue { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
.kategori-block { background: white; border-radius: 12px; border: 1px solid #f0f0f0; margin-bottom: 12px; overflow: hidden; }
.kat-header { display: flex; align-items: center; gap: 12px; padding: 14px 18px; cursor: pointer; }
.kat-header:hover { background: #f9fafb; }
.kat-name { font-size: 14px; font-weight: 700; color: #111827; margin: 0; }
.kat-sub { font-size: 11.5px; color: #9ca3af; margin: 2px 0 0; }
.kat-stat { font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 10px; margin-left: 5px; }
.kat-stat--total { background: #f1f5f9; color: #374151; }
.kat-stat--green { background: #dcfce7; color: #166534; }
.kat-stat--orange { background: #ffedd5; color: #9a3412; }
.stok-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.stok-table th { background: #f9fafb; text-align: left; padding: 12px 14px; color: #6b7280; font-size: 11px; text-transform: uppercase; }
.stok-table td { padding: 12px 14px; border-top: 1px solid #f9fafb; }
.td-name { font-weight: 600; color: #111827; margin: 0; }
.td-green { color: #16a34a; font-weight: bold; }
.td-orange { color: #ea580c; font-weight: bold; }
.progress-bar { width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; margin-top: 4px; }
.progress-fill { height: 100%; transition: width 0.5s; }
.fill--high { background: #16a34a; }
.fill--mid { background: #f59e0b; }
.fill--low { background: #ef4444; }
.laporan-footer { background: white; border-radius: 12px; border: 1px solid #f0f0f0; padding: 14px 18px; display: flex; justify-content: space-between; font-size: 12px; color: #9ca3af; }
.rotate-180 { transform: rotate(180deg); }
.spinner-lg { width: 28px; height: 28px; border: 3px solid #e5e7eb; border-top-color: #16a34a; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes rotate { to { transform: rotate(360deg); } }
.rotating { animation: rotate 0.8s linear infinite; }
</style>