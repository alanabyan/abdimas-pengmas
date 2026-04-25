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
            <label class="block text-sm font-medium text-emerald-700 mb-2">Filter Berdasarkan Kategori</label>
            <select v-model="selectedKategori" @change="onKategoriChange"
              class="w-full border border-emerald-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-emerald-50 font-medium text-left">
              <option value="">-- Semua Kategori --</option>
              <option v-for="k in daftarKategori" :key="k.id" :value="k.id">
                {{ k.nama_kategori || k.nama }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2 text-left">
            <label class="block text-sm font-medium text-gray-700 mb-2">Barang</label>
            <select v-model="form.barang_id" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50 text-left">
              <option value="" disabled>-- Pilih Barang --</option>
              <option v-for="b in daftarBarangTerfilter" :key="b.id" :value="b.id">
                {{ b.nama_barang }} - (Stok: {{ b.stok_tersedia }})
              </option>
            </select>
            <p v-if="daftarBarangTerfilter.length === 0 && selectedKategori" class="text-xs text-red-500 mt-1 italic">
              *Tidak ada barang tersedia di kategori ini.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Jumlah Pinjam</label>
            <input v-model="form.jumlah" type="number" min="1" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 text-left">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Kondisi Barang</label>
            <select v-model="form.kondisi_pinjam" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500">
              <option value="Baik">Baik</option>
              <option value="Rusak Ringan">Rusak Ringan</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Keperluan</label>
          <textarea v-model="form.keperluan" required rows="3"
            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500"
            placeholder="Tuliskan keperluan peminjaman..."></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Tanggal Pinjam</label>
            <input v-model="form.tgl_pinjam" type="date" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 font-sans">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2 text-left">Rencana Kembali</label>
            <input v-model="form.tgl_rencana_kembali" type="date" required
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 font-sans">
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const daftarWarga = ref([])
const daftarBarang = ref([])
const daftarKategori = ref([])
const selectedKategori = ref('')

const form = ref({
  warga_id: '',
  barang_id: '',
  jumlah: 1,
  kondisi_pinjam: 'Baik',
  keperluan: '',
  tgl_pinjam: '',
  tgl_rencana_kembali: ''
})

// Filter Otomatis
const daftarBarangTerfilter = computed(() => {
  if (!selectedKategori.value) return daftarBarang.value
  return daftarBarang.value.filter(b => b.kategori_id == selectedKategori.value)
})

const onKategoriChange = () => {
  form.value.barang_id = ''
}

const fetchDataPilihan = async () => {
  try {
    const [resWarga, resBarang, resKategori] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/warga'),
      axios.get('http://127.0.0.1:8000/api/v1/barang'),
      axios.get('http://127.0.0.1:8000/api/v1/kategori')
    ])
    daftarWarga.value = resWarga.data.data || resWarga.data
    daftarBarang.value = resBarang.data.data || resBarang.data
    daftarKategori.value = resKategori.data.data || resKategori.data
  } catch (error) {
    console.error("Gagal load data API:", error)
  }
}

onMounted(() => {
  fetchDataPilihan()
})

const simpanData = async () => {
  try {
    const payload = { ...form.value, marbot_id: 1 }
    const response = await axios.post('http://127.0.0.1:8000/api/v1/peminjaman', payload)
    alert(response.data.message || 'Data Peminjaman Berhasil Disimpan!')
    router.push('/peminjaman')
  } catch (error) {
    const msg = error.response?.data?.message || 'Gagal menyimpan data.'
    alert(msg)
  }
}
</script>