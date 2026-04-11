import { createRouter, createWebHistory } from 'vue-router'
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'

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