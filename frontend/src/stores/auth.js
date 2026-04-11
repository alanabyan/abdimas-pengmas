import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(null)
    const isInitialized = ref(false)

    const isLoggedIn = computed(() => !!token.value)

    async function initialize() {
        const savedToken = localStorage.getItem('simba_token')
        const savedUser = localStorage.getItem('simba_user')
        if (savedToken && savedUser) {
            try {
                token.value = savedToken
                user.value = JSON.parse(savedUser)
            } catch (err) {
                console.warn('[auth] Data localStorage korup, membersihkan...', err)
                localStorage.removeItem('simba_token')
                localStorage.removeItem('simba_user')
            }
        }
        isInitialized.value = true
    }

    // Login: lempar error ke atas agar Login.vue bisa handle per status HTTP.
    async function login(email, password) {
        const data = await authService.login(email, password)
        token.value = data.token
        user.value = data.user
        localStorage.setItem('simba_token', data.token)
        localStorage.setItem('simba_user', JSON.stringify(data.user))
        return data
    }

    async function logout() {
        try {
            await authService.logout()
        } catch (err) {
            console.warn('[auth] Logout request gagal, membersihkan state lokal...', err?.response?.status ?? err?.message)
        } finally {
            // Selalu bersihkan, apapun yang terjadi di server
            token.value = null
            user.value = null
            localStorage.removeItem('simba_token')
            localStorage.removeItem('simba_user')
        }
    }
    
    async function refreshUser() {
        try {
            const res = await authService.getMe()
            user.value = res.data
            localStorage.setItem('simba_user', JSON.stringify(res.data))
        } catch (err) {
            // 401 → interceptor di api.js sudah handle (redirect ke login).
            // Selain 401 → log saja, jangan crash, data lama masih bisa dipakai.
            if (err?.response?.status !== 401) {
                console.warn('[auth] Gagal refresh profil dari server:', err?.response?.status ?? err?.message)
            }
        }
    }

    // Update user lokal setelah edit profil berhasil — tanpa request ulang ke server.
    function updateUser(updated) {
        user.value = updated
        localStorage.setItem('simba_user', JSON.stringify(updated))
    }

    return { user, token, isInitialized, isLoggedIn, initialize, login, logout, refreshUser, updateUser }
})