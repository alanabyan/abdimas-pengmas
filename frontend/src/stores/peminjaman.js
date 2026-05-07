import { defineStore } from 'pinia'
import { ref } from 'vue'
import peminjamanService from '@/services/peminjamanService'

export const usePeminjamanStore = defineStore('peminjaman', () => {
    const peminjamans = ref([])
    const allPeminjamans = ref([])
    const pengembalians = ref([])
    const currentPeminjaman = ref(null)
    const loading = ref(false)
    const meta = ref({ current_page: 1, last_page: 1, total: 0 })

    async function fetchPeminjamans(params = {}) {
        loading.value = true
        try {
            const res = await peminjamanService.getAll(params)
            peminjamans.value = res.data
            meta.value = { current_page: res.current_page, last_page: res.last_page, total: res.total, per_page: res.per_page }
        } finally { loading.value = false }
    }

    async function fetchAllPeminjamans() {
        try {
            const res = await peminjamanService.getAll({per_page: 9999})
            allPeminjamans.value = res.data
        } catch {}
    }

    async function fetchPeminjaman(id) {
        loading.value = true
        try { currentPeminjaman.value = await peminjamanService.getOne(id) }
        finally { loading.value = false }
    }

    async function createPeminjaman(payload) {
        const result = await peminjamanService.create(payload)
        peminjamans.value.unshift(result)
        return result
    }

    async function updatePeminjaman(id, payload) {
        const updated = await peminjamanService.update(id, payload)
        const idx = peminjamans.value.findIndex(p => p.id === id)
        if (idx !== -1) peminjamans.value[idx] = updated
        if (currentPeminjaman.value?.id === id) currentPeminjaman.value = updated
        return updated
    }

    async function batalPeminjaman(id) {
        await peminjamanService.batal(id)
        const idx = peminjamans.value.findIndex(p => p.id === id)
        if (idx !== -1) peminjamans.value[idx].status = 'Batal'
    }

    async function konfirmasiPeminjaman(id) {
        const updated = await peminjamanService.konfirmasi(id)
        const idx = peminjamans.value.findIndex(p => p.id === id)
        if (idx !== -1) peminjamans.value[idx] = updated
        if (currentPeminjaman.value?.id === id) currentPeminjaman.value = updated
        return updated
    }

    async function fetchPengembalians(params = {}) {
        loading.value = true
        try {
            const res = await peminjamanService.getPengembalian(params)
            pengembalians.value = res.data
        } finally { loading.value = false }
    }

    async function validasiPengembalian(id, payload) {
        const updated = await peminjamanService.validasiKembali(id, payload)
        pengembalians.value = pengembalians.value.filter(p => p.id !== id)
        const idx = peminjamans.value.findIndex(p => p.id === id)
        if (idx !== -1) peminjamans.value[idx] = updated
        return updated
    }

    return {
        peminjamans, allPeminjamans, pengembalians, currentPeminjaman, loading, meta,
        fetchPeminjamans, fetchAllPeminjamans, fetchPeminjaman, createPeminjaman, updatePeminjaman,
        batalPeminjaman, konfirmasiPeminjaman, fetchPengembalians, validasiPengembalian,
    }
})
