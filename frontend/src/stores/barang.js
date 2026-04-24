import { defineStore } from 'pinia'
import { ref } from 'vue'
import barangService from '@/services/barangService'

export const useBarangStore = defineStore('barang', () => {
    const barangs = ref([])
    const currentBarang = ref(null)
    const loading = ref(false)
    const meta = ref({ current_page: 1, last_page: 1, total: 0 })

    async function fetchBarangs(params = {}) {
        loading.value = true
        try {
            const res = await barangService.getAll(params)
            barangs.value = res.data ?? []
            meta.value = { current_page: res.current_page, last_page: res.last_page, total: res.total, per_page: res.per_page }
        } finally { loading.value = false }
    }

    async function fetchBarang(id) {
        loading.value = true
        try { currentBarang.value = await barangService.getOne(id) }
        finally { loading.value = false }
    }

    async function createBarang(formData) {
        const result = await barangService.create(formData)
        barangs.value.unshift(result)
        return result
    }

    async function updateBarang(id, formData) {
        const updated = await barangService.update(id, formData)
        const idx = barangs.value.findIndex(b => b.id === id)
        if (idx !== -1) barangs.value[idx] = updated
        if (currentBarang.value?.id === id) currentBarang.value = updated
        return updated
    }

    async function deleteBarang(id) {
        await barangService.remove(id)
        barangs.value = barangs.value.filter(b => b.id !== id)
        if (currentBarang.value?.id === id) currentBarang.value = null
    }

    return { barangs, currentBarang, loading, meta, fetchBarangs, fetchBarang, createBarang, updateBarang, deleteBarang }
})
