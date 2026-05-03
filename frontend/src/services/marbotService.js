// GET    /marbot                    → { data:[...] }
// POST   /marbot                    → { data:{...} }
// GET    /marbot/:id                → { data:{...} }
// PUT    /marbot/:id                → { data:{...} }
// DELETE /marbot/:id                → { message }
// POST   /marbot/:id/reset-password → { message }
import api from './api'

export default {
    getAll: () => api.get('/marbot').then(r => r.data),
    getOne: (id) => api.get(`/marbot/${id}`).then(r => r.data.data),
    create: (payload) => api.post('/marbot', payload).then(r => r.data.data),
    update: (id, payload) => api.put(`/marbot/${id}`, payload).then(r => r.data.data),
    remove: (id) => api.delete(`/marbot/${id}`).then(r => r.data),
    resetPassword: (id, payload) => api.post(`/marbot/${id}/reset-password`, payload).then(r => r.data),
}
