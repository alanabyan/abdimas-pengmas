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
const showNotif = ref(false) 
const showProfileMenu = ref(false) // State untuk dropdown profil [cite: 163]

const unreadCount = computed(() => notifikasiStore.unreadCount)

const initials = computed(() => {
    // Sesuai tabel marbots kolom nama 
    const nama = authStore.user?.nama_marbot || 'Marbot'
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

const bacaNotif = async (id) => {
    try {
        await notifikasiStore.tandaiBaca(id)
    } catch (error) {
        console.error("Gagal update status baca:", error)
    }
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

            <header class="h-14 bg-white border-b flex items-center px-5 gap-2 relative text-left">

                <button class="md:hidden p-2 rounded hover:bg-gray-100" @click="mobileOpen = true">
                    <Menu class="w-5 h-5" />
                </button>

                <div class="flex-1" />

                <div class="relative">
                    <button 
                        @click="showNotif = !showNotif; showProfileMenu = false"
                        class="relative w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all"
                    >
                        <Bell class="w-5 h-5 text-slate-600" />
                        <span v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full border-2 border-white font-bold">
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </button>

                    <div v-if="showNotif" 
                        class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden"
                    >
                        <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-[#064E3B] text-xs uppercase tracking-widest">Notifikasi</h3>
                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">
                                {{ unreadCount }} Baru
                            </span>
                        </div>
                        
                        <div class="max-h-80 overflow-y-auto">
                            <div v-for="n in notifikasiStore.notifikasis" :key="n.id" 
                                class="p-4 hover:bg-emerald-50 transition cursor-pointer border-b border-gray-50 last:border-0"
                                @click="bacaNotif(n.id)"
                            >
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                        <span v-if="n.peminjaman">⏰</span>
                                        <span v-else>⚠️</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[11px] text-gray-800 leading-relaxed font-medium">{{ n.pesan }}</p>
                                        <p class="text-[9px] text-gray-400 mt-1 uppercase">Barusan</p>
                                    </div>
                                    <div v-if="!n.dibaca" class="w-2 h-2 bg-emerald-500 rounded-full self-center"></div>
                                </div>
                            </div>

                            <div v-if="notifikasiStore.notifikasis.length === 0" class="p-10 text-center text-gray-400 text-[11px] italic">
                                Belum ada pemberitahuan.
                            </div>
                        </div>

                        <RouterLink to="/notifikasi" @click="showNotif = false" class="block p-3 text-center text-[10px] font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition uppercase tracking-widest">
                            Lihat Semua
                        </RouterLink>
                    </div>
                </div>

                <div class="relative">
                    <button 
                        @click="showProfileMenu = !showProfileMenu; showNotif = false"
                        class="w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold shadow-sm hover:bg-green-700 transition-all border-2 border-white ring-1 ring-gray-100"
                    >
                        {{ initials }}
                    </button>

                    <div v-if="showProfileMenu" 
                        class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden"
                    >
                        <div class="p-4 border-b border-gray-50 bg-gray-50/30">
                            <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Akun Marbot</p>
                            <p class="text-xs font-bold text-[#064E3B] truncate">{{ authStore.user?.nama_marbot }}</p>
                            <p class="text-[10px] text-gray-500 truncate">{{ authStore.user?.email }}</p>
                        </div>
                        
                        <div class="p-2">
                            <RouterLink 
                                to="/pengaturan" 
                                @click="showProfileMenu = false"
                                class="flex items-center gap-3 w-full p-2.5 text-left text-xs text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition"
                            >
                                <Settings class="w-4 h-4" /> Pengaturan Profil
                            </RouterLink>
                            
                            <button 
                                @click="handleLogout"
                                class="flex items-center gap-3 w-full p-2.5 text-left text-xs text-red-500 hover:bg-red-50 rounded-xl transition"
                            >
                                <LogOutIcon class="w-4 h-4" /> Keluar Sistem
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <RouterView />
            </main>

        </div>
    </div>

    <div v-if="showNotif || showProfileMenu" @click="showNotif = false; showProfileMenu = false" class="fixed inset-0 z-40"></div>
</template>