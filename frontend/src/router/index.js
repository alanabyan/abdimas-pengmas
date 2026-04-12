import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'

// Import Komponen Lu
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'

const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  { 
    path: '/', 
    redirect: '/peminjaman' // Redirect ke peminjaman aja biar langsung kelihatan
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

  // --- Group Route Peminjaman (Punya Lu) ---
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

  // 1. Kalau butuh login tapi belum login, lempar ke Login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  // 2. Kalau sudah login tapi mau buka halaman Login lagi, lempar ke Peminjaman
  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'peminjaman.index' })
  }

  // Set Title Halaman
  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router