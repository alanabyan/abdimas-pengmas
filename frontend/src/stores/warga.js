import { defineStore } from 'pinia'
import { ref } from 'vue'
import wargaService from '@/services/wargaService'

export const useWargaStore = defineStore('warga', () => {
    const wargas = ref([])
    const currentWarga = ref(null)
    const loading = ref(false)
    const meta = ref({ current_page: 1, last_page: 1, total: 0 })

    async function fetchWargas(params = {}) {
        loading.value = true
        try {
            const res = await wargaService.getAll(params)
            wargas.value = res.data
            meta.value = { current_page: res.current_page, last_page: res.last_page, total: res.total, per_page: res.per_page }
        } finally { loading.value = false }
    }

    async function fetchWarga(id) {
        loading.value = true
        try { currentWarga.value = await wargaService.getOne(id) }
        finally { loading.value = false }
    }

    async function createWarga(payload) {
        const result = await wargaService.create(payload)
        wargas.value.unshift(result)
        return result
    }

    async function updateWarga(id, payload) {
        const updated = await wargaService.update(id, payload)
        const idx = wargas.value.findIndex(w => w.id === id)
        if (idx !== -1) wargas.value[idx] = updated
        if (currentWarga.value?.id === id) currentWarga.value = updated
        return updated
    }

    async function deleteWarga(id) {
        await wargaService.remove(id)
        wargas.value = wargas.value.filter(w => w.id !== id)
        if (currentWarga.value?.id === id) currentWarga.value = null
    }

    return { wargas, currentWarga, loading, meta, fetchWargas, fetchWarga, createWarga, updateWarga, deleteWarga }
})
