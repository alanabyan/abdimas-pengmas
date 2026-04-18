<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterView, RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotifikasiStore } from '@/stores/notifikasi'
import NavItem from '@/components/NavItem.vue'

import {
    LayoutDashboard,
    Box,
    Tags,
    Users,
    ArrowRightLeft,
    RotateCcw,
    BarChart3,
    Settings,
    Bell,
    Menu,
    LogOutIcon
} from "lucide-vue-next"

const authStore = useAuthStore()
const notifikasiStore = useNotifikasiStore()
const router = useRouter()

const mobileOpen = ref(false)

const unreadCount = computed(() => notifikasiStore.unreadCount)

const initials = computed(() => {
    const nama = authStore.user?.nama || 'M'
    return nama.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const menus = [
    { label: "Dashboard", to: "/dashboard", icon: LayoutDashboard },
    { label: "Barang", to: "/barang", icon: Box },
    { label: "Kategori", to: "/kategori", icon: Tags },
    { label: "Warga", to: "/warga", icon: Users },
    { label: "Peminjaman", to: "/peminjaman", icon: ArrowRightLeft },
    { label: "Pengembalian", to: "/pengembalian", icon: RotateCcw },
    { label: "Laporan", to: "/laporan", icon: BarChart3 },
    { label: "Pengaturan", to: "/pengaturan", icon: Settings },
]

async function handleLogout() {
    await authStore.logout()
    router.push({ name: 'Login' })
}

onMounted(() => {
    notifikasiStore.fetchNotifikasis().catch(() => { })
})
</script>

<template>
    <div class="flex h-screen bg-[#f1f5f0] overflow-hidden">

        <aside
            class="w-[220px] bg-white flex flex-col fixed md:static z-40 h-full transition-transform duration-300"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

            <div class="flex items-center gap-3 px-4 py-5 ">
                <div>
                    <p class="text-[#064E3B] text-sm font-extrabold">SIMBA v2.0</p>
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider">
                        Sistem Inventaris Masjid
                    </p>
                </div>
            </div>

            <nav class="flex-1 px-2 py-3 space-y-1 overflow-y-auto">
                <NavItem v-for="menu in menus" :key="menu.to" :to="menu.to" :icon="menu.icon" :label="menu.label" />
            </nav>

            <div class="flex items-center border-t-2 border-[#a7ffd9] gap-2 px-4 py-5 ">
                <div
                    class="w-8 h-8 rounded-full bg-green-600 text-white text-xs font-bold flex items-center justify-center">
                    {{ initials }}
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-[#064E3B] truncate">
                        {{ authStore.user?.nama_marbot || 'Marbot' }}
                    </p>
                </div>

                <button @click="handleLogout" class="text-slate-400 transition-all duration-200 ease-linear hover:text-[#064E3B]">
                    <LogOutIcon size="18" />
                </button>
            </div>
        </aside>

        <div v-if="mobileOpen" @click="mobileOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden" />

        <div class="flex-1 flex flex-col">

            <header class="h-14 bg-white border-b flex items-center px-5 gap-2">

                <button class="md:hidden p-2 rounded hover:bg-gray-100" @click="mobileOpen = true">
                    <Menu class="w-5 h-5" />
                </button>

                <div class="flex-1" />

                <RouterLink class="relative w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center">
                    <Bell class="w-5 h-5 text-slate-600" />
                    <span v-if="unreadCount > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1 rounded-full">
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </RouterLink>

                <div
                    class="w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold">
                    {{ initials }}
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <RouterView />
            </main>

        </div>
    </div>
</template>