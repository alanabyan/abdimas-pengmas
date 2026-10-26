<template>
  <main class="finance-page">
    <header class="finance-header">
      <a class="finance-brand" href="/"><span>AP</span><strong>Abdimas Pengmas</strong></a>
      <button class="logout-button" type="button" @click="logout"><LogOut :size="16" /> Keluar</button>
    </header>
    <section class="finance-content">
      <p class="eyebrow">Ruang bendahara</p>
      <h1>Assalamu'alaikum, {{ auth.user?.nama || auth.user?.nama_bendahara || 'Bendahara' }}.</h1>
      <p class="intro">Pengajuan infaq dan sedekah yang masuk melalui halaman Abdimas Pengmas.</p>

      <div class="summary-row">
        <div><span>JUMLAH PENGAJUAN</span><strong>{{ summary.total_pengajuan }}</strong></div>
        <div><span>MENUNGGU KONFIRMASI</span><strong>{{ summary.menunggu_konfirmasi }}</strong></div>
        <div><span>TOTAL NOMINAL DIAJUKAN</span><strong>{{ formatCurrency(summary.total_nominal) }}</strong></div>
      </div>

      <section class="submission-section">
        <div class="section-head"><div><h2>Pengajuan terbaru</h2><p>Pengajuan belum menandakan pembayaran telah diterima.</p></div><button type="button" class="refresh-button" :disabled="loading" @click="loadSubmissions"><RefreshCw :size="15" /> Muat ulang</button></div>
        <p v-if="errorMessage" class="message error">{{ errorMessage }}</p>
        <div v-else-if="loading" class="empty-state">Memuat pengajuan...</div>
        <div v-else-if="!submissions.length" class="empty-state">Belum ada pengajuan infaq atau sedekah.</div>
        <div v-else class="table-wrap">
          <table>
            <thead><tr><th>Nama</th><th>Jenis</th><th>Nominal</th><th>Kontak</th><th>Status</th><th>Tanggal</th></tr></thead>
            <tbody>
              <tr v-for="item in submissions" :key="item.id">
                <td><strong>{{ item.nama }}</strong><small v-if="item.catatan">{{ item.catatan }}</small></td>
                <td class="capitalize">{{ item.jenis }}</td>
                <td>{{ formatCurrency(item.jumlah) }}</td>
                <td><a :href="`tel:${item.telepon}`">{{ item.telepon }}</a><small v-if="item.email">{{ item.email }}</small></td>
                <td><span class="status-pill">{{ formatStatus(item.status) }}</span></td>
                <td>{{ formatDate(item.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="pagination.last_page > 1" class="pagination-bar">
          <button type="button" :disabled="pagination.current_page <= 1 || loading" @click="loadSubmissions(pagination.current_page - 1)">Sebelumnya</button>
          <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button type="button" :disabled="pagination.current_page >= pagination.last_page || loading" @click="loadSubmissions(pagination.current_page + 1)">Berikutnya</button>
        </div>
      </section>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut, RefreshCw } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import infaqService from '@/services/infaqService'

const auth = useAuthStore()
const router = useRouter()
const submissions = ref([])
const loading = ref(false)
const errorMessage = ref('')
const summary = ref({ total_pengajuan: 0, total_nominal: 0, menunggu_konfirmasi: 0 })
const pagination = ref({ current_page: 1, last_page: 1 })

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0)
}

function formatDate(value) {
  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value))
}

function formatStatus(value) {
  return value.replaceAll('_', ' ')
}

async function loadSubmissions(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await infaqService.getSubmissions(page)
    submissions.value = response.data || []
    summary.value = response.summary || summary.value
    pagination.value = response.pagination || pagination.value
  } catch {
    errorMessage.value = 'Daftar pengajuan belum dapat dimuat.'
  } finally {
    loading.value = false
  }
}

async function logout() {
  await auth.logout()
  router.push({ name: 'Login' })
}

