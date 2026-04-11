<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-emerald-900">Daftar Peminjaman Barang</h1>
        <p class="text-sm text-emerald-700">Manajemen inventaris masjid aktif</p>
      </div>
      <button 
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-md transition-all font-medium"
      >
        + Tambah Pinjaman
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-emerald-600">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Peminjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Barang</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Tgl Pinjam</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase">Status</th>
            <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
          <tr class="hover:bg-emerald-50/50 transition">
            <td class="px-6 py-4 font-medium text-gray-900">Budi Santoso</td>
            <td class="px-6 py-4">Tenda Dome (2 unit)</td>
            <td class="px-6 py-4">11 April 2026</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                Sedang Dipinjam
              </span>
            </td>
            <td class="px-6 py-4 text-center">
              <button class="text-emerald-600 hover:text-emerald-800 font-semibold mr-3 underline">Detail</button>
              <button class="text-red-600 hover:text-red-800 font-semibold underline">Hapus</button>
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

// 1. Definisikan variabel reaktif buat nampung data
const listPeminjaman = ref([])

// 2. Fungsi buat ambil data dari Backend Alan
const getPeminjaman = async () => {
  try {
    // Sesuaikan port Laravel lu (biasanya 8000)
    const response = await axios.get('http://localhost:8000/api/v1/peminjaman')
    // Simpan hasil datanya (cek structure API Alan, biasanya response.data.data)
    listPeminjaman.value = response.data.data
  } catch (error) {
    console.error("Waduh, gagal narik data:", error)
    alert("Gagal koneksi ke API Backend. Pastikan Laravel sudah jalan!")
  }
}

// 3. Jalankan fungsi pas halaman dibuka
onMounted(() => {
  getPeminjaman()
})
</script>