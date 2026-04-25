<template>
  <div class="p-6 space-y-6 text-left">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-[#064E3B] uppercase tracking-tight">Semua Notifikasi</h1>
        <p class="text-emerald-700 text-xs mt-1">Daftar peringatan keterlambatan dan stok inventaris.</p>
      </div>
      <button 
        @click="handleRefresh" 
        :disabled="loading"
        class="flex items-center gap-2 text-xs bg-white border border-emerald-200 text-emerald-700 px-4 py-2 rounded-xl font-bold hover:bg-emerald-50 transition shadow-sm disabled:opacity-50"
      >
        <span v-if="loading">Memuat...</span>
        <span v-else>🔄 Refresh Data</span>
      </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="notifikasiStore.notifikasis.length > 0" class="divide-y divide-gray-50">
        <div 
          v-for="n in notifikasiStore.notifikasis" 
          :key="n.id" 
          class="p-6 hover:bg-gray-50 transition flex items-start gap-5"
          :class="{'bg-emerald-50/40': !n.dibaca}"
        >
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-2xl flex-shrink-0 shadow-sm">
            {{ n.peminjaman ? '⏰' : '⚠️' }}
          </div>
          
          <div class="flex-1">
            <div class="flex justify-between items-start mb-1">
              <p class="font-bold text-gray-800 text-sm">
                {{ n.peminjaman ? 'Peringatan Keterlambatan' : 'Peringatan Stok' }} [cite: 159, 289]
              </p>
              <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ n.created_at }}</span>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">{{ n.pesan }} [cite: 159]</p>
            
            <div class="mt-4 flex gap-3">
              <button 
                v-if="!n.dibaca"
                @click="notifikasiStore.tandaiBaca(n.id)"
                class="text-[10px] font-black text-emerald-600 hover:text-emerald-800 uppercase tracking-widest"
              >
                Tandai Sudah Dibaca [cite: 196]
              </button>
              <button 
                @click="notifikasiStore.destroy(n.id)"
                class="text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-widest"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="p-20 text-center">
        <div class="text-5xl mb-4">🍃</div>
        <p class="text-gray-400 text-sm font-medium">Belum ada notifikasi baru hari ini.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotifikasiStore } from '@/stores/notifikasi' // Pastikan path ini benar sesuai folder [cite: 84]

const notifikasiStore = useNotifikasiStore()
const loading = ref(false)

const handleRefresh = async () => {
  loading.value = true
  try {
    // Memanggil action fetch dari Pinia Store [cite: 79]
    await notifikasiStore.fetchNotifikasis()
  } catch (error) {
    console.error("Gagal refresh notifikasi:", error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  handleRefresh()
})
</script>