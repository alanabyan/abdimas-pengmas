import api from './api'

export default {
  submit: (payload) => api.post('/infaq', payload).then((response) => response.data),
  getSubmissions: (page = 1) => api.get('/infaq', { params: { page } }).then((response) => response.data),
}