// GET    /peminjaman                   → { data:[...], current_page, ... }
// POST   /peminjaman                   → { data:{...} }
// GET    /peminjaman/:id               → { data:{...} }
// PUT    /peminjaman/:id               → { data:{...} }
// DELETE /peminjaman/:id               → { message }  (batalkan)
// PATCH  /peminjaman/:id/konfirmasi    → { data:{...} }
// GET    /pengembalian                 → { data:[...] }
// POST   /pengembalian/:id/validasi    → { data:{...} }
import api from './api'

export default {
    getAll: (params) => api.get('/peminjaman', { params }).then(r => r.data),
    getOne: (id) => api.get(`/peminjaman/${id}`).then(r => r.data.data),
    create: (payload) => api.post('/peminjaman', payload).then(r => r.data.data),
    update: (id, payload) => api.put(`/peminjaman/${id}`, payload).then(r => r.data.data),
    batal: (id) => api.delete(`/peminjaman/${id}`).then(r => r.data),
    konfirmasi: (id) => api.patch(`/peminjaman/${id}/konfirmasi`).then(r => r.data.data),

    getPengembalian: (params) => api.get('/pengembalian', { params }).then(r => r.data),
    validasiKembali: (id, payload) => api.post(`/pengembalian/${id}/validasi`, payload).then(r => r.data.data),
}
