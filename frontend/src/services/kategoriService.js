// GET    /kategori      → { data: [...] }
// POST   /kategori      → { data: {...} }
// GET    /kategori/:id  → { data: {...} }
// PUT    /kategori/:id  → { data: {...} }
// DELETE /kategori/:id  → { message }
import api from './api'

export default {
    getAll: () => api.get('/kategori').then(r => r.data),
    getOne: (id) => api.get(`/kategori/${id}`).then(r => r.data.data),
    create: (payload) => api.post('/kategori', payload).then(r => r.data.data),
    update: (id, payload) => api.put(`/kategori/${id}`, payload).then(r => r.data.data),
    remove: (id) => api.delete(`/kategori/${id}`).then(r => r.data),
}
