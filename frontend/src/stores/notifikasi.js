import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useNotifikasiStore = defineStore('notifikasi', () => {
    const notifikasis = ref([])
    const loading = ref(false)
    const unreadCount = computed(() => notifikasis.value.filter(n => !n.dibaca).length)

    async function fetchNotifikasis() {
        loading.value = true
        try {
            const res = await api.get('/notifikasi')
            notifikasis.value = res.data.data ?? res.data
        } finally { loading.value = false }
    }

    async function tandaiBaca(id) {
        await api.patch(`/notifikasi/${id}/baca`)
        const n = notifikasis.value.find(n => n.id === id)
        if (n) n.dibaca = true
    }

    async function tandaiSemuaBaca() {
        const belum = notifikasis.value.filter(n => !n.dibaca)
        await Promise.all(belum.map(n => api.patch(`/notifikasi/${n.id}/baca`)))
        notifikasis.value.forEach(n => { n.dibaca = true })
    }

    async function hapusNotifikasi(id) {
        await api.delete(`/notifikasi/${id}`)
        notifikasis.value = notifikasis.value.filter(n => n.id !== id)
    }

    return { notifikasis, loading, unreadCount, fetchNotifikasis, tandaiBaca, tandaiSemuaBaca, hapusNotifikasi }
})