onMounted(loadSubmissions)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap');
.finance-page { min-height: 100vh; background: #f4f6f1; color: #20342a; font-family: 'DM Sans', sans-serif; }
.finance-header { height: 68px; padding: 0 max(22px, calc((100% - 1120px) / 2)); background: white; border-bottom: 1px solid #e0e6dc; display: flex; align-items: center; justify-content: space-between; }
.finance-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
.finance-brand span { display: grid; place-items: center; width: 34px; height: 34px; color: white; background: #176b4b; border-radius: 50% 50% 50% 6px; font: 800 11px 'Manrope', sans-serif; }
.finance-brand strong { font: 800 13px 'Manrope', sans-serif; }
.logout-button, .refresh-button { display: inline-flex; align-items: center; gap: 8px; color: #42554a; border: 1px solid #d9e2d8; background: white; padding: 9px 12px; cursor: pointer; font: 600 11px 'DM Sans', sans-serif; }
.finance-content { max-width: 1120px; margin: auto; padding: 54px 22px 70px; }
.eyebrow { margin-bottom: 11px; color: #176b4b; font-size: 10px; text-transform: uppercase; font-weight: 800; }
h1 { margin: 0; font: 800 32px/1.2 'Manrope', sans-serif; letter-spacing: 0; }
.intro { color: #718077; font-size: 13px; margin: 12px 0 30px; }
.summary-row { display: grid; grid-template-columns: repeat(3, 1fr); border: 1px solid #dce4da; background: white; }
.summary-row > div { padding: 20px 22px; }
.summary-row > div + div { border-left: 1px solid #e1e7df; }
.summary-row span { display: block; color: #76837a; font-size: 9px; font-weight: 800; }
.summary-row strong { display: block; margin-top: 10px; font: 800 24px 'Manrope', sans-serif; }
.submission-section { margin-top: 36px; }
.section-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 17px; }
.section-head h2 { margin: 0 0 5px; font: 800 19px 'Manrope', sans-serif; letter-spacing: 0; }
.section-head p { color: #76837a; font-size: 11px; margin: 0; }
.refresh-button:disabled { opacity: .6; }
.table-wrap { overflow-x: auto; border: 1px solid #dce4da; background: white; }
table { width: 100%; border-collapse: collapse; text-align: left; min-width: 760px; }
th { background: #edf2ea; color: #637169; text-transform: uppercase; font-size: 9px; padding: 12px 14px; }
td { border-top: 1px solid #edf0eb; padding: 14px; color: #43534a; font-size: 11px; vertical-align: top; }
td strong, td small { display: block; }
td strong { color: #24382d; font-weight: 700; }
td small { color: #849087; margin-top: 5px; max-width: 220px; line-height: 1.5; }
td a { color: #176b4b; text-decoration: none; }
.capitalize { text-transform: capitalize; }
.status-pill { display: inline-block; padding: 5px 8px; background: #fff4dd; color: #96601f; font-size: 9px; text-transform: capitalize; }
.empty-state, .message { padding: 25px; text-align: center; background: white; border: 1px solid #dce4da; color: #718077; font-size: 12px; }
.pagination-bar { display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 13px; color: #718077; font-size: 10px; }
.pagination-bar button { border: 1px solid #d9e2d8; background: white; padding: 8px 10px; color: #42554a; font: 600 10px 'DM Sans', sans-serif; cursor: pointer; }
.pagination-bar button:disabled { opacity: .5; cursor: not-allowed; }
.message.error { color: #a5352b; background: #fff5f3; }
@media (max-width: 620px) { .finance-content { padding: 36px 16px 50px; } h1 { font-size: 27px; } .summary-row { grid-template-columns: 1fr; } .summary-row > div { padding: 16px 18px; } .summary-row > div + div { border-left: 0; border-top: 1px solid #e1e7df; } .summary-row strong { font-size: 21px; } .section-head { align-items: flex-start; } .section-head p { max-width: 220px; line-height: 1.5; } }
</style>