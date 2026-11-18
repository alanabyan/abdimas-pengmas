import api from './api'

export default {
  submit: (payload) => api.post('/infaq', payload).then((response) => response.data),
  getSubmissions: (page = 1) => api.get('/infaq', { params: { page } }).then((response) => response.data),
  updateStatus: (id, payload) => api.patch(`/infaq/${id}/status`, payload).then((response) => response.data),
  getReport: (month = null) => api.get('/laporan/keuangan', { params: month ? { month } : {} }).then((response) => response.data),
  getReportPdf: (month = null) => api.get('/laporan/keuangan/pdf', {
    params: month ? { month } : {},
    responseType: 'blob',
  }).then((response) => response.data),
}