<template>
  <div class="p-6 bg-gray-50 min-h-screen text-left font-sans">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-emerald-900 uppercase">Pengaturan Akun</h1>
      <p class="text-emerald-700 text-sm">Kelola informasi profil dan keamanan akun Marbot[cite: 163].</p>
    </div>

    <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="bg-emerald-600 p-8 text-white">
        <h2 class="text-xl font-bold">{{ form.nama_marbot || 'Profil Marbot' }} </h2>
        <p class="text-emerald-100 text-xs italic">Administrator SIMBA v2.0</p>
      </div>

      <div class="p-8">
        <form @submit.prevent="handleUpdate">
          <div class="mb-6">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Nama Lengkap </label>
            <input 
              v-model="form.nama_marbot" 
              type="text" 
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 outline-none"
              required
            />
          </div>

          <div class="mb-6">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Email </label>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-400 outline-none cursor-not-allowed"
              readonly
            />
          </div>

          <div class="mb-6">
            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Password Baru </label>
            <div class="relative">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                v-model="form.password" 
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 outline-none"
                placeholder="Kosongkan jika tidak ganti"
              />
              <button 
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-600 focus:outline-none"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 014.13-4.403m2.623-1.074A10.012 10.012 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 4.403m-5.418-5.418a3 3 0 114.243 4.243m-4.243-4.243L3 3l3.59 3.59m9.41 9.41L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <div class="mt-8">
            <button 
              type="submit" 
              :disabled="loading"
              class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-emerald-700 transition disabled:opacity-50"
            >
              {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(false)
const showPassword = ref(false)
const form = ref({
  nama_marbot: '',
  email: '',
  password: ''
})

const fetchProfile = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/v1/marbot/1') // Sesuai Dokumen [cite: 202]
    if (res.data && res.data.data) {
      form.value.nama_marbot = res.data.data.nama_marbot
      form.value.email = res.data.data.email
    }
  } catch (error) {
    console.error("Gagal load profil:", error)
  }
}

const handleUpdate = async () => {
  loading.value = true
  try {
    const payload = {
      nama_marbot: form.value.nama_marbot,
      email: form.value.email
    }
    
    // Hanya kirim password jika user mengetik sesuatu
    if (form.value.password) {
      payload.password = form.value.password
    }

    await axios.put('http://127.0.0.1:8000/api/v1/marbot/profile/update', payload)
    
    alert("Profil berhasil diupdate!")
    form.value.password = ''
  } catch (error) {
    // Kalau masih error 422, kita bisa liat pesan error dari Laravel di sini
    console.error("Detail Validasi:", error.response?.data?.errors) 
    alert("Gagal update. Cek kembali format inputan!")
  } finally {
    loading.value = false
  }
}

onMounted(fetchProfile)
</script>