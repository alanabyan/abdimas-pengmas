// GET    /barang                  → { data:[...], current_page, last_page, total }
// POST   /barang                  → { data:{...} }  multipart/form-data
// GET    /barang/:id              → { data:{...} }
// PUT    /barang/:id              → via POST + ?_method=PUT  (agar bisa upload file)
// DELETE /barang/:id              → { message }
// GET    /barang/:id/ketersediaan → { data:{...} }
// POST   /barang/:id/foto         → { foto_url }
// DELETE /barang/:id/foto         → { message }
import api from './api'

export default {
    getAll: (params) =>
        api.get('/barang', { params }).then(r => r.data),

    getOne: (id) =>
        api.get(`/barang/${id}`).then(r => r.data.data),

    create: (formData) =>
        api.post('/barang', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(r => r.data.data),

    update: (id, formData) =>
        api.post(`/barang/${id}?_method=PUT`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(r => r.data.data),

    remove: (id) =>
        api.delete(`/barang/${id}`).then(r => r.data),

    cekKetersediaan: (id) =>
        api.get(`/barang/${id}/ketersediaan`).then(r => r.data.data),

    uploadFoto: (id, formData) =>
        api.post(`/barang/${id}/foto`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(r => r.data),

    hapusFoto: (id) =>
        api.delete(`/barang/${id}/foto`).then(r => r.data),
}
