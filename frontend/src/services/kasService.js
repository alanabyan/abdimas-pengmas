import api from './api'

export default {
  index: (month = null) => api.get('/kas', { params: month ? { month } : {} }).then((response) => response.data),
  weekly: (week = null) => api.get('/kas/weekly', { params: week ? { week } : {} }).then((response) => response.data),
  store: (payload) => api.post('/kas', payload).then((response) => response.data),
  exportPdf: (month = null) => api.get('/kas/pdf', {
    params: month ? { month } : {},
    responseType: 'blob',
  }).then((response) => response.data),
  exportWeeklyPdf: (week = null) => api.get('/kas/weekly/pdf', {
    params: week ? { week } : {},
    responseType: 'blob',
  }).then((response) => response.data),
}
