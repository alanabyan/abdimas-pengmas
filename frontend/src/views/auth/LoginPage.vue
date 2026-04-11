<template>
    <div class="login-root">
        <div class="bg-grid" aria-hidden="true"></div>
        <div class="login-card">
            <div class="flex justify-center mb-3">
                <div class="logo-box">
                    <svg viewBox="0 0 40 40" fill="none" width="34" height="34">
                        <rect x="12" y="22" width="16" height="12" rx="1" fill="#166534" />
                        <rect x="16" y="16" width="8" height="8" rx="1" fill="#166534" />
                        <path d="M20 8C14 8 12 14 12 16L28 16C28 14 26 8 20 8Z" fill="#16a34a" />
                        <path d="M12 22C8 22 7 18 7 17L12 17Z" fill="#166534" />
                        <path d="M28 22C32 22 33 18 33 17L28 17Z" fill="#166534" />
                        <rect x="6" y="24" width="4" height="10" rx="0.5" fill="#14532d" />
                        <path d="M8 24C6.5 24 6 22 6 21.5L10 21.5C10 22 9.5 24 8 24Z" fill="#16a34a" />
                        <rect x="30" y="24" width="4" height="10" rx="0.5" fill="#14532d" />
                        <path d="M32 24C30.5 24 30 22 30 21.5L34 21.5C34 22 33.5 24 32 24Z" fill="#16a34a" />
                        <path d="M17 34L17 28Q20 25 23 28L23 34Z" fill="#bbf7d0" />
                    </svg>
                </div>
            </div>

            <h1 class="text-center text-[22px] font-extrabold text-masjid-900 tracking-tight mb-1">SIMBA v2.0</h1>
            <p class="text-center text-[10px] font-semibold text-gray-500 tracking-[1.8px] uppercase mb-4">MARBOT ACCESS
                PORTAL</p>

            <div class="h-0.5 bg-gradient-to-r from-transparent via-masjid-500 to-transparent rounded mb-6 opacity-70">
            </div>

            <form @submit.prevent="handleLogin" novalidate>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Username or Email</label>
                    <div class="input-wrap" :class="{ 'ring-red-400 border-red-400': errors.email }">
                        <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 10a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                        <input v-model="form.email" type="text" placeholder="marbot_id / email" autocomplete="username"
                            :disabled="loading" @input="errors.email = ''" />
                    </div>
                    <p v-if="errors.email" class="text-red-500 text-[11px] mt-1 font-medium">{{ errors.email }}</p>
                </div>

                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-gray-700">Password</label>
                        <span
                            class="text-xs font-semibold text-masjid-600 cursor-pointer hover:text-masjid-800">Forgot?</span>
                    </div>
                    <div class="input-wrap" :class="{ 'ring-red-400 border-red-400': errors.password }">
                        <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <input v-model="form.password" :type="showPw ? 'text' : 'password'" placeholder="••••••••"
                            autocomplete="current-password" :disabled="loading" @input="errors.password = ''" />
                        <button type="button"
                            class="px-3 text-gray-400 hover:text-gray-600 transition flex items-center"
                            @click="showPw = !showPw" tabindex="-1">
                            <svg v-if="!showPw" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd"
                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg v-else viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path fill-rule="evenodd"
                                    d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"
                                    clip-rule="evenodd" />
                                <path
                                    d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="errors.password" class="text-red-500 text-[11px] mt-1 font-medium">{{ errors.password }}
                    </p>
                </div>

                <div v-if="errors.server"
                    class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-2.5 mb-4 text-red-700 text-xs font-medium">
                    <svg class="w-4 h-4 flex-shrink-0 mt-px" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ errors.server }}
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full py-3 bg-masjid-600 hover:bg-masjid-700 active:scale-[.98] text-white text-sm font-bold rounded-lg transition-all duration-200 shadow-md shadow-masjid-600/30 flex items-center justify-center min-h-[46px]">
                    <span v-if="!loading">Masuk →</span>
                    <span v-else class="flex gap-1 items-center">
                        <span class="dot-pulse"></span><span class="dot-pulse" style="animation-delay:.2s"></span><span
                            class="dot-pulse" style="animation-delay:.4s"></span>
                    </span>
                </button>

                <p class="text-center text-xs text-gray-400 mt-4">
                    Need a new account?
                    <a href="mailto:admin@simba.id" class="text-masjid-600 font-semibold hover:underline">Contact
                        Administrator</a>
                </p>
            </form>
        </div>

        <footer class="relative z-10 flex items-center gap-2 text-[11px] text-gray-400 mt-7">
            <span>Help Center</span>
            <span class="text-gray-300 text-[8px]">•</span>
            <span>Privacy Policy</span>
            <span class="text-gray-300 text-[8px]">•</span>
            <span>Terms of Service</span>
        </footer>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const showPw = ref(false)
