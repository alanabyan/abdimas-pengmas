<template>
  <div v-if="dataPinjam" class="fixed inset-0 bg-emerald-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
      
      <div class="bg-emerald-700 p-4 text-white flex justify-between items-center font-bold">
        <span>Konfirmasi Barang Kembali</span>
        <button @click="$emit('close')" class="hover:bg-emerald-800 rounded-lg p-1">✕</button>
      </div>

      <form @submit.prevent="submitValidasi" class="p-6 space-y-5 text-left">
        
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-sm space-y-1">
          <p class="text-gray-500">
            Peminjam: <span class="text-emerald-900 font-bold">{{ dataPinjam?.warga?.nama_warga || 'Tidak diketahui' }}</span>
          </p>
          <p class="text-gray-500">
            Item: <span class="text-emerald-900 font-bold">{{ dataPinjam?.barang?.nama_barang || 'Barang Terhapus' }} ({{ dataPinjam?.jumlah }} unit)</span>
          </p>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kondisi Saat Ini</label>
          <select 
            v-model="form.kondisi_kembali" 
            class="w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 py-3 font-medium text-gray-700"
            required
          >
            <option value="Baik">✅ Baik / Lengkap</option>
            <option value="Rusak">⚠️ Rusak</option>
            <option value="Hilang">❌ Hilang</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Catatan Marbot</label>
          <textarea 
            v-model="form.catatan" 
            placeholder="Ada bagian yang lecet atau alasan hilang..."
            class="w-full border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 h-24 p-3"
          ></textarea>
        </div>

        <div class="flex space-x-3">
          <button 
            type="button" 
            @click="$emit('close')"
            class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 font-bold"
          >
            Batal
          </button>
          <button 
            type="submit" 
            :disabled="loading"
            class="flex-1 py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 font-bold shadow-lg disabled:bg-emerald-300"
          >
            {{ loading ? 'Menyimpan...' : 'Simpan Data' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'

// Definisikan props agar Vue tahu dataPinjam itu dikirim dari Index.vue
const props = defineProps({
  dataPinjam: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])
const loading = ref(false)

const form = reactive({
  kondisi_kembali: 'Baik',
  catatan: ''
})

const submitValidasi = async () => {
  if (!props.dataPinjam?.id) return alert('ID Peminjaman tidak ditemukan!')
  
  loading.value = true
  try {
    const url = `http://localhost:8000/api/v1/pengembalian/${props.dataPinjam.id}`
    const response = await axios.post(url, form)
    
    alert(response.data.message)
    emit('success')
    emit('close')
  } catch (error) {
    alert('Aduh Wa, gagal simpan data!')
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>