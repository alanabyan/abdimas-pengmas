<template>
  <div class="p-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Tambah Peminjaman</h1>
      <router-link to="/peminjaman" class="text-emerald-600 hover:text-emerald-800 font-semibold">
        &larr; Kembali
      </router-link>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
      <form @submit.prevent="simpanData" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Peminjam (Warga)</label>
            <select v-model="form.warga_id" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50">
              <option value="" disabled>-- Pilih Peminjam --</option>
              <option v-for="w in daftarWarga" :key="w.id" :value="w.id">
                {{ w.nama_warga }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Barang</label>
            <select v-model="form.barang_id" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50">
              <option value="" disabled>-- Pilih Barang --</option>
              <option v-for="b in daftarBarang" :key="b.id" :value="b.id">
                {{ b.nama_barang }} (Tersedia: {{ b.stok_tersedia }})
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Pinjam</label>
            <input v-model="form.jumlah" type="number" min="1" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kondisi Barang</label>
            <select v-model="form.kondisi_pinjam" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
              <option value="Baik">Baik</option>
              <option value="Rusak Ringan">Rusak Ringan</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan</label>
          <textarea v-model="form.keperluan" required rows="3"
            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500"
            placeholder="Contoh: Untuk keperluan kerja bakti RT 05"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pinjam</label>
            <input v-model="form.tgl_pinjam" type="date" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rencana Kembali</label>
            <input v-model="form.tgl_rencana_kembali" type="date" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <button type="submit"
            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200">
            Simpan Peminjaman
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// Wadah buat daftar pilihan
const daftarWarga = ref([])
const daftarBarang = ref([])

// Wadah data form
const form = ref({
  warga_id: '',
  barang_id: '',
  jumlah: 1,
  kondisi_pinjam: 'Baik',
  keperluan: '',
  tgl_pinjam: '',
  tgl_rencana_kembali: ''
})

// Ambil data pilihan dari API
const fetchDataPilihan = async () => {
  try {
    const [resWarga, resBarang] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/warga'),
      axios.get('http://127.0.0.1:8000/api/v1/barang')
    ])
    daftarWarga.value = resWarga.data.data || resWarga.data
    daftarBarang.value = resBarang.data.data || resBarang.data
  } catch (error) {
    console.error("Gagal ambil data pilihan:", error)
  }
}

onMounted(() => {
  fetchDataPilihan()
})

const simpanData = async () => {
  try {
    const payload = { ...form.value, marbot_id: 1 }
    await axios.post('http://127.0.0.1:8000/api/v1/peminjaman', payload)
    alert('Alhamdulillah! Berhasil!')
    router.push('/peminjaman')
  } catch (error) {
    alert('Gagal nyimpen data!')
    console.error(error.response?.data)
  }
}
</script>