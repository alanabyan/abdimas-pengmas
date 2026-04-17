<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-emerald-900">Daftar Peminjaman Barang</h1>
        <p class="text-sm text-emerald-700">Manajemen inventaris masjid aktif — SIMBA v2.0</p>
      </div>
      <router-link 
        to="/peminjaman/tambah"
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-md transition-all font-medium"
      >
        + Tambah Pinjaman
      </router-link>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-emerald-600">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Peminjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Barang</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase text-center">Jumlah</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Tgl Pinjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Status</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
          <tr v-for="p in listPeminjaman" :key="p.id" class="hover:bg-emerald-50/50 transition">
            <td class="px-6 py-4 font-medium text-gray-900">
              {{ p.warga?.nama_warga || 'User Terhapus' }}
            </td>
            <td class="px-6 py-4">
              {{ p.barang?.nama_barang || 'Barang Terhapus' }}
            </td>
            <td class="px-6 py-4 text-center">{{ p.jumlah }}</td>
            <td class="px-6 py-4">{{ p.tgl_pinjam }}</td>
            <td class="px-6 py-4">
              <span 
                :class="getStatusClass(p.status)"
                class="px-2.5 py-0.5 rounded-full text-xs font-medium border"
              >
                {{ p.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <router-link 
                v-if="['Aktif', 'Terlambat', 'Pinjam'].includes(p.status)"
                :to="`/peminjaman/${p.id}/validasi`"
                class="text-amber-600 hover:text-amber-800 font-semibold mr-3 underline"
              >
                Validasi Kembali
              </router-link>
              
              <button 
                @click="hapusPinjaman(p.id)"
                class="text-red-600 hover:text-red-800 font-semibold underline"
              >
                Hapus
              </button>
            </td>
          </tr>

          <tr v-if="listPeminjaman.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
              Belum ada data peminjaman.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const listPeminjaman = ref([])

/**
 * Styling Badge berdasarkan Enum Dokumen Alan 
 * 'Aktif', 'Selesai', 'Terlambat', 'Batal', 'Rusak/Hilang'
 */
const getStatusClass = (status) => {
  switch (status) {
    case 'Selesai': return 'bg-green-100 text-green-800 border-green-200'
    case 'Aktif': 
    case 'Pinjam': return 'bg-blue-100 text-blue-800 border-blue-200'
    case 'Terlambat': return 'bg-red-100 text-red-800 border-red-200'
    case 'Rusak/Hilang': return 'bg-gray-100 text-gray-800 border-gray-200'
    default: return 'bg-yellow-100 text-yellow-800 border-yellow-200'
  }
}

const getPeminjaman = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/v1/peminjaman')
    listPeminjaman.value = response.data.data || response.data
  } catch (error) {
    console.error("Gagal narik data:", error)
  }
}

const hapusPinjaman = async (id) => {
  // Sesuai alur v2.0: Batalkan peminjaman (stok dikembalikan) [cite: 186]
  if (!confirm('Yakin mau hapus? Stok bakal balik otomatis (jika status Aktif/Pinjam).')) return
  
  try {
    const response = await axios.delete(`http://localhost:8000/api/v1/peminjaman/${id}`)
    alert(response.data.message || 'Data berhasil dihapus!')
    getPeminjaman() 
  } catch (error) {
    alert('Gagal menghapus data!')
  }
}

onMounted(() => {
  getPeminjaman()
})
</script>