import { defineStore } from 'pinia'
import { ref } from 'vue'
import marbotService from '@/services/marbotService'

export const useMarbotStore = defineStore('marbot', () => {
  const marbots       = ref([])
  const currentMarbot = ref(null)
  const loading       = ref(false)

  async function fetchMarbots() {
    loading.value = true
    try {
      const res = await marbotService.getAll()
      marbots.value = res.data ?? res
    } finally { loading.value = false }
  }

  async function fetchMarbot(id) {
    loading.value = true
    try { currentMarbot.value = await marbotService.getOne(id) }
    finally { loading.value = false }
  }

  async function createMarbot(payload) {
    const result = await marbotService.create(payload)
    marbots.value.unshift(result)
    return result
  }

  async function updateMarbot(id, payload) {
    const updated = await marbotService.update(id, payload)
    const idx = marbots.value.findIndex(m => m.id === id)
    if (idx !== -1) marbots.value[idx] = updated
    if (currentMarbot.value?.id === id) currentMarbot.value = updated
    return updated
  }

  async function deleteMarbot(id) {
    await marbotService.remove(id)
    marbots.value = marbots.value.filter(m => m.id !== id)
  }

  async function resetPassword(id, payload) {
    return await marbotService.resetPassword(id, payload)
  }

  return { marbots, currentMarbot, loading, fetchMarbots, fetchMarbot, createMarbot, updateMarbot, deleteMarbot, resetPassword }
})
