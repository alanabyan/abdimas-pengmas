import api from './api'

export default {
  getRegistrations: () => api.get('/qurban').then((response) => response.data),
  createRegistration: (payload) => api.post('/qurban', payload).then((response) => response.data),
  updateSetting: (payload) => api.patch('/qurban/setting', payload).then((response) => response.data),
  updateStatus: (id, payload) => api.patch(`/qurban/${id}/status`, payload).then((response) => response.data),
}
