import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'

const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  { path: '/', redirect: '/dashboard' },

  // --- Ini Route punya Alan (Auth) ---
  {
    path: '/',
    component: AuthLayout,
    children: [
      { path: 'login', name: 'Login', component: Login, meta: { guest: true } },
    ],
  },

  // --- Ini Route punya Lu (Peminjaman) ---
  {
    path: '/peminjaman',
    name: 'peminjaman.index',
    component: DaftarPinjaman
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

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }
  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'Dashboard' })
  }

  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router