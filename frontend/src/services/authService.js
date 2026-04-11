// POST /auth/login          → { token, user }
// POST /auth/logout         → { message }
// GET  /auth/me             → { data: {...} }
// PUT  /auth/me             → { data: {...} }
// PUT  /auth/me/password    → { message }
import api from './api'

export default {
    login: (email, password) => api.post('/auth/login', { email, password }).then(r => r.data),
    logout: () => api.post('/auth/logout').then(r => r.data),
    getMe: () => api.get('/auth/me').then(r => r.data),
    updateMe: (payload) => api.put('/auth/me', payload).then(r => r.data),
    ubahPassword: (payload) => api.put('/auth/me/password', payload).then(r => r.data),
}
