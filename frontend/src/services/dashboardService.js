// GET /dashboard/statistik          → { data:{...} }
// GET /dashboard/grafik-barang      → { data:[...] }
// GET /dashboard/grafik-peminjaman  → { data:[...] }
import api from './api'

export default {
    getStatistik: () => api.get('/dashboard/statistik').then(r => r.data.data),
    getGrafikBarang: () => api.get('/dashboard/grafik-barang').then(r => r.data.data),
    getGrafikPeminjaman: () => api.get('/dashboard/grafik-peminjaman').then(r => r.data.data),
}
