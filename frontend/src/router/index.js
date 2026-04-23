import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// Import Komponen
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'
import DaftarWarga from '@/views/warga/DaftarWarga.vue'
import TambahWarga from '@/views/warga/TambahWarga.vue'
import EditWarga from '@/views/warga/EditWarga.vue'
import DetailWarga from '@/views/warga/DetailWarga.vue'
import MenuLaporan from '@/views/laporan/MenuLaporan.vue'
import LaporanPeminjaman from '@/views/laporan/LaporanPeminjaman.vue'
import LaporanKerusakan from '@/views/laporan/LaporanKerusakan.vue'
import LaporanStok from '@/views/laporan/LaporanStok.vue'
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

  // --- Group Route Peminjaman  ---
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { 
        path: 'warga',
        name: 'DaftarWarga', 
        component: DaftarWarga, 
        meta: { title: 'Data Warga' }
      },
      {
        path: '/warga/tambah',
        name: 'TambahWarga',
        component: TambahWarga,
        meta: { title: 'Tambah Warga' }
      },
      {
        path: '/warga/:id/edit',
        name: 'EditWarga',
        component: EditWarga,
        meta: { title: 'Edit Warga' }
      },
      {
        path: '/warga/:id',
        name: 'DetailWarga',
        component: DetailWarga,
        meta: { title: 'Detail Warga' }
      },
      {
        path: 'peminjaman',
        name: 'peminjaman.index',
        component: DaftarPinjaman,
        meta: { title: 'Daftar Peminjaman' }
      },
      {
        path: 'peminjaman/tambah',
        name: 'peminjaman.create',
        component: TambahPinjaman,
        meta: { title: 'Tambah Peminjaman' }
      },
      {
        path: 'peminjaman/:id/validasi',
        name: 'peminjaman.validasi',
        component: ValidasiKembali,
        meta: { title: 'Validasi Pengembalian' }
      },

      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('../views/dashboard/Dashboard.vue'), // Cek folder dashboard-nya
        meta: { title: 'Dashboard' }
      },
      {
        path: "/laporan",
        name: 'Rekap Laporan',
        component: MenuLaporan,
        meta: {title: 'Rekap Laporan'}
      },
      {
        path: "/laporan/peminjaman",
        name: 'Laporan Peminjaman',
        component: LaporanPeminjaman,
        meta: {title: 'Laporan Peminjaman'}
      },
      {
        path: "/laporan/kerusakan",
        name: 'Laporan Kerusakan',
        component: LaporanKerusakan,
        meta: {title: 'Laporan Kerusakan'}
      },
      {
        path: "/laporan/stok",
        name: 'Laporan Stok Barang',
        component: LaporanStok,
        meta: {title: 'Laporan Stok Barang'}
      },
    ]
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