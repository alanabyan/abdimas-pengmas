<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans text-left">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-emerald-900">Assalamu'alaikum, Marbot!</h1>
      <p class="text-emerald-700">Berikut ringkasan inventaris masjid hari ini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-emerald-500 flex justify-between items-center transition hover:shadow-md">
        <div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Unit</div>
          <div class="text-3xl font-black text-gray-800">{{ stats.totalBarang }}</div>
        </div>
        <div class="bg-emerald-50 p-2 rounded-lg text-xl">📦</div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-blue-500 flex justify-between items-center transition hover:shadow-md">
        <div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Dipinjam</div>
          <div class="text-3xl font-black text-gray-800">{{ stats.totalDipinjam }}</div>
        </div>
        <div class="bg-blue-50 p-2 rounded-lg text-xl">🤝</div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-red-500 flex justify-between items-center transition hover:shadow-md">
        <div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Kondisi Rusak</div>
          <div class="text-3xl font-black text-gray-800">{{ stats.totalRusak }}</div>
        </div>
        <div class="bg-red-50 p-2 rounded-lg text-xl">⚠️</div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-orange-500 flex justify-between items-center transition hover:shadow-md">
        <div>
          <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Stok Tipis</div>
          <div class="text-3xl font-black text-gray-800">{{ stats.stokTipis }}</div>
        </div>
        <div class="bg-orange-50 p-2 rounded-lg text-xl">📉</div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm">
        <h2 class="text-lg font-bold text-emerald-900 mb-4">Tren Peminjaman Barang (Minggu Ini)</h2>
        <div class="h-[300px]">
          <canvas id="peminjamanChart"></canvas>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Distribusi per Kategori</h2>
        <div class="space-y-4">
          <div v-for="kat in listKategori" :key="kat.id" class="group">
            <div class="flex justify-between items-center mb-1">
              <span class="text-sm font-bold text-gray-700">{{ kat.nama_kategori || kat.nama }}</span>
              <span class="text-xs font-bold text-emerald-600">{{ kat.barangs_count || 0 }} Unit</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
              <div class="bg-emerald-500 h-2 rounded-full transition-all duration-700" 
                :style="{ width: calculatePercentage(kat.barangs_count) + '%' }"></div>
            </div>
          </div>
          <div v-if="listKategori.length === 0" class="text-center py-10 text-gray-400 italic text-sm">
            Belum ada kategori.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const listKategori = ref([])
const stats = ref({
  totalBarang: 0,
  totalDipinjam: 0,
  totalRusak: 0,
  stokTipis: 0
})

let myChart = null

// Fungsi hitung persen biar gak error pas render progress bar
const calculatePercentage = (count) => {
  if (stats.value.totalBarang === 0 || !count) return 0
  return Math.min((count / stats.value.totalBarang) * 100, 100)
}

const initChart = (chartData) => {
  const ctx = document.getElementById('peminjamanChart')
  if (myChart) myChart.destroy()

  myChart = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { 
          beginAtZero: true, 
          ticks: { stepSize: 1 },
          grid: { color: '#f3f4f6' } 
        },
        x: { grid: { display: false } }
      }
    }
  })
}

const fetchData = async () => {
  try {
    const [resB, resK, resP, resChart] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/v1/barang'),
      axios.get('http://127.0.0.1:8000/api/v1/kategori'),
      axios.get('http://127.0.0.1:8000/api/v1/peminjaman'),
      axios.get('http://127.0.0.1:8000/api/v1/laporan/tren-peminjaman')
    ])

    const unpack = (res) => res.data.data || res.data || []

    const barangs = unpack(resB)
    const kategoris = unpack(resK)
    const pinjams = unpack(resP)

    // Update Stats Card
    stats.value = {
      totalBarang: barangs.length,
      totalDipinjam: pinjams.filter(p => p.status === 'Aktif' || p.status === 'Pinjam' || p.status === 'Terlambat').length,
      totalRusak: barangs.filter(b => b.kondisi === 'Rusak' || b.kondisi === 'rusak').length,
      stokTipis: barangs.filter(b => b.stok_tersedia < 5).length
    }

    // Balikin list kategori
    listKategori.value = kategoris
    
    // Gambar Chart
    if (resChart.data) {
      initChart(resChart.data)
    }

    console.log("Dashboard Suksesss, Wa!")

  } catch (error) {
    console.error("Dashboard error:", error)
  }
}

onMounted(fetchData)
</script>