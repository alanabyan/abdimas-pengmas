import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthLayout from '@/layouts/AuthLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// --- Import Komponen (Peminjaman & Warga) ---
import DaftarPinjaman from '../views/peminjaman/DaftarPinjaman.vue'
import TambahPinjaman from '../views/peminjaman/TambahPinjaman.vue'
import DaftarWarga from '@/views/warga/DaftarWarga.vue'
import TambahWarga from '@/views/warga/TambahWarga.vue'
import EditWarga from '@/views/warga/EditWarga.vue'
import DetailWarga from '@/views/warga/DetailWarga.vue'

// Import Pengaturan
import Pengaturan from '../views/pengaturan/Pengaturan.vue'

// --- Import Komponen (Laporan & Barang) ---
import MenuLaporan from '@/views/laporan/MenuLaporan.vue'
import LaporanPeminjaman from '@/views/laporan/LaporanPeminjaman.vue'
import LaporanKerusakan from '@/views/laporan/LaporanKerusakan.vue'
import LaporanStok from '@/views/laporan/LaporanStok.vue'
import DaftarBarang from '@/views/barang/DaftarBarang.vue'
import TambahBarang from '@/views/barang/TambahBarang.vue'
import EditBarang from '@/views/barang/EditBarang.vue'
import DaftarKategori from '@/views/kategori/DaftarKategori.vue'
import TambahKategori from '@/views/kategori/TambahKategori.vue'

// --- Import Komponen (PENGEMBALIAN) ---
const DaftarPengembalian = () => import('@/views/pengembalian/Index.vue')
const Login = () => import('@/views/auth/LoginPage.vue')

const routes = [
  // --- ROOT REDIRECT ---
  {
    path: '/',
    // Jika user ke "/", sistem akan lempar ke dashboard (yang nanti dicek loginnya)
    redirect: '/dashboard' 
  },

  // --- Group Route Login (GUEST ONLY) ---
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

  // --- Group Route Utama (STRICT LOGIN) ---
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true }, // Semua anak (children) wajib login
    children: [
      // Dashboard
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/dashboard/Dashboard.vue'),
        meta: { title: 'Dashboard' }
      },

      // Warga
      { path: 'warga', name: 'DaftarWarga', component: DaftarWarga, meta: { title: 'Data Warga' } },
      { path: 'warga/tambah', name: 'TambahWarga', component: TambahWarga, meta: { title: 'Tambah Warga' } },
      { path: 'warga/:id/edit', name: 'EditWarga', component: EditWarga, meta: { title: 'Edit Warga' } },
      { path: 'warga/:id', name: 'DetailWarga', component: DetailWarga, meta: { title: 'Detail Warga' } },

      // Peminjaman
      { path: 'peminjaman', name: 'peminjaman.index', component: DaftarPinjaman, meta: { title: 'Daftar Peminjaman' } },
      { path: 'peminjaman/tambah', name: 'peminjaman.create', component: TambahPinjaman, meta: { title: 'Tambah Peminjaman' } },
      
      // Pengembalian
      { path: 'pengembalian', name: 'pengembalian.index', component: DaftarPengembalian, meta: { title: 'Daftar Pengembalian' } },

      // Notifikasi & Pengaturan
      { path: 'notifikasi', name: 'Notifikasi', component: () => import('@/views/Notifikasi.vue'), meta: { title: 'Notifikasi' } },
      { path: 'pengaturan', name: 'pengaturan', component: Pengaturan, meta: { title: 'Pengaturan Akun' } },

      // Laporan
      { path: 'laporan', name: 'Rekap Laporan', component: MenuLaporan, meta: { title: 'Rekap Laporan' } },
      { path: 'laporan/peminjaman', name: 'Laporan Peminjaman', component: LaporanPeminjaman, meta: { title: 'Laporan Peminjaman' } },
      { path: 'laporan/kerusakan', name: 'Laporan Kerusakan', component: LaporanKerusakan, meta: { title: 'Laporan Kerusakan' } },
      { path: 'laporan/stok', name: 'Laporan Stok Barang', component: LaporanStok, meta: { title: 'Laporan Stok Barang' } },

      // Barang & Kategori
      { path: 'barang', name: 'Daftar Barang', component: DaftarBarang, meta: { title: 'Daftar Barang' } },
      { path: 'barang/tambah', name: 'Tambah Barang', component: TambahBarang, meta: { title: 'Tambah Barang' } },
      { path: 'barang/:id/edit', name: 'Edit Barang', component: EditBarang, meta: { title: 'Edit Barang' } },
      { path: 'kategori', name: 'Daftar Kategori', component: DaftarKategori, meta: { title: 'Daftar Kategori' } },
      { path: 'kategori/tambah', name: 'Tambah Kategori', component: TambahKategori, meta: { title: 'Tambah Kategori' } },
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

// --- Middleware Penjaga Pintu (SECURITY GUARD) ---
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  
  // Tunggu store inisialisasi token dari localStorage
  if (!auth.isInitialized) await auth.initialize()

  // 1. Jika halaman butuh login tapi user BELUM login
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({ name: 'Login' })
  }

  // 2. Jika user SUDAH login tapi mau akses halaman login lagi
  if (to.meta.guest && auth.isLoggedIn) {
    return next({ name: 'Dashboard' })
  }

  // Set judul tab browser secara dinamis
  document.title = to.meta.title ? `${to.meta.title} — SIMBA` : 'SIMBA'
  next()
})

export default router