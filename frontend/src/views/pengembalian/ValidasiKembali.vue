<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md border border-emerald-100 overflow-hidden">
      <div class="bg-emerald-600 p-4">
        <h2 class="text-xl font-bold text-white text-center">Validasi Pengembalian Barang</h2>
      </div>

      <form @submit.prevent="submitValidasi" class="p-6 space-y-4">
        <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100 mb-6">
          <p class="text-sm text-emerald-800 font-semibold text-center uppercase tracking-wider">Detail Transaksi</p>
          <div class="grid grid-cols-2 gap-2 mt-2 text-sm text-gray-700">
            <span class="font-medium">Barang:</span> <span>{{ detailPeminjaman.barang?.nama_barang }}</span>
            <span class="font-medium">Jumlah:</span> <span>{{ detailPeminjaman.jumlah }} Unit</span>
            <span class="font-medium">Peminjam:</span> <span>{{ detailPeminjaman.warga?.nama_warga }}</span>
          </div>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">Kondisi Barang Saat Kembali</label>
          <select 
            v-model="form.kondisi_kembali" 
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
            required
          >
            <option value="Baik">Baik (Stok kembali utuh)</option>
            <option value="Rusak">Rusak (Stok kembali, butuh servis)</option>
            <option value="Hilang">Hilang (Stok TIDAK bertambah)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1">
            Catatan Tambahan 
            <span v-if="form.kondisi_kembali !== 'Baik'" class="text-red-500">*</span>
          </label>
          <textarea 
            v-model="form.catatan" 
            rows="3" 
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"
            :placeholder="form.kondisi_kembali === 'Baik' ? 'Catatan opsional...' : 'Jelaskan kronologi kerusakan/kehilangan secara detail'"
            :required="form.kondisi_kembali !== 'Baik'"
          ></textarea>
        </div>

        <div class="flex gap-3 pt-4">
          <button 
            type="button" 
            @click="$router.push('/peminjaman')"
            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-lg transition"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg shadow-lg transition"
          >
            Simpan Validasi
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const detailPeminjaman = ref({})
const form = ref({
  kondisi_kembali: 'Baik',
  catatan: ''
})

// Load data peminjaman buat info di atas
const fetchDetail = async () => {
  try {
    const response = await axios.get(`http://localhost:8000/api/v1/peminjaman/${route.params.id}`)
    detailPeminjaman.value = response.data.data
  } catch (error) {
    alert('Data peminjaman tidak ditemukan!')
    router.push('/peminjaman')
  }
}

const submitValidasi = async () => {
  try {
    const response = await axios.post(`http://localhost:8000/api/v1/pengembalian/${route.params.id}/validasi`, form.value)
    alert(response.data.message)
    router.push('/peminjaman')
  } catch (error) {
    alert('Gagal memvalidasi pengembalian!')
  }
}

onMounted(fetchDetail)
</script>