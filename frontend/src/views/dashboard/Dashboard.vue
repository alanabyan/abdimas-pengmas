<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-emerald-900">Assalamu'alaikum, Marbot!</h1>
      <p class="text-sm text-emerald-700">Berikut ringkasan inventaris masjid hari ini.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-emerald-500 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Unit</p>
          <span class="p-2 bg-emerald-50 rounded-lg">📦</span>
        </div>
        <p class="text-3xl font-black text-gray-800">{{ stats.total_barang }}</p>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-blue-500 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Dipinjam</p>
          <span class="p-2 bg-blue-50 rounded-lg">🤝</span>
        </div>
        <p class="text-3xl font-black text-gray-800">{{ stats.peminjaman_aktif }}</p>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-red-500 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kondisi Rusak</p>
          <span class="p-2 bg-red-50 rounded-lg">⚠️</span>
        </div>
        <p class="text-3xl font-black text-gray-800">{{ stats.barang_rusak }}</p>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-amber-500 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Stok Tipis</p>
          <span class="p-2 bg-amber-50 rounded-lg">📉</span>
        </div>
        <p class="text-3xl font-black text-gray-800">{{ stats.stok_rendah }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100">
        <h3 class="text-lg font-bold text-emerald-900 mb-4">Tren Peminjaman Barang (Minggu Ini)</h3>
        
        <div class="h-[350px]">
            <Bar 
                v-if="loaded && chartData" 
                :data="chartData" 
                :options="chartOptions" 
                :key="JSON.stringify(chartData)" 
            />
    <div v-else class="flex items-center justify-center h-full">
        <p class="text-gray-500 italic">Memuat grafik...</p>
        </div>
    </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const loaded = ref(false)
const stats = ref({ total_barang: 0, peminjaman_aktif: 0, barang_rusak: 0, stok_rendah: 0 })
const chartData = ref(null)

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true }
  }
}

const fetchData = async () => {
  try {
    // 1. Tarik statistik (Kartu)
    const resStats = await axios.get('http://localhost:8000/api/v1/dashboard/statistik')
    console.log('Cek Stats:', resStats.data) // Tambahin ini buat ngecek di console F12
    stats.value = resStats.data.data

    // 2. Tarik grafik
    const resGrafik = await axios.get('http://localhost:8000/api/v1/dashboard/grafik')
    console.log('Cek Grafik:', resGrafik.data) // Tambahin ini juga
    
    if (resGrafik.data.data) {
        chartData.value = resGrafik.data.data
        loaded.value = true // PENTING: loaded dipasang TERAKHIR setelah data masuk
    }
  } catch (error) {
    console.error("Gagal muat data dashboard:", error)
  }
}

onMounted(fetchData)
</script>