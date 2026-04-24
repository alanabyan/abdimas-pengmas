import { defineStore } from 'pinia'
import { ref } from 'vue'
import kategoriService from '@/services/kategoriService'

export const useKategoriStore = defineStore('kategori', () => {
    const kategoris = ref([])
    const currentKategori = ref(null)
    const loading = ref(false)

    async function fetchKategoris() {
        loading.value = true
        try {
            const res = await kategoriService.getAll()
            kategoris.value = res.data ?? res
        } finally { loading.value = false }
    }

    async function fetchKategori(id) {
        loading.value = true
        try { currentKategori.value = await kategoriService.getOne(id) }
        finally { loading.value = false }
    }

    async function createKategori(payload) {
        const result = await kategoriService.create(payload)
        kategoris.value.push(result)
        return result
    }

    async function updateKategori(id, payload) {
        const updated = await kategoriService.update(id, payload)
        const idx = kategoris.value.findIndex(k => k.id === id)
        if (idx !== -1) kategoris.value[idx] = updated
        if (currentKategori.value?.id === id) currentKategori.value = updated
        return updated
    }

    async function deleteKategori(id) {
        await kategoriService.remove(id)
        kategoris.value = kategoris.value.filter(k => k.id !== id)
    }

    return { kategoris, currentKategori, loading, fetchKategoris, fetchKategori, createKategori, updateKategori, deleteKategori }
})
