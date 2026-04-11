import { createRouter, createWebHistory } from 'vue-router'
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'

<<<<<<< Updated upstream
=======
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
  },

  {
    path: '/peminjaman/tambah',
    name: 'peminjaman.create',
    component: TambahPinjaman
  }
]

>>>>>>> Stashed changes
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/peminjaman',
      name: 'peminjaman.index',
      component: DaftarPinjaman
    }
    // BAGIAN LOGIN KITA MATIIN DULU SEMENTARA BIAR GAK ERROR
    // ,{
    //   path: '/login',
    //   name: 'login',
    //   component: () => import('../views/auth/LoginPage.vue')
    // }
  ]
})

export default router