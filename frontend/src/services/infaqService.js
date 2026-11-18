import api from './api'

export default {
  submit: (payload) => api.post('/infaq', payload).then((response) => response.data),
  uploadReceipt: (token, file) => {
    const formData = new FormData()
    formData.append('bukti', file)
    return api.post(`/infaq/${token}/receipt`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then((response) => response.data)
  },
  getSubmissions: (page = 1) => api.get('/infaq', { params: { page } }).then((response) => response.data),
  downloadReceipt: (id) => api.get(`/infaq/${id}/receipt`, { responseType: 'blob' }).then((response) => response.data),
  updateStatus: (id, payload) => api.patch(`/infaq/${id}/status`, payload).then((response) => response.data),
}