// GET    /warga           → { data:[...], current_page, last_page, total }
// POST   /warga           → { data:{...} }
// GET    /warga/:id       → { data:{...warga, peminjamans:[...]} }
// PUT    /warga/:id       → { data:{...} }
// DELETE /warga/:id       → { message }
// GET    /warga/search?q= → { data:[...] }  max 10, autocomplete
// GET    /warga/:id/riwayat → paginated peminjaman
import api from './api'

export default {
    getAll: (params) => api.get('/warga', { params }).then(r => r.data),
    getOne: (id) => api.get(`/warga/${id}`).then(r => r.data.data),
    create: (payload) => api.post('/warga', payload).then(r => r.data.data),
    update: (id, payload) => api.put(`/warga/${id}`, payload).then(r => r.data.data),
    remove: (id) => api.delete(`/warga/${id}`).then(r => r.data),
    search: (keyword) => api.get('/warga/search', { params: { q: keyword } }).then(r => r.data.data),
    getRiwayat: (id, params) => api.get(`/warga/${id}/riwayat`, { params }).then(r => r.data),
}
