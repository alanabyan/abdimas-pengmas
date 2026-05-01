<template>
  <div class="p-6 bg-gray-50 min-h-screen text-left">
    <div class="mb-6 flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold text-emerald-900 font-sans">Laporan Peminjaman</h1>
        <p class="text-emerald-700 text-sm">Data riwayat peminjaman barang inventaris.</p>
      </div>

      <button @click="cetakLaporan"
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition flex items-center gap-2">
        Cetak PDF
      </button>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Kategori</label>
        <select v-model="filter.kategori_id" class="w-full border-gray-200 rounded-lg text-sm focus:ring-emerald-500">
          <option value="">Semua Kategori</option>
          <option v-for="k in daftarKategori" :key="k.id" :value="k.id">
            {{ k.nama_kategori || k.nama }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Mulai</label>
        <input v-model="filter.tgl_mulai" type="date" class="w-full border-gray-200 rounded-lg text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Selesai</label>
        <input v-model="filter.tgl_selesai" type="date" class="w-full border-gray-200 rounded-lg text-sm">
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Peminjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Barang & Kategori</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Jumlah</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tgl Pinjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">
          <tr v-for="p in filteredLaporan" :key="p.id" class="hover:bg-gray-50/50">
            <td class="px-6 py-4">
              <div class="text-sm font-bold text-gray-900">{{ p.warga?.nama_warga }}</div>
              <div class="text-[10px] text-gray-400">ID: #{{ p.id }}</div>
            </td>
            <td class="px-6 py-4 text-left">
              <div class="text-sm text-gray-800 font-medium">{{ p.barang?.nama_barang }}</div>
              <div
                class="text-[10px] inline-block px-1.5 py-0.5 bg-emerald-50 text-emerald-600 rounded font-bold uppercase">
                {{ p.barang?.kategori?.nama_kategori || p.barang?.kategori?.nama || 'Tanpa Kategori' }}
              </div>
            </td>
            <td class="px-6 py-4 text-center text-sm font-bold text-gray-700">{{ p.jumlah }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ p.tgl_pinjam }}</td>
            <td class="px-6 py-4">
              <span :class="getStatusStyle(p.status)" class="px-2 py-1 rounded text-[10px] font-black uppercase">
                {{ p.status }}
              </span>
            </td>
          </tr>
          <tr v-if="filteredLaporan.length === 0">
            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic text-sm">
              Tidak ada data peminjaman yang cocok dengan filter.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const listLaporan = ref([])
const daftarKategori = ref([])
const filter = ref({
  kategori_id: '',
  tgl_mulai: '',
  tgl_selesai: ''
})

// Fungsi Filter Dinamis (Frontend)
const filteredLaporan = computed(() => {
  return listLaporan.value.filter(item => {
    const matchKategori = !filter.value.kategori_id || item.barang?.kategori_id == filter.value.kategori_id
    const matchTglMulai = !filter.value.tgl_mulai || item.tgl_pinjam >= filter.value.tgl_mulai
    const matchTglSelesai = !filter.value.tgl_selesai || item.tgl_pinjam <= filter.value.tgl_selesai
    return matchKategori && matchTglMulai && matchTglSelesai
  })
})

const getStatusStyle = (status) => {
  if (status === 'Aktif' || status === 'Pinjam') return 'bg-blue-100 text-blue-700'
  if (status === 'Selesai' || status === 'Kembali') return 'bg-emerald-100 text-emerald-700'
  return 'bg-red-100 text-red-700'
}

const fetchData = async () => {
  try {
    const [resP, resK] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/peminjaman'),
      axios.get('http://127.0.0.1:8000/api/v1/kategori')
    ])
    listLaporan.value = resP.data.data || resP.data || []
    daftarKategori.value = resK.data.data || resK.data || []
  } catch (error) {
    console.error("Gagal load laporan:", error)
  }
}

const cetakLaporan = () => {
  window.print() // Sederhana dulu pakai print browser
}

onMounted(fetchData)
</script>