// GET /laporan/peminjaman     → { data:[...], total }
// GET /laporan/peminjaman/pdf → blob PDF
// GET /laporan/kerusakan      → { data:[...], total }
// GET /laporan/kerusakan/pdf  → blob PDF
// GET /laporan/stok           → { data:[...], grand_total:{...} }
import api from './api'

export default {
    getPeminjaman: (params) =>
        api.get('/laporan/peminjaman', { params }).then(r => r.data),

    getKerusakan: (params) =>
        api.get('/laporan/kerusakan', { params }).then(r => r.data),

    getStok: () =>
        api.get('/laporan/stok').then(r => r.data),

    downloadPeminjamanPdf: (params) =>
        api.get('/laporan/peminjaman/pdf', { params, responseType: 'blob' }).then(r => r.data),

    downloadKerusakanPdf: (params) =>
        api.get('/laporan/kerusakan/pdf', { params, responseType: 'blob' }).then(r => r.data),
}
