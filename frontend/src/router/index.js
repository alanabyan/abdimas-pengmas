import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import Layout & Komponen
import AuthLayout from '@/layouts/AuthLayout.vue'
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'

const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  { 
    path: '/', 
    redirect: '/dashboard' 
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

// --- Middleware/Penjaga Pintu (Punya Alan) ---
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  if (!auth.isInitialized) await auth.initialize()

  // Kalau butuh login tapi belum login, lempar ke halaman Login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }
  
  // Kalau mau ke halaman login tapi udah login, lempar ke Dashboard
  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'Dashboard' }) // Pastiin rute 'Dashboard' lu udah ada ya!
  }

  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router