import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'

// Import Komponen
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'
// Pastikan path ValidasiKembali ini bener ya, Wa!
const ValidasiKembali = () => import('@/views/pengembalian/ValidasiKembali.vue')
const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  { 
    path: '/', 
    redirect: '/peminjaman' 
  },

  // --- Group Route Login (Punya Alan) ---
  {
    path: '/',
    component: AuthLayout,
    children: [
      { 
        path: 'login', 
        name: 'Login', 
        component: Login, 
        meta: { guest: true, title: 'Login' } 
      },
    ],
  },

  // --- Group Route Peminjaman (Punya Najwa) ---
  {
    path: '/peminjaman',
    name: 'peminjaman.index',
    component: DaftarPinjaman,
    meta: { requiresAuth: true, title: 'Daftar Peminjaman' }
  },
  {
    path: '/peminjaman/tambah',
    name: 'peminjaman.create',
    component: TambahPinjaman,
    meta: { requiresAuth: true, title: 'Tambah Peminjaman' }
  },
  {
    path: '/peminjaman/:id/validasi',
    name: 'peminjaman.validasi',
    component: ValidasiKembali, // Pake variabel yang di-import di atas biar bersih
    meta: { requiresAuth: true, title: 'Validasi Pengembalian' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, saved) {
    return saved ?? { top: 0 }
  },
})

// --- Middleware Penjaga Pintu ---
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  if (!auth.isInitialized) await auth.initialize()

  // 1. Proteksi Halaman yang butuh Login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  // 2. Cegah User yang sudah Login balik ke halaman Guest (Login Page)
  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'peminjaman.index' })
  }

  // Set Title Browser
  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router