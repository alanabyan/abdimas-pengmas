import { createRouter, createWebHistory } from 'vue-router'
// Import Komponen
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'
import AuthLayout from '../layouts/AuthLayout.vue' // Pastiin path ini bener ya, Wa!

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
        meta: { guest: true } 
      },
    ],
  },

  // --- Group Route Peminjaman (Punya Lu) ---
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

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  // PANGGIL VARIABEL routes YANG DI ATAS TADI
  routes: routes 
})

export default router