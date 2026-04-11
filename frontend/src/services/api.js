import axios from 'axios'
import router from '@/router'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
    withCredentials: true,
    timeout: 20000,
})

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('simba_token')
        if (token) config.headers.Authorization = `Bearer ${token}`
        return config
    },
    (error) => Promise.reject(error)
)

api.interceptors.response.use(
    (res) => res,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('simba_token')
            localStorage.removeItem('simba_user')
            if (router.currentRoute.value?.name !== 'Login') {
                router.push({ name: 'Login' })
            }
        }
        return Promise.reject(error)
    }
)

export default api