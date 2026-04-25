<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-emerald-900">Proses Pengembalian Barang</h1>
      <p class="text-sm text-emerald-700">Daftar warga yang belum mengembalikan barang inventaris.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-emerald-600">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Peminjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Barang & Kategori</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Jumlah</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Batas Kembali</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <tr v-for="item in listData" :key="item.id" class="hover:bg-emerald-50/50 transition">
            <td class="px-6 py-4">
              <div class="font-bold text-gray-900 text-left">{{ item.warga?.nama_warga }}</div>
              <div class="text-[10px] text-gray-500 text-left">📞 {{ item.warga?.no_hp || '-' }}</div>
            </td>

            <td class="px-6 py-4 text-left">
              <div class="text-sm font-bold text-gray-800">{{ item.barang?.nama_barang }}</div>
              <div class="mt-1">
                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                  {{ item.barang?.kategori?.nama || item.barang?.kategori?.nama_kategori || 'Tanpa Kategori' }}
                </span>
              </div>
            </td>

            <td class="px-6 py-4 text-center">
              <span class="text-sm font-black text-gray-700">{{ item.jumlah }}</span>
              <span class="text-[10px] text-gray-400 ml-1 font-bold uppercase">Unit</span>
            </td>

            <td class="px-6 py-4 text-sm text-gray-600 text-left">
              <div class="font-medium">{{ item.tgl_rencana_kembali }}</div>
              <div class="text-[10px] text-gray-400">Pinjam: {{ item.tgl_pinjam }}</div>
            </td>

            <td class="px-6 py-4 text-center">
              <button 
                @click="bukaValidasi(item)"
                class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition transform hover:scale-105 active:scale-95"
              >
                Proses Kembali
              </button>
            </td>
          </tr>

          <tr v-if="listData.length === 0">
            <td colspan="5" class="px-6 py-16 text-center">
              <div class="text-4xl mb-2">✅</div>
              <div class="text-gray-400 italic text-sm font-medium">
                Alhamdulillah, tidak ada tanggungan pinjaman barang saat ini.
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ValidasiKembali 
      v-if="isModalOpen" 
      :dataPinjam="selectedItem" 
      @close="isModalOpen = false" 
      @success="fetchData" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ValidasiKembali from '@/views/pengembalian/ValidasiKembali.vue'

const listData = ref([])
const isModalOpen = ref(false)
const selectedItem = ref(null)

const fetchData = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/v1/pengembalian')
    
    // Auto-detect format Laravel (data.data atau data)
    if (response.data.data) {
      listData.value = response.data.data
    } else {
      listData.value = response.data
    }
    
    console.log("Data Pengembalian sinkron, Wa!", listData.value)
  } catch (error) {
    console.error("Gagal ambil data pengembalian:", error)
  }
}

const bukaValidasi = (item) => {
  selectedItem.value = item
  isModalOpen.value = true
}

onMounted(fetchData)
</script>