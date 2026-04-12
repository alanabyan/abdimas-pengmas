import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'

// 1. Pastiin ini di-import (Cek path-nya bener ga di folder views/peminjaman)
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'

const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  { path: '/', redirect: '/dashboard' },

  // --- Ini Route punya Alan (Auth) ---
  {
    path: '/',
    component: AuthLayout,
    children: [
      { path: 'login', name: 'Login', component: Login, meta: { guest: true, title: 'Login' } },
    ],
  },

  // --- Ini Route punya Lu (Peminjaman) ---
  {
    path: '/peminjaman',
    name: 'peminjaman.index',
    component: DaftarPinjaman,
    meta: { requiresAuth: true, title: 'Daftar Peminjaman' }
  },
  
  // 2. TAMBAHIN INI BIAR HALAMAN TAMBAH MUNCUL
  {
    path: '/peminjaman/tambah',
    name: 'peminjaman.create',
    component: TambahPinjaman,
    meta: { requiresAuth: true, title: 'Tambah Peminjaman' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, saved) {
    return saved ?? { top: 0 }
  },
})

// --- Middleware/Penjaga Pintu punya Alan ---
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  if (!auth.isInitialized) await auth.initialize()

  // Jika butuh login tapi belum login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }
  
  // Jika sudah login tapi mau ke halaman guest (seperti login page)
  if (to.meta.guest && auth.isLoggedIn) {
    // Pastiin rute 'Dashboard' lu emang ada ya, kalau nggak ada ganti ke '/'
    return next({ path: '/peminjaman' }) 
  }

  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router