const loading = ref(false)
const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '', server: '' })

function validate() {
    errors.email = errors.password = errors.server = ''
    let ok = true
    if (!form.email.trim()) { errors.email = 'Email wajib diisi.'; ok = false }
    if (!form.password) { errors.password = 'Password wajib diisi.'; ok = false }
    return ok
}

async function handleLogin() {
    if (!validate()) return
    loading.value = true
    try {
        await authStore.login(form.email.trim(), form.password)
        router.push(route.query.redirect || '/dashboard')
    } catch (err) {
        const status = err?.response?.status
        if (status === 422 || status === 401) errors.server = 'Email atau password salah.'
        else if (status === 403) errors.server = err?.response?.data?.message || 'Akun dinonaktifkan.'
        else errors.server = 'Gagal terhubung ke server. Coba lagi.'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.login-root {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #eef0f2;
    padding: 24px 16px 72px;
    position: relative;
    font-family: 'DM Sans', 'Segoe UI', sans-serif;
}

.bg-grid {
    position: fixed;
    inset: 0;
    background-image: radial-gradient(circle, #c4c9d1 1px, transparent 1px);
    background-size: 24px 24px;
    opacity: 0.5;
    pointer-events: none;
    z-index: 0;
}

.login-card {
    position: relative;
    z-index: 1;
    background: #fff;
    border-radius: 18px;
    width: 100%;
    max-width: 400px;
    padding: 34px 34px 28px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .04), 0 8px 32px rgba(0, 0, 0, .09), 0 0 0 1px rgba(0, 0, 0, .04);
    animation: cardIn .4s cubic-bezier(.22, 1, .36, 1) both;
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(16px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.logo-box {
    width: 52px;
    height: 52px;
    background: #f0fdf4;
    border: 2px solid #bbf7d0;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(22, 163, 74, .12);
}

.input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #e5e7eb;
    border-radius: 9px;
    background: #fafafa;
    transition: border-color .2s, box-shadow .2s;
    overflow: hidden;
}

.input-wrap:focus-within {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, .1);
    background: #fff;
}

.input-icon {
    width: 16px;
    height: 16px;
    padding: 0 10px 0 12px;
    color: #9ca3af;
    flex-shrink: 0;
    box-sizing: content-box;
}

.input-wrap input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 11px 10px 11px 0;
    font-size: 13.5px;
    color: #111827;
    font-family: inherit;
}

.input-wrap input::placeholder {
    color: #c4c9d1;
}

.input-wrap input:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.dot-pulse {
    display: inline-block;
    width: 7px;
    height: 7px;
    background: rgba(255, 255, 255, .9);
    border-radius: 50%;
    animation: dp 1.2s ease-in-out infinite;
}

@keyframes dp {

    0%,
    80%,
    100% {
        transform: scale(.7);
        opacity: .5;
    }

    40% {
        transform: scale(1);
        opacity: 1;
    }
}

@media (max-width: 440px) {
    .login-card {
        padding: 26px 20px 22px;
        border-radius: 14px;
    }
}
</style>
